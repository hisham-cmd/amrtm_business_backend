<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\ChangePasswordRequest;
use App\Http\Requests\Business\SubmitRequestRequest;
use App\Http\Requests\Business\UpdateProfileRequest;
use App\Models\Business\Office;
use App\Models\Business\OfficeMessage;
use App\Models\Business\OfficeService;
use App\Models\Business\Specialty;
use App\Models\BusinessNotification;
use App\Models\Category;
use App\Models\Entity;
use App\Models\GovService;
use App\Models\ServicePayment;
use App\Models\ServiceRequest;
use App\Services\ServiceRequestService;
use App\Support\AttachmentScanner;
use App\Support\ContactDataGuard;
use App\Support\MessageAttachmentStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ServiceCatalogController extends Controller
{
    public function index(): View
    {
        $categories = collect();
        $totalEntities = 0;
        $totalServices = 0;
        $officeCounts = [
            'law'         => 0,
            'services'    => 0,
            'customs'     => 0,
            'accounting'  => 0,
            'engineering' => 0,
            'freelance'   => 0,
            'consultants' => 0,
        ];

        try {
            $categories = Category::with([
                'entities' => fn($q) => $q->where('is_active', true)
                    ->with(['govServices' => fn($sq) => $sq->where('is_active', true)->orderBy('sort_order')])
                    ->orderBy('sort_order'),
            ])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($cat) {
                $entitiesCount = $cat->entities->count();
                $servicesCount = $cat->entities->sum(fn($ent) => $ent->govServices->count());

                return [
                    'id'             => $cat->id,
                    'key'            => $cat->key,
                    'name_ar'        => $cat->name_ar,
                    'name_en'        => $cat->name_en,
                    'icon'           => $cat->icon,
                    'color'          => $cat->color,
                    'bg'             => $cat->bg,
                    'entities_count' => $entitiesCount,
                    'services_count' => $servicesCount,
                    'entities'       => $cat->entities->map(fn($ent) => [
                        'id'             => $ent->id,
                        'name_ar'        => $ent->name_ar,
                        'name_en'        => $ent->name_en,
                        'icon'           => $ent->icon,
                        'color'          => $ent->color,
                        'bg'             => $ent->bg,
                        'tag_ar'         => $ent->tag_ar,
                        'tag_en'         => $ent->tag_en,
                        'services_count' => $ent->govServices->count(),
                        'services'       => $ent->govServices->map(fn($svc) => [
                            'id'             => $svc->id,
                            'name_ar'        => $svc->name_ar,
                            'name_en'        => $svc->name_en,
                            'icon'           => $svc->icon,
                            'price'          => (float) $svc->price,
                            'duration'       => \App\Support\ServiceDuration::format($svc->duration_min, $svc->duration_max, $svc->duration_unit),
                            'duration_min'   => $svc->duration_min,
                            'duration_max'   => $svc->duration_max,
                            'duration_unit'  => $svc->duration_unit,
                        ]),
                    ]),
                ];
            });

            $totalEntities = $categories->sum('entities_count');
            $totalServices = $categories->sum('services_count');

            $dbOfficeCounts = Office::where('is_active', true)
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();

            $officeCounts = array_merge($officeCounts, $dbOfficeCounts);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Categories DB fetch skipped (offline/fallback mode): ' . $e->getMessage());
            $categories = collect();
        }

        try {
            $officeCounts['consultants'] = Office::consultants()
                ->where('is_active', true)
                ->where('is_verified', true)
                ->visibleInDirectory()
                ->count();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Consultants count fetch skipped: ' . $e->getMessage());
            $officeCounts['consultants'] = 0;
        }

        try {
            $homepageSettings = \App\Models\HomepageSetting::query()->pluck('value', 'key');
            $homepageSlides   = \App\Models\HomepageSlide::active()->get()->map(fn ($s) => [
                'id'         => $s->id,
                'title'      => $s->title,
                'image_url'  => $s->image_url,
                'link_url'   => $s->link_url,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Homepage settings/slides fetch skipped: ' . $e->getMessage());
            $homepageSettings = collect();
            $homepageSlides   = collect();
        }

        $homepageMedia = [
            'video_file'   => $this->resolveMediaUrl($homepageSettings['video_file'] ?? 'videos/0829.mp4'),
            'video_poster' => $this->resolveMediaUrl($homepageSettings['video_poster'] ?? 'images/logo2.jpg'),
        ];

        return view('update_service.index', compact(
            'categories',
            'totalEntities',
            'totalServices',
            'officeCounts',
            'homepageSettings',
            'homepageSlides',
            'homepageMedia'
        ));
    }

    public function userDashboard(): View
    {
        return view('update_service.user_dashboard');
    }

    public function categoryPage(Request $request, string $key): View
    {
        $perPage = max(1, min(48, (int) $request->input('per_page', 12)));

        try {
            $category = Category::where('key', $key)->where('is_active', true)->first();

            if (!$category) {
                $category = new Category([
                    'key' => $key,
                    'name_ar' => $key === 'ministries' ? 'الوزارات' : ($key === 'authorities' ? 'الهيئات والمؤسسات الحكومية' : 'الشركات والجهات الخاصة'),
                    'name_en' => ucfirst($key),
                ]);
                $category->setRelation('entities', collect());
            }

            $entitiesQuery = Entity::where('category_id', $category->id)
                ->where('is_active', true)
                ->with(['govServices' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
                ->orderBy('sort_order');

            $entities = $entitiesQuery->get();
            $category->setRelation('entities', $entities);

            $totalServices = $entities->sum(fn($e) => $e->govServices->count());
            $allServicesCount = $category->entities()->where('is_active', true)
                ->whereHas('govServices', fn($q) => $q->where('is_active', true))
                ->sum(\Illuminate\Support\Facades\DB::raw('(SELECT COUNT(*) FROM bs_services WHERE bs_services.entity_id = bs_entities.id AND bs_services.is_active = 1)'));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('categoryPage DB error: ' . $e->getMessage());
            $category = new Category([
                'key' => $key,
                'name_ar' => $key === 'ministries' ? 'الوزارات' : ($key === 'authorities' ? 'الهيئات والمؤسسات الحكومية' : 'الشركات والجهات الخاصة'),
                'name_en' => ucfirst($key),
            ]);
            $category->setRelation('entities', collect());
            $entities = collect();
            $totalServices = 0;
            $allServicesCount = 0;
        }

        return view('update_service.catalog_category', compact('category', 'entities', 'totalServices', 'allServicesCount'));
    }

    public function entityPage(Request $request, string $key, int $entityId): View
    {
        $perPage = max(1, min(48, (int) $request->input('per_page', 6)));

        try {
            $category = Category::where('key', $key)->where('is_active', true)->first();
            if (!$category) {
                $category = new Category(['key' => $key, 'name_ar' => 'الجهة', 'name_en' => 'Entity']);
            }

            $entity = Entity::where('id', $entityId)
                ->where('is_active', true)
                ->first();

            if (!$entity) {
                $entity = new Entity(['id' => $entityId, 'name_ar' => 'الجهة المطلوبة', 'name_en' => 'Requested Entity']);
                $entity->setRelation('govServices', collect());
                $allServices = collect();
            } else {
                $servicesBase = GovService::where('entity_id', $entity->id)
                    ->where('is_active', true)
                    ->orderBy('sort_order');

                $allServices = $servicesBase->get();
                $services = $servicesBase->paginate($perPage)->withQueryString();
                $entity->setRelation('govServices', $services);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('entityPage DB error: ' . $e->getMessage());
            $category = new Category(['key' => $key, 'name_ar' => 'الجهة', 'name_en' => 'Entity']);
            $entity = new Entity(['id' => $entityId, 'name_ar' => 'الجهة المطلوبة', 'name_en' => 'Requested Entity']);
            $entity->setRelation('govServices', collect());
            $allServices = collect();
        }

        return view('update_service.catalog_entity', compact('category', 'entity', 'allServices'));
    }

    /**
     * دليل المستشارين — واجهة مستقلة عن دليل المكاتب العام.
     * يعرض حسابات "المستشار" (scopeConsultants) فقط، وليس نوع freelance العادي.
     */
    public function consultantsDirectory(): View
    {
        return view('update_service.consultants_directory', $this->consultantsDirectoryData());
    }

    public function consultantsDirectory2(): View
    {
        return view('update_service.consultants_directory2', $this->consultantsDirectoryData());
    }

    private function consultantsDirectoryData(): array
    {
        $consultants = collect();
        $topConsultant = null;
        $totalConsultations = 0;
        $totalVisitors = 0;
        $verifiedCount = 0;

        try {
            $consultants = Office::consultants()
                ->where('is_active', true)
                ->where('is_verified', true)
                ->visibleInDirectory()
                ->with(['specialtiesRelation', 'services'])
                ->withCount([
                    'requests as completed_consultations_count' => fn($q) => $q->where('status', 'done'),
                    'requests as total_requests_count',
                ])
                ->get();

            $totalConsultations = $consultants->sum('total_requests_count');
            $totalVisitors = $consultants->sum('views_count');
            $verifiedCount = $consultants->where('is_verified', true)->count();

            $topConsultant = $consultants
                ->sortByDesc('completed_consultations_count')
                ->first(fn($o) => ($o->completed_consultations_count ?? 0) > 0);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('consultantsDirectory DB error: ' . $e->getMessage());
            $consultants = collect();
        }

        $specOptions = collect();

        try {
            $specOptions = Specialty::where('is_consultant', true)
                ->where('is_active', true)
                ->orderBy('name_ar')
                ->pluck('name_ar');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('consultantsDirectory specOptions DB error: ' . $e->getMessage());
        }

        $specialtyCards = $this->consultantSpecialtyCards();

        return compact(
            'consultants',
            'topConsultant',
            'totalConsultations',
            'totalVisitors',
            'verifiedCount',
            'specOptions',
            'specialtyCards'
        ) + [
            'categories'        => \App\Support\ConsultantCatalog::categories(),
            'businessActivities' => \App\Support\ConsultantCatalog::businessActivities(),
        ];
    }

    /**
     * بطاقات تخصصات المستشارين (الكتالوج الكامل) مع عدد المستشارين المعتمدين لكل تخصص.
     *
     * @return array<int, array{
     *     id: int,
     *     name_ar: string,
     *     name_en: string,
     *     office_type: ?string,
     *     category: ?string,
     *     business_activity: ?string,
     *     consultants_count: int
     * }>
     */
    private function consultantSpecialtyCards(): array
    {
        $cards = [];

        try {
            $officeIds = Office::consultants()
                ->where('is_active', true)
                ->where('is_verified', true)
                ->visibleInDirectory()
                ->pluck('id');

            $counts = collect();
            if ($officeIds->isNotEmpty()) {
                $counts = DB::connection('business')
                    ->table('bs_office_specialties')
                    ->whereIn('office_id', $officeIds)
                    ->select('specialty_id')
                    ->selectRaw('COUNT(*) as c')
                    ->groupBy('specialty_id')
                    ->pluck('c', 'specialty_id');
            }

            $specialties = Specialty::where('is_consultant', true)
                ->where('is_active', true)
                ->orderBy('name_ar')
                ->get();

            foreach ($specialties as $spec) {
                $cards[] = [
                    'id'                => $spec->id,
                    'name_ar'           => $spec->name_ar,
                    'name_en'           => $spec->name_en ?? $spec->name_ar,
                    'office_type'       => $spec->office_type,
                    'category'          => $spec->category ?? null,
                    'business_activity' => $spec->business_activity ?? null,
                    'consultants_count' => (int) ($counts[$spec->id] ?? 0),
                ];
            }

            usort($cards, function ($a, $b) {
                if ($a['consultants_count'] !== $b['consultants_count']) {
                    return $b['consultants_count'] <=> $a['consultants_count'];
                }
                return strcmp($a['name_ar'], $b['name_ar']);
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('consultantSpecialtyCards DB error: ' . $e->getMessage());
        }

        return $cards;
    }

    /**
     * صفحة تخصص المستشارين — تعرض بطاقات المستشارين المعتمدين لهذا التخصص.
     */
    public function consultantSpecialtyDetail(int $specialtyId): View
    {
        try {
            $specialty = Specialty::where('id', $specialtyId)
                ->where('is_consultant', true)
                ->where('is_active', true)
                ->first();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('consultantSpecialtyDetail DB error: ' . $e->getMessage());
            $specialty = null;
        }

        if (! $specialty) {
            abort(404);
        }

        $consultants = collect();

        try {
            $consultants = Office::consultants()
                ->where('is_active', true)
                ->where('is_verified', true)
                ->visibleInDirectory()
                ->whereHas('specialtiesRelation', fn($q) => $q->whereKey($specialty->id)->where('is_active', true))
                ->with(['specialtiesRelation', 'services'])
                ->withCount([
                    'requests as completed_consultations_count' => fn($q) => $q->where('status', 'done'),
                    'requests as total_requests_count',
                ])
                ->orderBy('name_ar')
                ->get();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('consultantSpecialtyDetail consultants DB error: ' . $e->getMessage());
        }

        $totalConsultants = $consultants->count();

        $cities = $consultants->pluck('city')->filter()->unique()->sort()->values();

        $specOptions = collect();
        try {
            $specOptions = Specialty::where('is_consultant', true)
                ->where('is_active', true)
                ->orderBy('name_ar')
                ->pluck('name_ar');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('consultantSpecialtyDetail specOptions DB error: ' . $e->getMessage());
        }

        return view('update_service.consultant_specialty', compact('specialty', 'consultants', 'totalConsultants', 'cities', 'specOptions'));
    }

    /**
     * صفحة تفاصيل مستشار واحد.
     */
    public function consultantDetail(int $officeId): View
    {
        try {
            $office = Office::consultants()
                ->where('id', $officeId)
                ->where('is_active', true)
                ->where('is_verified', true)
                ->with([
                    'specialtiesRelation',
                    'services' => fn($q) => $q->where('is_active', true)->where('approval_status', 'approved')->orderBy('sort_order'),
                ])
                ->withCount([
                    'requests as completed_consultations_count' => fn($q) => $q->where('status', 'done'),
                    'requests as total_requests_count',
                ])
                ->first();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('consultantDetail DB error: ' . $e->getMessage());
            $office = null;
        }

        if (!$office) {
            $office = new Office([
                'id'             => $officeId,
                'name_ar'        => 'استشاري معتمد',
                'name_en'        => 'Certified Consultant',
                'subscription_type' => 'subscription',
                'views_count'    => 0,
            ]);
            $office->setRelation('specialtiesRelation', collect());
            $office->setRelation('services', collect());
        } else {
            $office->incrementViews();
        }

        return view('update_service.consultant_detail', compact('office'));
    }

    /**
     * دليل المكاتب المساندة — صفحة لكل نوع (محاماة / خدمات / جمركي / محاسبة / هندسة / مهن حرة).
     * تُعرض التخصصات كبطاقات، وكل بطاقة تجمّع الخدمات المعتمدة (بلا تكرار) لكل شركات النشاط.
     * طلبات هذه الصفحات تنتظر إسناد إدارة المنصة، ولا يُعرض أي تواصل مباشر مع المكتب.
     */
    public function officeDirectory(string $type): View
    {
        $specialties  = [];
        $totalOffices = 0;

        try {
            $specialties = $this->aggregateSpecialtyCards($type);
            $totalOffices = Office::where('type', $type)
                ->where('is_active', true)
                ->where('is_verified', true)
                ->supportingOffices()
                ->count();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('officeDirectory aggregation error: ' . $e->getMessage());
        }

        return view('update_service.office_directory', compact('type', 'specialties', 'totalOffices'));
    }

    /**
     * صفحة تخصص موحّدة تعرض الخدمات المعتمدة بلا تكرار لكل مكاتب النشاط.
     * معرفات المكاتب القديمة تُحوَّل تلقائياً إلى تخصصها الرئيسي.
     */
    public function specialtyDetail(string $type, int $specialtyId): View|\Illuminate\Http\RedirectResponse
    {
        try {
            $specialty = Specialty::where('id', $specialtyId)
                ->where('office_type', $type)
                ->where('is_active', true)
                ->first();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('specialtyDetail DB error: ' . $e->getMessage());
            $specialty = null;
        }

        if (! $specialty) {
            try {
                $office = Office::where('id', $specialtyId)
                    ->where('type', $type)
                    ->where('is_active', true)
                    ->where('is_verified', true)
                    ->supportingOffices()
                    ->first();

                if ($office) {
                    $primary = $office->specialtiesRelation()->where('is_active', true)->first();
                    if ($primary) {
                        return redirect()->route('amrtm.offices.detail', [$type, $primary->id], 301);
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('specialtyDetail office→specialty redirect failed: ' . $e->getMessage());
            }

            abort(404);
        }

        $services     = [];
        $officesCount = 0;

        try {
            foreach ($this->aggregateSpecialtyCards($type) as $card) {
                if ($card['id'] === $specialty->id) {
                    $services     = $card['services'];
                    $officesCount = $card['offices_count'];
                    break;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('specialtyDetail aggregation error: ' . $e->getMessage());
        }

        return view('update_service.specialty_detail', compact('type', 'specialty', 'services', 'officesCount'));
    }

    /**
     * تجميع خدمات المكاتب المساندة لقطاع معين حسب التخصص مع إزالة التكرار
     * (نفس مصدر الخدمة = خدمة واحدة حتى لو تقدم بها عدة مكاتب).
     *
     * @return array<int, array{
     *     id: int,
     *     specialty: Specialty,
     *     offices_count: int,
     *     services_count: int,
     *     services: array<int, array<string, mixed>>
     * }>
     */
    private function aggregateSpecialtyCards(string $type): array
    {
        $officeIds = Office::where('type', $type)
            ->where('is_active', true)
            ->where('is_verified', true)
            ->supportingOffices()
            ->pluck('id');

        $cards = [];

        if ($officeIds->isEmpty()) {
            return $cards;
        }

        $services = OfficeService::whereIn('office_id', $officeIds)
            ->where('approval_status', 'approved')
            ->where('is_active', true)
            ->with('specialty')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($services as $svc) {
            $spec = $svc->specialty;
            if (! $spec || ! $spec->is_active || $spec->office_type !== $type) {
                continue;
            }

            if (! isset($cards[$spec->id])) {
                $cards[$spec->id] = [
                    'specialty' => $spec,
                    'offices'   => [],
                    'services'  => [],
                ];
            }

            $cards[$spec->id]['offices'][$svc->office_id] = true;

            $key = $svc->source_service_id
                ? 'source:' . $svc->source_service_id
                : 'custom:' . trim((string) $svc->name_ar) . '|' . trim((string) $svc->price);

            if (! isset($cards[$spec->id]['services'][$key])) {
                $cards[$spec->id]['services'][$key] = [
                    'office_service_id' => $svc->id,
                    'service_id'        => $svc->source_service_id,
                    'entity_id'         => $svc->entity_id,
                    'name_ar'           => $svc->name_ar,
                    'name_en'           => $svc->name_en ?? $svc->name_ar,
                    'description_ar'    => $svc->description_ar,
                    'description_en'    => $svc->description_en,
                    'price'             => (float) $svc->price,
                    'duration'          => $svc->duration,
                    'offices_count'     => 0,
                ];
            }

            $cards[$spec->id]['services'][$key]['offices_count']++;
        }

        ksort($cards);

        $result = [];
        foreach ($cards as $specId => $card) {
            $result[] = [
                'id'             => $specId,
                'specialty'      => $card['specialty'],
                'offices_count'  => count($card['offices']),
                'services_count' => count($card['services']),
                'services'       => array_values($card['services']),
            ];
        }

        return $result;
    }

    public function submitOfficeRequest(Request $request): JsonResponse
    {
        $request->validate([
            'office_id'         => 'nullable|integer',
            'office_service_id' => 'required|integer',
            'client_name'       => 'nullable|string|max:100',
            'client_phone'      => 'nullable|string|max:20',
            'client_id_number'  => 'nullable|string|max:20',
            'notes'             => 'nullable|string|max:1000',
            'consultation_type' => 'nullable|string|in:standard,video',
        ]);

        // صفحة التخصص الموحّدة لا تمرر «office_id»: الخدمة معتمدة من الإدارة
        // ويُرسل الطلب لشبكة المكاتب حيث يتكفل المكتب المؤهل الأول بحجزه.
        if ($request->filled('office_id')) {
            $office = Office::where('id', $request->office_id)
                ->where('is_active', true)->where('is_verified', true)
                ->firstOrFail();

            $service = OfficeService::where('id', $request->office_service_id)
                ->where('office_id', $office->id)->where('is_active', true)
                ->where('approval_status', 'approved')
                ->firstOrFail();
        } else {
            $service = OfficeService::where('id', $request->office_service_id)
                ->where('is_active', true)
                ->where('approval_status', 'approved')
                ->firstOrFail();

            $office = $service->office;
        }

        if (! $office) {
            return response()->json(['message' => 'الخدمة غير متاحة حالياً'], 422);
        }

        // المستشارون (اشتراك) يحصلون على الطلب مباشرة وبشكل فوري.
        // المكاتب المساندة تُفتح الطلب في «شبكة المكاتب»: يُبث من إدارة المنصة
        // للمكاتب المؤهلة أنفسُها، وأول مكتب يحجز الطلب يتولى تنفيذه.
        $isConsultant     = $office->isConsultant();
        $user             = \Illuminate\Support\Facades\Auth::guard('business')->user();
        $commissionAmount = round($service->price * ($office->commission_rate / 100), 2);
        $refNumber        = 'OF-' . strtoupper(substr(md5(uniqid()), 0, 8));

        // المكاتب المساندة تُدفع منصةُ العاملة مسبقاً من رصيد العميل عند الإرسال؛
        // المستشارون يمرّون بلا دفع مسبق (التحصيل عند التسوية).
        if (! $isConsultant && $service->price > 0) {
            $balance = ServicePayment::getBalance($user->id);

            if ($balance < (float) $service->price) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'balance' => [
                        'رصيدك غير كافٍ لدفع قيمة الخدمة. ' .
                        'رصيدك الحالي: ' . $balance . ' ريال، ' .
                        'المطلوب: ' . $service->price . ' ريال.',
                    ],
                ]);
            }
        }

        $req = ServiceRequest::create([
            'ref_number'            => $refNumber,
            'origin'                => ServiceRequest::ORIGIN_OFFICE,
            'user_id'               => $user->id,
            'service_id'            => $service->source_service_id ?? null,
            'entity_id'             => $service->entity_id ?? null,
            'office_service_id'     => $service->id,
            'office_id'             => $isConsultant ? $office->id : null,
            'client_name'           => $request->input('client_name', $user->name),
            'client_phone'          => $request->input('client_phone', $user->phone),
            'client_email'          => $user->email,
            'client_id_number'      => $request->input('client_id_number', $user->id_number),
            'notes'                 => $request->notes,
            'price'                 => $service->price,
            'commission_amount'     => $commissionAmount,
            'status'                => 'pending',
            'office_status'         => 'pending',
            'fulfillment'           => $isConsultant
                ? ServiceRequest::FULFILLMENT_ASSIGNED
                : ServiceRequest::FULFILLMENT_OPEN,
            'assigned_at'           => $isConsultant ? now() : null,
            'consultation_type'     => $request->input('consultation_type', 'standard'),
            'payment_status'        => $isConsultant ? null : 'prepaid',
            'paid_at'               => $isConsultant ? null : now(),
            'data_retention_until'  => now()->addYears(5),
        ]);

        if (! $isConsultant && $service->price > 0) {
            ServicePayment::create([
                'user_id'        => $user->id,
                'request_id'     => $req->id,
                'amount'         => $service->price,
                'type'           => 'payment',
                'description_ar' => "دفع خدمة مكتب: {$service->name_ar}",
                'description_en' => "Office service payment: {$service->name_ar}",
                'status'         => 'completed',
            ]);
        }

        \App\Models\RequestLog::create([
            'request_id' => $req->id,
            'user_id'    => $user->id,
            'status'     => 'pending',
            'log_type'   => 'status_change',
            'note'       => $isConsultant
                ? 'تم إرسال الطلب المباشر إلى ' . $office->name_ar
                : 'تم فتح الطلب في شبكة المكاتب بانتظار بث إدارة المنصة',
        ]);

        // Notifications
        BusinessNotification::forAdmin('new_office_request',
            $isConsultant
                ? "طلب مباشر جديد — {$office->name_ar}"
                : "طلب مكتب مساند بانتظار البث لشبكة المكاتب — {$office->name_ar}",
            "العميل {$user->name} طلب خدمة من {$office->name_ar} — المرجع: {$refNumber} — المبلغ: {$service->price} ر.س",
            ['request_id' => $req->id, 'ref_number' => $refNumber, 'office_id' => $office->id]
        );

        if ($isConsultant) {
            BusinessNotification::forOffice($office->id, 'new_office_request',
                'طلب خدمة جديد من عميل',
                "تلقيت طلباً جديداً من {$user->name} — المرجع: {$refNumber}",
                ['request_id' => $req->id, 'ref_number' => $refNumber]
            );
        }

        if ($user) {
            BusinessNotification::forUser($user->id, 'request_submitted',
                'تم إرسال طلبك بنجاح',
                $isConsultant
                    ? "تم إرسال طلبك إلى {$office->name_ar} — المرجع: {$refNumber}"
                    : "تم استلام طلبك وفُتح في شبكة المكاتب المساندة، وسيتولى أول مكتب مؤهل حجزه — المرجع: {$refNumber}",
                ['request_id' => $req->id, 'ref_number' => $refNumber]
            );
        }

        return response()->json([
            'message'    => $isConsultant
                ? 'تم إرسال طلبك بنجاح. سيتواصل معك المكتب قريباً.'
                : 'تم إرسال طلبك بنجاح. فُتح في شبكة المكاتب المساندة وسيتولاه أول مكتب مؤهل خلال وقت قصير.',
            'ref_number' => $refNumber,
        ], 201);
    }

    /* ── JSON: all categories with entities & services ── */
    public function apiServices(): JsonResponse
    {
        try {
            $categories = Category::with([
                'entities' => fn($q) => $q->where('is_active', true)
                    ->with(['govServices' => fn($sq) => $sq->where('is_active', true)->orderBy('sort_order')])
                    ->orderBy('sort_order'),
            ])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($cat) => [
                'id'       => $cat->id,
                'key'      => $cat->key,
                'name_ar'  => $cat->name_ar,
                'name_en'  => $cat->name_en,
                'icon'     => $cat->icon,
                'color'    => $cat->color,
                'bg'       => $cat->bg,
                'entities' => $cat->entities->map(fn($ent) => [
                    'id'       => $ent->id,
                    'name_ar'  => $ent->name_ar,
                    'name_en'  => $ent->name_en,
                    'icon'     => $ent->icon,
                    'color'    => $ent->color,
                    'bg'       => $ent->bg,
                    'tag_ar'   => $ent->tag_ar,
                    'tag_en'   => $ent->tag_en,
                    'services' => $ent->govServices->map(fn($svc) => [
                        'id'             => $svc->id,
                        'name_ar'        => $svc->name_ar,
                        'name_en'        => $svc->name_en,
                        'icon'           => $svc->icon,
                        'price'          => (float) $svc->price,
                        'duration'       => \App\Support\ServiceDuration::format($svc->duration_min, $svc->duration_max, $svc->duration_unit),
                        'duration_min'   => $svc->duration_min,
                        'duration_max'   => $svc->duration_max,
                        'duration_unit'  => $svc->duration_unit,
                    ]),
                ]),
            ]);

            return response()->json($categories);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('apiServices DB error: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    /* ── JSON: public office type counts (for main page) ── */
    public function publicOfficeTypes(): JsonResponse
    {
        $types = ['law', 'services', 'customs'];
        $result = ['law' => 0, 'services' => 0, 'customs' => 0];

        try {
            $rows = Office::where('is_active', true)
                ->where('is_verified', true)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get()
                ->keyBy('type');

            foreach ($types as $t) {
                $result[$t] = (int) ($rows[$t]->count ?? 0);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('publicOfficeTypes DB error: ' . $e->getMessage());
        }

        return response()->json($result);
    }

    /* ── JSON: public consultants directory (للواجهة الأمامية المنفصلة) ── */
    public function apiConsultants(): JsonResponse
    {
        try {
            $consultants = Office::consultants()
                ->where('is_active', true)
                ->where('is_verified', true)
                ->visibleInDirectory()
                ->with(['specialtiesRelation', 'services'])
                ->withCount([
                    'requests as completed_consultations_count' => fn($q) => $q->where('status', 'done'),
                    'requests as total_requests_count',
                ])
                ->get();

            return response()->json($consultants->map(fn($o) => [
                'id'             => $o->id,
                'office_code'    => $o->office_code,
                'name_ar'        => $o->name_ar,
                'name_en'        => $o->name_en,
                'bio'            => $o->bio,
                'logo'           => $o->logo,
                'type'           => $o->type,
                'city'           => $o->city,
                'region'         => $o->region,
                'is_verified'    => (bool) $o->is_verified,
                'views_count'    => (int) $o->views_count,
                'completed_consultations_count' => (int) ($o->completed_consultations_count ?? 0),
                'total_requests_count'          => (int) ($o->total_requests_count ?? 0),
                'specialties'    => $o->specialtiesRelation->map(fn($s) => [
                    'id'      => $s->id,
                    'name_ar' => $s->name_ar,
                    'name_en' => $s->name_en,
                ]),
                'services'       => $o->services->map(fn($svc) => [
                    'id'      => $svc->id,
                    'name_ar' => $svc->name_ar,
                    'price'   => (float) $svc->price,
                ]),
            ]));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('apiConsultants DB error: ' . $e->getMessage());

            return response()->json([]);
        }
    }

    /* ── JSON: consultants specialty cards (بطاقات التخصصات) ── */
    public function apiConsultantSpecialties(): JsonResponse
    {
        return response()->json([
            'categories'         => \App\Support\ConsultantCatalog::categories(),
            'businessActivities' => \App\Support\ConsultantCatalog::businessActivities(),
            'specialties'        => $this->consultantSpecialtyCards(),
        ]);
    }

    /* ── JSON: submit service request ── */
    public function submitRequest(SubmitRequestRequest $request, ServiceRequestService $service): JsonResponse
    {
        $sr = $service->submit(
            $request->validated(),
            $request->file('attachments') ?? [],
            auth('business')->user()
        );

        return response()->json(['ref_number' => $sr->ref_number, 'id' => $sr->id], 201);
    }

    /* ── JSON: my requests list (paginated, optional ?status=) ── */
    public function myRequests(Request $request): JsonResponse
    {
        $query = ServiceRequest::with(['govService', 'entity', 'logs', 'office'])
            ->where('user_id', auth('business')->id());

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $paginator = $query->orderByDesc('created_at')->paginate(10);

        $paginator->getCollection()->transform(fn($r) => $r->toApiArray());

        return response()->json($paginator);
    }

    /* ── JSON: single request detail ── */
    public function myRequestShow(int $id): JsonResponse
    {
        $sr = ServiceRequest::with(['govService', 'entity.category', 'logs', 'office'])->findOrFail($id);

        // Policy check: user can only view own requests; admin can view all
        if (!auth('business')->user()->isAdmin() && $sr->user_id !== auth('business')->id()) {
            abort(403, 'غير مصرح لك بعرض هذا الطلب.');
        }

        return response()->json($sr);
    }

    /* ── JSON: client conversation with the executing office ── */
    public function getRequestMessages(int $id): JsonResponse
    {
        $user = auth('business')->user();
        $sr   = ServiceRequest::findOrFail($id);

        if (!$user->isAdmin() && $sr->user_id !== $user->id) {
            abort(403, 'غير مصرح لك بعرض محادثة هذا الطلب.');
        }

        // رسائل المكتب تصبح مقروءة تلقائياً عند فتح المحادثة من طرف العميل.
        if (!$user->isAdmin()) {
            OfficeMessage::where('request_id', $id)
                ->where('sender_type', 'office')
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        $messages = OfficeMessage::where('request_id', $id)
            ->orderBy('created_at')
            ->get(['id', 'sender_type', 'message', 'attachments', 'is_read', 'created_at']);

        return response()->json($messages);
    }

    /* ── JSON: client sends a message to the executing office ── */
    public function sendRequestMessage(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'message'       => 'required_without:attachments|string|max:2000',
            'attachments'   => 'sometimes|array|max:' . MessageAttachmentStorage::MAX_FILES_PER_MESSAGE,
            'attachments.*' => 'file|mimes:' . MessageAttachmentStorage::ALLOWED_MIMES
                . '|max:' . (int) (MessageAttachmentStorage::MAX_FILE_BYTES / 1024),
        ], [
            'message.required_without' => 'الرسالة أو مرفق واحد على الأقل مطلوب',
            'message.max'              => 'الرسالة طويلة جداً',
            'attachments.array'        => 'صيغة المرفقات غير صحيحة',
            'attachments.max'          => 'الحد الأقصى ' . MessageAttachmentStorage::MAX_FILES_PER_MESSAGE . ' ملفات للرسالة',
            'attachments.*.mimes'      => 'نوع الملف غير مسموح (صور / PDF / مستندات / ملفات نصية)',
            'attachments.*.max'        => 'حجم الملف لا يتجاوز 10MB',
        ]);

        $user = auth('business')->user();
        $sr   = ServiceRequest::findOrFail($id);

        if ($sr->user_id !== $user->id) {
            abort(403, 'غير مصرح لك بالتواصل على هذا الطلب.');
        }

        if ($sr->office_id === null || $sr->office === null) {
            return response()->json(['message' => 'لم يُسند هذا الطلب إلى مكتب بعد.'], 422);
        }

        $attachments = $request->file('attachments', []);
        $findings    = [];

        if (ContactDataGuard::isEnforced($sr->office)) {
            // فحص نص الرسالة + كل مرفق (اسم / محتوى / EXIF / OCR) قبل الحفظ.
            $findings = ContactDataGuard::scan((string) $request->message);

            foreach ($attachments as $file) {
                foreach (AttachmentScanner::scanUploadedFile($file)['findings'] as $finding) {
                    $findings[] = $finding;
                }
            }

            if (count($findings) > 0) {
                ContactDataGuard::recordViolation($sr, 'client', $request->message ?: 'مرفقات', $findings);

                return response()->json([
                    'message'  => 'لا يمكنك إرسال بيانات تواصل أو عبارات تدل على الخروج من المنصة (هاتف / بريد / رابط / مرفق يحتوي عليها).',
                    'findings' => $findings,
                ], 422);
            }
        }

        $msg = OfficeMessage::create([
            'request_id'  => $sr->id,
            'office_id'   => $sr->office_id,
            'sender_type' => 'client',
            'sender_id'   => $user->id,
            'message'     => $request->message,
            'attachments' => MessageAttachmentStorage::store($attachments),
        ]);

        BusinessNotification::forOffice(
            $sr->office_id,
            'message_received',
            "رسالة جديدة — طلب #{$sr->ref_number}",
            mb_substr($request->message ?: 'مرفقات', 0, 160),
            ['request_id' => $sr->id, 'ref_number' => $sr->ref_number],
            $sr->id
        );

        return response()->json($msg->toArray(), 201);
    }

    /* ── Web: client request tracking (full timeline page) ── */
    public function trackRequest(int $id): View
    {
        $sr = ServiceRequest::with(['govService', 'entity.category', 'office', 'logs'])
            ->findOrFail($id);

        // Policy check: user can only track own requests; admin can view all
        if (!auth('business')->user()->isAdmin() && $sr->user_id !== auth('business')->id()) {
            abort(403, 'غير مصرح لك بتتبع هذا الطلب.');
        }

        $timeline = $this->buildRequestTimeline($sr);
        $statusMeta = $this->statusMeta($sr);

        return view('update_service.request_track', compact('sr', 'timeline', 'statusMeta'));
    }

    /**
     * Build the chronological lifecycle stages of a request for the client timeline.
     * Canonical stages: submitted → assigned/internal → accepted (office) → processing
     * → waiting_docs (optional) → done. A rejected request gets a terminal red stage.
     * Notes/timestamps are pulled from the request logs when available.
     */
    private function buildRequestTimeline(ServiceRequest $sr): array
    {
        $logs = $sr->logs->sortBy('created_at')->values();
        $office = $sr->office;

        $firstLog = function (array $types) use ($logs) {
            foreach ($logs as $l) {
                if (in_array($l->log_type, $types, true)) {
                    return $l;
                }
            }
            return null;
        };
        $lastLog = function (array $types) use ($logs) {
            for ($i = $logs->count() - 1; $i >= 0; $i--) {
                if (in_array($logs[$i]->log_type, $types, true)) {
                    return $logs[$i];
                }
            }
            return null;
        };
        $officeStatusLog = fn (string $target) => collect($logs)
            ->first(
                fn ($l) => $l->log_type === 'office_status'
                    && is_string($l->note) && str_contains($l->note, ':' . $target)
            );

        $rejected = $sr->status === 'rejected';
        $done     = $sr->status === 'done';
        $wasAssigned = $office !== null || $firstLog(['assigned_to_office']) !== null;

        /* ── Current milestone (rank) from live request state ── */
        $currentRank = 0;
        if ($rejected) {
            $currentRank = 90;
        } elseif ($done) {
            $currentRank = 60;
        } elseif ($sr->office_status === 'waiting_docs') {
            $currentRank = 50;
        } elseif ($sr->office_status === 'in_progress' || $sr->status === 'in_progress') {
            $currentRank = 40;
        } elseif ($sr->office_status === 'accepted' || $sr->status === 'processing') {
            $currentRank = 30;
        } elseif ($sr->fulfillment !== null) {
            $currentRank = 20;
        } else {
            $currentRank = 10;
        }

        $stages = [];
        $add = function (int $rank, string $key, string $label, string $icon, $time, ?string $note) use (&$stages, $currentRank) {
            $state = $rank < $currentRank ? 'done' : ($rank === $currentRank ? 'current' : 'upcoming');
            $stages[] = [
                'rank'  => $rank,
                'stage' => $key,
                'label' => $label,
                'icon'  => $icon,
                'time'  => $time ? \Carbon\Carbon::parse($time) : null,
                'note'  => $note,
                'state' => $state,
            ];
        };

        /* 1) تقديم الطلب */
        $submitted = $firstLog(['status_change']) ?: $sr->created_at;
        $add(10, 'submitted', 'تم تقديم الطلب', 'ti-file-check',
            $submitted ? ($submitted->created_at ?? $submitted) : $sr->created_at,
            $submitted->note ?? null);

        /* 2) الإسناد / المعالجة الداخلية */
        if ($wasAssigned) {
            $assignLog = $firstLog(['assigned_to_office']);
            $add(20, 'assigned',
                $office ? "تم الإسناد إلى {$office->name_ar}" : 'تم الإسناد إلى مكتب مساند',
                'ti-building',
                $assignLog?->created_at ?? $sr->assigned_at ?? $sr->created_at,
                $assignLog?->note ?? null);
        } else {
            $internalLog = $firstLog(['internal_handling']);
            $add(20, 'assigned', 'تتولى المنصة معالجة الطلب', 'ti-settings',
                $internalLog?->created_at ?? $sr->created_at,
                $internalLog?->note ?? null);
        }

        /* 3) قبول المكتب المساند (يظهر فقط عند الإسناد) */
        if ($wasAssigned) {
            $acceptLog = $officeStatusLog('accepted');
            $add(30, 'accepted', 'قبل المكتب المساند الطلب', 'ti-thumb-up',
                $acceptLog?->created_at ?? $sr->assigned_at ?? null,
                $acceptLog?->note ?? null);
        }

        /* 4) قيد التنفيذ */
        $procLog = $lastLog(['office_status', 'status_change', 'internal_handling']);
        $procTime = $procLog?->created_at ?? $sr->assigned_at ?? null;
        $add(40, $wasAssigned ? 'processing' : 'processing', 'قيد التنفيذ', 'ti-tools',
            $currentRank > 30 ? $procTime : null,
            $procLog?->note ?? null);

        /* 5) بانتظار مستندات (اختياري — يُعرض فقط عند حدوثه) */
        $waitLog = $officeStatusLog('waiting_docs') ?: $lastLog(['office_status']);
        $showWaiting = $sr->office_status === 'waiting_docs'
            || ($waitLog && $waitLog->log_type === 'office_status' && is_string($waitLog->note) && str_contains($waitLog->note, ':waiting_docs'));
        if ($showWaiting) {
            $waitingLog = $officeStatusLog('waiting_docs');
            $add(50, 'waiting_docs', 'بانتظار مستندات إضافية منك', 'ti-file-alert',
                $waitingLog?->created_at ?? null,
                $waitingLog?->note ?? null);
        }

        /* 6) الاكتمال */
        $doneLog = $officeStatusLog('done') ?: $lastLog(['status_change']);
        $add(60, 'done', 'اكتملت العملية', 'ti-circle-check',
            $sr->completed_at ?? ($doneLog?->created_at ?? null),
            ($doneLog && $doneLog->status === 'done') ? $doneLog->note : null);

        /* ── الطرفي: رفض / إعادة إسناد ── */
        if ($rejected) {
            $rejectNote = $sr->reject_reason ?: $lastLog(['status_change', 'office_rejected'])?->note;
            $rejectTime = $lastLog(['status_change', 'office_rejected'])?->created_at ?? $sr->updated_at;
            $add(90, 'rejected', 'تعذّر إتمام الطلب', 'ti-x',
                \Carbon\Carbon::parse($rejectTime), $rejectNote);
        }

        return $stages;
    }

    /** Arabic metadata (badge style + label) for the current request status. */
    private function statusMeta(ServiceRequest $sr): array
    {
        $status = $sr->status;
        $labels = ServiceRequest::statusLabels();

        $badge = match ($status) {
            'done'        => 'bg-[var(--cui-primary-soft)] text-[var(--cui-primary)]',
            'rejected'    => 'bg-red-50 text-red-600 border-red-100',
            'processing', 'in_progress' => 'bg-blue-50 text-blue-600 border-blue-100',
            default       => 'bg-amber-50 text-amber-700 border-amber-100',
        };

        return [
            'label' => $labels[$status] ?? 'غير معروف',
            'badge' => $badge,
            'status' => $status,
        ];
    }

    /* ── JSON: user dashboard stats ── */
    public function userStats(): JsonResponse
    {
        $user = auth('business')->user();
        $uid  = $user->id;

        // Single aggregation query instead of 5 separate count queries
        $counts = ServiceRequest::where('user_id', $uid)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'pending') as pending,
                SUM(status IN ('processing', 'in_progress')) as processing,
                SUM(status = 'done') as done,
                SUM(status = 'rejected') as rejected
            ")
            ->first();

        $recentRequests = ServiceRequest::with(['govService', 'entity', 'logs', 'office'])
            ->where('user_id', $uid)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($r) => $r->toApiArray());

        $recentPayments = ServicePayment::where('user_id', $uid)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'user' => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'phone'   => $user->phone ?? '',
                'role'    => $user->role,
                'balance' => ServicePayment::getBalance($uid),
                'stats'   => [
                    'total'      => (int) $counts->total,
                    'pending'    => (int) $counts->pending,
                    'processing' => (int) $counts->processing,
                    'done'       => (int) $counts->done,
                    'rejected'   => (int) $counts->rejected,
                ],
            ],
            'recent_requests' => $recentRequests,
            'recent_payments' => $recentPayments,
        ]);
    }

    /* ── JSON: payment history ── */
    public function paymentHistory(): JsonResponse
    {
        $payments = ServicePayment::where('user_id', auth('business')->id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($payments);
    }

    /* ── JSON: update profile ── */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth('business')->user();
        $user->update($request->validated());

        return response()->json(['message' => 'تم حفظ البيانات', 'user' => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
        ]]);
    }

    /* ── JSON: change password ── */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = auth('business')->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'كلمة المرور الحالية غير صحيحة'], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح']);
    }

    /**
     * JSON: بيانات الصفحة الرئيسية كاملة (للواجهة الأمامية المنفصلة).
     * نفس البيانات التي كانت تصيِّرها view('update_service.index').
     */
    public function apiHome(): JsonResponse
    {
        $categories   = collect();
        $officeCounts = [
            'law'         => 0,
            'services'    => 0,
            'customs'     => 0,
            'accounting'  => 0,
            'engineering' => 0,
            'freelance'   => 0,
            'consultants' => 0,
        ];

        try {
            $categories = Category::with([
                'entities' => fn($q) => $q->where('is_active', true)
                    ->with(['govServices' => fn($sq) => $sq->where('is_active', true)->orderBy('sort_order')])
                    ->orderBy('sort_order'),
            ])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($cat) => [
                'id'             => $cat->id,
                'key'            => $cat->key,
                'name_ar'        => $cat->name_ar,
                'name_en'        => $cat->name_en,
                'icon'           => $cat->icon,
                'color'          => $cat->color,
                'bg'             => $cat->bg,
                'entities_count' => $cat->entities->count(),
                'services_count' => $cat->entities->sum(fn($ent) => $ent->govServices->count()),
            ]);

            $dbCounts = Office::where('is_active', true)
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();
            $officeCounts = array_merge($officeCounts, $dbCounts);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('apiHome categories error: ' . $e->getMessage());
        }

        try {
            $officeCounts['consultants'] = Office::consultants()
                ->where('is_active', true)
                ->where('is_verified', true)
                ->visibleInDirectory()
                ->count();
        } catch (\Throwable $e) {
            $officeCounts['consultants'] = 0;
        }

        try {
            $homepageSettings = \App\Models\HomepageSetting::query()->pluck('value', 'key');
            $homepageSlides   = \App\Models\HomepageSlide::active()->get()->map(fn($s) => [
                'id'        => $s->id,
                'title'     => $s->title,
                'image_url' => $s->image_url,
                'link_url'  => $s->link_url,
            ]);
        } catch (\Throwable $e) {
            $homepageSettings = collect();
            $homepageSlides   = collect();
        }

        $homepageMedia = [
            'video_file'   => $this->resolveMediaUrl($homepageSettings['video_file'] ?? 'videos/0829.mp4'),
            'video_poster' => $this->resolveMediaUrl($homepageSettings['video_poster'] ?? 'images/logo2.jpg'),
        ];

        return response()->json([
            'categories'       => $categories,
            'officeCounts'     => $officeCounts,
            'homepageSettings' => $homepageSettings,
            'homepageSlides'   => $homepageSlides,
            'homepageMedia'    => $homepageMedia,
        ]);
    }

    /**
     * JSON: صفحة تصنيف الكتالوج — نفس بيانات view('update_service.catalog_category').
     */
    public function apiCatalogCategory(string $key): JsonResponse
    {
        try {
            $category = Category::where('key', $key)->where('is_active', true)->first();

            if (!$category) {
                $category = new Category([
                    'key' => $key,
                    'name_ar' => $key === 'ministries' ? 'الوزارات' : ($key === 'authorities' ? 'الهيئات والمؤسسات الحكومية' : 'الشركات والجهات الخاصة'),
                    'name_en' => ucfirst($key),
                ]);
                $category->setRelation('entities', collect());
            }

            $entitiesQuery = Entity::where('category_id', $category->id)
                ->where('is_active', true)
                ->with(['govServices' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
                ->orderBy('sort_order');

            $entities = $entitiesQuery->get();
            $category->setRelation('entities', $entities);

            return response()->json([
                'category' => [
                    'id'      => $category->id,
                    'key'     => $category->key,
                    'name_ar' => $category->name_ar,
                    'name_en' => $category->name_en,
                    'icon'    => $category->icon,
                    'color'   => $category->color,
                    'bg'      => $category->bg,
                ],
                'entities' => $entities->map(fn($e) => [
                    'id'         => $e->id,
                    'name_ar'    => $e->name_ar,
                    'name_en'    => $e->name_en,
                    'icon'       => $e->icon,
                    'color'      => $e->color,
                    'bg'         => $e->bg,
                    'tag_ar'     => $e->tag_ar,
                    'tag_en'     => $e->tag_en,
                    'images'     => $e->images,
                    'services'   => $e->govServices->map(fn($s) => [
                        'id'      => $s->id,
                        'name_ar' => $s->name_ar,
                        'name_en' => $s->name_en,
                    ]),
                ]),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('apiCatalogCategory DB error: ' . $e->getMessage());

            return response()->json(['category' => ['key' => $key, 'name_ar' => $key, 'name_en' => $key, 'icon' => null, 'color' => null, 'bg' => null], 'entities' => []]);
        }
    }

    /**
     * JSON: صفحة جهة — نفس بيانات view('update_service.catalog_entity').
     */
    public function apiCatalogEntity(string $key, int $entityId): JsonResponse
    {
        try {
            $category = Category::where('key', $key)->where('is_active', true)->first();
            $entity = Entity::where('id', $entityId)->where('is_active', true)->first();

            if (!$entity) {
                return response()->json(['category' => ['key' => $key, 'name_ar' => 'الجهة', 'name_en' => 'Entity'], 'entity' => null, 'services' => []]);
            }

            $services = GovService::where('entity_id', $entity->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn($s) => [
                    'id'            => $s->id,
                    'name_ar'       => $s->name_ar,
                    'name_en'       => $s->name_en,
                    'icon'          => $s->icon,
                    'price'         => (float) $s->price,
                    'duration'      => \App\Support\ServiceDuration::format($s->duration_min, $s->duration_max, $s->duration_unit),
                    'duration_min'  => $s->duration_min,
                    'duration_max'  => $s->duration_max,
                    'duration_unit' => $s->duration_unit,
                    'description'   => $s->description,
                ]);

            return response()->json([
                'category' => [
                    'id'      => $category?->id,
                    'key'     => $category?->key ?? $key,
                    'name_ar' => $category?->name_ar ?? 'الجهة',
                    'name_en' => $category?->name_en ?? 'Entity',
                    'color'   => $category?->color,
                    'bg'      => $category?->bg,
                ],
                'entity' => [
                    'id'      => $entity->id,
                    'name_ar' => $entity->name_ar,
                    'name_en' => $entity->name_en,
                    'icon'    => $entity->icon,
                    'color'   => $entity->color,
                    'bg'      => $entity->bg,
                    'tag_ar'  => $entity->tag_ar,
                    'tag_en'  => $entity->tag_en,
                    'images'  => $entity->images,
                ],
                'services' => $services,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('apiCatalogEntity DB error: ' . $e->getMessage());

            return response()->json(['category' => ['key' => $key, 'name_ar' => 'الجهة', 'name_en' => 'Entity'], 'entity' => null, 'services' => []]);
        }
    }

    /**
     * JSON: دليل المكاتب حسب النوع (law|services|customs|accounting|engineering|freelance).
     */
    public function apiOfficeDirectory(string $type): JsonResponse
    {
        try {
            $offices = Office::where('type', $type)
                ->where('is_active', true)
                ->where('is_verified', true)
                ->visibleInDirectory()
                ->with(['specialtiesRelation'])
                ->withCount('requests as total_requests_count')
                ->get();

            return response()->json([
                'type'    => $type,
                'offices' => $offices->map(fn($o) => [
                    'id'             => $o->id,
                    'office_code'    => $o->office_code,
                    'name_ar'        => $o->name_ar,
                    'name_en'        => $o->name_en,
                    'bio'            => $o->bio,
                    'logo'           => $o->logo,
                    'city'           => $o->city,
                    'region'         => $o->region,
                    'is_verified'    => (bool) $o->is_verified,
                    'views_count'    => (int) $o->views_count,
                    'total_requests_count' => (int) ($o->total_requests_count ?? 0),
                    'specialties'    => $o->specialtiesRelation->map(fn($s) => [
                        'id'      => $s->id,
                        'name_ar' => $s->name_ar,
                        'name_en' => $s->name_en,
                    ]),
                ]),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('apiOfficeDirectory DB error: ' . $e->getMessage());

            return response()->json(['type' => $type, 'offices' => []]);
        }
    }

    /**
     * JSON: تفاصيل مكتب/مستشار واحد.
     */
    public function apiOfficeDetail(int $officeId): JsonResponse
    {
        try {
            $office = Office::with(['specialtiesRelation', 'services'])
                ->withCount('requests as total_requests_count')
                ->find($officeId);

            if (!$office) {
                return response()->json(['office' => null]);
            }

            return response()->json([
                'office' => [
                    'id'             => $office->id,
                    'office_code'    => $office->office_code,
                    'name_ar'        => $office->name_ar,
                    'name_en'        => $office->name_en,
                    'bio'            => $office->bio,
                    'logo'           => $office->logo,
                    'type'           => $office->type,
                    'city'           => $office->city,
                    'region'         => $office->region,
                    'is_verified'    => (bool) $office->is_verified,
                    'views_count'    => (int) $office->views_count,
                    'total_requests_count' => (int) ($office->total_requests_count ?? 0),
                    'specialties'    => $office->specialtiesRelation->map(fn($s) => [
                        'id'      => $s->id,
                        'name_ar' => $s->name_ar,
                        'name_en' => $s->name_en,
                    ]),
                    'services'       => $office->services->map(fn($svc) => [
                        'id'      => $svc->id,
                        'name_ar' => $svc->name_ar,
                        'price'   => (float) $svc->price,
                    ]),
                ],
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('apiOfficeDetail DB error: ' . $e->getMessage());

            return response()->json(['office' => null]);
        }
    }

    /**
     * JSON: صفحة دليل المكاتب — تخصصات النوع مع عدّادات (مطابق لـ office_directory.blade.php).
     */
    public function apiOfficeSpecialties(string $type): JsonResponse
    {
        try {
            $specialties = \App\Models\Business\Specialty::where('is_active', true)
                ->where('office_type', $type)
                ->with(['services' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
                ->orderBy('name_ar')
                ->get();

            // عدّ المكاتب المرتبطة بكل تخصص
            $pivot = \Illuminate\Support\Facades\DB::connection('business')
                ->table('bs_office_specialties')
                ->whereIn('specialty_id', $specialties->pluck('id'))
                ->selectRaw('specialty_id, count(*) as offices_count')
                ->groupBy('specialty_id')
                ->pluck('offices_count', 'specialty_id');

            $totalOffices = \App\Models\Business\Office::where('type', $type)
                ->where('is_active', true)
                ->where('is_verified', true)
                ->visibleInDirectory()
                ->count();

            $cfg = $this->officeTypeConfig($type);

            return response()->json([
                'type'          => $type,
                'config'        => $cfg,
                'total_specialties' => $specialties->count(),
                'total_offices' => $totalOffices,
                'specialties'   => $specialties->map(fn($s) => [
                    'id'            => $s->id,
                    'name_ar'       => $s->name_ar,
                    'name_en'       => $s->name_en ?? $s->name_ar,
                    'offices_count' => (int) ($pivot[$s->id] ?? 0),
                    'services_count' => $s->services->count(),
                    'services'      => $s->services->take(3)->map(fn($svc) => [
                        'name_ar' => $svc->name_ar,
                        'name_en' => $svc->name_en ?? $svc->name_ar,
                    ]),
                    'more_count'    => max(0, $s->services->count() - 3),
                ]),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('apiOfficeSpecialties DB error: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());

            return response()->json([
                'type' => $type,
                'config' => $this->officeTypeConfig($type),
                'total_specialties' => 0,
                'total_offices' => 0,
                'specialties' => [],
            ]);
        }
    }

    private function officeTypeConfig(string $type): array
    {
        $map = [
            'law' => [
                'icon' => 'ti-scale', 'color' => '#006C35', 'accent' => '#0B3B2C',
                'gradient' => 'linear-gradient(135deg,#0B3B2C,#006C35)',
                'badge_ar' => 'مكاتب متخصصة',
                'hint_ar' => 'اختر التخصص المناسب؛ طلبك ينتظر إسناد إدارة المنصة لأحد المكاتب المعتمدة',
                'name_ar' => 'مكاتب المحاماة', 'name_en' => 'Law Firms',
                'desc_ar' => 'مكاتب محاماة معتمدة متخصصة في الاستشارات القانونية والتمثيل أمام الجهات القضائية',
            ],
            'services' => [
                'icon' => 'ti-briefcase', 'color' => '#006C35', 'accent' => '#0B3B2C',
                'gradient' => 'linear-gradient(135deg,#0B3B2C,#006C35)',
                'badge_ar' => 'مكاتب تنفيذ معاملات',
                'hint_ar' => 'اختر التخصص المناسب؛ طلبك ينتظر إسناد إدارة المنصة لأول مكتب مساند مؤهل',
                'name_ar' => 'مكاتب الخدمات والتعقيب', 'name_en' => 'Service & Expediting Offices',
                'desc_ar' => 'مكاتب متخصصة في إنهاء المعاملات الحكومية والرسمية بكل سهولة وسرعة',
            ],
            'customs' => [
                'icon' => 'ti-truck', 'color' => '#006C35', 'accent' => '#0B3B2C',
                'gradient' => 'linear-gradient(135deg,#0B3B2C,#006C35)',
                'badge_ar' => 'شركات تخليص جمركي',
                'hint_ar' => 'اختر التخصص المناسب؛ طلبك ينتظر إسناد إدارة المنصة لإحدى الشركات المعتمدة',
                'name_ar' => 'شركات التخليص الجمركي', 'name_en' => 'Customs Clearance Companies',
                'desc_ar' => 'شركات متخصصة في تخليص البضائع وإجراءات الاستيراد والتصدير',
            ],
            'accounting' => [
                'icon' => 'ti-calculator', 'color' => '#006C35', 'accent' => '#0B3B2C',
                'gradient' => 'linear-gradient(135deg,#0B3B2C,#006C35)',
                'badge_ar' => 'استشارات مالية وضريبية',
                'hint_ar' => 'اختر التخصص المناسب؛ طلبك ينتظر إسناد إدارة المنصة لأحد المكاتب المعتمدة',
                'name_ar' => 'مكاتب المحاسبة والاستشارات المالية والضريبية', 'name_en' => 'Accounting & Tax Consulting',
                'desc_ar' => 'خدمات المحاسبة والاستشارات المالية والضريبية للشركات والأفراد',
            ],
            'engineering' => [
                'icon' => 'ti-building', 'color' => '#006C35', 'accent' => '#0B3B2C',
                'gradient' => 'linear-gradient(135deg,#0B3B2C,#006C35)',
                'badge_ar' => 'استشارات هندسية',
                'hint_ar' => 'اختر التخصص المناسب؛ طلبك ينتظر إسناد إدارة المنصة لأحد المكاتب المعتمدة',
                'name_ar' => 'الاستشارات الهندسية والتصميم والإشراف', 'name_en' => 'Engineering Consulting',
                'desc_ar' => 'خدمات التصميم الهندسي والإشراف وإدارة المشاريع',
            ],
            'freelance' => [
                'icon' => 'ti-user', 'color' => '#006C35', 'accent' => '#0B3B2C',
                'gradient' => 'linear-gradient(135deg,#0B3B2C,#006C35)',
                'badge_ar' => 'مهنيون متخصصون',
                'hint_ar' => 'اختر التخصص المناسب؛ طلبك ينتظر إسناد إدارة المنصة للخبير المعتمد',
                'name_ar' => 'أصحاب المهن الحرة', 'name_en' => 'Freelance Professionals',
                'desc_ar' => 'مقدمو الخدمات المهنية المستقلون في مختلف التخصصات',
            ],
        ];

        return $map[$type] ?? $map['services'];
    }

    private function resolveMediaUrl(string $path): string
    {
        if (str_starts_with($path, 'homepage/')) {
            // تُخدم عبر Laravel مباشرة (بدل Storage::url الذي يعتمد على
            // سيم لينك public/storage المعطل على الاستضافة → 403)
            return url('media/public/' . ltrim($path, '/'));
        }

        return asset($path);
    }
}
