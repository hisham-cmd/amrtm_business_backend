<?php

namespace App\Support;

/*
|--------------------------------------------------------------------------
| كتالوج تصنيف الاستشارات الاستشارية
|--------------------------------------------------------------------------
|
| يوفّر مصدر الحقيقة الموحّد لتصنيف تخصصات المستشارين (150 تخصصاً) عبر
| بُعدين متعامدين:
|
|   1) الفئة (category)      : التصنيف الوظيفي الواسع للمجال الاستشاري.
|   2) النشاط التجاري (business_activity) : القطاع/النشاط الذي تُخدمه الاستشارة.
|
| يُستخدم الكتالوج في:
|   - هجرة البيانات (إضافة category / business_activity لصفوف bs_specialties).
|   - دليل المستشارين (فلترة بالمستشارين حسب الفئة والنشاط).
|   - نموذج تسجيل المستشار (تحديد الفئة والنشاط عند اختيار التخصص).
|
*/

class ConsultantCatalog
{
    /**
     * الفئات الاستشارية الأساسية.
     *
     * @var array<string, array{label_ar: string, label_en: string, icon: string}>
     */
    public const CATEGORIES = [
        'management' => [
            'label_ar' => 'الإدارة والاستراتيجية',
            'label_en' => 'Management & Strategy',
            'icon'     => 'ti-briefcase',
        ],
        'finance' => [
            'label_ar' => 'المالية والمحاسبة والاستثمار',
            'label_en' => 'Finance & Investment',
            'icon'     => 'ti-coin',
        ],
        'legal' => [
            'label_ar' => 'القانونية والحوكمة',
            'label_en' => 'Legal & Governance',
            'icon'     => 'ti-scale',
        ],
        'marketing' => [
            'label_ar' => 'التجارة والتسويق',
            'label_en' => 'Trade & Marketing',
            'icon'     => 'ti-trending-up',
        ],
        'tech' => [
            'label_ar' => 'التقنية والتحول الرقمي',
            'label_en' => 'Technology & Digital',
            'icon'     => 'ti-device-desktop',
        ],
        'hr_edu' => [
            'label_ar' => 'الموارد البشرية والتعليم',
            'label_en' => 'HR & Education',
            'icon'     => 'ti-users',
        ],
        'industrial' => [
            'label_ar' => 'الهندسة والتصنيع',
            'label_en' => 'Engineering & Manufacturing',
            'icon'     => 'ti-building-factory',
        ],
        'realestate' => [
            'label_ar' => 'العقارات والإنشاءات',
            'label_en' => 'Real Estate & Construction',
            'icon'     => 'ti-building-skyscraper',
        ],
        'logistics' => [
            'label_ar' => 'النقل واللوجستيات',
            'label_en' => 'Transport & Logistics',
            'icon'     => 'ti-truck',
        ],
        'energy_env' => [
            'label_ar' => 'الطاقة والبيئة والزراعة',
            'label_en' => 'Energy, Environment & Agriculture',
            'icon'     => 'ti-leaf',
        ],
        'health' => [
            'label_ar' => 'الصحة والدواء',
            'label_en' => 'Health & Pharma',
            'icon'     => 'ti-stethoscope',
        ],
        'tourism' => [
            'label_ar' => 'السياحة والضيافة والفعاليات',
            'label_en' => 'Tourism, Hospitality & Events',
            'icon'     => 'ti-cup',
        ],
        'media' => [
            'label_ar' => 'الإعلام والمحتوى والعلاقات العامة',
            'label_en' => 'Media, Content & PR',
            'icon'     => 'ti-news',
        ],
        'govt' => [
            'label_ar' => 'القطاع الحكومي وغير الربحي',
            'label_en' => 'Public & Non-Profit Sector',
            'icon'     => 'ti-building-community',
        ],
        'sports' => [
            'label_ar' => 'الرياضة والترفيه',
            'label_en' => 'Sports & Entertainment',
            'icon'     => 'ti-trophy',
        ],
        'personal' => [
            'label_ar' => 'الاستشارات الشخصية والاجتماعية',
            'label_en' => 'Personal & Social',
            'icon'     => 'ti-heart',
        ],
    ];

    /**
     * الأنشطة التجارية (القطاعات) المستهدفة بالاستشارة.
     *
     * @var array<string, array{label_ar: string, label_en: string, icon: string}>
     */
    public const BUSINESS_ACTIVITIES = [
        'government' => [
            'label_ar' => 'القطاع الحكومي وشبه الحكومي',
            'label_en' => 'Public Sector',
            'icon'     => 'ti-building-community',
        ],
        'financial' => [
            'label_ar' => 'القطاع المالي والمصرفي',
            'label_en' => 'Financial Sector',
            'icon'     => 'ti-coin',
        ],
        'industrial' => [
            'label_ar' => 'القطاع الصناعي والتعديني',
            'label_en' => 'Industrial & Mining',
            'icon'     => 'ti-building-factory',
        ],
        'construction' => [
            'label_ar' => 'قطاع العقارات والمقاولات',
            'label_en' => 'Real Estate & Contracting',
            'icon'     => 'ti-building-skyscraper',
        ],
        'retail' => [
            'label_ar' => 'قطاع التجارة والتجزئة',
            'label_en' => 'Retail & Trade',
            'icon'     => 'ti-shopping-bag',
        ],
        'services' => [
            'label_ar' => 'قطاع الخدمات',
            'label_en' => 'Services Sector',
            'icon'     => 'ti-building',
        ],
        'transport' => [
            'label_ar' => 'قطاع النقل واللوجستيات',
            'label_en' => 'Transport & Logistics',
            'icon'     => 'ti-truck',
        ],
        'energy' => [
            'label_ar' => 'قطاع الطاقة والمياه',
            'label_en' => 'Energy & Utilities',
            'icon'     => 'ti-bolt',
        ],
        'healthcare' => [
            'label_ar' => 'القطاع الصحي',
            'label_en' => 'Healthcare',
            'icon'     => 'ti-stethoscope',
        ],
        'education' => [
            'label_ar' => 'قطاع التعليم والتدريب',
            'label_en' => 'Education',
            'icon'     => 'ti-school',
        ],
        'tech' => [
            'label_ar' => 'قطاع التقنية والاتصالات',
            'label_en' => 'Technology & Telecom',
            'icon'     => 'ti-device-desktop',
        ],
        'agriculture' => [
            'label_ar' => 'القطاع الزراعي والغذائي',
            'label_en' => 'Agriculture & Food',
            'icon'     => 'ti-leaf',
        ],
        'hospitality' => [
            'label_ar' => 'قطاع السياحة والضيافة',
            'label_en' => 'Tourism & Hospitality',
            'icon'     => 'ti-cup',
        ],
        'creative' => [
            'label_ar' => 'قطاع الإعلام والإبداع',
            'label_en' => 'Media & Creative',
            'icon'     => 'ti-palette',
        ],
        'nonprofit' => [
            'label_ar' => 'القطاع غير الربحي',
            'label_en' => 'Non-Profit Sector',
            'icon'     => 'ti-heart-handshake',
        ],
        'personal' => [
            'label_ar' => 'الأفراد والأسر',
            'label_en' => 'Individuals & Families',
            'icon'     => 'ti-user-heart',
        ],
    ];

    /**
     * مصفوفة الفئات للاستخدام المباشر في الصنف (كخريطة مفتاح/عناصر).
     */
    public static function categories(): array
    {
        return self::CATEGORIES;
    }

    public static function businessActivities(): array
    {
        return self::BUSINESS_ACTIVITIES;
    }

    /**
     * الفئات (الأقسام) التابعة لنشاط تجاري معيّن — مبنية من الكتالوج الفعلي.
     *
     * @return string[] مفاتيح الفئات التي تحتوي تخصصات تخدم هذا النشاط.
     */
    public static function categoriesForActivity(?string $activityKey): array
    {
        if ($activityKey === null || $activityKey === '') {
            return array_keys(self::CATEGORIES);
        }

        $cats = [];
        foreach (self::catalog() as $meta) {
            if (($meta['business_activity'] ?? null) === $activityKey) {
                $cats[$meta['category']] = true;
            }
        }

        return array_values(array_intersect(array_keys(self::CATEGORIES), array_keys($cats)));
    }

    /**
     * الأنشطة التجارية التي تخدمها الفئة المعطاة — معكوس الدالة السابقة.
     *
     * @return string[]
     */
    public static function activitiesForCategory(string $categoryKey): array
    {
        $acts = [];
        foreach (self::catalog() as $meta) {
            if (($meta['category'] ?? null) === $categoryKey) {
                $acts[$meta['business_activity']] = true;
            }
        }

        return array_values(array_intersect(array_keys(self::BUSINESS_ACTIVITIES), array_keys($acts)));
    }

    /**
     * «الفئة» — توزيع الـ 150 استشارة على الفئة والنشاط التجاري.
     *
     * @return array<string, array{category: string, business_activity: string}>
     */
    public static function catalog(): array
    {
        return [
            // ── الفئة: الإدارة والاستراتيجية ────────────────────────────
            'الاستشارات الإدارية'                                      => ['category' => 'management', 'business_activity' => 'services'],
            'استشارات الجودة والحوكمة'                                  => ['category' => 'management', 'business_activity' => 'services'],
            'استشارات الامتثال وإدارة المخاطر'                           => ['category' => 'management', 'business_activity' => 'services'],
            'استشارات ريادة الأعمال والمنشآت الناشئة'                    => ['category' => 'management', 'business_activity' => 'services'],
            'استشارات إدارة المشاريع'                                   => ['category' => 'management', 'business_activity' => 'services'],
            'استشارات إدارة وتشغيل المنشآت'                              => ['category' => 'management', 'business_activity' => 'services'],
            'استشارات الابتكار وتطوير الأعمال'                           => ['category' => 'management', 'business_activity' => 'services'],
            'استشارات التحول المؤسسي والتخصيص'                           => ['category' => 'management', 'business_activity' => 'government'],
            'استشارات الخصخصة والتخصيص'                                 => ['category' => 'management', 'business_activity' => 'government'],
            'استشارات التخطيط الاستراتيجي'                               => ['category' => 'management', 'business_activity' => 'services'],
            'استشارات دراسات الجدوى'                                    => ['category' => 'management', 'business_activity' => 'services'],
            'استشارات تطوير المنتجات والخدمات'                           => ['category' => 'management', 'business_activity' => 'services'],
            'استشارات إدارة التغيير المؤسسي'                             => ['category' => 'management', 'business_activity' => 'services'],
            'استشارات الشراكات والتحالفات الإستراتيجية'                   => ['category' => 'management', 'business_activity' => 'services'],
            'استشارات إدارة المعرفة والابتكار'                           => ['category' => 'management', 'business_activity' => 'services'],
            'استشارات التطوير المؤسسي'                                  => ['category' => 'management', 'business_activity' => 'services'],

            // ── الفئة: المالية والمحاسبة والاستثمار ────────────────────
            'الاستشارات المالية'                                       => ['category' => 'finance', 'business_activity' => 'financial'],
            'الاستشارات المحاسبية'                                      => ['category' => 'finance', 'business_activity' => 'financial'],
            'الاستشارات الضريبية والزكوية'                              => ['category' => 'finance', 'business_activity' => 'financial'],
            'الاستشارات الاقتصادية'                                     => ['category' => 'finance', 'business_activity' => 'financial'],
            'الاستشارات الاستثمارية'                                    => ['category' => 'finance', 'business_activity' => 'financial'],
            'استشارات التأمين'                                          => ['category' => 'finance', 'business_activity' => 'financial'],
            'استشارات البنوك والتمويل'                                  => ['category' => 'finance', 'business_activity' => 'financial'],
            'استشارات الاستثمار الأجنبي'                                => ['category' => 'finance', 'business_activity' => 'financial'],
            'استشارات تقييم المنشآت الاقتصادية'                          => ['category' => 'finance', 'business_activity' => 'financial'],
            'استشارات تقييم المجوهرات والمعادن الثمينة'                  => ['category' => 'finance', 'business_activity' => 'financial'],
            'استشارات تقييم الأعمال الفنية والمقتنيات'                   => ['category' => 'finance', 'business_activity' => 'creative'],
            'استشارات تقييم الأصول المتخصصة'                            => ['category' => 'finance', 'business_activity' => 'financial'],
            'استشارات تقييم الشركات والاستحواذ'                          => ['category' => 'finance', 'business_activity' => 'financial'],
            'استشارات الدمج والاستحواذ'                                 => ['category' => 'finance', 'business_activity' => 'financial'],
            'استشارات إعادة الهيكلة المالية والإدارية'                   => ['category' => 'finance', 'business_activity' => 'financial'],
            'استشارات الاقتصاد الرقمي والتقنيات المالية'                 => ['category' => 'finance', 'business_activity' => 'tech'],
            'استشارات العملات الرقمية والأصول الافتراضية'                => ['category' => 'finance', 'business_activity' => 'tech'],
            'استشارات إدارة الثروات والأصول'                             => ['category' => 'finance', 'business_activity' => 'financial'],
            'استشارات إدارة الأصول والممتلكات'                           => ['category' => 'finance', 'business_activity' => 'construction'],
            'استشارات التقييم والتثمين'                                  => ['category' => 'finance', 'business_activity' => 'financial'],
            'استشارات الأصول والاستثمارات المتخصصة'                      => ['category' => 'finance', 'business_activity' => 'financial'],

            // ── الفئة: القانونية والحوكمة ─────────────────────────────
            'الاستشارات القانونية'                                      => ['category' => 'legal', 'business_activity' => 'services'],
            'استشارات العلامات التجارية'                                => ['category' => 'legal', 'business_activity' => 'retail'],
            'الاستشارات الشرعية'                                        => ['category' => 'legal', 'business_activity' => 'personal'],
            'استشارات المناقصات والعقود'                                => ['category' => 'legal', 'business_activity' => 'government'],
            'استشارات الامتيازات والتوكيلات الدولية'                    => ['category' => 'legal', 'business_activity' => 'retail'],
            'استشارات التحكيم وتسوية النزاعات'                           => ['category' => 'legal', 'business_activity' => 'services'],
            'استشارات الملكية الفكرية وبراءات الاختراع'                  => ['category' => 'legal', 'business_activity' => 'creative'],
            'استشارات الحوكمة العائلية والشركات العائلية'               => ['category' => 'legal', 'business_activity' => 'personal'],
            'استشارات العلاقات الدولية والتعاون التجاري'                => ['category' => 'legal', 'business_activity' => 'government'],
            'استشارات الأنظمة والتشريعات الحكومية'                      => ['category' => 'legal', 'business_activity' => 'government'],
            'استشارات التجارة الدولية والاتفاقيات التجارية'              => ['category' => 'legal', 'business_activity' => 'retail'],

            // ── الفئة: التجارة والتسويق ───────────────────────────────
            'الاستشارات التجارية'                                      => ['category' => 'marketing', 'business_activity' => 'retail'],
            'الاستشارات التسويقية'                                      => ['category' => 'marketing', 'business_activity' => 'retail'],
            'استشارات الامتياز التجاري (الفرنشايز)'                     => ['category' => 'marketing', 'business_activity' => 'retail'],
            'استشارات التجارة الإلكترونية'                               => ['category' => 'marketing', 'business_activity' => 'retail'],
            'استشارات الأسواق الدولية'                                  => ['category' => 'marketing', 'business_activity' => 'retail'],
            'استشارات المزادات الإلكترونية'                             => ['category' => 'marketing', 'business_activity' => 'retail'],
            'استشارات تحليل الأسواق والمنافسين'                         => ['category' => 'marketing', 'business_activity' => 'retail'],
            'استشارات خدمة العملاء وتجربة المستفيد'                     => ['category' => 'marketing', 'business_activity' => 'services'],
            'استشارات الذكاء التنافسي وتحليل البيانات'                  => ['category' => 'marketing', 'business_activity' => 'tech'],

            // ── الفئة: التقنية والتحول الرقمي ─────────────────────────
            'الاستشارات التقنية والتحول الرقمي'                         => ['category' => 'tech', 'business_activity' => 'tech'],
            'استشارات الأمن السيبراني'                                  => ['category' => 'tech', 'business_activity' => 'tech'],
            'استشارات الذكاء الاصطناعي'                                 => ['category' => 'tech', 'business_activity' => 'tech'],
            'استشارات التطبيقات والمنصات الإلكترونية'                   => ['category' => 'tech', 'business_activity' => 'tech'],
            'استشارات البيانات والتحليل الرقمي'                         => ['category' => 'tech', 'business_activity' => 'tech'],
            'استشارات المدن الذكية'                                     => ['category' => 'tech', 'business_activity' => 'government'],
            'استشارات الامتثال التقني وحوكمة البيانات'                  => ['category' => 'tech', 'business_activity' => 'tech'],
            'استشارات الأمن المعلوماتي والخصوصية'                       => ['category' => 'tech', 'business_activity' => 'tech'],

            // ── الفئة: الموارد البشرية والتعليم ───────────────────────
            'استشارات الموارد البشرية'                                  => ['category' => 'hr_edu', 'business_activity' => 'services'],
            'الاستشارات المهنية والتوظيف'                               => ['category' => 'hr_edu', 'business_activity' => 'services'],
            'الاستشارات التدريبية والتعليمية'                           => ['category' => 'hr_edu', 'business_activity' => 'education'],
            'استشارات التعليم الإلكتروني'                               => ['category' => 'hr_edu', 'business_activity' => 'education'],
            'استشارات التطوير القيادي والتنفيذي'                        => ['category' => 'hr_edu', 'business_activity' => 'services'],
            'استشارات المحتوى التعليمي والتدريب الاحترافي'               => ['category' => 'hr_edu', 'business_activity' => 'education'],
            'استشارات إدارة المواهب والكفاءات'                          => ['category' => 'hr_edu', 'business_activity' => 'services'],

            // ── الفئة: الهندسة والتصنيع ───────────────────────────────
            'الاستشارات الهندسية'                                      => ['category' => 'industrial', 'business_activity' => 'construction'],
            'الاستشارات الصناعية'                                      => ['category' => 'industrial', 'business_activity' => 'industrial'],
            'استشارات البتروكيماويات'                                   => ['category' => 'industrial', 'business_activity' => 'industrial'],
            'استشارات المعادن والتعدين'                                 => ['category' => 'industrial', 'business_activity' => 'industrial'],
            'استشارات المصانع وخطوط الإنتاج'                            => ['category' => 'industrial', 'business_activity' => 'industrial'],
            'استشارات التشغيل والصيانة'                                 => ['category' => 'industrial', 'business_activity' => 'industrial'],
            'استشارات التقييم الصناعي'                                  => ['category' => 'industrial', 'business_activity' => 'industrial'],
            'استشارات تقييم الآلات والمعدات'                            => ['category' => 'industrial', 'business_activity' => 'industrial'],
            'استشارات تقييم أضرار المركبات'                             => ['category' => 'industrial', 'business_activity' => 'transport'],
            'استشارات تقييم أضرار الممتلكات'                            => ['category' => 'industrial', 'business_activity' => 'construction'],
            'استشارات الأمن الصناعي'                                    => ['category' => 'industrial', 'business_activity' => 'industrial'],
            'استشارات السلامة المهنية'                                  => ['category' => 'industrial', 'business_activity' => 'industrial'],
            'استشارات التصنيع والتوطين الصناعي'                         => ['category' => 'industrial', 'business_activity' => 'industrial'],
            'استشارات قطاع السيارات والنقل'                             => ['category' => 'industrial', 'business_activity' => 'transport'],
            'استشارات الصناعات العسكرية'                                => ['category' => 'industrial', 'business_activity' => 'government'],
            'استشارات أنظمة الجودة العالمية (ISO)'                     => ['category' => 'industrial', 'business_activity' => 'industrial'],
            'استشارات الأمن والسلامة'                                   => ['category' => 'industrial', 'business_activity' => 'industrial'],

            // ── الفئة: العقارات والإنشاءات ────────────────────────────
            'الاستشارات العقارية'                                      => ['category' => 'realestate', 'business_activity' => 'construction'],
            'استشارات المقاولات والتطوير العقاري'                       => ['category' => 'realestate', 'business_activity' => 'construction'],
            'استشارات التقييم العقاري'                                  => ['category' => 'realestate', 'business_activity' => 'construction'],
            'استشارات إدارة وتشغيل المراكز التجارية'                    => ['category' => 'realestate', 'business_activity' => 'retail'],
            'استشارات إدارة الأملاك والمرافق'                           => ['category' => 'realestate', 'business_activity' => 'construction'],
            'استشارات الاستثمار العقاري الدولي'                         => ['category' => 'realestate', 'business_activity' => 'construction'],
            'استشارات تطوير المدن والمشاريع العملاقة'                   => ['category' => 'realestate', 'business_activity' => 'government'],

            // ── الفئة: النقل واللوجستيات ──────────────────────────────
            'الاستشارات اللوجستية والنقل'                               => ['category' => 'logistics', 'business_activity' => 'transport'],
            'الاستشارات الجمركية والتخليص'                               => ['category' => 'logistics', 'business_activity' => 'transport'],
            'استشارات التصدير والاستيراد'                               => ['category' => 'logistics', 'business_activity' => 'transport'],
            'استشارات الطيران والموانئ والخدمات البحرية'                 => ['category' => 'logistics', 'business_activity' => 'transport'],
            'استشارات الطيران'                                          => ['category' => 'logistics', 'business_activity' => 'transport'],
            'الاستشارات البحرية'                                        => ['category' => 'logistics', 'business_activity' => 'transport'],
            'استشارات الشحن والخدمات الملاحية'                          => ['category' => 'logistics', 'business_activity' => 'transport'],
            'استشارات الموانئ والخدمات اللوجستية البحرية'               => ['category' => 'logistics', 'business_activity' => 'transport'],
            'استشارات سلاسل الإمداد'                                    => ['category' => 'logistics', 'business_activity' => 'transport'],
            'استشارات النقل الذكي'                                      => ['category' => 'logistics', 'business_activity' => 'transport'],
            'استشارات الامتيازات البحرية واللوجستية'                    => ['category' => 'logistics', 'business_activity' => 'transport'],
            'استشارات تشغيل وإدارة المطارات'                            => ['category' => 'logistics', 'business_activity' => 'transport'],
            'استشارات تشغيل وإدارة الموانئ'                             => ['category' => 'logistics', 'business_activity' => 'transport'],
            'استشارات الخدمات الأرضية والطيران الخاص'                   => ['category' => 'logistics', 'business_activity' => 'transport'],

            // ── الفئة: الطاقة والبيئة والزراعة ────────────────────────
            'الاستشارات الزراعية'                                      => ['category' => 'energy_env', 'business_activity' => 'agriculture'],
            'الاستشارات البيئية'                                       => ['category' => 'energy_env', 'business_activity' => 'agriculture'],
            'استشارات التعدين والطاقة'                                  => ['category' => 'energy_env', 'business_activity' => 'energy'],
            'استشارات الطاقة المتجددة'                                  => ['category' => 'energy_env', 'business_activity' => 'energy'],
            'استشارات الهيدروجين الأخضر والاستدامة'                     => ['category' => 'energy_env', 'business_activity' => 'energy'],
            'استشارات الطاقات البديلة'                                  => ['category' => 'energy_env', 'business_activity' => 'energy'],
            'استشارات الاستدامة والمسؤولية الاجتماعية'                  => ['category' => 'energy_env', 'business_activity' => 'services'],
            'استشارات الامتثال البيئي والاستدامة الصناعية'              => ['category' => 'energy_env', 'business_activity' => 'industrial'],
            'استشارات الأمن الغذائي'                                    => ['category' => 'energy_env', 'business_activity' => 'agriculture'],
            'استشارات الأمن المائي'                                     => ['category' => 'energy_env', 'business_activity' => 'agriculture'],
            'استشارات الثروة السمكية'                                   => ['category' => 'energy_env', 'business_activity' => 'agriculture'],

            // ── الفئة: الصحة والدواء ──────────────────────────────────
            'الاستشارات الطبية والصحية'                                 => ['category' => 'health', 'business_activity' => 'healthcare'],
            'استشارات الأغذية والدواء'                                  => ['category' => 'health', 'business_activity' => 'agriculture'],
            'استشارات القطاع الصحي وإدارة المستشفيات'                   => ['category' => 'health', 'business_activity' => 'healthcare'],
            'استشارات الخدمات الصحية الرقمية'                           => ['category' => 'health', 'business_activity' => 'healthcare'],
            'استشارات الصناعات الدوائية'                                => ['category' => 'health', 'business_activity' => 'healthcare'],

            // ── الفئة: السياحة والضيافة والفعاليات ────────────────────
            'الاستشارات السياحية والفندقية'                             => ['category' => 'tourism', 'business_activity' => 'hospitality'],
            'استشارات الفعاليات والمناسبات'                             => ['category' => 'tourism', 'business_activity' => 'hospitality'],
            'استشارات الفندقة والضيافة'                                 => ['category' => 'tourism', 'business_activity' => 'hospitality'],
            'استشارات تنظيم المعارض والمؤتمرات'                         => ['category' => 'tourism', 'business_activity' => 'hospitality'],
            'استشارات سلاسل المطاعم والمقاهي'                           => ['category' => 'tourism', 'business_activity' => 'hospitality'],
            'استشارات إدارة الحشود وتنظيم الفعاليات الكبرى'             => ['category' => 'tourism', 'business_activity' => 'hospitality'],

            // ── الفئة: الإعلام والمحتوى والعلاقات العامة ─────────────
            'الاستشارات الإعلامية والعلاقات العامة'                     => ['category' => 'media', 'business_activity' => 'creative'],
            'استشارات الإعلام الرقمي وصناعة المحتوى'                    => ['category' => 'media', 'business_activity' => 'creative'],
            'استشارات المحتوى الرقمي والإنتاج الإعلامي'                 => ['category' => 'media', 'business_activity' => 'creative'],
            'استشارات إدارة السمعة والعلامة المؤسسية'                   => ['category' => 'media', 'business_activity' => 'services'],
            'استشارات الاقتصاد الإبداعي'                                => ['category' => 'media', 'business_activity' => 'creative'],

            // ── الفئة: القطاع الحكومي وغير الربحي ────────────────────
            'استشارات الدفاع المدني وإدارة الأزمات'                     => ['category' => 'govt', 'business_activity' => 'government'],
            'استشارات العلاقات الحكومية'                                => ['category' => 'govt', 'business_activity' => 'government'],
            'استشارات الأوقاف والجمعيات غير الربحية'                    => ['category' => 'govt', 'business_activity' => 'nonprofit'],
            'استشارات الخدمات البلدية والتراخيص'                        => ['category' => 'govt', 'business_activity' => 'government'],
            'استشارات التحول الحكومي والخدمات الإلكترونية'              => ['category' => 'govt', 'business_activity' => 'government'],
            'استشارات القطاع العسكري والدفاعي'                          => ['category' => 'govt', 'business_activity' => 'government'],

            // ── الفئة: الرياضة والترفيه ──────────────────────────────
            'الاستشارات الرياضية'                                      => ['category' => 'sports', 'business_activity' => 'hospitality'],
            'استشارات القطاع الترفيهي'                                  => ['category' => 'sports', 'business_activity' => 'hospitality'],
            'استشارات قطاع الرياضات الإلكترونية'                        => ['category' => 'sports', 'business_activity' => 'tech'],
            'استشارات الاستثمار الرياضي وإدارة الأندية'                 => ['category' => 'sports', 'business_activity' => 'services'],

            // ── الفئة: الاستشارات الشخصية والاجتماعية ────────────────
            'الاستشارات الأسرية والاجتماعية'                            => ['category' => 'personal', 'business_activity' => 'personal'],
            'الاستشارات النفسية'                                        => ['category' => 'personal', 'business_activity' => 'personal'],
        ];
    }

    /**
     * بيانات فئة معينة مع fallback آمن.
     */
    public static function category(?string $key): array
    {
        if ($key === null || $key === '') {
            return [
                'label_ar' => 'أخرى',
                'label_en' => 'Other',
                'icon'     => 'ti-tag',
            ];
        }

        return self::CATEGORIES[$key] ?? [
            'label_ar' => 'أخرى',
            'label_en' => 'Other',
            'icon'     => 'ti-tag',
        ];
    }

    /**
     * بيانات نشاط تجاري معيّن مع fallback آمن.
     */
    public static function businessActivity(?string $key): array
    {
        if ($key === null || $key === '') {
            return [
                'label_ar' => 'عام',
                'label_en' => 'General',
                'icon'     => 'ti-tag',
            ];
        }

        return self::BUSINESS_ACTIVITIES[$key] ?? [
            'label_ar' => 'عام',
            'label_en' => 'General',
            'icon'     => 'ti-tag',
        ];
    }

    public static function categoryLabel(?string $key): string
    {
        return self::category($key)['label_ar'];
    }

    public static function categoryIcon(?string $key): string
    {
        return self::category($key)['icon'];
    }

    public static function activityLabel(?string $key): string
    {
        return self::businessActivity($key)['label_ar'];
    }

    public static function activityIcon(?string $key): string
    {
        return self::businessActivity($key)['icon'];
    }

    /**
     * استخراج بيانات تخصّص (فئة + نشاط) باسمه العربي مع fallback آمن.
     */
    public static function specialtyMeta(string $nameAr): array
    {
        $meta = self::catalog()[trim($nameAr)] ?? null;

        if ($meta && isset(self::CATEGORIES[$meta['category']], self::BUSINESS_ACTIVITIES[$meta['business_activity']])) {
            return $meta;
        }

        return ['category' => null, 'business_activity' => null];
    }
}