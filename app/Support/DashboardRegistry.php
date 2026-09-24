<?php

namespace App\Support;

use App\Models\TypeInterface;

/**
 * مصدر الحقيقة الوحيد لواجهات لوحة التحكم الموحّدة وسجل صلاحياتها لكل نوع حساب.
 *
 * - types()      : أنواع الحسابات (عميل فردي/منشأة/مدير/مشرف/منشأة مساندة/استشارية/مدمجة).
 * - interfaces() : الواجهات المتاحة (الاسم، الأيقونة، المجموعة، رابط الوصول).
 * - defaults()   : الواجهات المفعّلة افتراضياً لكل نوع حساب.
 * - النوع المدمج support_consultant = منشأة مساندة + استشارية تعملان معاً؛
 *   واجهاته افتراضياً اتحاد النوعين، ويُدار كعمود مستقل في "الهيكل التنظيمي".
 * - ملاحظة: غياب سجل في جدول bs_type_interfaces = استخدام القيمة الافتراضية.
 *           وجود سجل = تطبيق الـ override المحدد من صفحة "الهيكل التنظيمي".
 */
class DashboardRegistry
{
    public const TYPE_INDIVIDUAL = 'individual';

    public const TYPE_ESTABLISHMENT = 'establishment';

    public const TYPE_ADMIN = 'admin';

    public const TYPE_SUPERVISOR = 'supervisor';

    public const TYPE_SUPPORT_OFFICE = 'support_office';

    public const TYPE_CONSULTANT = 'consultant';

    public const TYPE_MIXED = 'support_consultant';

    /**
     * أنواع الحسابات بدلائلها العربية/الإنجليزية.
     */
    public static function types(): array
    {
        return [
            self::TYPE_INDIVIDUAL => ['ar' => 'عميل فردي', 'en' => 'Individual Client', 'icon' => 'ti-user'],
            self::TYPE_ESTABLISHMENT => ['ar' => 'عميل منشأة', 'en' => 'Establishment Client', 'icon' => 'ti-building'],
            self::TYPE_ADMIN => ['ar' => 'مدير النظام', 'en' => 'System Admin', 'icon' => 'ti-crown'],
            self::TYPE_SUPERVISOR => ['ar' => 'مشرف', 'en' => 'Supervisor', 'icon' => 'ti-shield'],
            self::TYPE_SUPPORT_OFFICE => ['ar' => 'منشأة مكاتب مساندة', 'en' => 'Support Office Facility', 'icon' => 'ti-building'],
            self::TYPE_CONSULTANT => ['ar' => 'منشأة استشارية', 'en' => 'Consulting Facility', 'icon' => 'ti-building-bank'],
            self::TYPE_MIXED => ['ar' => 'منشأة مساندة واستشارية', 'en' => 'Support & Consulting Facility', 'icon' => 'ti-building-community'],
        ];
    }

    /**
     * كل الواجهات مع روابط الوصول لكل سياق مستخدم.
     * مفتاح المسار قد يكون: admin / user / office (للروابط)، أو path افتراضي.
     */
    public static function interfaces(): array
    {
        return [
'overview' => [
                'ar' => 'نظرة عامة', 'en' => 'Overview', 'icon' => 'ti-layout-dashboard',
                'group' => 'الرئيسية',
                'paths' => ['admin' => '/admin/overview'],
                'path' => '/dashboard-hub',
            ],

            // ---- الطلبات ----
            'requests' => [
                'ar' => 'كل الطلبات', 'en' => 'All Requests', 'icon' => 'ti-inbox',
                'group' => 'الطلبات',
                'paths' => ['admin' => '/admin/requests', 'office' => '/office/dashboard#reqs'],
                'path' => '/admin/requests',
            ],
            'my_requests' => [
                'ar' => 'طلباتي', 'en' => 'My Requests', 'icon' => 'ti-clipboard-list',
                'group' => 'الطلبات',
                'paths' => ['user' => '/dashboard#requests', 'admin' => '/admin/requests'],
                'path' => '/dashboard#requests',
            ],
            'pool_requests' => [
                'ar' => 'شبكة المكاتب', 'en' => 'Offices Network', 'icon' => 'ti-hierarchy-2',
                'group' => 'الطلبات',
                'paths' => ['office' => '/office/dashboard#pool-reqs'],
                'path' => '/office/dashboard#pool-reqs',
            ],
            'consultations' => [
                'ar' => 'الاستشارات', 'en' => 'Consultations', 'icon' => 'ti-messages',
                'group' => 'الطلبات',
                'paths' => ['office' => '/office/dashboard#direct-reqs', 'admin' => '/admin/requests'],
                'path' => '/admin/requests',
            ],

            // ---- الخدمات ----
            'services' => [
                'ar' => 'خدمات المنشأة', 'en' => 'Facility Services', 'icon' => 'ti-list-check',
                'group' => 'الخدمات',
                'paths' => ['office' => '/office/dashboard#services', 'admin' => '/admin/catalog'],
                'path' => '/admin/catalog',
            ],

            // ---- المالية ----
            'payments' => [
                'ar' => 'المدفوعات', 'en' => 'Payments', 'icon' => 'ti-cash',
                'group' => 'المالية',
                'paths' => ['user' => '/dashboard#payments', 'admin' => '/admin/finance'],
                'path' => '/admin/finance',
            ],
            'finance' => [
                'ar' => 'الحسابات المالية', 'en' => 'Finance Ledger', 'icon' => 'ti-wallet',
                'group' => 'المالية',
                'paths' => ['office' => '/office/dashboard#finance', 'admin' => '/admin/finance'],
                'path' => '/admin/finance',
            ],
            'office_finance' => [
                'ar' => 'مالية المكاتب', 'en' => 'Offices Finance', 'icon' => 'ti-coins',
                'group' => 'المالية',
                'paths' => ['admin' => '/admin/off-finance'],
                'path' => '/admin/off-finance',
            ],

            // ---- الإدارة ----
            'offices' => [
                'ar' => 'إدارة المكاتب', 'en' => 'Offices Management', 'icon' => 'ti-buildings',
                'group' => 'الإدارة',
                'paths' => ['admin' => '/admin/offices'],
                'path' => '/admin/offices',
            ],
            'office_specialties' => [
                'ar' => 'تخصصات المكاتب', 'en' => 'Office Specialties', 'icon' => 'ti-adjustments',
                'group' => 'الإدارة',
                'paths' => ['admin' => '/admin/office-specialties'],
                'path' => '/admin/office-specialties',
            ],
            'office_service_approvals' => [
                'ar' => 'اعتماد خدمات المكاتب', 'en' => 'Office Services Approval', 'icon' => 'ti-badge-check',
                'group' => 'الإدارة',
                'paths' => ['admin' => '/admin/services-approvals'],
                'path' => '/admin/services-approvals',
            ],
            'users' => [
                'ar' => 'المستخدمون', 'en' => 'Users', 'icon' => 'ti-users',
                'group' => 'الإدارة',
                'paths' => ['admin' => '/admin/users'],
                'path' => '/admin/users',
            ],
            'catalog' => [
                'ar' => 'كتالوج الخدمات', 'en' => 'Services Catalog', 'icon' => 'ti-book',
                'group' => 'الإدارة',
                'paths' => ['admin' => '/admin/catalog'],
                'path' => '/admin/catalog',
            ],
            'pricing' => [
                'ar' => 'التسعير', 'en' => 'Pricing', 'icon' => 'ti-tag',
                'group' => 'الإدارة',
                'paths' => ['admin' => '/admin/pricing'],
                'path' => '/admin/pricing',
            ],
            'contracts' => [
                'ar' => 'العقود', 'en' => 'Contracts', 'icon' => 'ti-file-text',
                'group' => 'الإدارة',
                'paths' => ['admin' => '/admin/contracts', 'office' => '/office/dashboard#contracts', 'user' => '/dashboard'],
                'path' => '/admin/contracts',
            ],
            'analytics' => [
                'ar' => 'التحليلات', 'en' => 'Analytics', 'icon' => 'ti-chart-bar',
                'group' => 'الإدارة',
                'paths' => ['admin' => '/admin/analytics'],
                'path' => '/admin/analytics',
            ],
            'logs' => [
                'ar' => 'سجل النشاطات', 'en' => 'Activity Logs', 'icon' => 'ti-terminal',
                'group' => 'الإدارة',
                'paths' => ['admin' => '/admin/logs'],
                'path' => '/admin/logs',
            ],
            'permissions' => [
                'ar' => 'الصلاحيات', 'en' => 'Permissions', 'icon' => 'ti-key',
                'group' => 'الإدارة',
                'paths' => ['admin' => '/admin/permissions'],
                'path' => '/admin/permissions',
            ],
            'org_structure' => [
                'ar' => 'الهيكل التنظيمي', 'en' => 'Organization Structure', 'icon' => 'ti-sitemap',
                'group' => 'الإدارة',
                'paths' => ['admin' => '/admin/org-structure'],
                'path' => '/admin/org-structure',
            ],
            'settings' => [
                'ar' => 'الإعدادات', 'en' => 'Settings', 'icon' => 'ti-settings',
                'group' => 'الإدارة',
                'paths' => ['admin' => '/admin/settings', 'office' => '/office/profile'],
                'path' => '/admin/settings',
            ],
            'homepage' => [
                'ar' => 'إعدادات الصفحة الرئيسية', 'en' => 'Homepage Settings', 'icon' => 'ti-home',
                'group' => 'الإدارة',
                'paths' => ['admin' => '/admin/homepage'],
                'path' => '/admin/homepage',
            ],
            'icons' => [
                'ar' => 'مكتبة الأيقونات', 'en' => 'Icons Library', 'icon' => 'ti-icons',
                'group' => 'الإدارة',
                'paths' => ['admin' => '/admin/icons'],
                'path' => '/admin/icons',
            ],
            'messages' => [
                'ar' => 'التواصل والمخالفات', 'en' => 'Messages & Violations', 'icon' => 'ti-messages',
                'group' => 'الإدارة',
                'paths' => ['admin' => '/admin/messages'],
                'path' => '/admin/messages',
            ],

            // ---- الحساب ----
            'profile' => [
                'ar' => 'الملف الشخصي', 'en' => 'Profile', 'icon' => 'ti-user-circle',
                'group' => 'الحساب',
                'paths' => ['user' => '/dashboard#profile', 'office' => '/office/profile', 'admin' => '/admin/settings'],
                'path' => '/dashboard#profile',
            ],
        ];
    }

    /**
     * الواجهات المفعّلة افتراضياً لكل نوع حساب.
     * استثناء: المدير/المشرف يرَون كل الواجهات ما لم يُفصل سجل صريح.
     */
    public static function defaults(): array
    {
        return [
            self::TYPE_INDIVIDUAL => ['overview', 'my_requests', 'payments', 'profile'],
            self::TYPE_ESTABLISHMENT => ['overview', 'my_requests', 'payments', 'contracts', 'profile'],
            self::TYPE_ADMIN => array_keys(self::interfaces()),
            self::TYPE_SUPERVISOR => array_keys(self::interfaces()),
            self::TYPE_SUPPORT_OFFICE => ['overview', 'requests', 'pool_requests', 'services', 'finance', 'contracts', 'profile'],
            self::TYPE_CONSULTANT => ['overview', 'consultations', 'services', 'finance', 'contracts', 'profile'],
            // النوع المدمج: منشأة مساندة + استشارية معاً = اتحاد افتراضيات النوعين.
            self::TYPE_MIXED => ['overview', 'requests', 'pool_requests', 'consultations', 'services', 'finance', 'contracts', 'profile'],
        ];
    }

    /**
     * مجموعات القوائم بالترتيب مع عناوينها.
     */
    public static function groups(): array
    {
        return [
            'الرئيسية',
            'الطلبات',
            'الخدمات',
            'المالية',
            'الإدارة',
            'الحساب',
        ];
    }

    /**
     * الواجهات المفعّلة لمجموعة من أنواع الحسابات (الاتحاد) مع تطبيق
     * التعديلات المحفوظة في جدول bs_type_interfaces إن وُجدت.
     */
    public static function enabledInterfaces(array $typeKeys): array
    {
        $keys = collect($typeKeys)->map(fn ($t) => (string) $t)->filter(fn ($t) => in_array($t, self::typeKeys(), true));

        if ($keys->isEmpty()) {
            return [];
        }

        // المنشأة المدمجة (مساندة + استشارية) تُدخل معها النوع المزيج ليُطبَّق
        // ضبطه المحفوظ في الهيكل التنظيمي كنقطة تحكم موحّدة للاتحاد.
        if ($keys->contains(self::TYPE_SUPPORT_OFFICE) && $keys->contains(self::TYPE_CONSULTANT)) {
            $keys->push(self::TYPE_MIXED);
        }

        $keys = $keys->unique()->values();

        $allKeys = array_keys(self::interfaces());
        $enabled = [];
        foreach ($keys as $typeKey) {
            foreach (self::defaults()[$typeKey] ?? [] as $interfaceKey) {
                $enabled[$interfaceKey] = true;
            }
        }

        $dbRows = TypeInterface::query()
            ->whereIn('type_key', $keys->all())
            ->whereIn('interface_key', $allKeys)
            ->get();

        // السجلات الصريحة تُعالج بعد افتراضات كل الأنواع حتى لو تعارضت.
        foreach ($dbRows as $row) {
            if (! isset($enabled[$row->interface_key])) {
                continue;
            }
            if (! $row->is_enabled) {
                // يُفصل فقط إذا كان كل أنواع المستخدم قُصرت عليه؛ ولتبسيط السلوك:
                // أي سجل معطّل يزال الواجهة إلا إذا كانت مفعّلة بنوع آخر بسجله الخاص.
                $stillEnabledByOther = TypeInterface::query()
                    ->where('interface_key', $row->interface_key)
                    ->where('type_key', '!=', $row->type_key)
                    ->whereIn('type_key', $keys->all())
                    ->where('is_enabled', true)
                    ->exists();
                if (! $stillEnabledByOther) {
                    unset($enabled[$row->interface_key]);
                }
            } elseif ($row->is_enabled && ! isset($enabled[$row->interface_key])) {
                $enabled[$row->interface_key] = true;
            }
        }

        return array_keys($enabled);
    }

    /**
     * بناء قائمة التنقل للمستخدم (مجموعات مرتبة مع روابط جاهزة).
     */
    public static function menuFor(array $typeKeys, string $linkContext = 'user'): array
    {
        $enabled = self::enabledInterfaces($typeKeys);
        $defs = self::interfaces();
        $groups = [];

        foreach (self::groups() as $group) {
            $items = [];
            foreach ($enabled as $interfaceKey) {
                $def = $defs[$interfaceKey] ?? null;
                if (! $def || ($def['group'] ?? null) !== $group) {
                    continue;
                }
                $items[] = [
                    'key' => $interfaceKey,
                    'ar' => $def['ar'],
                    'en' => $def['en'],
                    'icon' => $def['icon'],
                    'href' => $def['paths'][$linkContext] ?? ($def['path'] ?? '#'),
                    'isActive' => null, // يُحدد في الـ view بناءً على request
                ];
            }
            if ($items !== []) {
                $groups[] = ['label' => $group, 'items' => $items];
            }
        }

        return $groups;
    }

    /**
     * حالة النوع/الواجهة: سجل حقيقي بالتفضيل أو القيمة الافتراضية.
     */
    public static function enabledState(?TypeInterface $row, string $typeKey, string $interfaceKey): bool
    {
        if ($row !== null) {
            return $row->is_enabled;
        }

        return in_array($interfaceKey, self::defaults()[$typeKey] ?? [], true);
    }

    /**
     * مفاتيح كل الأنواع بالترتيب.
     */
    public static function typeKeys(): array
    {
        return array_keys(self::types());
    }

    /**
     * كل الواجهات بالترتيب.
     */
    public static function interfaceKeys(): array
    {
        return array_keys(self::interfaces());
    }
}


