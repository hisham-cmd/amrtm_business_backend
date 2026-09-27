<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use App\Models\Business\Office;
use App\Models\Business\OfficeUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Support\ServiceCustomFields;

class ProviderAccountController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | صفحة التسجيل
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $registrationType = $request->query('type', 'office');

        $mode = match ($registrationType) {
            'consultant' => 'consultant',
            'mixed'      => 'mixed',
            'client'     => 'client',
            default      => 'office',
        };

        $defaultAccountType = $request->query('account_type', 'individual');
        if (!in_array($defaultAccountType, ['individual', 'establishment'], true)) {
            $defaultAccountType = 'individual';
        }

        return view('update_service.provider-account', [
            'mode' => $mode,
            'defaultClientAccountType' => $defaultAccountType,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | جلب التخصصات والخدمات التابعة لها حسب نوع المكتب
    |--------------------------------------------------------------------------
    */

    public function specialties(Request $request)
    {
        $isConsultant = $request->query('mode') === 'consultant';

        if (! $isConsultant) {
            $request->validate([
                'office_type' => [
                    'required',
                    'in:law,services,customs,accounting,engineering,freelance',
                ],
            ]);
        }

        $query = DB::connection('business')
            ->table('bs_specialties')
            ->where('is_active', 1);

        if ($isConsultant) {
            $query->where('is_consultant', 1);
        } else {
            $query->where('office_type', $request->office_type);
        }

        $specialties = $query->orderBy('name_ar')
            ->get([
                'id',
                'name_ar',
                'name_en',
                'category',
                'business_activity',
            ]);

        $catalog = $this->getServicesCatalog();

        $linkedSpecialties = \App\Models\Business\Specialty::with([
            'services' => fn($q) => $q->where('is_active', true)->orderBy('sort_order'),
        ])
            ->whereKey($specialties->pluck('id'))
            ->get()
            ->keyBy('id');

        $linkedSpecialtyIds = DB::connection('business')
            ->table('bs_specialty_services')
            ->whereIn('specialty_id', $specialties->pluck('id'))
            ->distinct()
            ->pluck('specialty_id')
            ->all();

        $specialtiesWithServices = $specialties->map(function ($item) use ($catalog, $request, $linkedSpecialties, $linkedSpecialtyIds) {
            $name = trim($item->name_ar);
            $cleanName = preg_replace('/^\d+\.\s*/u', '', $name); // remove numbering like "26. "

            $linked = $linkedSpecialties->get($item->id);

            if (in_array($item->id, $linkedSpecialtyIds)) {
                $services = $linked->services->map(fn($svc) => $this->withDuration([
                    'id'           => $svc->id,
                    'name_ar'      => $svc->name_ar,
                    'name_en'      => $svc->name_en ?? $svc->name_ar,
                    'entity_id'    => $svc->entity_id,
                    'price'        => (float) $svc->price,
                    'duration'     => \App\Support\ServiceDuration::format($svc->duration_min, $svc->duration_max, $svc->duration_unit),
                    'requirements' => $svc->custom_fields,
                ]))->values()->all();
            } else {
                $services = $catalog[$cleanName] ?? $catalog[$name] ?? $this->getDefaultServicesForType($request->office_type, $name);
                $services = collect($services)
                    ->map(fn($svc) => $this->withDuration((array) $svc))
                    ->values()
                    ->all();
            }

            return [
                'id'                => $item->id,
                'name_ar'           => $item->name_ar,
                'name_en'           => $item->name_en ?? $item->name_ar,
                'category'          => $item->category ?? null,
                'business_activity' => $item->business_activity ?? null,
                'services'          => $services,
            ];
        });

        return response()->json([
            'success'     => true,
            'specialties' => $specialtiesWithServices,
        ]);
    }

    /**
     * تطبيع مدة الخدمة إلى نطاق (min/max/unit) + نص التنسيق للعرض.
     */
    protected function withDuration(array $svc): array
    {
        $min  = isset($svc['duration_min']) && is_numeric($svc['duration_min']) ? (int) $svc['duration_min'] : null;
        $max  = isset($svc['duration_max']) && is_numeric($svc['duration_max']) ? (int) $svc['duration_max'] : null;
        $unit = !empty($svc['duration_unit']) ? trim((string) $svc['duration_unit']) : null;

        if ($min === null && $max === null) {
            $parsed = \App\Support\ServiceDuration::parse($svc['duration'] ?? null);
            $min    = $parsed['min'];
            $max    = $parsed['max'];
            $unit   = $parsed['unit'];
        }

        $svc['duration_min']  = $min;
        $svc['duration_max']  = $max;
        $svc['duration_unit'] = $unit;
        $svc['duration']      = \App\Support\ServiceDuration::format($min, $max, $unit)
            ?? (isset($svc['duration']) && $svc['duration'] ? $svc['duration'] : null);

        return $svc;
    }

    /**
     * كتالوج الخدمات القياسية لكل تخصص
     */
    protected function getServicesCatalog(): array
    {
        return [
            // المحاماة والخدمات القانونية (Law)
            'القضايا التجارية' => [
                ['name_ar' => 'تمثيل قضائي في المنازعات التجارية', 'name_en' => 'Commercial Litigation Representation', 'price' => 5000, 'duration' => '30 يوم'],
                ['name_ar' => 'صياغة المذكرات واللوائح الجوابية التجارية', 'name_en' => 'Drafting Commercial Pleadings', 'price' => 2500, 'duration' => '7 أيام'],
                ['name_ar' => 'تسوية الديون والنزاعات المالية ودياً', 'name_en' => 'Amicable Debt Settlement', 'price' => 3000, 'duration' => '14 يوم'],
                ['name_ar' => 'استشارات العقود والصفقات التجارية', 'name_en' => 'Commercial Contract Consultation', 'price' => 1500, 'duration' => '3 أيام'],
            ],
            'قضايا الشركات' => [
                ['name_ar' => 'تسوية منازعات الشركاء وحصص الشراكة', 'name_en' => 'Partners Dispute Settlement', 'price' => 4000, 'duration' => '21 يوم'],
                ['name_ar' => 'صياغة وتعديل عقود التأسيس وقرارات الشركاء', 'name_en' => 'Articles of Association Drafting', 'price' => 2000, 'duration' => '5 أيام'],
                ['name_ar' => 'التمثيل القضائي للشركات أمام المحاكم', 'name_en' => 'Corporate Legal Representation', 'price' => 5000, 'duration' => '30 يوم'],
                ['name_ar' => 'الفحص والتدقيق القانوني النافي للجهالة للشركات', 'name_en' => 'Legal Due Diligence', 'price' => 6000, 'duration' => '15 يوم'],
            ],
            'تأسيس الشركات والتحول النظامي' => [
                ['name_ar' => 'إجراءات تأسيس الشركات وإصدار التراخيص', 'name_en' => 'Company Incorporation & Licensing', 'price' => 3000, 'duration' => '7 أيام'],
                ['name_ar' => 'التحول النظامي للكيانات والشركات', 'name_en' => 'Corporate Restructuring & Conversion', 'price' => 5000, 'duration' => '20 يوم'],
                ['name_ar' => 'إعداد اللوائح الداخلية وسياسات العمل', 'name_en' => 'Internal Corporate Bylaws Drafting', 'price' => 2500, 'duration' => '10 أيام'],
            ],
            'حوكمة الشركات والامتثال' => [
                ['name_ar' => 'إعداد أدلة ولوائح الحوكمة المؤسسية', 'name_en' => 'Corporate Governance Framework', 'price' => 6000, 'duration' => '25 يوم'],
                ['name_ar' => 'تقارير الامتثال النظامي والرقابي', 'name_en' => 'Regulatory Compliance Reports', 'price' => 3500, 'duration' => '14 يوم'],
                ['name_ar' => 'تنظيم اجتماعات مجالس الإدارة والجمعيات', 'name_en' => 'Board Meetings & General Assembly Setup', 'price' => 2000, 'duration' => '5 أيام'],
            ],
            'عمليات الاندماج والاستحواذ' => [
                ['name_ar' => 'إعداد ومراجعة اتفاقيات الاندماج والاستحواذ', 'name_en' => 'M&A Agreements Drafting', 'price' => 8000, 'duration' => '30 يوم'],
                ['name_ar' => 'التحقق القانوني واستيفاء الموافقات النظامية', 'name_en' => 'Regulatory Approvals & Clearance', 'price' => 5000, 'duration' => '20 يوم'],
            ],
            'الإفلاس وإعادة التنظيم المالي' => [
                ['name_ar' => 'تقديم طلبات افتتاح إجراءات الإفلاس', 'name_en' => 'Bankruptcy Proceedings Filing', 'price' => 6000, 'duration' => '30 يوم'],
                ['name_ar' => 'إعداد خطة ومقترح إعادة التنظيم المالي', 'name_en' => 'Financial Restructuring Proposal', 'price' => 8000, 'duration' => '45 يوم'],
                ['name_ar' => 'تمثيل الدائنين والمدينين أمام لجان ومحاكم الإفلاس', 'name_en' => 'Creditor/Debtor Representation', 'price' => 4500, 'duration' => '21 يوم'],
            ],
            'الاستثمار الأجنبي' => [
                ['name_ar' => 'استخراج وتعديل تراخيص الاستثمار الأجنبي (MISA)', 'name_en' => 'Foreign Investment License (MISA)', 'price' => 4000, 'duration' => '10 أيام'],
                ['name_ar' => 'تأسيس فروع الشركات الأجنبية في المملكة', 'name_en' => 'Foreign Branch Establishment', 'price' => 5000, 'duration' => '15 يوم'],
                ['name_ar' => 'استشارات الحوافز والإعفاءات الاستثمارية', 'name_en' => 'Investment Incentives Advisory', 'price' => 2000, 'duration' => '5 أيام'],
            ],
            'الامتياز التجاري (الفرنشايز)' => [
                ['name_ar' => 'صياغة ومراجعة عقود الامتياز التجاري (الفرنشايز)', 'name_en' => 'Franchise Agreements Drafting', 'price' => 3500, 'duration' => '10 أيام'],
                ['name_ar' => 'إعداد وثيقة الإفصاح وقيد الامتياز التجاري', 'name_en' => 'Franchise Disclosure Document', 'price' => 3000, 'duration' => '7 أيام'],
            ],
            'الوكالات التجارية' => [
                ['name_ar' => 'تسجيل وقيد الوكالات التجارية في وزارة التجارة', 'name_en' => 'Commercial Agency Registration', 'price' => 2500, 'duration' => '7 أيام'],
                ['name_ar' => 'صياغة ومراجعة عقود الوكالة والتوزيع', 'name_en' => 'Agency & Distribution Contracts', 'price' => 2000, 'duration' => '5 أيام'],
            ],
            'عقود التوزيع والامتياز' => [
                ['name_ar' => 'صياغة اتفاقيات التوزيع الحصري والتوريد', 'name_en' => 'Exclusive Distribution Agreements', 'price' => 2500, 'duration' => '7 أيام'],
                ['name_ar' => 'تسوية منازعات التوزيع وسلاسل الإمداد', 'name_en' => 'Distribution Disputes Settlement', 'price' => 3500, 'duration' => '15 يوم'],
            ],
            'الملكية الفكرية' => [
                ['name_ar' => 'تسجيل وحماية حقوق الملكية الفكرية', 'name_en' => 'IP Registration & Protection', 'price' => 2000, 'duration' => '10 أيام'],
                ['name_ar' => 'ملاحقة التعديات على حقوق الملكية الفكرية', 'name_en' => 'IP Infringement Litigation', 'price' => 4500, 'duration' => '25 يوم'],
            ],
            'العلامات التجارية' => [
                ['name_ar' => 'تسجيل وإيداع العلامات التجارية محلياً ودولياً', 'name_en' => 'Trademark Registration & Filing', 'price' => 1500, 'duration' => '5 أيام'],
                ['name_ar' => 'الاعتراض على العلامات التجارية ومتابعة الطعون', 'name_en' => 'Trademark Opposition & Appeals', 'price' => 3000, 'duration' => '15 يوم'],
            ],
            'براءات الاختراع' => [
                ['name_ar' => 'إيداع وتسجيل طلبات براءات الاختراع', 'name_en' => 'Patent Filing & Registration', 'price' => 3500, 'duration' => '20 يوم'],
                ['name_ar' => 'صياغة عقود ترخيص واستغلال براءات الاختراع', 'name_en' => 'Patent Licensing Agreements', 'price' => 3000, 'duration' => '10 أيام'],
            ],
            'حقوق المؤلف والحقوق المجاورة' => [
                ['name_ar' => 'تسجيل وتوثيق المصنفات وحقوق المؤلف', 'name_en' => 'Copyright Registration & Protection', 'price' => 1500, 'duration' => '7 أيام'],
                ['name_ar' => 'صياغة عقود النشر والإنتاج والتنازل عن الحقوق', 'name_en' => 'Publishing & Copyright Assignment Contracts', 'price' => 2000, 'duration' => '5 أيام'],
            ],
            'التجارة الإلكترونية' => [
                ['name_ar' => 'صياغة الشروط والأحكام وسياسات المتاجر الإلكترونية', 'name_en' => 'E-Commerce Terms & Conditions', 'price' => 1500, 'duration' => '4 أيام'],
                ['name_ar' => 'الامتثال لنظام التجارة الإلكترونية وتراخيص المنصات', 'name_en' => 'E-Commerce Regulatory Compliance', 'price' => 2500, 'duration' => '7 أيام'],
            ],
            'الجرائم المعلوماتية' => [
                ['name_ar' => 'الترافع في قضايا مكافحة جرائم المعلوماتية', 'name_en' => 'Cybercrime Legal Defense', 'price' => 4000, 'duration' => '20 يوم'],
                ['name_ar' => 'إثبات الأدلة الرقمية والتعديات الإلكترونية', 'name_en' => 'Digital Evidence & Cyber Dispute Proof', 'price' => 3000, 'duration' => '10 أيام'],
            ],
            'حماية البيانات والخصوصية' => [
                ['name_ar' => 'إعداد سياسات الخصوصية وحماية البيانات الشخصية', 'name_en' => 'Data Privacy Policy (PDPL)', 'price' => 2500, 'duration' => '7 أيام'],
                ['name_ar' => 'تدقيق الامتثال لنظام حماية البيانات الشخصية', 'name_en' => 'Data Privacy Compliance Audit', 'price' => 4000, 'duration' => '15 يوم'],
            ],
            'العقود وصياغتها ومراجعتها' => [
                ['name_ar' => 'صياغة العقود التجارية والمدنية باللغتين', 'name_en' => 'Bilingual Contract Drafting', 'price' => 1800, 'duration' => '5 أيام'],
                ['name_ar' => 'مراجعة وتدقيق العقود والاتفاقيات والملاحق', 'name_en' => 'Contract Review & Risk Analysis', 'price' => 1200, 'duration' => '3 أيام'],
                ['name_ar' => 'صياغة اتفاقيات عدم الإفصاح (NDA) ومذكرات التفاهم', 'name_en' => 'NDA & MOU Drafting', 'price' => 1000, 'duration' => '2 يوم'],
            ],
            'التحكيم التجاري' => [
                ['name_ar' => 'صياغة اتفاقيات ومشارطات التحكيم', 'name_en' => 'Arbitration Agreement Drafting', 'price' => 2500, 'duration' => '5 أيام'],
                ['name_ar' => 'تمثيل الأطراف أمام هيئات ومراكز التحكيم', 'name_en' => 'Arbitration Tribunal Representation', 'price' => 6000, 'duration' => '30 يوم'],
                ['name_ar' => 'تنفيذ أحكام التحكيم المحلية والأجنبية', 'name_en' => 'Arbitral Award Enforcement', 'price' => 4000, 'duration' => '15 يوم'],
            ],
            'الوساطة وتسوية المنازعات' => [
                ['name_ar' => 'إدارة جلسات الوساطة والصلح الودي', 'name_en' => 'Mediation & Conciliation Sessions', 'price' => 2500, 'duration' => '7 أيام'],
                ['name_ar' => 'صياغة محاضر واتفاقيات الصلح المعتمدة', 'name_en' => 'Settlement Agreement Drafting', 'price' => 1800, 'duration' => '3 أيام'],
            ],
            'التنفيذ وإجراءات محاكم التنفيذ' => [
                ['name_ar' => 'تقديم ومتابعة طلبات التنفيذ المالية والسندات', 'name_en' => 'Enforcement Requests Filing', 'price' => 2000, 'duration' => '10 أيام'],
                ['name_ar' => 'منازعات التنفيذ الوقتية والموضوعية', 'name_en' => 'Enforcement Dispute Defense', 'price' => 3500, 'duration' => '15 يوم'],
                ['name_ar' => 'إجراءات استرداد وتتبع الأموال المنفذة', 'name_en' => 'Asset Tracing & Recovery', 'price' => 4000, 'duration' => '20 يوم'],
            ],
            'العقارات' => [
                ['name_ar' => 'استشارات الصكوك والإفراغ العقاري والملكية', 'name_en' => 'Real Estate Title & Deed Advisory', 'price' => 1500, 'duration' => '5 أيام'],
                ['name_ar' => 'الترافع في النزاعات العقارية وحقوق الارتفاق', 'name_en' => 'Real Estate Litigation', 'price' => 4500, 'duration' => '25 يوم'],
            ],
            'التطوير العقاري' => [
                ['name_ar' => 'صياغة اتفاقيات التطوير العقاري والصناديق العقارية', 'name_en' => 'Real Estate Development Contracts', 'price' => 5000, 'duration' => '15 يوم'],
                ['name_ar' => 'عقود البيع على الخارطة وتراخيص وافي', 'name_en' => 'Off-Plan Sales Contracts (Wafi)', 'price' => 3500, 'duration' => '10 أيام'],
            ],
            'المقاولات والإنشاءات' => [
                ['name_ar' => 'صياغة ومراجعة عقود المقاولات والفيدك (FIDIC)', 'name_en' => 'FIDIC & Construction Contracts', 'price' => 3500, 'duration' => '10 أيام'],
                ['name_ar' => 'مطالبات التأخير ومنازعات مستخلصات المقاولين', 'name_en' => 'Construction Delay & Payment Claims', 'price' => 5000, 'duration' => '20 يوم'],
            ],
            'نزع الملكية والتعويضات' => [
                ['name_ar' => 'متابعة إجراءات وتظلمات نزع الملكية للمنفعة العامة', 'name_en' => 'Eminent Domain & Expropriation Appeals', 'price' => 3500, 'duration' => '15 يوم'],
                ['name_ar' => 'المطالبة بالتعويضات العادلة أمام ديوان المظالم', 'name_en' => 'Expropriation Compensation Claims', 'price' => 4500, 'duration' => '25 يوم'],
            ],
            'القضايا العمالية' => [
                ['name_ar' => 'إعداد وصياغة لوائح تنظيم العمل المعتمدة', 'name_en' => 'Workplace Regulations Drafting', 'price' => 2000, 'duration' => '7 أيام'],
                ['name_ar' => 'الترافع والدفاع أمام المحاكم واللجان العمالية', 'name_en' => 'Labor Court Representation', 'price' => 3000, 'duration' => '20 يوم'],
                ['name_ar' => 'تسوية الخلافات العمالية الودية ومخالصات نهاية الخدمة', 'name_en' => 'Labor Dispute & Settlement Agreements', 'price' => 1500, 'duration' => '5 أيام'],
            ],
            'التأمينات الاجتماعية' => [
                ['name_ar' => 'الاعتراض على مخالفات واشتراكات التأمينات الاجتماعية', 'name_en' => 'Social Insurance Appeals (GOSI)', 'price' => 2000, 'duration' => '10 أيام'],
                ['name_ar' => 'استشارات استحقاقات وإصابات العمل والتقاعد', 'name_en' => 'Pension & Work Injury Claims', 'price' => 1500, 'duration' => '5 أيام'],
            ],
            'الأحوال الشخصية' => [
                ['name_ar' => 'الترافع في دعاوى الأحوال الشخصية والفسخ والخلع', 'name_en' => 'Personal Status Litigation', 'price' => 3500, 'duration' => '20 يوم'],
                ['name_ar' => 'صياغة وتوثيق الاتفاقيات الأسرية وعقود الزواج', 'name_en' => 'Family Agreements Drafting', 'price' => 1500, 'duration' => '5 أيام'],
            ],
            'الحضانة والزيارة والنفقة' => [
                ['name_ar' => 'الترافع في دعاوى إثبات الحضانة والزيارة والنفقة', 'name_en' => 'Custody, Visitation & Alimony Claims', 'price' => 3000, 'duration' => '15 يوم'],
                ['name_ar' => 'تنفيذ أحكام الحضانة والرؤية والنفقة', 'name_en' => 'Family Court Judgment Enforcement', 'price' => 2000, 'duration' => '10 أيام'],
            ],
            'التركات والمواريث' => [
                ['name_ar' => 'حصر وقسمة التركات الرضائية والقضائية', 'name_en' => 'Estate Inventory & Inheritance Division', 'price' => 5000, 'duration' => '30 يوم'],
                ['name_ar' => 'تصفية الشركات والأموال المشاعة للتركات', 'name_en' => 'Estate Liquidation & Settlement', 'price' => 6000, 'duration' => '45 يوم'],
            ],
            'الأوقاف والوصايا' => [
                ['name_ar' => 'صياغة وتوثيق صكوك الأوقاف والوصايا واللوائح', 'name_en' => 'Endowment & Will Deeds Drafting', 'price' => 3000, 'duration' => '10 أيام'],
                ['name_ar' => 'حوكمة الأوقاف ونظارة الوقف والمنازعات الوقفية', 'name_en' => 'Endowment Governance & Disputes', 'price' => 4500, 'duration' => '25 يوم'],
            ],
            'القضايا الجزائية' => [
                ['name_ar' => 'حضور التحقيقات والاستجوابات أمام النيابة العامة', 'name_en' => 'Public Prosecution Interrogation Attendance', 'price' => 3000, 'duration' => '7 أيام'],
                ['name_ar' => 'الترافع والدفاع الجنائي أمام المحاكم الجزائية', 'name_en' => 'Criminal Court Defense', 'price' => 5000, 'duration' => '30 يوم'],
            ],
            'غسل الأموال وتمويل الإرهاب' => [
                ['name_ar' => 'الدفاع والترافع في قضايا غسل الأموال والجرائم المالية', 'name_en' => 'Anti-Money Laundering (AML) Defense', 'price' => 8000, 'duration' => '30 يوم'],
                ['name_ar' => 'تقييم الامتثال ومكافحة غسل الأموال للمؤسسات', 'name_en' => 'AML Compliance Assessment', 'price' => 5000, 'duration' => '15 يوم'],
            ],
            'مكافحة الفساد والرشوة' => [
                ['name_ar' => 'التمثيل والدفاع في قضايا نزاهة ومكافحة الفساد', 'name_en' => 'Anti-Corruption & Bribery Legal Defense', 'price' => 7000, 'duration' => '30 يوم'],
                ['name_ar' => 'استشارات وسياسات مكافحة الاحتيال والفساد المؤسسي', 'name_en' => 'Anti-Fraud & Ethics Policies', 'price' => 4000, 'duration' => '15 يوم'],
            ],
            'القضايا الإدارية (ديوان المظالم)' => [
                ['name_ar' => 'الطعن في القرارات الإدارية السلبية والإيجابية', 'name_en' => 'Administrative Decisions Appeal', 'price' => 4500, 'duration' => '25 يوم'],
                ['name_ar' => 'دعاوى التعويض عن الأضرار ضد الجهات الحكومية', 'name_en' => 'Administrative Compensation Claims', 'price' => 5000, 'duration' => '30 يوم'],
            ],
            'الوظيفة العامة والتأديب' => [
                ['name_ar' => 'التظلم والترافع في القرارات التأديبية للموظف العام', 'name_en' => 'Civil Service Disciplinary Appeals', 'price' => 3000, 'duration' => '15 يوم'],
                ['name_ar' => 'المطالبة بالبدلات والمستحقات الوظيفية والترقيات', 'name_en' => 'Employee Allowances & Promotion Claims', 'price' => 2500, 'duration' => '15 يوم'],
            ],
            'المناقصات والمشتريات الحكومية' => [
                ['name_ar' => 'الاعتراض على نتائج الترسية ومنازعات المنافسات الحكومية', 'name_en' => 'Government Procurement Disputes', 'price' => 4000, 'duration' => '15 يوم'],
                ['name_ar' => 'صياغة وتفسير عقود المشتريات والمنافسات الحكومية', 'name_en' => 'Government Tenders Contract Advisory', 'price' => 3000, 'duration' => '10 أيام'],
            ],
            'الزكاة والضرائب' => [
                ['name_ar' => 'الاعتراض أمام لجان الفصل في المخالفات والمنازعات الضريبية', 'name_en' => 'Tax & Zakat Appeals Committee Defense', 'price' => 5000, 'duration' => '25 يوم'],
                ['name_ar' => 'الاستشارات الضريبية والزكوية الوقائية للشركات', 'name_en' => 'Preventive Tax Legal Advisory', 'price' => 2500, 'duration' => '7 أيام'],
            ],
            'الجمارك والتجارة الدولية' => [
                ['name_ar' => 'الاعتراض على القرارات الجمركية والمخالفات التقييمية', 'name_en' => 'Customs Tariff & Valuation Appeals', 'price' => 3500, 'duration' => '15 يوم'],
                ['name_ar' => 'استشارات الاتفاقيات التجارية الدولية والتجارة الحرة', 'name_en' => 'International Trade Agreements Advisory', 'price' => 3000, 'duration' => '10 أيام'],
            ],
            'البنوك والتمويل' => [
                ['name_ar' => 'الترافع أمام لجان تسوية المنازعات والمخالفات المصرفية', 'name_en' => 'Banking Disputes Committee Representation', 'price' => 5000, 'duration' => '25 يوم'],
                ['name_ar' => 'صياغة ومراجعة عقود التمويل الإسلامي والضمانات البنكية', 'name_en' => 'Islamic Finance & Securities Contracts', 'price' => 3500, 'duration' => '10 أيام'],
            ],
            'التأمين' => [
                ['name_ar' => 'الترافع أمام لجان الفصل في المنازعات والمخالفات التأمينية', 'name_en' => 'Insurance Disputes Committee Representation', 'price' => 4000, 'duration' => '20 يوم'],
                ['name_ar' => 'مطالبات التعويض التأميني للمركبات والممتلكات والصحة', 'name_en' => 'Insurance Compensation Claims', 'price' => 2500, 'duration' => '14 يوم'],
            ],
            'الأوراق المالية وسوق المال' => [
                ['name_ar' => 'الترافع أمام لجان الفصل في منازعات الأوراق المالية (CRSD)', 'name_en' => 'Securities Disputes Committee Representation', 'price' => 7000, 'duration' => '30 يوم'],
                ['name_ar' => 'الامتثال للوائح هيئة السوق المالية ومتطلبات الطرح', 'name_en' => 'CMA Compliance & Offering Advisory', 'price' => 6000, 'duration' => '20 يوم'],
            ],
            'النقل والخدمات اللوجستية' => [
                ['name_ar' => 'صياغة عقود النقل والخدمات اللوجستية والتخزين', 'name_en' => 'Logistics & Warehousing Contracts', 'price' => 2500, 'duration' => '7 أيام'],
                ['name_ar' => 'منازعات عقود النقل البري وتلف وفقدان الشحنات', 'name_en' => 'Cargo Loss & Transit Claims', 'price' => 3500, 'duration' => '15 يوم'],
            ],
            'القانون البحري' => [
                ['name_ar' => 'صياغة وتدقيق عقود الشحن والتأجير البحري وسندات الشحن', 'name_en' => 'Maritime Charterparty & Bills of Lading', 'price' => 4500, 'duration' => '15 يوم'],
                ['name_ar' => 'منازعات الحوادث البحرية وحجز السفن والتعويضات', 'name_en' => 'Ship Arrest & Maritime Casualty Disputes', 'price' => 8000, 'duration' => '30 يوم'],
            ],
            'قانون الطيران' => [
                ['name_ar' => 'عقود شراء وتأجير وصيانة الطائرات وتراخيص الطيران', 'name_en' => 'Aviation Leasing & Maintenance Contracts', 'price' => 7000, 'duration' => '25 يوم'],
                ['name_ar' => 'منازعات الناقلين الجويين وحقوق المسافرين والشحن الجوي', 'name_en' => 'Air Carrier & Cargo Claims', 'price' => 4000, 'duration' => '20 يوم'],
            ],
            'الطاقة والتعدين' => [
                ['name_ar' => 'صياغة اتفاقيات التنقيب والاستكشاف وتراخيص التعدين', 'name_en' => 'Mining Licenses & Concession Agreements', 'price' => 8000, 'duration' => '30 يوم'],
                ['name_ar' => 'عقود مشاريع الطاقة المتجددة ومحطات التوليد', 'name_en' => 'Renewable Energy Project Contracts', 'price' => 7000, 'duration' => '25 يوم'],
            ],
            'القطاع الصحي والأخطاء الطبية' => [
                ['name_ar' => 'الترافع أمام الهيئات الصحية الشرعية في دعاوى الأخطاء الطبية', 'name_en' => 'Medical Malpractice Defense & Claims', 'price' => 5000, 'duration' => '30 يوم'],
                ['name_ar' => 'تراخيص المنشآت الصحية وعقود التشغيل الطبي', 'name_en' => 'Healthcare Licensing & Operating Agreements', 'price' => 3500, 'duration' => '15 يوم'],
            ],
            'التعليم والجامعات' => [
                ['name_ar' => 'لوائح وحوكمة المنشآت التعليمية والجامعات الأهلية', 'name_en' => 'Educational Institution Bylaws & Governance', 'price' => 4000, 'duration' => '20 يوم'],
                ['name_ar' => 'عقود الخدمات التعليمية وحقوق الملكية للمناهج والبحوث', 'name_en' => 'Educational & Research IP Contracts', 'price' => 2500, 'duration' => '10 أيام'],
            ],
            'الجمعيات الأهلية والقطاع غير الربحي' => [
                ['name_ar' => 'تأسيس وترخيص الجمعيات والمؤسسات الأهلية وغير الربحية', 'name_en' => 'Non-Profit Organization Incorporation', 'price' => 3000, 'duration' => '15 يوم'],
                ['name_ar' => 'حوكمة القطاع غير الربحي ولوائح العمل والامتثال', 'name_en' => 'NPO Governance & Compliance Framework', 'price' => 3500, 'duration' => '20 يوم'],
            ],
            'الاستشارات القانونية والتمثيل القضائي' => [
                ['name_ar' => 'تقديم الاستشارات القانونية والشرعية الشاملة', 'name_en' => 'Comprehensive Legal Consultation', 'price' => 1500, 'duration' => '3 أيام'],
                ['name_ar' => 'التمثيل القضائي وحضور الجلسات والترافع الشامل', 'name_en' => 'Full Litigation Representation', 'price' => 5000, 'duration' => '30 يوم'],
                ['name_ar' => 'إعداد الرأي القانوني والدراسات النظامية المتخصصة', 'name_en' => 'Legal Opinions & Research Memorandums', 'price' => 2500, 'duration' => '7 أيام'],
            ],

            // مكاتب الخدمات والتعقيب (Services)
            'الخدمات الحكومية' => [
                ['name_ar' => 'إصدار وتجديد الرخص البلدية والمهنية والتجارية', 'name_en' => 'Municipal & Commercial License Issuance', 'price' => 500, 'duration' => '3 أيام'],
                ['name_ar' => 'استخراج التراخيص الصناعية وتصاريح الدفاع المدني', 'name_en' => 'Industrial & Civil Defense Permits', 'price' => 1200, 'duration' => '7 أيام'],
                ['name_ar' => 'خدمات هيئة الغذاء والدواء وتسجيل المنتجات', 'name_en' => 'SFDA Registration Services', 'price' => 1500, 'duration' => '10 أيام'],
                ['name_ar' => 'تحديث بيانات المنشأة لدى كافة الجهات الحكومية', 'name_en' => 'Government Data Update', 'price' => 400, 'duration' => '2 يوم'],
            ],
            'خدمات التعقيب' => [
                ['name_ar' => 'متابعة وتخليص معاملات وزارة التجارة والغرف التجارية', 'name_en' => 'Ministry of Commerce Expediting', 'price' => 600, 'duration' => '3 أيام'],
                ['name_ar' => 'معاملات وزارة الموارد البشرية ومنصات قوى ومدد', 'name_en' => 'Qiwa & Mudad Platform Transactions', 'price' => 500, 'duration' => '2 يوم'],
                ['name_ar' => 'إنهاء إجراءات الجوازات ونقل الكفالات والتأشيرات', 'name_en' => 'Passports & Visa Processing', 'price' => 400, 'duration' => '2 يوم'],
                ['name_ar' => 'تصديق وتوثيق الوثائق والشهادات الرسمية', 'name_en' => 'Document Attestation & Certification', 'price' => 300, 'duration' => '1 يوم'],
            ],
            'خدمات التوثيق' => [
                ['name_ar' => 'توثيق وإفراغ العقارات والرهون العقارية فورياً', 'name_en' => 'Instant Real Estate Notarization', 'price' => 800, 'duration' => '1 يوم'],
                ['name_ar' => 'توثيق عقود تأسيس الشركات وقرارات تعديل الشركاء', 'name_en' => 'Articles of Association Notarization', 'price' => 600, 'duration' => '1 يوم'],
                ['name_ar' => 'إصدار وفسخ وتعديل الوكالات الشرعية للأفراد والشركات', 'name_en' => 'Power of Attorney (POA) Issuance', 'price' => 300, 'duration' => '1 يوم'],
                ['name_ar' => 'توثيق الإقرارات المالية وعقود الإيجار والاتفاقيات', 'name_en' => 'Financial Declaration & Lease Notarization', 'price' => 400, 'duration' => '1 يوم'],
            ],
            'خدمات تأسيس الشركات' => [
                ['name_ar' => 'إصدار السجل التجاري الموحد وعقد التأسيس الإلكتروني', 'name_en' => 'Commercial Registration & Electronic Articles', 'price' => 1000, 'duration' => '2 يوم'],
                ['name_ar' => 'فتح وتفعيل الملفات الحكومية الموحدة (العمل، الزكاة، التأمينات)', 'name_en' => 'Government Files Opening & Activation', 'price' => 800, 'duration' => '3 أيام'],
                ['name_ar' => 'استخراج شهادات السعودة والتأمينات والزكاة والاشتراكات', 'name_en' => 'Saudization & Compliance Certificates', 'price' => 500, 'duration' => '2 يوم'],
                ['name_ar' => 'حجز الأسماء التجارية وتعديل الأنشطة التجارية', 'name_en' => 'Trade Name Reservation & Activity Update', 'price' => 400, 'duration' => '1 يوم'],
            ],

            // شركات التخليص الجمركي (Customs)
            'التخليص الجمركي' => [
                ['name_ar' => 'التخليص الجمركي للشحنات البحرية والحاويات', 'name_en' => 'Sea Freight & Container Clearance', 'price' => 1200, 'duration' => '3 أيام'],
                ['name_ar' => 'التخليص الجمركي للشحنات الجوية السريعة', 'name_en' => 'Air Freight Customs Clearance', 'price' => 800, 'duration' => '2 يوم'],
                ['name_ar' => 'التخليص الجمركي البري عبر المنافذ والموانئ الجافة', 'name_en' => 'Land Border Customs Clearance', 'price' => 900, 'duration' => '2 يوم'],
                ['name_ar' => 'خدمات الفسح المسبق والمستودعات الجمركية', 'name_en' => 'Pre-Clearance & Bonded Warehousing', 'price' => 1500, 'duration' => '4 أيام'],
            ],
            'الاستيراد والتصدير' => [
                ['name_ar' => 'تسجيل وإصدار شهادات المطابقة والمنتجات عبر منصة سابر', 'name_en' => 'SABER Certificate of Conformity', 'price' => 700, 'duration' => '3 أيام'],
                ['name_ar' => 'استخراج شهادات المنشأ ورخص الاستيراد والتصدير', 'name_en' => 'Certificate of Origin & Export Licenses', 'price' => 500, 'duration' => '2 يوم'],
                ['name_ar' => 'إدارة وثائق الشحن والتأمين والمطابقة الدولية', 'name_en' => 'Shipping & Insurance Documentation', 'price' => 800, 'duration' => '3 أيام'],
            ],
            'الاستشارات الجمركية' => [
                ['name_ar' => 'تحديد البنود الجمركية والتعريفة الجمركية المتكاملة', 'name_en' => 'HS Code Classification & Tariff Advisory', 'price' => 600, 'duration' => '2 يوم'],
                ['name_ar' => 'استشارات الإعفاءات الجمركية والاتفاقيات التجارية الإقليمية', 'name_en' => 'Customs Exemptions & FTA Advisory', 'price' => 1200, 'duration' => '5 أيام'],
                ['name_ar' => 'معالجة وتسوية الاعتراضات والنزاعات الجمركية', 'name_en' => 'Customs Penalty & Dispute Settlement', 'price' => 2000, 'duration' => '10 أيام'],
            ],
            'إنهاء الإجراءات الجمركية' => [
                ['name_ar' => 'المعاينة والفحص الفني وسحب العينات المخبرية', 'name_en' => 'Customs Physical Inspection & Lab Sampling', 'price' => 600, 'duration' => '2 يوم'],
                ['name_ar' => 'سداد الرسوم الجمركية والضرائب وإصدار إذن الفسح النهائي', 'name_en' => 'Duty Payment & Final Release Permit', 'price' => 400, 'duration' => '1 يوم'],
                ['name_ar' => 'خدمات نقل وتعتيق وتوصيل الحاويات من الميناء للمستودع', 'name_en' => 'Port Drayage & Container Delivery', 'price' => 1000, 'duration' => '2 يوم'],
            ],

            // مكاتب المحاسبة والضرائب (Accounting)
            'المحاسبة المالية' => [
                ['name_ar' => 'إمساك الدفاتر والسجلات المحاسبية الشهرية والسنوية', 'name_en' => 'Bookkeeping & General Ledger Management', 'price' => 1500, 'duration' => '30 يوم'],
                ['name_ar' => 'إعداد قيود اليومية وموازين المراجعة الدورية', 'name_en' => 'Journal Entries & Trial Balance Preparation', 'price' => 1000, 'duration' => '7 أيام'],
                ['name_ar' => 'تسوية ومطابقة الحسابات البنكية والموردين والعملاء', 'name_en' => 'Bank & AR/AP Reconciliations', 'price' => 800, 'duration' => '5 أيام'],
            ],
            'مراجعة الحسابات' => [
                ['name_ar' => 'التدقيق المالي المستقل وإصدار تقرير المراجع المعتمد', 'name_en' => 'External Financial Audit & Certified Report', 'price' => 5000, 'duration' => '20 يوم'],
                ['name_ar' => 'فحص وتقييم نظم الرقابة والامتثال المالي الداخلي', 'name_en' => 'Internal Control Review & Assessment', 'price' => 3500, 'duration' => '15 يوم'],
                ['name_ar' => 'الفحص النافي للجهالة المالي (Financial Due Diligence)', 'name_en' => 'Financial Due Diligence', 'price' => 6000, 'duration' => '20 يوم'],
            ],
            'الزكاة والضرائب' => [
                ['name_ar' => 'إعداد وتقديم الإقرارات الزكوية وإصدار الشهادة الزكوية', 'name_en' => 'Zakat Declaration & Certificate Issuance', 'price' => 2000, 'duration' => '7 أيام'],
                ['name_ar' => 'إعداد ورفع إقرارات ضريبة القيمة المضافة (VAT)', 'name_en' => 'VAT Return Preparation & Filing', 'price' => 1000, 'duration' => '3 أيام'],
                ['name_ar' => 'إعداد ملفات التسعير التحويلي وضريبة الاستقطاع والدخل', 'name_en' => 'Transfer Pricing & WHT Advisory', 'price' => 3500, 'duration' => '14 يوم'],
                ['name_ar' => 'الاعتراض والتسوية على الفروقات والربوط الضريبية', 'name_en' => 'ZATCA Tax Assessment Objections', 'price' => 3000, 'duration' => '15 يوم'],
            ],
            'إعداد القوائم المالية' => [
                ['name_ar' => 'إعداد القوائم المالية الكاملة وفق المعايير الدولية (IFRS)', 'name_en' => 'IFRS Financial Statements Preparation', 'price' => 3000, 'duration' => '10 أيام'],
                ['name_ar' => 'إعداد الموازنات التقديرية وتوقعات التدفقات النقدية', 'name_en' => 'Budgeting & Cash Flow Forecasting', 'price' => 2500, 'duration' => '7 أيام'],
                ['name_ar' => 'تحليل المؤشرات المالية وتقارير الأداء المالي للإدارة', 'name_en' => 'Financial Ratio Analysis & Management Reports', 'price' => 1800, 'duration' => '5 أيام'],
            ],
            'المحاسبة الإدارية' => [
                ['name_ar' => 'تحليل التكاليف وهياكل التسعير وهوامش الربحية', 'name_en' => 'Cost Accounting & Pricing Analysis', 'price' => 2500, 'duration' => '10 أيام'],
                ['name_ar' => 'إعداد دراسات الجدوى والخطط المالية للمشاريع', 'name_en' => 'Financial Feasibility Studies & Modeling', 'price' => 4500, 'duration' => '15 يوم'],
                ['name_ar' => 'تطوير الدليل المحاسبي والسياسات والإجراءات المالية', 'name_en' => 'Chart of Accounts & SOPs Design', 'price' => 3000, 'duration' => '14 يوم'],
            ],

            // الاستشارات الهندسية (Engineering)
            'الهندسة المعمارية' => [
                ['name_ar' => 'التصميم المعماري للمباني السكنية والتجارية والمشاريع', 'name_en' => 'Architectural Design for Buildings', 'price' => 5000, 'duration' => '20 يوم'],
                ['name_ar' => 'إعداد المخططات التنفيذية وتفاصيل الواجهات الخارجية', 'name_en' => 'Working Drawings & Façade Design', 'price' => 3500, 'duration' => '14 يوم'],
                ['name_ar' => 'التصميم الداخلي والديكور والمخططات الثلاثية الأبعاد (3D)', 'name_en' => 'Interior Design & 3D Visualizations', 'price' => 3000, 'duration' => '12 يوم'],
                ['name_ar' => 'استخراج وتعديل رخص البناء والترميم عبر منصة بلدي', 'name_en' => 'Building Permits Issuance (Balady)', 'price' => 2000, 'duration' => '7 أيام'],
            ],
            'الهندسة المدنية' => [
                ['name_ar' => 'التصميم الإنشائي وحساب الأحمال والهياكل الخرسانية والمعدنية', 'name_en' => 'Structural Design & Analysis', 'price' => 4000, 'duration' => '15 يوم'],
                ['name_ar' => 'فحص وتقارير التربة والأساسات والسلامة الإنشائية', 'name_en' => 'Soil Investigation & Structural Safety Reports', 'price' => 2500, 'duration' => '7 أيام'],
                ['name_ar' => 'الإشراف الهندسي الموقعي على أعمال الصب والخرسانة', 'name_en' => 'Site Engineering Supervision', 'price' => 3000, 'duration' => '30 يوم'],
                ['name_ar' => 'تقييم وتدعيم المباني القائمة وتأهيل المنشآت', 'name_en' => 'Building Retrofitting & Assessment', 'price' => 3500, 'duration' => '14 يوم'],
            ],
            'الهندسة الكهربائية' => [
                ['name_ar' => 'تصميم شبكات الإنارة وتوزيع القوى وجداول الأحمال', 'name_en' => 'Electrical Lighting & Power Distribution Design', 'price' => 2500, 'duration' => '10 أيام'],
                ['name_ar' => 'تصميم أنظمة التيار الخفيف (كاميرات، شبكات، إنذار، تحكم)', 'name_en' => 'Low Current Systems Design', 'price' => 2000, 'duration' => '7 أيام'],
                ['name_ar' => 'مخططات محطات التحويل والتغذية وموافقات شركة الكهرباء', 'name_en' => 'Substation Design & SEC Approvals', 'price' => 3500, 'duration' => '14 يوم'],
            ],
            'الهندسة الميكانيكية' => [
                ['name_ar' => 'تصميم أنظمة التكييف والتهوية وتوزيع الهواء (HVAC)', 'name_en' => 'HVAC Mechanical Systems Design', 'price' => 3000, 'duration' => '10 أيام'],
                ['name_ar' => 'تصميم أنظمة مكافحة الحريق والإنذار وموافقات السلامة', 'name_en' => 'Firefighting & Life Safety Systems Design', 'price' => 2500, 'duration' => '8 أيام'],
                ['name_ar' => 'تصميم شبكات التغذية بالمياه والصرف الصحي وتصريف الأمطار', 'name_en' => 'Plumbing & Drainage Systems Design', 'price' => 2000, 'duration' => '7 أيام'],
            ],
            'هندسة المشاريع' => [
                ['name_ar' => 'إدارة وتخطيط المشاريع الهندسية وإعداد الجداول الزمنية', 'name_en' => 'Project Management & CPM Scheduling', 'price' => 4500, 'duration' => '30 يوم'],
                ['name_ar' => 'إعداد جداول الكميات والمواصفات ودفاتر الشروط (BOQ)', 'name_en' => 'Bill of Quantities (BOQ) & Specs Preparation', 'price' => 3000, 'duration' => '12 يوم'],
                ['name_ar' => 'مراجعة وتدقيق المستخلصات وأوامر التغيير واستلام الأعمال', 'name_en' => 'Payment Claims & Variation Orders Review', 'price' => 2500, 'duration' => '15 يوم'],
            ],
            'التصميم الهندسي' => [
                ['name_ar' => 'التخطيط العمراني وتصميم المخططات السكنية والتجارية', 'name_en' => 'Urban Planning & Master Plan Design', 'price' => 6000, 'duration' => '25 يوم'],
                ['name_ar' => 'تصاميم اللاندسكيب وتنسيق المواقع والمساحات الخضراء', 'name_en' => 'Landscape Architecture Design', 'price' => 2500, 'duration' => '10 أيام'],
                ['name_ar' => 'إعداد النماذج الهندسية ثلاثية الأبعاد ونمذجة البناء (BIM)', 'name_en' => 'Building Information Modeling (BIM)', 'price' => 4000, 'duration' => '15 يوم'],
            ],

            // أصحاب المهن الحرة (Freelance)
            'البرمجة وتطوير المواقع' => [
                ['name_ar' => 'تصميم وتطوير مواقع وتطبيقات الويب المتجاوبة', 'name_en' => 'Full-Stack Web Development', 'price' => 3500, 'duration' => '14 يوم'],
                ['name_ar' => 'برمجة وتطوير تطبيقات الهواتف الذكية (iOS & Android)', 'name_en' => 'Mobile App Development', 'price' => 6000, 'duration' => '30 يوم'],
                ['name_ar' => 'ربط بوابات الدفع الإلكتروني والخدمات السحابية والـ APIs', 'name_en' => 'Payment Gateway & API Integration', 'price' => 1500, 'duration' => '5 أيام'],
                ['name_ar' => 'صيانة وتحسين أداء وحماية المواقع والأنظمة التقنية', 'name_en' => 'Web Maintenance & Speed Optimization', 'price' => 1200, 'duration' => '7 أيام'],
            ],
            'التصميم الجرافيكي' => [
                ['name_ar' => 'تصميم الهويات البصرية الكاملة والشعارات ودليل الهوية', 'name_en' => 'Brand Identity & Logo Design', 'price' => 2000, 'duration' => '7 أيام'],
                ['name_ar' => 'تصميم واجهات وتجربة المستخدم للتطبيقات والمواقع (UI/UX)', 'name_en' => 'UI/UX Design & Prototyping', 'price' => 3000, 'duration' => '12 يوم'],
                ['name_ar' => 'تصاميم السوشيال ميديا والحملات الإعلانية المبتكرة', 'name_en' => 'Social Media Campaign Designs', 'price' => 1000, 'duration' => '5 أيام'],
                ['name_ar' => 'تصميم المطبوعات والبروشورات والكتالوجات الترويجية', 'name_en' => 'Print & Promotional Collateral Design', 'price' => 800, 'duration' => '3 أيام'],
            ],
            'التسويق الرقمي' => [
                ['name_ar' => 'إدارة الحملات الإعلانية الممولة (Google, Meta, TikTok)', 'name_en' => 'Paid Ads Campaign Management', 'price' => 2000, 'duration' => '30 يوم'],
                ['name_ar' => 'تحسين محركات البحث وتصدر نتائج البحث الأولى (SEO)', 'name_en' => 'Search Engine Optimization (SEO)', 'price' => 2500, 'duration' => '30 يوم'],
                ['name_ar' => 'إدارة وتفعيل حسابات وسائل التواصل الاجتماعي وصناعة المحتوى', 'name_en' => 'Social Media Management & Content', 'price' => 1800, 'duration' => '30 يوم'],
                ['name_ar' => 'إعداد استراتيجية التسويق الرقمي وبناء مسارات البيع (Funnels)', 'name_en' => 'Digital Marketing Strategy & Sales Funnels', 'price' => 2500, 'duration' => '10 أيام'],
            ],
            'الترجمة' => [
                ['name_ar' => 'الترجمة القانونية المعتمدة للعقود والوثائق الرسمية', 'name_en' => 'Certified Legal Translation', 'price' => 100, 'duration' => '2 يوم'],
                ['name_ar' => 'الترجمة التجارية والمالية للتقارير والقوائم والمذكرات', 'name_en' => 'Commercial & Financial Translation', 'price' => 80, 'duration' => '2 يوم'],
                ['name_ar' => 'الترجمة التقنية والطبية المتخصصة والمصطلحات الدقيقة', 'name_en' => 'Technical & Medical Translation', 'price' => 120, 'duration' => '3 أيام'],
                ['name_ar' => 'تعريب المواقع الإلكترونية والتطبيقات والبرمجيات', 'name_en' => 'Software & Website Localization', 'price' => 1500, 'duration' => '7 أيام'],
            ],
            'كتابة المحتوى' => [
                ['name_ar' => 'كتابة المحتوى الإعلاني والتسويقي وصياغة النصوص المقنعة (Copywriting)', 'name_en' => 'Marketing & Ad Copywriting', 'price' => 800, 'duration' => '4 أيام'],
                ['name_ar' => 'كتابة المقالات المتخصصة والمحتوى المتوافق مع معايير SEO', 'name_en' => 'SEO Blog & Article Writing', 'price' => 500, 'duration' => '3 أيام'],
                ['name_ar' => 'صياغة البروفايلات التعريفية للشركات والبيانات الصحفية', 'name_en' => 'Corporate Company Profile & Press Releases', 'price' => 1200, 'duration' => '5 أيام'],
                ['name_ar' => 'التدقيق اللغوي والتحرير وإعادة الصياغة الاحترافية', 'name_en' => 'Proofreading & Professional Editing', 'price' => 400, 'duration' => '2 يوم'],
            ],
        ];
    }

    /**
     * خدمات افتراضية في حال إضافة تخصص غير مسجل بالقائمة
     */
    protected function getDefaultServicesForType(?string $officeType, string $specialtyName): array
    {
        return [
            ['name_ar' => 'استشارة متخصصة في ' . $specialtyName, 'name_en' => 'Specialized Consultation in ' . $specialtyName, 'price' => 500, 'duration' => '3 أيام'],
            ['name_ar' => 'إعداد وتجهيز مستندات ومعاملات ' . $specialtyName, 'name_en' => 'Document & Transaction Processing for ' . $specialtyName, 'price' => 1000, 'duration' => '7 أيام'],
            ['name_ar' => 'متابعة وإنجاز الخدمة الشاملة عبر المنصة', 'name_en' => 'Full Service Fulfillment via Platform', 'price' => 1500, 'duration' => '14 يوم'],
        ];
    }

    /**
     * تحديد وضع التسجيل من استعلام الرابط أو حقل مخفي.
     */
    protected function resolveMode(Request $request): string
    {
        $registrationType = $request->query('type', $request->input('account_type', 'office'));

        return match ($registrationType) {
            'consultant' => 'consultant',
            'mixed'      => 'mixed',
            default      => 'office',
        };
    }



    /*
    |--------------------------------------------------------------------------
    | حفظ طلب تسجيل المكتب
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | وضع التسجيل (يُستخدم لاحقاً لتحديد الحقول الإلزامية)
        |--------------------------------------------------------------------------
        */
        $mode = $this->resolveMode($request);

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            /*
            |--------------------------------------------------------------------------
            | بيانات المكتب
            |--------------------------------------------------------------------------
            */

            'name_ar' => [
                'required',
                'string',
                'max:191',
            ],

            'name_en' => [
                'required',
                'string',
                'max:191',
            ],

            'office_type' => [
                $mode === 'consultant' ? 'nullable' : 'required',
                'in:law,services,customs,accounting,engineering,freelance',
            ],

            'entity_type' => [
                'required',
                'in:company,institution',
            ],

            'subscription_type' => [
                'nullable',
                'in:commission,subscription',
            ],

            'phone' => [
                'required',
                'string',
                'max:191',
            ],

            'email' => [
                'required',
                'email',
                'max:191',
                'unique:business.bs_offices,email',
                'unique:business.bs_office_users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}$/',
            ],

            /*
            |--------------------------------------------------------------------------
            | العنوان
            |--------------------------------------------------------------------------
            */

            'country' => [
                'required',
                'string',
                'max:191',
            ],

            'governorate' => [
                'required',
                'string',
                'max:191',
            ],

            'city' => [
                'required',
                'string',
                'max:191',
            ],

            'district' => [
                'nullable',
                'string',
                'max:191',
            ],

            'street' => [
                'nullable',
                'string',
                'max:191',
            ],

            'building_number' => [
                'nullable',
                'string',
                'max:191',
            ],

            'office_number' => [
                'nullable',
                'string',
                'max:191',
            ],

            /*
            |--------------------------------------------------------------------------
            | أرقام المستندات
            |--------------------------------------------------------------------------
            */

            'cr_number' => [
                'required',
                'string',
                'max:191',
            ],

            'license_number' => [
                'required',
                'string',
                'max:191',
            ],

            'cr_expiry_date' => [
                'nullable',
                'date',
            ],

            'license_expiry_date' => [
                'nullable',
                'date',
            ],

            'trademark_registration_number' => [
                'nullable',
                'string',
                'max:80',
            ],

            'logo' => [
                'nullable',
                'file',
                'mimes:png,jpg,jpeg,webp',
                'max:2048',
            ],

            /*
            |--------------------------------------------------------------------------
            | التخصص والخدمات
            |--------------------------------------------------------------------------
            */

            'specialties' => [
                'nullable',
                'array',
            ],

            'specialty' => [
                'nullable',
            ],

            'business_activity' => [
                'nullable',
                'string',
                'max:64',
                'in:' . implode(',', array_keys(\App\Support\ConsultantCatalog::businessActivities())),
            ],

            // الفئات/الأقسام التابعة للنشاط التجاري (قائمة متعددة)
            'categories' => [
                'nullable',
                'array',
                'max:16',
            ],

            'categories.*' => [
                'string',
                'max:32',
                'distinct',
                'in:' . implode(',', array_keys(\App\Support\ConsultantCatalog::categories())),
            ],

            'category' => [
                'nullable',
                'string',
                'max:64',
            ],

            'manual_specialty' => [
                'nullable',
                'string',
                'max:255',
            ],

            'services' => [
                'nullable',
                'array',
            ],

            'custom_services' => [
                'nullable',
                'array',
            ],

            'services.*.custom_fields' => [
                'nullable',
                'array',
                'max:30',
            ],
            'services.*.custom_fields.*.key' => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/',
                'distinct',
            ],
            'services.*.custom_fields.*.type' => [
                'required',
                'string',
                'in:' . implode(',', ServiceCustomFields::TYPES),
            ],
            'services.*.custom_fields.*.label_ar' => [
                'required',
                'string',
                'max:200',
            ],
            'services.*.custom_fields.*.label_en' => [
                'nullable',
                'string',
                'max:200',
            ],
            'services.*.custom_fields.*.placeholder_ar' => [
                'nullable',
                'string',
                'max:250',
            ],
            'services.*.custom_fields.*.placeholder_en' => [
                'nullable',
                'string',
                'max:250',
            ],
            'services.*.custom_fields.*.help_ar' => [
                'nullable',
                'string',
                'max:500',
            ],
            'services.*.custom_fields.*.help_en' => [
                'nullable',
                'string',
                'max:500',
            ],
            'services.*.custom_fields.*.required' => [
                'nullable',
                'boolean',
            ],
            'services.*.custom_fields.*.min' => [
                'nullable',
                'numeric',
            ],
            'services.*.custom_fields.*.max' => [
                'nullable',
                'numeric',
            ],
            'services.*.custom_fields.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'services.*.custom_fields.*.options' => [
                'nullable',
                'array',
                'max:50',
            ],
            'services.*.custom_fields.*.options.*.value' => [
                'required_with:services.*.custom_fields.*.options',
                'string',
                'max:100',
            ],
            'services.*.custom_fields.*.options.*.label_ar' => [
                'required_with:services.*.custom_fields.*.options',
                'string',
                'max:200',
            ],
            'services.*.custom_fields.*.options.*.label_en' => [
                'nullable',
                'string',
                'max:200',
            ],

            'custom_services.*.custom_fields' => [
                'nullable',
                'array',
                'max:30',
            ],
            'custom_services.*.custom_fields.*.key' => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/',
                'distinct',
            ],
            'custom_services.*.custom_fields.*.type' => [
                'required',
                'string',
                'in:' . implode(',', ServiceCustomFields::TYPES),
            ],
            'custom_services.*.custom_fields.*.label_ar' => [
                'required',
                'string',
                'max:200',
            ],
            'custom_services.*.custom_fields.*.label_en' => [
                'nullable',
                'string',
                'max:200',
            ],
            'custom_services.*.custom_fields.*.placeholder_ar' => [
                'nullable',
                'string',
                'max:250',
            ],
            'custom_services.*.custom_fields.*.placeholder_en' => [
                'nullable',
                'string',
                'max:250',
            ],
            'custom_services.*.custom_fields.*.help_ar' => [
                'nullable',
                'string',
                'max:500',
            ],
            'custom_services.*.custom_fields.*.help_en' => [
                'nullable',
                'string',
                'max:500',
            ],
            'custom_services.*.custom_fields.*.required' => [
                'nullable',
                'boolean',
            ],
            'custom_services.*.custom_fields.*.min' => [
                'nullable',
                'numeric',
            ],
            'custom_services.*.custom_fields.*.max' => [
                'nullable',
                'numeric',
            ],
            'custom_services.*.custom_fields.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'custom_services.*.custom_fields.*.options' => [
                'nullable',
                'array',
                'max:50',
            ],
            'custom_services.*.custom_fields.*.options.*.value' => [
                'required_with:custom_services.*.custom_fields.*.options',
                'string',
                'max:100',
            ],
            'custom_services.*.custom_fields.*.options.*.label_ar' => [
                'required_with:custom_services.*.custom_fields.*.options',
                'string',
                'max:200',
            ],
            'custom_services.*.custom_fields.*.options.*.label_en' => [
                'nullable',
                'string',
                'max:200',
            ],


            /*
            |--------------------------------------------------------------------------
            | الملفات
            |--------------------------------------------------------------------------
            */

            'commercial_register_image' => [
                'required',
                'file',
                'mimetypes:image/jpeg,image/png,application/pdf',
                'max:5120',
            ],

            'license_image' => [
                'required',
                'file',
                'mimetypes:image/jpeg,image/png,application/pdf',
                'max:5120',
            ],

            'trademark_certificate' => [
                'nullable',
                'file',
                'mimetypes:image/jpeg,image/png,application/pdf',
                'max:5120',
                'required_with:trademark_registration_number',
            ],

            'certificates' => [
                'nullable',
                'array',
            ],

            'certificates.*' => [
                'file',
                'mimetypes:image/jpeg,image/png,application/pdf',
                'max:5120',
            ],

            'appreciation_certificates' => [
                'nullable',
                'array',
            ],

            'appreciation_certificates.*' => [
                'file',
                'mimetypes:image/jpeg,image/png,application/pdf',
                'max:5120',
            ],

            'cv' => [
                'required',
                'file',
                'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'max:5120',
            ],

        ], [

            /*
            |--------------------------------------------------------------------------
            | بيانات منشأة العقود
            |--------------------------------------------------------------------------
            */

            'legal_name.required' =>
                'الاسم التجاري للمنشأة مطلوب.',

            'phone_dial.required' =>
                'يرجى اختيار رمز الدولة للجوال.',

            'terms.required' =>
                'يجب الموافقة على شروط الاستخدام وسياسة الخصوصية.',

            'terms.accepted' =>
                'يجب الموافقة على شروط الاستخدام وسياسة الخصوصية.',

            /*
            |--------------------------------------------------------------------------
            | بيانات المكتب
            |--------------------------------------------------------------------------
            */

            'name_ar.required' =>
                'اسم المكتب بالعربية مطلوب.',

            'name_en.required' =>
                'اسم المكتب بالإنجليزية مطلوب.',

            'office_type.required' =>
                'نوع المكتب مطلوب.',

            'office_type.in' =>
                'نوع المكتب غير صحيح.',

            'entity_type.required' =>
                'نوع المنشأة (شركة / مؤسسة) مطلوب.',

            'entity_type.in' =>
                'نوع المنشأة غير صحيح.',

            'subscription_type.in' =>
                'نوع الاشتراك غير صحيح.',

            'phone.required' =>
                'رقم الجوال مطلوب.',

            'email.required' =>
                'البريد الإلكتروني مطلوب.',

            'email.email' =>
                'البريد الإلكتروني غير صحيح.',

            'email.unique' =>
                'هذا البريد الإلكتروني مسجل مسبقاً.',

            'password.required' =>
                'كلمة المرور مطلوبة.',

            'password.min' =>
                'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',

            'password.regex' =>
                'كلمة المرور يجب أن تتضمن حرفاً إنجليزياً كبيراً وصغيراً ورقماً ورمزاً خاصاً (!@#$%^&*).',

            'password.confirmed' =>
                'تأكيد كلمة المرور غير مطابق.',

            /*
            |--------------------------------------------------------------------------
            | العنوان
            |--------------------------------------------------------------------------
            | الدولة / المنطقة / المدينة إلزامية — لاظهار المنشأة في الدليل
            */

            'country.required' =>
                'الدولة مطلوبة.',

            'governorate.required' =>
                'المنطقة مطلوبة.',

            'city.required' =>
                'المدينة مطلوبة.',

            /*
            |--------------------------------------------------------------------------
            | الأرقام
            |--------------------------------------------------------------------------
            */

            'cr_number.required' =>
                'رقم السجل التجاري مطلوب.',

            'license_number.required' =>
                'رقم ترخيص المزاولة المهنية مطلوب.',

            /*
            |--------------------------------------------------------------------------
            | التخصص
            |--------------------------------------------------------------------------
            */

            'specialty.required' =>
                'يرجى اختيار التخصص.',

            /*
            |--------------------------------------------------------------------------
            | شعار المكتب
            |--------------------------------------------------------------------------
            */

            'logo.image'  =>
                'يجب أن يكون الشعار صورة.',
            'logo.mimes'  =>
                'يجب أن يكون الشعار من نوع PNG أو JPG أو JPEG أو WebP.',
            'logo.max'    =>
                'حجم ملف الشعار يجب ألا يتجاوز 2MB.',

            /*
            |--------------------------------------------------------------------------
            | السجل التجاري
            |--------------------------------------------------------------------------
            */

            'commercial_register_image.required' =>
                'ملف السجل التجاري مطلوب.',

            'commercial_register_image.file' =>
                'ملف السجل التجاري غير صالح.',

            'commercial_register_image.mimetypes' =>
                'يجب أن يكون السجل التجاري ملفاً من نوع JPG أو JPEG أو PNG أو PDF.',

            'commercial_register_image.max' =>
                'حجم ملف السجل التجاري يجب ألا يتجاوز 5MB.',

            /*
            |--------------------------------------------------------------------------
            | الترخيص
            |--------------------------------------------------------------------------
            */

            'license_image.required' =>
                'ملف الترخيص مطلوب.',

            'license_image.file' =>
                'ملف الترخيص غير صالح.',

            'license_image.mimetypes' =>
                'يجب أن يكون الترخيص ملفاً من نوع JPG أو JPEG أو PNG أو PDF.',

            'license_image.max' =>
                'حجم ملف الترخيص يجب ألا يتجاوز 5MB.',

            /*
            |--------------------------------------------------------------------------
            | العلامة التجارية
            |--------------------------------------------------------------------------
            */

            'trademark_certificate.file' =>
                'ملف شهادة العلامة التجارية غير صالح.',

            'trademark_certificate.mimetypes' =>
                'يجب أن تكون شهادة العلامة التجارية JPG أو JPEG أو PNG أو PDF.',

            'trademark_certificate.max' =>
                'حجم شهادة العلامة التجارية يجب ألا يتجاوز 5MB.',

            'trademark_certificate.required_with' =>
                'ملف شهادة العلامة التجارية مطلوب عند إدخال رقم تسجيل العلامة التجارية.',

            /*
            |--------------------------------------------------------------------------
            | الشهادات العملية
            |--------------------------------------------------------------------------
            */

            'certificates.array' =>
                'صيغة الشهادات العملية غير صحيحة.',

            'certificates.*.file' =>
                'أحد ملفات الشهادات العملية غير صالح.',

            'certificates.*.mimetypes' =>
                'يجب أن تكون الشهادات العملية JPG أو JPEG أو PNG أو PDF.',

            'certificates.*.max' =>
                'حجم أحد ملفات الشهادات العملية يتجاوز 5MB.',

            /*
            |--------------------------------------------------------------------------
            | شهادات التقدير
            |--------------------------------------------------------------------------
            */

            'appreciation_certificates.array' =>
                'صيغة شهادات التقدير غير صحيحة.',

            'appreciation_certificates.*.file' =>
                'أحد ملفات شهادات التقدير غير صالح.',

            'appreciation_certificates.*.mimetypes' =>
                'يجب أن تكون شهادات التقدير JPG أو JPEG أو PNG أو PDF.',

            'appreciation_certificates.*.max' =>
                'حجم أحد ملفات شهادات التقدير يتجاوز 5MB.',

            /*
            |--------------------------------------------------------------------------
            | CV
            |--------------------------------------------------------------------------
            */

            'cv.required' =>
                'السيرة الذاتية مطلوبة.',

            'cv.file' =>
                'ملف السيرة الذاتية غير صالح.',

            'cv.mimetypes' =>
                'السيرة الذاتية يجب أن تكون PDF أو DOC أو DOCX.',

            'cv.max' =>
                'حجم السيرة الذاتية يجب ألا يتجاوز 5MB.',

        ]);


        /*
        |--------------------------------------------------------------------------
        | معالجة واختيار التخصصات المتعددة
        |--------------------------------------------------------------------------
        */

        $hasOther = false;
        $dbSpecialties = collect();

        $rawSpecialties = $request->input('specialties', []);
        if (empty($rawSpecialties) && $request->has('specialty')) {
            $rawSpecialties = is_array($request->specialty) ? $request->specialty : [$request->specialty];
        }

        $hasOther = in_array('other', $rawSpecialties);
        $selectedSpecialtyIds = array_values(array_filter($rawSpecialties, fn($v) => is_numeric($v)));

        if (empty($selectedSpecialtyIds) && !$hasOther && !trim((string)$request->manual_specialty)) {
            /*
            |----------------------------------------------------------------------
            | كان هنا back()->withErrors(...) وهو يولّد 302 نحو الجذر في مشروع
            | API-only بلا نموذج Blade، فيختفي سبب الرفض تماماً وتبدو الواجهة
            | كأن المستخدم أُعيد إلى الصفحة الرئيسية بلا سبب.
            */

            throw \Illuminate\Validation\ValidationException::withMessages([
                'specialties' => 'يرجى اختيار تخصص واحد على الأقل أو كتابة تخصص يدوي.',
            ]);
        }

        if ($hasOther && !trim((string)$request->manual_specialty) && empty($selectedSpecialtyIds)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'manual_specialty' => 'يرجى كتابة التخصص اليدوي.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | جلب التخصصات المعتمدة المختارة من قاعدة البيانات
        |--------------------------------------------------------------------------
        */

        if (!empty($selectedSpecialtyIds)) {
            $dbSpecialtiesQuery = DB::connection('business')
                ->table('bs_specialties')
                ->whereIn('id', $selectedSpecialtyIds)
                ->where('is_active', 1);

            if ($mode !== 'consultant') {
                $dbSpecialtiesQuery->where('office_type', $request->office_type);
            }

            $dbSpecialties = $dbSpecialtiesQuery->get();
        }



        /*
        |--------------------------------------------------------------------------
        | الملفات التي تم حفظها
        |--------------------------------------------------------------------------
        */

        $storedFiles = [];

        /*
        |--------------------------------------------------------------------------
        | Transaction
        |--------------------------------------------------------------------------
        */

        $connection = DB::connection('business');

        try {

            $connection->beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | تحديد أنواع الحسابات المفعلّة عند الإنشاء (موحّد)
            |--------------------------------------------------------------------------
            | mode: office → مكتب مساند | consultant → مستشار | mixed → مساند + استشاري معاً
            */

            $mode = $this->resolveMode($request);

            $subscriptionType = in_array($request->subscription_type, ['commission', 'subscription'], true)
                ? $request->subscription_type
                : (in_array($mode, ['consultant', 'mixed'], true) ? 'subscription' : 'commission');

            $accountTypes = match ($mode) {
                'consultant' => [Office::ACCOUNT_TYPE_CONSULTANT],
                // المنشأة المدمجة: مساند + استشاري في حساب واحد.
                'mixed'      => [Office::ACCOUNT_TYPE_SUPPORT_OFFICE, Office::ACCOUNT_TYPE_CONSULTANT],
                default      => [Office::ACCOUNT_TYPE_SUPPORT_OFFICE],
            };

            /*
            |--------------------------------------------------------------------------
            | إنشاء المكتب
            |--------------------------------------------------------------------------
            */

            $office = Office::create([

                'type' =>
                    $request->input('office_type', $mode === 'consultant' ? 'freelance' : null),

                'business_activity' =>
                    $request->input('business_activity') ?? (isset($dbSpecialties) && $dbSpecialties->isNotEmpty() ? $dbSpecialties->first()?->business_activity : null),

                'category' =>
                    $request->input('category') ?? (isset($dbSpecialties) && $dbSpecialties->isNotEmpty() ? $dbSpecialties->first()?->category : null),

                // الفئات/الأقسام المتعددة المختارة (مرتبطة بالنشاط التجاري) — تُحفظ JSON
                'business_categories' =>
                    $request->filled('categories')
                        ? array_values(array_intersect(
                            (array) $request->input('categories'),
                            array_keys(\App\Support\ConsultantCatalog::categories())
                        ))
                        : null,

                'name_ar' =>
                    $request->name_ar,

                'name_en' =>
                    $request->name_en,

                'entity_type' =>
                    $request->entity_type,

                'description_ar' =>
                    null,

                'description_en' =>
                    null,

                'phone' =>
                    $request->phone,

                'email' =>
                    $request->email,

                'city' =>
                    $request->city,

                'cr_number' =>
                    $request->cr_number,

                'logo' =>
                    null,

                'specialties' =>
                    null,

                'is_active' =>
                    1,

                'is_verified' =>
                    0,

                'commission_rate' =>
                    0.00,

                'subscription_type' =>
                    $subscriptionType,

                'account_types' =>
                    $accountTypes,

                'public_token' =>
                    Str::random(40),

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),

            ]);


            /*
            |--------------------------------------------------------------------------
            | كود المكتب
            |--------------------------------------------------------------------------
            */

            $officeCode =
                'OFF-' .
                str_pad(
                    (string) $office->id,
                    6,
                    '0',
                    STR_PAD_LEFT
                );


            $office->office_code = $officeCode;

            $office->save();

            // ── رفع شعار المكتب ──
            if ($request->hasFile('logo')) {
                $ext  = $request->file('logo')->getClientOriginalExtension();
                $name = 'office_' . $office->id . '_' . \Illuminate\Support\Str::random(8) . '.' . $ext;
                $request->file('logo')->move(public_path('images/uploads'), $name);
                $office->update(['logo' => 'images/uploads/' . $name]);
            }


            /*
            |--------------------------------------------------------------------------
            | إنشاء مستخدم المكتب
            |--------------------------------------------------------------------------
            */

            $user = OfficeUser::create([

                'office_id' =>
                    $office->id,

                'name' =>
                    trim((string) $request->input('manager_name')) !== ''
                        ? trim((string) $request->input('manager_name'))
                        : $request->name_ar,

                'email' =>
                    $request->email,

                'password' =>
                    Hash::make($request->password),

                'role' =>
                    'owner',

                'is_active' =>
                    1,

            ]);


            /*
            |--------------------------------------------------------------------------
            | إنشاء Profile المكتب
            |--------------------------------------------------------------------------
            */

            $connection
                ->table('bs_office_profiles')
                ->insert([

                    'office_id' =>
                        $office->id,

                    'license_number' =>
                        $request->license_number,

                    'cr_number' =>
                        $request->cr_number,

                    'cr_expiry_date' =>
                        $request->cr_expiry_date,

                    'license_expiry_date' =>
                        $request->license_expiry_date,

                    'mobile' =>
                        $request->phone,

                    'country' =>
                        $request->country,

                    'governorate' =>
                        $request->governorate,

                    'city' =>
                        $request->city,

                    'district' =>
                        $request->district,

                    'street' =>
                        $request->street,

                    'building_number' =>
                        $request->building_number,

                    'office_number' =>
                        $request->office_number,

                    'description_ar' =>
                        null,

                    'description_en' =>
                        null,

                    /*
                    |--------------------------------------------------------------------------
                    | مهم جداً
                    |--------------------------------------------------------------------------
                    | الحقل NOT NULL
                    */

                    'handled_cases' =>
                        0,

                    'custom_specialty' =>
                        ($hasOther && trim((string)$request->manual_specialty))
                            ? trim((string)$request->manual_specialty)
                            : null,

                    'profile_completed' =>
                        1,

                    'verification_status' =>
                        'pending',

                    'submitted_at' =>
                        now(),

                    'approved_at' =>
                        null,

                    'office_code' =>
                        $officeCode,

                    'qr_code' =>
                        $officeCode,

                    'trademark_registration_number' =>
                        $request->trademark_registration_number,

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),

                ]);


            /*
            |--------------------------------------------------------------------------
            | حفظ التخصصات المتعددة
            |--------------------------------------------------------------------------
            */

            $specialtyNames = [];
            foreach ($dbSpecialties as $spec) {
                $connection
                    ->table('bs_office_specialties')
                    ->insert([
                        'office_id'    => $office->id,
                        'specialty_id' => $spec->id,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                $specialtyNames[] = $spec->name_ar;
            }

            if ($hasOther && trim((string)$request->manual_specialty)) {
                $specialtyNames[] = trim((string)$request->manual_specialty);
            }

            $office->specialties = $specialtyNames;
            $office->save();


            /*
            |--------------------------------------------------------------------------
            | حفظ الخدمات المختارة والمخصصة للمكتب
            |--------------------------------------------------------------------------
            */

            $allServicesToInsert = [];

            // 1. الخدمات القياسية المختارة من التخصصات (كتالوج) — تظهر فوراً
            if ($request->has('services') && is_array($request->services)) {
                foreach ($request->services as $svc) {
                    if (is_array($svc)) {
                        $nameAr          = trim((string)($svc['name_ar'] ?? ''));
                        $nameEn          = trim((string)($svc['name_en'] ?? $nameAr));
                        $price           = isset($svc['price']) && is_numeric($svc['price']) ? (float)$svc['price'] : 0.00;
                        $durationText    = !empty($svc['duration']) ? trim((string)$svc['duration']) : null;
                        $durationMin     = isset($svc['duration_min']) && is_numeric($svc['duration_min']) ? (int)$svc['duration_min'] : null;
                        $durationMax     = isset($svc['duration_max']) && is_numeric($svc['duration_max']) ? (int)$svc['duration_max'] : null;
                        $durationUnit    = !empty($svc['duration_unit']) ? trim((string)$svc['duration_unit']) : null;
                        $requirements    = !empty($svc['requirements']) ? trim((string)$svc['requirements']) : null;
                        $specialtyId     = !empty($svc['specialty_id']) && is_numeric($svc['specialty_id']) ? (int)$svc['specialty_id'] : null;
                        $entityId        = !empty($svc['entity_id']) && is_numeric($svc['entity_id']) ? (int)$svc['entity_id'] : null;
                        $sourceServiceId = !empty($svc['source_service_id']) && is_numeric($svc['source_service_id']) ? (int)$svc['source_service_id'] : null;
                        $customFields    = ServiceCustomFields::normalize($svc['custom_fields'] ?? null);
                    } else {
                        $nameAr          = trim((string)$svc);
                        $nameEn          = $nameAr;
                        $price           = 0.00;
                        $durationText    = null;
                        $durationMin     = null;
                        $durationMax     = null;
                        $durationUnit    = null;
                        $requirements    = null;
                        $specialtyId     = null;
                        $entityId        = null;
                        $sourceServiceId = null;
                        $customFields    = [];
                    }

                    if ($durationMin === null || $durationMax === null) {
                        $parsed = \App\Support\ServiceDuration::parse($durationText);
                        $durationMin  ??= $parsed['min'];
                        $durationMax  ??= $parsed['max'];
                        $durationUnit ??= $parsed['unit'];
                    }

                    if ($nameAr !== '') {
                        $allServicesToInsert[] = [
                            'name_ar'          => $nameAr,
                            'name_en'          => $nameEn !== '' ? $nameEn : $nameAr,
                            'price'            => $price,
                            'duration_min'     => $durationMin,
                            'duration_max'     => $durationMax,
                            'duration_unit'    => $durationUnit,
                            'requirements'     => $requirements,
                            'custom_fields'     => json_encode($customFields, JSON_UNESCAPED_UNICODE),
                            'specialty_id'     => $specialtyId,
                            'entity_id'        => $specialtyId ? $entityId : null,
                            'source_service_id'=> $sourceServiceId,
                            'source_type'      => $sourceServiceId ? 'catalog' : 'manual',
                            'approval_status'  => 'approved',
                            'is_active'        => 1,
                        ];
                    }
                }
            }

            // 2. الخدمات المضافة يدوياً عبر زر (+) — تخضع لمراجعة الإدارة
            if ($request->has('custom_services') && is_array($request->custom_services)) {
                foreach ($request->custom_services as $customSvc) {
                    if (is_array($customSvc)) {
                        $nameAr       = trim((string)($customSvc['name_ar'] ?? ''));
                        $nameEn       = trim((string)($customSvc['name_en'] ?? $nameAr));
                        $price        = isset($customSvc['price']) && is_numeric($customSvc['price']) ? (float)$customSvc['price'] : 0.00;
                        $durationText = !empty($customSvc['duration']) ? trim((string)$customSvc['duration']) : null;
                        $durationMin  = isset($customSvc['duration_min']) && is_numeric($customSvc['duration_min']) ? (int)$customSvc['duration_min'] : null;
                        $durationMax  = isset($customSvc['duration_max']) && is_numeric($customSvc['duration_max']) ? (int)$customSvc['duration_max'] : null;
                        $durationUnit = !empty($customSvc['duration_unit']) ? trim((string)$customSvc['duration_unit']) : null;
                        $requirements = !empty($customSvc['requirements']) ? trim((string)$customSvc['requirements']) : null;
                        $specialtyId  = !empty($customSvc['specialty_id']) && is_numeric($customSvc['specialty_id']) ? (int)$customSvc['specialty_id'] : null;
                        $customFields = ServiceCustomFields::normalize($customSvc['custom_fields'] ?? null);
                    } else {
                        $nameAr       = trim((string)$customSvc);
                        $nameEn       = $nameAr;
                        $price        = 0.00;
                        $durationText = null;
                        $durationMin  = null;
                        $durationMax  = null;
                        $durationUnit = null;
                        $requirements = null;
                        $specialtyId  = null;
                        $customFields = [];
                    }

                    if ($durationMin === null || $durationMax === null) {
                        $parsed = \App\Support\ServiceDuration::parse($durationText);
                        $durationMin  ??= $parsed['min'];
                        $durationMax  ??= $parsed['max'];
                        $durationUnit ??= $parsed['unit'];
                    }

                    if ($nameAr !== '') {
                        $allServicesToInsert[] = [
                            'name_ar'          => $nameAr,
                            'name_en'          => $nameEn !== '' ? $nameEn : $nameAr,
                            'price'            => $price,
                            'duration_min'     => $durationMin,
                            'duration_max'     => $durationMax,
                            'duration_unit'    => $durationUnit,
                            'requirements'     => $requirements,
                            'custom_fields'    => json_encode($customFields, JSON_UNESCAPED_UNICODE),
                            'specialty_id'     => $specialtyId,
                            'entity_id'        => null,
                            'source_service_id'=> null,
                            'source_type'      => 'custom',
                            'approval_status'  => 'pending',
                            'is_active'        => 0,
                        ];
                    }
                }
            }

            // إدراج الخدمات في جدول bs_office_services
            $seenServices = [];
            $serviceSortOrder = 1;
            foreach ($allServicesToInsert as $svcItem) {
                if (isset($seenServices[$svcItem['name_ar']])) {
                    continue;
                }
                $seenServices[$svcItem['name_ar']] = true;

                $connection
                    ->table('bs_office_services')
                    ->insert([
                        'office_id'        => $office->id,
                        'name_ar'          => $svcItem['name_ar'],
                        'name_en'          => $svcItem['name_en'],
                        'description_ar'   => null,
                        'description_en'   => null,
                        'price'            => $svcItem['price'],
                        'duration_min'     => $svcItem['duration_min'],
                        'duration_max'     => $svcItem['duration_max'],
                        'duration_unit'    => $svcItem['duration_unit'],
                        'requirements'     => $svcItem['requirements'],
                        'custom_fields'    => $svcItem['custom_fields'],
                        'specialty_id'     => $svcItem['specialty_id'],
                        'entity_id'        => $svcItem['entity_id'],
                        'source_service_id'=> $svcItem['source_service_id'],
                        'source_type'      => $svcItem['source_type'],
                        'approval_status'  => $svcItem['approval_status'],
                        'is_active'        => $svcItem['is_active'],
                        'sort_order'       => $serviceSortOrder++,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
            }



            /*
            |--------------------------------------------------------------------------
            | دالة مساعدة لحفظ المستند
            |--------------------------------------------------------------------------
            */

            $saveDocument = function (
                $file,
                string $documentType,
                string $folder
            ) use (
                $office,
                $connection,
                &$storedFiles
            ) {

                if (!$file || !$file->isValid()) {
                    return;
                }

                $path = $file->store(
                    'office-documents/' .
                    $office->id .
                    '/' .
                    $folder,
                    'public'
                );

                $storedFiles[] = $path;

                $connection
                    ->table('bs_office_documents')
                    ->insert([

                        'office_id' =>
                            $office->id,

                        'document_type' =>
                            $documentType,

                        'file' =>
                            $path,

                        'file_name' =>
                            $file->getClientOriginalName(),

                        'is_verified' =>
                            0,

                        'created_at' =>
                            now(),

                        'updated_at' =>
                            now(),

                    ]);
            };


            /*
            |--------------------------------------------------------------------------
            | السجل التجاري
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('commercial_register_image')) {

                $saveDocument(
                    $request->file('commercial_register_image'),
                    'commercial_register',
                    'commercial-register'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | الترخيص
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('license_image')) {

                $saveDocument(
                    $request->file('license_image'),
                    'license',
                    'license'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | شهادة العلامة التجارية
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('trademark_certificate')) {

                $saveDocument(
                    $request->file('trademark_certificate'),
                    'trademark_certificate',
                    'trademark'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | الشهادات العملية
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('certificates')) {

                foreach (
                    $request->file('certificates')
                    as $file
                ) {

                    $saveDocument(
                        $file,
                        'certificate',
                        'certificates'
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | شهادات التقدير
            |--------------------------------------------------------------------------
            */

            if (
                $request->hasFile(
                    'appreciation_certificates'
                )
            ) {

                foreach (
                    $request->file(
                        'appreciation_certificates'
                    )
                    as $file
                ) {

                    $saveDocument(
                        $file,
                        'appreciation_certificate',
                        'appreciation-certificates'
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | السيرة الذاتية
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('cv')) {

                $saveDocument(
                    $request->file('cv'),
                    'cv',
                    'cv'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Commit
            |--------------------------------------------------------------------------
            */

            $connection->commit();


            /*
            |--------------------------------------------------------------------------
            | نجاح العملية
            |--------------------------------------------------------------------------
            */

            /*
            |----------------------------------------------------------------------
            | نجاح العملية
            |----------------------------------------------------------------------
            | هذا المشروع API-only، لذا الاستجابة دائماً JSON.
            | الاعتماد على wantsJson() كان يفعّل مسار redirect (302 نحو الجذر)
            | عندما لا يصل Accept: application/json، فيبدو للواجهة كأن الطلب
            | أُهمل وتمّ التوجيه للصفحة الرئيسية — وهو ما كان يحدث.
            */

            return response()->json([
                'isSuccess'  => true,
                'statusCode' => 201,
                'value'      => [
                    'redirect' => $this->frontendUrl('provider-account/create'),
                    'officeId' => $office->id,
                ],
                'error'      => null,
            ], 201);


        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Rollback
            |--------------------------------------------------------------------------
            */

            if ($connection->transactionLevel() > 0) {
                $connection->rollBack();
            }


            /*
            |--------------------------------------------------------------------------
            | حذف الملفات المرفوعة
            |--------------------------------------------------------------------------
            */

            foreach ($storedFiles as $path) {

                try {

                    Storage::disk('public')
                        ->delete($path);

                } catch (\Throwable $deleteException) {

                    Log::warning(
                        'Failed to delete uploaded file',
                        [
                            'path' =>
                                $path,

                            'error' =>
                                $deleteException->getMessage(),
                        ]
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | تسجيل الخطأ الحقيقي في Laravel Log
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Provider office registration failed',
                [
                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),

                    'office_type' =>
                        $request->office_type,

                    'email' =>
                        $request->email,
                ]
            );


            /*
            |----------------------------------------------------------------------
            | الاستجابة عند الفشل
            |----------------------------------------------------------------------
            | هذا المشروع API-only، فلا يوجد نموذج Blade ليرجع إليه back()
            | فيولّد 302 نحو الجذر ويبدو للواجهة كأن الأمر نجح بلا شيء.
            | لذلك نعيد دائماً JSON يحمل أخطاء التحقق أو الرسالة العامة.
            */

            if ($e instanceof \Illuminate\Validation\ValidationException) {

                return response()->json([
                    'isSuccess'  => false,
                    'value'      => null,
                    'message'    => collect($e->errors())->flatten()->first(),
                    'errors'     => $e->errors(),
                    'statusCode' => 422,
                ], 422);
            }

            return response()->json([
                'isSuccess'  => false,
                'value'      => null,
                'message'    => 'حدث خطأ غير متوقع أثناء حفظ الطلب.',
                'error'      => [
                    'message' => 'حدث خطأ غير متوقع أثناء حفظ الطلب.',
                    'code'    => 'REGISTRATION_FAILED',
                ],
                'statusCode' => 500,
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | رابط الواجهة الأمامية
    |--------------------------------------------------------------------------
    | هذا المشروع API-only، لذا أي إعادة توجيه لصفحة الواجهة تتم عبر
    | دومين FRONTEND_URL لا عبر route() (المسارات هنا للـ API فقط).
    */

    private function frontendUrl(string $path): string
    {
        $base = rtrim((string) env('FRONTEND_URL', 'http://127.0.0.1:8001'), '/');

        return $base . '/' . ltrim($path, '/');
    }
}