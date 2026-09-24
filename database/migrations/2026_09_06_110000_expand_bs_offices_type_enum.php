<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| توسيع قيم enum لنوع المنشأة في bs_offices
|--------------------------------------------------------------------------
|
| العمود type كان enum('law','services','customs') فقط بينما نظام التسجيل
| يدعم 6 أنواع (law, services, customs, accounting, engineering, freelance) —
| ما كان يسبب فشل إنشاء حساب عند اختيار الأنواع الإضافية (Data truncated).
|
*/

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        // قواعد البيانات غير MySQL (SQLite/PostgreSQL...) لا تحتوي على
        // قيد ENUM صارم، لذا لا داعي لإعادة تعريف العمود في منها.
        if (DB::connection('business')->getDriverName() !== 'mysql') {
            return;
        }

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

        DB::connection('business')->statement(
            "ALTER TABLE `bs_offices` MODIFY COLUMN `type` "
            . "ENUM('law','services','customs') "
            . "NOT NULL"
        );
    }
};