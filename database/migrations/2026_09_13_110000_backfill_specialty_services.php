<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| الربط الواقعي بين تخصصات المكاتب والخدمات الحكومية الحقيقية
|--------------------------------------------------------------------------
|
| يقوم هذا الملف بربط كل تخصص (bs_specialties) بالخدمات الحكومية الفعلية
| (bs_services) المتوافرة في قاعدة البيانات، بحيث يُظهر نموذج تسجيل المكتب
| الخدمات المرتبطة حقيقيةً بدلاً من الكتالوج المرمّز في عناصر التحكم.
|
| المبدأ:
|   - التخصصات تُحل بالاسم (office_type + name_ar) لضمان الاستقرار رغم اختلاف
|     المعرفات، مع معالجة الأسماء المكررة (الزكاة والضرائب في accounting/law).
|   - الخدمات مرتبطة بمعرّفاتها الحالية لكن يُتحقق من وجودها ونشاطها وقت
|     التنفيذ، فلا تُدرج أي خدمة غير موجودة.
|   - الإدراج idempotent عبر insertOrIgnore + القيد الفريد (specialty_id, service_id).
|   - الأنواع التي لا تملك خدمات فعلية (customs/engineering/freelance وأغلب
|     تخصصات المحاماة التخصصية) تبقى على الكتالوج المرمّز كـ fallback.
|
*/

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if (! $schema->hasTable('bs_specialties')
            || ! $schema->hasTable('bs_services')
            || ! $schema->hasTable('bs_specialty_services')) {
            return;
        }

        $db = DB::connection('business');

        $activeServiceIds = $db->table('bs_services')->where('is_active', 1)->pluck('id')->all();

        $specialties = $db->table('bs_specialties')
            ->where('is_active', 1)
            ->get(['id', 'office_type', 'name_ar'])
            ->keyBy(fn ($s) => $s->office_type.'|'.$s->name_ar);

        $rows = [];

        foreach ($this->specialtyServiceMap() as $entry) {
            $specialty = $specialties->get($entry['type'].'|'.$entry['specialty']);

            if (! $specialty) {
                continue;
            }

            foreach ($entry['services'] as $serviceId) {
                if (! in_array($serviceId, $activeServiceIds, true)) {
                    continue;
                }

                $rows[] = [
                    'specialty_id' => $specialty->id,
                    'service_id'   => $serviceId,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            $db->table('bs_specialty_services')->insertOrIgnore($chunk);
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if (! $schema->hasTable('bs_specialties')
            || ! $schema->hasTable('bs_specialty_services')) {
            return;
        }

        $db = DB::connection('business');

        $specialties = $db->table('bs_specialties')
            ->where('is_active', 1)
            ->get(['id', 'office_type', 'name_ar'])
            ->keyBy(fn ($s) => $s->office_type.'|'.$s->name_ar);

        foreach ($this->specialtyServiceMap() as $entry) {
            $specialty = $specialties->get($entry['type'].'|'.$entry['specialty']);

            if (! $specialty) {
                continue;
            }

            $db->table('bs_specialty_services')
                ->where('specialty_id', $specialty->id)
                ->whereIn('service_id', $entry['services'])
                ->delete();
        }
    }

    /**
     * خريطة الربط: نوع المكتب + اسم التخصص => معرّفات الخدمات المرتبطة.
     */
    protected function specialtyServiceMap(): array
    {
        $establishmentServices = [
            80, 81, 82, 84, 85, 86, 100, 101, 102, 105, 106, 107, 108, 109, 110, 154,
        ];

        $investmentServices = range(135, 162);

        return [
            // === خدمات (مكاتب إنجاز المعاملات الحكومية) ===
            [
                'type'       => 'services',
                'specialty'  => 'الخدمات الحكومية',
                'services'   => [
                    1, 2, 3, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 22, 23,
                    26, 27, 28, 42, 43, 44, 45, 46, 163, 164, 165, 166, 169, 170, 171, 183, 184,
                ],
            ],
            [
                'type'       => 'services',
                'specialty'  => 'خدمات التعقيب',
                'services'   => [
                    1, 2, 3, 14, 15, 42, 43, 44, 169, 170, 171, 172, 173, 174, 175,
                    176, 177, 178, 179, 180, 181, 182, 183,
                ],
            ],
            [
                'type'       => 'services',
                'specialty'  => 'خدمات التوثيق',
                'services'   => [9, 10, 11, 20, 65, 125, 169, 170, 171],
            ],
            [
                'type'       => 'services',
                'specialty'  => 'خدمات تأسيس الشركات',
                'services'   => $establishmentServices,
            ],

            // === محاسبة ===
            [
                'type'       => 'accounting',
                'specialty'  => 'المحاسبة المالية',
                'services'   => [17, 18, 26, 27, 28],
            ],
            [
                'type'       => 'accounting',
                'specialty'  => 'الزكاة والضرائب',
                'services'   => [17, 18, 26, 27, 28],
            ],

            // === قانون (محاماة) ===
            [
                'type'       => 'law',
                'specialty'  => 'القضايا التجارية',
                'services'   => [9, 111, 115, 118, 120, 126],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'قضايا الشركات',
                'services'   => [9, 91, 105, 120],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'تأسيس الشركات والتحول النظامي',
                'services'   => $establishmentServices,
            ],
            [
                'type'       => 'law',
                'specialty'  => 'حوكمة الشركات والامتثال',
                'services'   => [72, 73, 74, 76, 77, 78, 91, 105, 129],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'عمليات الاندماج والاستحواذ',
                'services'   => [91, 105, 137],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'الاستثمار الأجنبي',
                'services'   => array_merge([80, 81, 82], $investmentServices),
            ],
            [
                'type'       => 'law',
                'specialty'  => 'الامتياز التجاري (الفرنشايز)',
                'services'   => [87, 89, 118, 124],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'الوكالات التجارية',
                'services'   => [98, 121, 130, 131, 132],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'عقود التوزيع والامتياز',
                'services'   => [87, 89, 98, 130, 131, 132],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'العلامات التجارية',
                'services'   => [70, 71, 93, 123],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'الجرائم المعلوماتية',
                'services'   => [40, 41],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'حماية البيانات والخصوصية.',
                'services'   => [40, 41],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'العقود وصياغتها ومراجعتها',
                'services'   => [9, 20, 105, 125, 184],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'التحكيم التجاري',
                'services'   => [9],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'الوساطة وتسوية المنازعات',
                'services'   => [9, 111, 120],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'التنفيذ وإجراءات محاكم التنفيذ',
                'services'   => [9, 11],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'العقارات',
                'services'   => [11, 36, 37, 135, 136, 139, 149],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'التطوير العقاري',
                'services'   => [37, 142, 156, 168],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'المقاولات والإنشاءات',
                'services'   => [168],
            ],
            [
                'type'       => 'law',
                'specialty'  => '26. القضايا العمالية',
                'services'   => [14, 15, 16, 42, 43, 44, 183, 184],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'التأمينات الاجتماعية',
                'services'   => [42, 43, 44],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'الأحوال الشخصية',
                'services'   => [3, 10],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'الزكاة والضرائب',
                'services'   => [17, 18, 26, 27, 28],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'الجمارك والتجارة الدولية',
                'services'   => [26, 27, 28, 45, 46],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'البنوك والتمويل',
                'services'   => [51, 52],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'الأوراق المالية وسوق المال',
                'services'   => [31, 32],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'النقل والخدمات اللوجستية',
                'services'   => [45, 46, 47, 48],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'قانون الطيران',
                'services'   => [29, 30],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'الطاقة والتعدين',
                'services'   => [49, 50],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'القطاع الصحي والأخطاء الطبية',
                'services'   => [12, 13, 33, 34, 35],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'التعليم والجامعات',
                'services'   => [19, 20, 21, 65],
            ],
            [
                'type'       => 'law',
                'specialty'  => 'الاستشارات القانونية والتمثيل القضائي',
                'services'   => [9, 20, 105, 125, 184],
            ],
        ];
    }
};