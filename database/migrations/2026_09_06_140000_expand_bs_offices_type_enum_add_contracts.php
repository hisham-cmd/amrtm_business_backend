<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| إضافة قيمة "contracts" مؤقّتة لقائمة قيم enum لنوع المنشأة في bs_offices
|--------------------------------------------------------------------------
|
| أُضيفت القيمة contracts أثناء وجود مفهوم «منشأة عقود» كحساب منفصل،
| ثم أُلغي هذا المفهوم. تُزيل الهجرة اللاحقة هذه القيمة من enum MySQL
| (`bs_offices.type`) لتعود القائمة إلى قيمها الأصلية فقط.
| قواعد البيانات غير MySQL (SQLite/PostgreSQL...) لا تطبّق ENUM صارم.
|
*/

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        if (DB::connection('business')->getDriverName() !== 'mysql') {
            return;
        }

        DB::connection('business')->statement(
            "ALTER TABLE `bs_offices` MODIFY COLUMN `type` "
            . "ENUM('law','services','customs','accounting','engineering','freelance','contracts') "
            . "NOT NULL"
        );
    }

    public function down(): void
    {
        if (DB::connection('business')->getDriverName() !== 'mysql') {
            return;
        }

        DB::connection('business')->statement(
            "ALTER TABLE `bs_offices` MODIFY COLUMN `type` "
            . "ENUM('law','services','customs','accounting','engineering','freelance') "
            . "NOT NULL"
        );
    }
};