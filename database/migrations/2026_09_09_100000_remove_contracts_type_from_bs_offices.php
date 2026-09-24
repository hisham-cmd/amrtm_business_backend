<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| إزالة قيمة "contracts" من enum نوع المنشأة في bs_offices
|--------------------------------------------------------------------------
|
| أُلغي مفهوم حساب «منشأة عقود» المنفصل؛ أي منشأة مسجّلة تنشئ العقود مباشرة.
| تُزال القيمة contracts من enum عمود type في MySQL، وتُقيَّد أي صفوف قديمة
| كانت نوعها contracts إلى "services" كقيمة افتراضية عامة مأمونة
| (المنشآت القديمة لم تكن مكاتب مساندة مؤهلة ولا مستشارين).
| غير MySQL (SQLite/PostgreSQL...) لا تطبّق ENUM صارم فلا حاجة لتغيير.
|
*/

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if (! $schema->hasTable('bs_offices')) {
            return;
        }

        $driver = DB::connection('business')->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        // حصر أي صفوف قديمة بلا نوع صالح بعد حذف contracts قبل تعديل الـ enum
        DB::connection('business')->table('bs_offices')
            ->where('type', 'contracts')
            ->update(['type' => 'services']);

        DB::connection('business')->statement(
            "ALTER TABLE `bs_offices` MODIFY COLUMN `type` "
            . "ENUM('law','services','customs','accounting','engineering','freelance') "
            . "NOT NULL"
        );
    }

    public function down(): void
    {
        if (DB::connection('business')->getDriverName() !== 'mysql') {
            return;
        }

        if (! Schema::connection('business')->hasTable('bs_offices')) {
            return;
        }

        DB::connection('business')->statement(
            "ALTER TABLE `bs_offices` MODIFY COLUMN `type` "
            . "ENUM('law','services','customs','accounting','engineering','freelance','contracts') "
            . "NOT NULL"
        );
    }
};