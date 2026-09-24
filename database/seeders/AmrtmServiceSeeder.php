<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Entity;
use App\Models\GovService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AmrtmServiceSeeder extends Seeder
{
    public function run(): void
    {
        DB::connection('business')->statement('SET FOREIGN_KEY_CHECKS=0');
        GovService::truncate();
        Entity::truncate();
        Category::truncate();
        DB::connection('business')->statement('SET FOREIGN_KEY_CHECKS=1');

        // Ministries
        $min = Category::create([
            'key' => 'ministries', 'name_ar' => 'الوزارات', 'name_en' => 'Ministries',
            'icon' => 'ti-building-bank', 'color' => '#1A237E', 'bg' => 'rgba(26,35,126,.1)', 'sort_order' => 1,
        ]);

        $this->addEntities($min->id, [
            ['name_ar'=>'وزارة الداخلية','name_en'=>'Ministry of Interior','icon'=>'ti-shield','color'=>'#C62828','bg'=>'rgba(198,40,40,.11)','tag_ar'=>'الأمن والمواطنة','tag_en'=>'Security & Citizenship','services'=>[
                ['name_ar'=>'استخراج جواز السفر','name_en'=>'Passport Issuance','icon'=>'ti-id','price'=>300,'days'=>5],
                ['name_ar'=>'تجديد بطاقة الهوية','name_en'=>'ID Card Renewal','icon'=>'ti-id-badge','price'=>100,'days'=>3],
                ['name_ar'=>'استخراج وثيقة السجل العائلي','name_en'=>'Family Registration Document','icon'=>'ti-file','price'=>150,'days'=>4],
            ]],
            ['name_ar'=>'وزارة الاتصالات وتقنية المعلومات','name_en'=>'Ministry of Communications & IT','icon'=>'ti-wifi','color'=>'#1565C0','bg'=>'rgba(21,101,192,.11)','tag_ar'=>'التقنية والرقمنة','tag_en'=>'Technology & Digital','services'=>[
                ['name_ar'=>'تراخيص الاتصالات','name_en'=>'Telecom Licenses','icon'=>'ti-signal','price'=>500,'days'=>7],
                ['name_ar'=>'خدمات البريد الإلكتروني الحكومي','name_en'=>'Government Email Services','icon'=>'ti-mail','price'=>200,'days'=>3],
            ]],
            ['name_ar'=>'وزارة التجارة','name_en'=>'Ministry of Commerce','icon'=>'ti-shopping-cart','color'=>'#AD1457','bg'=>'rgba(173,20,87,.11)','tag_ar'=>'التجارة والأعمال','tag_en'=>'Trade & Business','services'=>[
                ['name_ar'=>'تسجيل شركة جديدة','name_en'=>'Company Registration','icon'=>'ti-building','price'=>800,'days'=>10],
                ['name_ar'=>'تجديد السجل التجاري','name_en'=>'Commercial Registration Renewal','icon'=>'ti-refresh','price'=>400,'days'=>5],
                ['name_ar'=>'استخراج شهادة عدم تعارض','name_en'=>'Non-Conflict Certificate','icon'=>'ti-certificate','price'=>200,'days'=>3],
            ]],
            ['name_ar'=>'وزارة العدل','name_en'=>'Ministry of Justice','icon'=>'ti-scale','color'=>'#4A148C','bg'=>'rgba(74,20,140,.11)','tag_ar'=>'القضاء والتوثيق','tag_en'=>'Justice & Notarization','services'=>[
                ['name_ar'=>'التوثيق القانوني','name_en'=>'Legal Documentation','icon'=>'ti-file-certificate','price'=>350,'days'=>4],
                ['name_ar'=>'استخراج وثيقة زواج','name_en'=>'Marriage Certificate','icon'=>'ti-heart','price'=>200,'days'=>3],
                ['name_ar'=>'إفراغ العقارات','name_en'=>'Property Transfer','icon'=>'ti-home','price'=>600,'days'=>7],
            ]],
            ['name_ar'=>'وزارة الصحة','name_en'=>'Ministry of Health','icon'=>'ti-heart-rate-monitor','color'=>'#C62828','bg'=>'rgba(198,40,40,.09)','tag_ar'=>'الرعاية الصحية','tag_en'=>'Healthcare','services'=>[
                ['name_ar'=>'تسجيل منشأة صحية','name_en'=>'Health Facility Registration','icon'=>'ti-building-hospital','price'=>1000,'days'=>14],
                ['name_ar'=>'ترخيص الممارسة الصحية','name_en'=>'Health Practice License','icon'=>'ti-stethoscope','price'=>500,'days'=>7],
            ]],
            ['name_ar'=>'وزارة الموارد البشرية','name_en'=>'Ministry of Human Resources','icon'=>'ti-users','color'=>'#0277BD','bg'=>'rgba(2,119,189,.11)','tag_ar'=>'العمل والتوظيف','tag_en'=>'Labor & Employment','services'=>[
                ['name_ar'=>'استخراج تصريح عمل','name_en'=>'Work Permit','icon'=>'ti-user-check','price'=>450,'days'=>6],
                ['name_ar'=>'نقل الكفالة','name_en'=>'Sponsorship Transfer','icon'=>'ti-transfer','price'=>300,'days'=>5],
                ['name_ar'=>'تسجيل منشأة تجارية','name_en'=>'Business Establishment Registration','icon'=>'ti-building','price'=>600,'days'=>8],
            ]],
            ['name_ar'=>'وزارة المالية','name_en'=>'Ministry of Finance','icon'=>'ti-coin','color'=>'#1A237E','bg'=>'rgba(26,35,126,.11)','tag_ar'=>'المالية والميزانية','tag_en'=>'Finance & Budget','services'=>[
                ['name_ar'=>'الخدمات الضريبية','name_en'=>'Tax Services','icon'=>'ti-receipt-tax','price'=>300,'days'=>5],
                ['name_ar'=>'استخراج شهادة الزكاة','name_en'=>'Zakat Certificate','icon'=>'ti-certificate','price'=>150,'days'=>3],
            ]],
            ['name_ar'=>'وزارة التعليم','name_en'=>'Ministry of Education','icon'=>'ti-school','color'=>'#00695C','bg'=>'rgba(0,105,92,.11)','tag_ar'=>'التعليم والتدريب','tag_en'=>'Education & Training','services'=>[
                ['name_ar'=>'قبول الطلاب','name_en'=>'Student Admission','icon'=>'ti-user-plus','price'=>50,'days'=>2],
                ['name_ar'=>'توثيق الشهادات','name_en'=>'Certificate Authentication','icon'=>'ti-certificate','price'=>200,'days'=>4],
                ['name_ar'=>'ترخيص منشأة تعليمية','name_en'=>'Educational Institution License','icon'=>'ti-building-school','price'=>1500,'days'=>20],
            ]],
            ['name_ar'=>'وزارة السياحة','name_en'=>'Ministry of Tourism','icon'=>'ti-plane','color'=>'#00838F','bg'=>'rgba(0,131,143,.11)','tag_ar'=>'السياحة والضيافة','tag_en'=>'Tourism & Hospitality','services'=>[
                ['name_ar'=>'تراخيص الفنادق','name_en'=>'Hotel Licenses','icon'=>'ti-building','price'=>2000,'days'=>14],
                ['name_ar'=>'ترخيص وكالة سياحية','name_en'=>'Travel Agency License','icon'=>'ti-map','price'=>1200,'days'=>10],
            ]],
            ['name_ar'=>'وزارة الاستثمار','name_en'=>'Ministry of Investment','icon'=>'ti-trending-up','color'=>'#2E7D32','bg'=>'rgba(46,125,50,.11)','tag_ar'=>'الاستثمار والأعمال','tag_en'=>'Investment & Business','services'=>[
                ['name_ar'=>'تراخيص الاستثمار الأجنبي','name_en'=>'Foreign Investment License','icon'=>'ti-world','price'=>3000,'days'=>15],
                ['name_ar'=>'ترخيص المنشأة الاستثمارية','name_en'=>'Investment Facility License','icon'=>'ti-building-factory','price'=>2000,'days'=>12],
            ]],
        ]);

        // Authorities
        $aut = Category::create([
            'key' => 'authorities', 'name_ar' => 'الهيئات', 'name_en' => 'Authorities',
            'icon' => 'ti-award', 'color' => '#6A1B9A', 'bg' => 'rgba(106,27,154,.1)', 'sort_order' => 2,
        ]);

        $this->addEntities($aut->id, [
            ['name_ar'=>'هيئة الزكاة والضرائب والجمارك','name_en'=>'ZATCA','icon'=>'ti-receipt-tax','color'=>'#6A1B9A','bg'=>'rgba(106,27,154,.1)','tag_ar'=>'الضرائب والجمارك','tag_en'=>'Tax & Customs','services'=>[
                ['name_ar'=>'التسجيل الضريبي','name_en'=>'Tax Registration','icon'=>'ti-file-description','price'=>0,'days'=>5],
                ['name_ar'=>'تقديم الإقرار الضريبي','name_en'=>'Tax Return Submission','icon'=>'ti-file-invoice','price'=>0,'days'=>3],
                ['name_ar'=>'استرداد ضريبة القيمة المضافة','name_en'=>'VAT Refund','icon'=>'ti-coin','price'=>100,'days'=>14],
            ]],
            ['name_ar'=>'الهيئة العامة للطيران المدني','name_en'=>'GACA','icon'=>'ti-plane','color'=>'#0277BD','bg'=>'rgba(2,119,189,.1)','tag_ar'=>'الطيران المدني','tag_en'=>'Civil Aviation','services'=>[
                ['name_ar'=>'تراخيص الطيران','name_en'=>'Aviation Licenses','icon'=>'ti-license','price'=>5000,'days'=>30],
                ['name_ar'=>'ترخيص الطائرات بدون طيار','name_en'=>'Drone License','icon'=>'ti-drone','price'=>500,'days'=>7],
            ]],
            ['name_ar'=>'هيئة السوق المالية','name_en'=>'CMA','icon'=>'ti-chart-candlestick','color'=>'#1B5E20','bg'=>'rgba(27,94,32,.1)','tag_ar'=>'السوق المالية','tag_en'=>'Financial Market','services'=>[
                ['name_ar'=>'تراخيص الأوراق المالية','name_en'=>'Securities Licenses','icon'=>'ti-chart-line','price'=>10000,'days'=>45],
                ['name_ar'=>'تسجيل الصناديق الاستثمارية','name_en'=>'Investment Fund Registration','icon'=>'ti-building-bank','price'=>5000,'days'=>30],
            ]],
            ['name_ar'=>'الهيئة العامة للغذاء والدواء','name_en'=>'SFDA','icon'=>'ti-pill','color'=>'#C62828','bg'=>'rgba(198,40,40,.1)','tag_ar'=>'الغذاء والدواء','tag_en'=>'Food & Drug','services'=>[
                ['name_ar'=>'تسجيل الأدوية','name_en'=>'Drug Registration','icon'=>'ti-pill','price'=>2000,'days'=>60],
                ['name_ar'=>'ترخيص منشأة غذائية','name_en'=>'Food Facility License','icon'=>'ti-building','price'=>1500,'days'=>20],
                ['name_ar'=>'تسجيل المنتجات الغذائية','name_en'=>'Food Product Registration','icon'=>'ti-package','price'=>800,'days'=>14],
            ]],
            ['name_ar'=>'الهيئة العامة للعقار','name_en'=>'General Real Estate Authority','icon'=>'ti-home-star','color'=>'#AD1457','bg'=>'rgba(173,20,87,.1)','tag_ar'=>'قطاع العقار','tag_en'=>'Real Estate','services'=>[
                ['name_ar'=>'تسجيل العقارات','name_en'=>'Property Registration','icon'=>'ti-home','price'=>800,'days'=>7],
                ['name_ar'=>'ترخيص شركة عقارية','name_en'=>'Real Estate Company License','icon'=>'ti-building','price'=>2000,'days'=>14],
            ]],
            ['name_ar'=>'الهيئة العامة للترفيه','name_en'=>'GEA','icon'=>'ti-confetti','color'=>'#E65100','bg'=>'rgba(230,81,0,.1)','tag_ar'=>'قطاع الترفيه','tag_en'=>'Entertainment','services'=>[
                ['name_ar'=>'تراخيص الفعاليات الترفيهية','name_en'=>'Entertainment Event Licenses','icon'=>'ti-ticket','price'=>3000,'days'=>14],
                ['name_ar'=>'ترخيص دور السينما','name_en'=>'Cinema License','icon'=>'ti-movie','price'=>5000,'days'=>30],
            ]],
            ['name_ar'=>'الهيئة الوطنية للأمن السيبراني','name_en'=>'NCA','icon'=>'ti-shield-lock','color'=>'#263238','bg'=>'rgba(38,50,56,.1)','tag_ar'=>'الأمن السيبراني','tag_en'=>'Cybersecurity','services'=>[
                ['name_ar'=>'اعتماد الأنظمة الأمنية','name_en'=>'Security System Accreditation','icon'=>'ti-shield-check','price'=>5000,'days'=>30],
                ['name_ar'=>'اختبار الاختراق المعتمد','name_en'=>'Certified Penetration Testing','icon'=>'ti-bug','price'=>3000,'days'=>14],
            ]],
        ]);

        // Government Companies
        $com = Category::create([
            'key' => 'companies', 'name_ar' => 'الشركات الحكومية', 'name_en' => 'Government Companies',
            'icon' => 'ti-building-factory', 'color' => '#1B5E20', 'bg' => 'rgba(27,94,32,.1)', 'sort_order' => 3,
        ]);

        $this->addEntities($com->id, [
            ['name_ar'=>'المؤسسة العامة للتأمينات الاجتماعية','name_en'=>'GOSI','icon'=>'ti-shield-check','color'=>'#1B5E20','bg'=>'rgba(27,94,32,.1)','tag_ar'=>'التأمينات الاجتماعية','tag_en'=>'Social Insurance','services'=>[
                ['name_ar'=>'التسجيل في التأمينات','name_en'=>'Social Insurance Registration','icon'=>'ti-user-check','price'=>0,'days'=>3],
                ['name_ar'=>'استخراج شهادة التأمينات','name_en'=>'Insurance Certificate','icon'=>'ti-certificate','price'=>50,'days'=>2],
                ['name_ar'=>'طلبات التقاعد','name_en'=>'Retirement Applications','icon'=>'ti-calendar','price'=>0,'days'=>30],
            ]],
            ['name_ar'=>'البريد السعودي','name_en'=>'Saudi Post (SPL)','icon'=>'ti-mail','color'=>'#C62828','bg'=>'rgba(198,40,40,.1)','tag_ar'=>'الخدمات البريدية','tag_en'=>'Postal Services','services'=>[
                ['name_ar'=>'خدمات الشحن','name_en'=>'Shipping Services','icon'=>'ti-package','price'=>80,'days'=>1],
                ['name_ar'=>'صندوق البريد','name_en'=>'PO Box','icon'=>'ti-mailbox','price'=>200,'days'=>2],
            ]],
            ['name_ar'=>'الخطوط الجوية العربية السعودية','name_en'=>'Saudi Arabian Airlines (Saudia)','icon'=>'ti-plane','color'=>'#1A237E','bg'=>'rgba(26,35,126,.1)','tag_ar'=>'الطيران','tag_en'=>'Aviation','services'=>[
                ['name_ar'=>'حجز التذاكر المؤسسي','name_en'=>'Corporate Ticket Booking','icon'=>'ti-ticket','price'=>0,'days'=>1],
                ['name_ar'=>'عقود السفر للشركات','name_en'=>'Corporate Travel Contracts','icon'=>'ti-file-description','price'=>500,'days'=>7],
            ]],
            ['name_ar'=>'صندوق التنمية الصناعية السعودي','name_en'=>'SIDF','icon'=>'ti-building-factory','color'=>'#1B5E20','bg'=>'rgba(27,94,32,.1)','tag_ar'=>'التنمية الصناعية','tag_en'=>'Industrial Development','services'=>[
                ['name_ar'=>'طلبات القروض الصناعية','name_en'=>'Industrial Loan Applications','icon'=>'ti-coin','price'=>0,'days'=>30],
                ['name_ar'=>'استشارات التطوير الصناعي','name_en'=>'Industrial Development Consulting','icon'=>'ti-users','price'=>0,'days'=>7],
            ]],
            ['name_ar'=>'البنك الأهلي السعودي','name_en'=>'SNB','icon'=>'ti-building-bank','color'=>'#1A237E','bg'=>'rgba(26,35,126,.1)','tag_ar'=>'الخدمات المصرفية','tag_en'=>'Banking Services','services'=>[
                ['name_ar'=>'فتح حساب تجاري','name_en'=>'Open Business Account','icon'=>'ti-wallet','price'=>0,'days'=>3],
                ['name_ar'=>'خطاب ضمان بنكي','name_en'=>'Bank Guarantee Letter','icon'=>'ti-file-certificate','price'=>500,'days'=>5],
            ]],
        ]);

        // Embassies
        $emb = Category::create([
            'key' => 'embassies', 'name_ar' => 'السفارات والقنصليات', 'name_en' => 'Embassies & Consulates',
            'icon' => 'ti-world', 'color' => '#00838F', 'bg' => 'rgba(0,131,143,.1)', 'sort_order' => 4,
        ]);

        $this->addEntities($emb->id, [
            ['name_ar'=>'السفارة السعودية في الولايات المتحدة','name_en'=>'Saudi Embassy - United States','icon'=>'ti-world','color'=>'#00838F','bg'=>'rgba(0,131,143,.1)','tag_ar'=>'سفارة','tag_en'=>'Embassy','services'=>[
                ['name_ar'=>'تصديق الوثائق','name_en'=>'Document Attestation','icon'=>'ti-file-check','price'=>400,'days'=>7],
                ['name_ar'=>'خدمات التأشيرة','name_en'=>'Visa Services','icon'=>'ti-visa','price'=>300,'days'=>5],
                ['name_ar'=>'توثيق عقود التجارة','name_en'=>'Trade Contract Authentication','icon'=>'ti-file-text','price'=>600,'days'=>10],
            ]],
            ['name_ar'=>'السفارة السعودية في المملكة المتحدة','name_en'=>'Saudi Embassy - United Kingdom','icon'=>'ti-world','color'=>'#00838F','bg'=>'rgba(0,131,143,.1)','tag_ar'=>'سفارة','tag_en'=>'Embassy','services'=>[
                ['name_ar'=>'تصديق الوثائق','name_en'=>'Document Attestation','icon'=>'ti-file-check','price'=>400,'days'=>7],
                ['name_ar'=>'خدمات التأشيرة','name_en'=>'Visa Services','icon'=>'ti-visa','price'=>300,'days'=>5],
            ]],
            ['name_ar'=>'السفارة السعودية في فرنسا','name_en'=>'Saudi Embassy - France','icon'=>'ti-world','color'=>'#00838F','bg'=>'rgba(0,131,143,.1)','tag_ar'=>'سفارة','tag_en'=>'Embassy','services'=>[
                ['name_ar'=>'تصديق الوثائق','name_en'=>'Document Attestation','icon'=>'ti-file-check','price'=>400,'days'=>7],
                ['name_ar'=>'خدمات الشنغن','name_en'=>'Schengen Services','icon'=>'ti-passport','price'=>500,'days'=>10],
            ]],
            ['name_ar'=>'القنصلية السعودية في نيويورك','name_en'=>'Saudi Consulate - New York','icon'=>'ti-building','color'=>'#0277BD','bg'=>'rgba(2,119,189,.1)','tag_ar'=>'قنصلية','tag_en'=>'Consulate','services'=>[
                ['name_ar'=>'تصديق الوثائق','name_en'=>'Document Attestation','icon'=>'ti-file-check','price'=>400,'days'=>7],
                ['name_ar'=>'التوثيق القنصلي','name_en'=>'Consular Authentication','icon'=>'ti-file-certificate','price'=>350,'days'=>5],
            ]],
            ['name_ar'=>'سفارة الولايات المتحدة في الرياض','name_en'=>'US Embassy - Riyadh','icon'=>'ti-world','color'=>'#1A237E','bg'=>'rgba(26,35,126,.1)','tag_ar'=>'سفارة أجنبية','tag_en'=>'Foreign Embassy','services'=>[
                ['name_ar'=>'تأشيرات الزيارة','name_en'=>'Visitor Visas','icon'=>'ti-plane','price'=>600,'days'=>14],
                ['name_ar'=>'تأشيرات العمل','name_en'=>'Work Visas','icon'=>'ti-briefcase','price'=>800,'days'=>21],
            ]],
            ['name_ar'=>'سفارة المملكة المتحدة في الرياض','name_en'=>'UK Embassy - Riyadh','icon'=>'ti-world','color'=>'#C62828','bg'=>'rgba(198,40,40,.1)','tag_ar'=>'سفارة أجنبية','tag_en'=>'Foreign Embassy','services'=>[
                ['name_ar'=>'تأشيرات زيارة بريطانيا','name_en'=>'UK Visit Visas','icon'=>'ti-passport','price'=>700,'days'=>15],
                ['name_ar'=>'تصديق الشهادات الأكاديمية','name_en'=>'Academic Certificate Attestation','icon'=>'ti-certificate','price'=>400,'days'=>7],
            ]],
        ]);
    }

    private function addEntities(int $categoryId, array $entities): void
    {
        foreach ($entities as $i => $data) {
            $entity = Entity::create([
                'category_id' => $categoryId,
                'name_ar'     => $data['name_ar'],
                'name_en'     => $data['name_en'],
                'icon'        => $data['icon'],
                'color'       => $data['color'],
                'bg'          => $data['bg'],
                'tag_ar'      => $data['tag_ar'] ?? null,
                'tag_en'      => $data['tag_en'] ?? null,
                'sort_order'  => $i + 1,
            ]);

            foreach ($data['services'] as $j => $svc) {
                GovService::create([
                    'entity_id'      => $entity->id,
                    'name_ar'        => $svc['name_ar'],
                    'name_en'        => $svc['name_en'],
                    'icon'           => $svc['icon'],
                    'price'          => $svc['price'],
                    'duration_min'   => $svc['days'],
                    'duration_max'   => $svc['days'],
                    'duration_unit'  => 'day',
                    'sort_order'     => $j + 1,
                ]);
            }
        }
    }
}
