<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $db = DB::connection('business');

        if (! $db->getSchemaBuilder()->hasTable('bs_company_profiles')) {
            return;
        }

        // بيانات الشركة الحقيقية (الطرف الأول في العقود) — تُضاف إذا كانت غير موجودة
        $profile = $db->table('bs_company_profiles')->first();

        if ($profile) {
            $db->table('bs_company_profiles')
                ->where('id', $profile->id)
                ->update([
                    'name'                    => 'مؤسسة آمر تم لخدمات الأعمال',
                    'commercial_registration' => '7036125610',
                    'address'                 => 'جدة، حي الحمراء، شارع فلسطين، مركز الجمجوم التجاري',
                    'email'                   => 'info@amrtm.com.sa',
                    'phone'                   => '0504915222',
                    'manager_name'            => 'صالح بن ناصر الشمراني',
                    'updated_at'              => now(),
                ]);
        } else {
            $db->table('bs_company_profiles')->insert([
                'name'                    => 'مؤسسة آمر تم لخدمات الأعمال',
                'commercial_registration' => '7036125610',
                'address'                 => 'جدة، حي الحمراء، شارع فلسطين، مركز الجمجوم التجاري',
                'email'                   => 'info@amrtm.com.sa',
                'phone'                   => '0504915222',
                'manager_name'            => 'صالح بن ناصر الشمراني',
                'created_at'              => now(),
                'updated_at'              => now(),
            ]);
        }
    }

    public function down(): void
    {
        // لا حاجة لحذف بيانات الشركة
    }
};
