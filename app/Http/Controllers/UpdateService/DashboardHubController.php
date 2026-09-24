<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use App\Models\Business\BusinessUser;
use App\Models\Business\Contract;
use App\Models\Business\Office;
use App\Models\Business\OfficeService;
use App\Models\ServiceRequest;
use App\Models\TypeInterface;
use App\Support\DashboardRegistry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * لوحة التحكم الموحّدة لكل أشخاص النظام:
 * عميل فردي/منشأة، مدير/مشرف، منشأة مكاتب مساندة، منشأة استشارية، ومنشأة
 * تجمع أكثر من نوع حساب في وقت واحد (يكون لها واجهات الأنواع مجتمعة).
 */
class DashboardHubController extends Controller
{
    public function index()
    {
        $officeUser = auth('office')->user();

        if ($officeUser) {
            /** @var Office $office */
            $office = $officeUser->office;
            $types = $office->accountTypes();
            $linkContext = 'office';
            $personaKey = implode(',', $types) ?: DashboardRegistry::TYPE_SUPPORT_OFFICE;

            $persona = [
                'key' => $personaKey,
                'label' => $this->personaLabelFor($types),
                'types' => $types,
                'name' => $office->name_ar ?: $office->name_en,
            ];

            $stats = [
                'requests' => ServiceRequest::query()->where('office_id', $office->id)->count(),
                'services' => OfficeService::query()->where('office_id', $office->id)->count(),
                'contracts' => Contract::query()
                    ->where(
                        fn ($q) => $q
                            ->where('created_by_office_id', $office->id)
                            ->orWhere('party_office_id', $office->id)
                    )
                    ->count(),
            ];
        } else {
            /** @var BusinessUser $user */
            $user = auth('business')->user();

            if ($user->isAdmin()) {
                $types = [DashboardRegistry::TYPE_ADMIN];
                $linkContext = 'admin';
                $persona = [
                    'key' => $user->role,
                    'label' => $user->role === 'supervisor' ? 'مشرف' : 'مدير النظام',
                    'types' => $types,
                    'name' => $user->name,
                ];
                $stats = [
                    'requests' => ServiceRequest::query()->count(),
                    'offices' => Office::query()->count(),
                    'users' => BusinessUser::query()->whereNotIn('role', ['admin', 'supervisor'])->count(),
                    'contracts' => Contract::query()->count(),
                ];
            } else {
                $type = $user->account_type === 'establishment'
                    ? DashboardRegistry::TYPE_ESTABLISHMENT
                    : DashboardRegistry::TYPE_INDIVIDUAL;
                $types = [$type];
                $linkContext = 'user';
                $persona = [
                    'key' => $type,
                    'label' => DashboardRegistry::types()[$type]['ar'],
                    'types' => $types,
                    'name' => $user->name,
                ];
                $stats = [
                    'my_requests' => ServiceRequest::query()->where('user_id', $user->id)->count(),
                ];
            }
        }

        $menu = $this->attachStats(
            DashboardRegistry::menuFor($types, $linkContext),
            $stats
        );

        return view('update_service.dashboard.hub', [
            'pageTitle' => 'لوحة التحكم الموحّدة',
            'persona' => $persona,
            'dashboardMenu' => $menu,
            'stats' => $stats,
            'interfaceDefs' => DashboardRegistry::interfaces(),
            'isOffice' => (bool) $officeUser,
        ]);
    }

    public function orgStructure()
    {
        $matrix = [];
        $rows = TypeInterface::query()->get()->keyBy(fn ($r) => $r->type_key.':'.$r->interface_key);

        foreach (DashboardRegistry::types() as $typeKey => $typeDef) {
            $matrix[$typeKey] = [
                'def' => $typeDef,
                'enabled' => [],
            ];
            foreach (DashboardRegistry::interfaceKeys() as $interfaceKey) {
                $matrix[$typeKey]['enabled'][$interfaceKey] = DashboardRegistry::enabledState(
                    $rows->get("{$typeKey}:{$interfaceKey}"),
                    $typeKey,
                    $interfaceKey
                );
            }
        }

        return view('update_service.dashboard.org_structure', [
            'pageTitle' => 'الهيكل التنظيمي',
            'dashboardMenu' => DashboardRegistry::menuFor([DashboardRegistry::TYPE_ADMIN], 'admin'),
            'matrix' => $matrix,
            'interfaceDefs' => DashboardRegistry::interfaces(),
            'interfaceGroups' => DashboardRegistry::groups(),
        ]);
    }

    public function toggleOrgStructure(Request $request)
    {
        $request->validate([
            'type_key' => ['required', 'string', Rule::in(DashboardRegistry::typeKeys())],
            'interface_key' => ['required', 'string', Rule::in(DashboardRegistry::interfaceKeys())],
            'enabled' => ['required', 'boolean'],
        ]);

        TypeInterface::query()->updateOrCreate(
            [
                'type_key' => $request->input('type_key'),
                'interface_key' => $request->input('interface_key'),
            ],
            [
                'is_enabled' => $request->boolean('enabled'),
            ]
        );

        return response()->json([
            'isSuccess' => true,
            'value' => [
                'type_key' => $request->input('type_key'),
                'interface_key' => $request->input('interface_key'),
                'is_enabled' => $request->boolean('enabled'),
            ],
            'error' => null,
            'statusCode' => 200,
        ]);
    }

    /**
     * تسمية عربية للمنشأة من أنواع حسابها (قد تكون أنواع متعددة مجتمعة).
     */
    protected function personaLabelFor(array $types): string
    {
        $hasSupport = in_array(DashboardRegistry::TYPE_SUPPORT_OFFICE, $types, true);
        $hasConsultant = in_array(DashboardRegistry::TYPE_CONSULTANT, $types, true);

        if ($hasSupport && $hasConsultant) {
            return DashboardRegistry::types()[DashboardRegistry::TYPE_MIXED]['ar'];
        }

        $order = [
            DashboardRegistry::TYPE_SUPPORT_OFFICE => 'منشأة مكاتب مساندة',
            DashboardRegistry::TYPE_CONSULTANT => 'منشأة استشارية',
        ];

        $labels = [];
        foreach ($order as $key => $label) {
            if (in_array($key, $types, true)) {
                $labels[] = $label;
            }
        }

        return $labels ? implode(' + ', $labels) : 'منشأة';
    }

    protected function attachStats(array $menuGroups, array $stats): array
    {
        foreach ($menuGroups as &$group) {
            foreach ($group['items'] as &$item) {
                $item['count'] = $stats[$item['key']] ?? null;
            }
            unset($item);
        }
        unset($group);

        return $menuGroups;
    }
}
