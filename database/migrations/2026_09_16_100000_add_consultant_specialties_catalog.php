<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| كتالوج تخصصات المستشارين (150 تخصصاً استشارياً)
|--------------------------------------------------------------------------
|
| يضيف عمود is_consultant إلى bs_specialties لتمييز كتالوج المستشارين عن
| كتالوج المكاتب المساندة، ويُدرج القائمة المعتمدة (150 تخصصاً) موزّعة على
| الأنواع الحالية حسب طبيعتها (law/services/customs/accounting/engineering/
| freelance) كي يبقى نموذج التسجيل كما هو.
|
| المبدأ:
|   - الإدراج idempotent: يتخطى أي تخصص موجود بالفعل بنفس (office_type + name_ar).
|   - العمود يُضاف بفلتر hasColumn للتوافق مع MySQL الحية و SQLite للاختبارات.
|   - تنفيذ الـ up مكررّاً لا يُنتج تكراراً.
|
*/

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_specialties')) {
            if (! $schema->hasColumn('bs_specialties', 'is_consultant')) {
                $schema->table('bs_specialties', function (Blueprint $table) {
                    $table->boolean('is_consultant')->default(false);
                });
            }

            $db = DB::connection('business');

            $existing = $db->table('bs_specialties')
                ->get(['office_type', 'name_ar'])
                ->map(fn ($s) => $s->office_type.'|'.$s->name_ar)
                ->all();

            $rows = [];

            foreach ($this->consultantCatalog() as $index => $entry) {
                $key = $entry['office_type'].'|'.$entry['name_ar'];

                if (in_array($key, $existing, true)) {
                    continue;
                }

                $rows[] = [
                    'office_type'   => $entry['office_type'],
                    'name_ar'       => $entry['name_ar'],
                    'name_en'       => null,
                    'is_active'     => true,
                    'is_consultant' => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            foreach (array_chunk($rows, 100) as $chunk) {
                $db->table('bs_specialties')->insert($chunk);
            }
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if (! $schema->hasTable('bs_specialties')) {
            return;
        }

        $db = DB::connection('business');

        $names = array_column($this->consultantCatalog(), 'name_ar');

        $db->table('bs_specialties')
            ->where('is_consultant', true)
            ->whereIn('name_ar', $names)
            ->delete();

        if ($schema->hasColumn('bs_specialties', 'is_consultant')) {
            $schema->table('bs_specialties', function (Blueprint $table) {
                $table->dropColumn('is_consultant');
            });
        }
    }

    /**
     * كتالوج تخصصات المستشارين المعتمد (150 تخصصاً) موزّع على الأنواع الحالية.
     *
     * @return array<int, array{name_ar: string, office_type: string}>
     */
    protected function consultantCatalog(): array
    {
        $catalog = [
            // services (استشارات الأعمال والخدمات العامة)
            'الاستشارات الإدارية' => 'services',
            'الاستشارات التجارية' => 'services',
            'الاستشارات الاقتصادية' => 'services',
            'الاستشارات العقارية' => 'services',
            'استشارات الموارد البشرية' => 'services',
            'الاستشارات المهنية والتوظيف' => 'services',
            'الاستشارات التدريبية والتعليمية' => 'services',
            'الاستشارات اللوجستية والنقل' => 'services',
            'الاستشارات السياحية والفندقية' => 'services',
            'استشارات الفعاليات والمناسبات' => 'services',
            'الاستشارات الإعلامية والعلاقات العامة' => 'services',
            'الاستشارات الأسرية والاجتماعية' => 'services',
            'الاستشارات النفسية' => 'services',
            'الاستشارات الطبية والصحية' => 'services',
            'الاستشارات الرياضية' => 'services',
            'استشارات الجودة والحوكمة' => 'services',
            'استشارات الامتثال وإدارة المخاطر' => 'services',
            'استشارات ريادة الأعمال والمنشآت الناشئة' => 'services',
            'استشارات إدارة المشاريع' => 'services',
            'استشارات الأسواق الدولية' => 'services',
            'استشارات سلاسل الإمداد' => 'services',
            'استشارات إدارة وتشغيل المنشآت' => 'services',
            'استشارات الأوقاف والجمعيات غير الربحية' => 'services',
            'استشارات الابتكار وتطوير الأعمال' => 'services',
            'استشارات التطوير القيادي والتنفيذي' => 'services',
            'استشارات القطاع الصحي وإدارة المستشفيات' => 'services',
            'استشارات الفندقة والضيافة' => 'services',
            'استشارات تنظيم المعارض والمؤتمرات' => 'services',
            'استشارات التقييم العقاري' => 'services',
            'استشارات التحول المؤسسي والتخصيص' => 'services',
            'استشارات الخصخصة والتخصيص' => 'services',
            'استشارات سلاسل المطاعم والمقاهي' => 'services',
            'استشارات إدارة وتشغيل المراكز التجارية' => 'services',
            'استشارات إدارة الأملاك والمرافق' => 'services',
            'استشارات إدارة الحشود وتنظيم الفعاليات الكبرى' => 'services',
            'استشارات الخدمات البلدية والتراخيص' => 'services',
            'استشارات الاستثمار العقاري الدولي' => 'services',
            'استشارات المحتوى التعليمي والتدريب الاحترافي' => 'services',
            'استشارات التحول الحكومي والخدمات الإلكترونية' => 'services',
            'استشارات قطاع السيارات والنقل' => 'services',
            'استشارات القطاع العسكري والدفاعي' => 'services',
            'استشارات الاستدامة والمسؤولية الاجتماعية' => 'services',
            'استشارات الأغذية والدواء' => 'services',
            'استشارات الأمن والسلامة' => 'services',
            'استشارات الدفاع المدني وإدارة الأزمات' => 'services',
            'استشارات التخطيط الاستراتيجي' => 'services',
            'استشارات دراسات الجدوى' => 'services',
            'استشارات تطوير المنتجات والخدمات' => 'services',
            'استشارات خدمة العملاء وتجربة المستفيد' => 'services',
            'استشارات إدارة التغيير المؤسسي' => 'services',
            'استشارات الأمن الغذائي' => 'services',
            'استشارات الثروة السمكية' => 'services',
            'استشارات الاقتصاد الإبداعي' => 'services',
            'استشارات إدارة المواهب والكفاءات' => 'services',
            'استشارات العلاقات الدولية والتعاون التجاري' => 'services',
            'استشارات الشراكات والتحالفات الإستراتيجية' => 'services',
            'استشارات إدارة المعرفة والابتكار' => 'services',
            'استشارات تشغيل وإدارة المطارات' => 'services',
            'استشارات تشغيل وإدارة الموانئ' => 'services',
            'استشارات الخدمات الأرضية والطيران الخاص' => 'services',
            'استشارات القطاع الترفيهي' => 'services',
            'استشارات قطاع الرياضات الإلكترونية' => 'services',
            'استشارات الاستثمار الرياضي وإدارة الأندية' => 'services',
            'استشارات التطوير المؤسسي' => 'services',

            // law (استشارات قانونية وتنظيمية)
            'الاستشارات القانونية' => 'law',
            'استشارات العلامات التجارية' => 'law',
            'استشارات الامتياز التجاري (الفرنشايز)' => 'law',
            'الاستشارات الشرعية' => 'law',
            'استشارات المناقصات والعقود' => 'law',
            'استشارات العلاقات الحكومية' => 'law',
            'استشارات الامتيازات والتوكيلات الدولية' => 'law',
            'استشارات الاستثمار الأجنبي' => 'law',
            'استشارات التحكيم وتسوية النزاعات' => 'law',
            'استشارات الملكية الفكرية وبراءات الاختراع' => 'law',
            'استشارات الحوكمة العائلية والشركات العائلية' => 'law',
            'استشارات الأنظمة والتشريعات الحكومية' => 'law',
            'استشارات التجارة الدولية والاتفاقيات التجارية' => 'law',

            // accounting (استشارات مالية ومحاسبية)
            'الاستشارات المالية' => 'accounting',
            'الاستشارات المحاسبية' => 'accounting',
            'الاستشارات الضريبية والزكوية' => 'accounting',
            'الاستشارات الاستثمارية' => 'accounting',
            'استشارات التأمين' => 'accounting',
            'استشارات البنوك والتمويل' => 'accounting',
            'استشارات تقييم المنشآت الاقتصادية' => 'accounting',
            'استشارات تقييم المجوهرات والمعادن الثمينة' => 'accounting',
            'استشارات تقييم الأعمال الفنية والمقتنيات' => 'accounting',
            'استشارات تقييم الأصول المتخصصة' => 'accounting',
            'استشارات تقييم الشركات والاستحواذ' => 'accounting',
            'استشارات الدمج والاستحواذ' => 'accounting',
            'استشارات إعادة الهيكلة المالية والإدارية' => 'accounting',
            'استشارات العملات الرقمية والأصول الافتراضية' => 'accounting',
            'استشارات إدارة الثروات والأصول' => 'accounting',
            'استشارات إدارة الأصول والممتلكات' => 'accounting',
            'استشارات التقييم والتثمين' => 'accounting',
            'استشارات الأصول والاستثمارات المتخصصة' => 'accounting',

            // engineering (استشارات هندسية وصناعية وطاقة)
            'الاستشارات الهندسية' => 'engineering',
            'الاستشارات الصناعية' => 'engineering',
            'الاستشارات الزراعية' => 'engineering',
            'الاستشارات البيئية' => 'engineering',
            'استشارات التعدين والطاقة' => 'engineering',
            'استشارات البتروكيماويات' => 'engineering',
            'استشارات المعادن والتعدين' => 'engineering',
            'استشارات الطيران' => 'engineering',
            'استشارات المقاولات والتطوير العقاري' => 'engineering',
            'استشارات المصانع وخطوط الإنتاج' => 'engineering',
            'استشارات التصنيع والتوطين الصناعي' => 'engineering',
            'استشارات المدن الذكية' => 'engineering',
            'استشارات الطاقة المتجددة' => 'engineering',
            'استشارات الطاقات البديلة' => 'engineering',
            'استشارات الهيدروجين الأخضر والاستدامة' => 'engineering',
            'استشارات النقل الذكي' => 'engineering',
            'استشارات التشغيل والصيانة' => 'engineering',
            'استشارات التقييم الصناعي' => 'engineering',
            'استشارات تقييم الآلات والمعدات' => 'engineering',
            'استشارات تقييم أضرار المركبات' => 'engineering',
            'استشارات تقييم أضرار الممتلكات' => 'engineering',
            'استشارات الأمن الصناعي' => 'engineering',
            'استشارات السلامة المهنية' => 'engineering',
            'استشارات الأمن المائي' => 'engineering',
            'استشارات الامتثال البيئي والاستدامة الصناعية' => 'engineering',
            'استشارات تطوير المدن والمشاريع العملاقة' => 'engineering',
            'استشارات الصناعات العسكرية' => 'engineering',
            'استشارات الصناعات الدوائية' => 'engineering',
            'استشارات أنظمة الجودة العالمية (ISO)' => 'engineering',

            // customs (استشارات جمركية وملاحية)
            'الاستشارات الجمركية والتخليص' => 'customs',
            'استشارات التصدير والاستيراد' => 'customs',
            'استشارات الطيران والموانئ والخدمات البحرية' => 'customs',
            'الاستشارات البحرية' => 'customs',
            'استشارات الشحن والخدمات الملاحية' => 'customs',
            'استشارات الموانئ والخدمات اللوجستية البحرية' => 'customs',
            'استشارات الامتيازات البحرية واللوجستية' => 'customs',

            // freelance (استشارات تقنية ورقمية وإبداعية)
            'الاستشارات التقنية والتحول الرقمي' => 'freelance',
            'استشارات الأمن السيبراني' => 'freelance',
            'استشارات الذكاء الاصطناعي' => 'freelance',
            'الاستشارات التسويقية' => 'freelance',
            'استشارات التجارة الإلكترونية' => 'freelance',
            'استشارات التطبيقات والمنصات الإلكترونية' => 'freelance',
            'استشارات البيانات والتحليل الرقمي' => 'freelance',
            'استشارات الإعلام الرقمي وصناعة المحتوى' => 'freelance',
            'استشارات التعليم الإلكتروني' => 'freelance',
            'استشارات المزادات الإلكترونية' => 'freelance',
            'استشارات المحتوى الرقمي والإنتاج الإعلامي' => 'freelance',
            'استشارات إدارة السمعة والعلامة المؤسسية' => 'freelance',
            'استشارات الامتثال التقني وحوكمة البيانات' => 'freelance',
            'استشارات الاقتصاد الرقمي والتقنيات المالية' => 'freelance',
            'استشارات الخدمات الصحية الرقمية' => 'freelance',
            'استشارات الذكاء التنافسي وتحليل البيانات' => 'freelance',
            'استشارات التقنية المالية والتأمين التقني' => 'freelance',
            'استشارات تحليل الأسواق والمنافسين' => 'freelance',
            'استشارات الأمن المعلوماتي والخصوصية' => 'freelance',
        ];

        $sorted = collect($catalog)
            ->sortBy(fn ($type, $name) => [$catalog[$name] ?? '', $name])
            ->mapWithKeys(fn ($type, $name) => [$name => ['name_ar' => $name, 'office_type' => $type]])
            ->values();

        if ($sorted->count() !== 150) {
            throw new RuntimeException('كتالوج المستشارين يجب أن يحتوي 150 تخصصاً بالضبط (وجد '.$sorted->count().').');
        }

        return $sorted->all();
    }
};