<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $db = DB::connection('business');

        if (! $db->getSchemaBuilder()->hasTable('bs_contract_types')) {
            return;
        }

        $defaults = [
            'عقد خدمات فورية'              => 2999,  // 2999.00 ر.س
            'عقد خدمات آمر تم السنوية'     => 4999,  // 4999.00 ر.س
            'عقد الاشتراكات في المنصة'     => 1499,  // 1499.00 ر.س
            'عقد الوساطة'                  => 3499,  // 3499.00 ر.س
        ];

        foreach ($defaults as $name => $price) {
            $db->table('bs_contract_types')
                ->where('name', $name)
                ->where('price', 0)
                ->update([
                    'price'      => $price,
                    'updated_at' => now()->toDateTimeString(),
                ]);
        }
    }

    public function down(): void
    {
        // لا شيء — نترك الأسعار كما هي في جدول البيانات
    }
};