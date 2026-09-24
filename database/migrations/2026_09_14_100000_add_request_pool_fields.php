<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| شبكة المكاتب (Request Pool): أعمدة البث والحجز
|--------------------------------------------------------------------------
|
| طلبات المكاتب المساندة لا تُسند مباشرة. تُفتح في «شبكة المكاتب»:
|   candidate_office_ids : المكاتب المؤهلة التي يمكنها حجز الطلب (JSON).
|   claimed_at           : لحظة حجز أول مكتب مؤهل للطلب (first-claim-wins).
|
| وتُضاف القيمة open إلى enum fulfillment:
|   internal  : تنجز المنصة الخدمة بنفسها.
|   assigned  : أُسند الطلب لمكتب محدد.
|   open      : الطلب مفتوح في الشبكة بانتظار أول مكتب يؤهله يحجزه.
|
| على SQLite (بيئة الاختبار) تُخزن enum كـ varchar فلا يحتاج تعديل.
| على MySQL تُعاد كتابة enum لإضافة القيمة الجديدة.
|
*/

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');
        $driver = DB::connection('business')->getDriverName();

        if (! $schema->hasTable('bs_requests')) {
            return;
        }

        $schema->table('bs_requests', function (Blueprint $table) use ($schema) {
            $cols = $schema->getColumnListing('bs_requests');

            if (! in_array('candidate_office_ids', $cols, true)) {
                $table->json('candidate_office_ids')->nullable()->after('office_status');
            }
            if (! in_array('claimed_at', $cols, true)) {
                $table->timestamp('claimed_at')->nullable()->after('assigned_at');
            }
        });

        if ($driver === 'mysql' && $schema->hasColumn('bs_requests', 'fulfillment')) {
            DB::connection('business')->statement(
                "ALTER TABLE `bs_requests` MODIFY `fulfillment` ENUM('internal','assigned','open') NULL"
            );
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');
        $driver = DB::connection('business')->getDriverName();

        if (! $schema->hasTable('bs_requests')) {
            return;
        }

        $cols = $schema->getColumnListing('bs_requests');

        $schema->table('bs_requests', function (Blueprint $table) use ($cols) {
            if (in_array('candidate_office_ids', $cols, true)) {
                $table->dropColumn('candidate_office_ids');
            }
            if (in_array('claimed_at', $cols, true)) {
                $table->dropColumn('claimed_at');
            }
        });

        if ($driver === 'mysql' && $schema->hasColumn('bs_requests', 'fulfillment')) {
            DB::connection('business')->statement(
                "ALTER TABLE `bs_requests` MODIFY `fulfillment` ENUM('internal','assigned') NULL"
            );
        }
    }
};