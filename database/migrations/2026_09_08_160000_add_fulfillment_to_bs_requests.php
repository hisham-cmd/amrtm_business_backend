<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| إضافة مسار التنفيذ (fulfillment) لطلبات الخدمات الحكومية
|--------------------------------------------------------------------------
|
| تمنح لوحة الأدمن خيارين عند معالجة الطلب قيد الانتظار:
|   - internal : تنجز المنصة الخدمة بنفسها (الوضع التاريخي).
|   - assigned : تُسند الخدمة إلى مكتب مساند يعالجها بعد قبولها صراحةً.
|
| العمودان office_id / office_status موجودان مسبقاً في bs_requests،
| فتُضاف هنا أعمدة الحالة والبيانات الوصفية الخاصة بالإسناد فقط.
|
*/

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_requests')) {
            $schema->table('bs_requests', function (Blueprint $table) {
                $columns = Schema::connection('business')->getColumnListing('bs_requests');

                if (! in_array('fulfillment', $columns, true)) {
                    $table->enum('fulfillment', ['internal', 'assigned'])
                        ->nullable()
                        ->after('office_status');
                }

                if (! in_array('assigned_at', $columns, true)) {
                    $table->timestamp('assigned_at')->nullable()->after('fulfillment');
                }

                if (! in_array('assigned_by', $columns, true)) {
                    $table->unsignedBigInteger('assigned_by')->nullable()->after('assigned_at');
                }
            });
        }

        if ($schema->hasTable('bs_requests')) {
            $indexes = Schema::connection('business')->getIndexes('bs_requests');
            $indexNames = array_map(fn($ix) => $ix['name'], $indexes);

            if (! in_array('bs_requests_fulfillment_status_index', $indexNames, true)) {
                $schema->table('bs_requests', function (Blueprint $table) {
                    $table->index(['fulfillment', 'status'], 'bs_requests_fulfillment_status_index');
                });
            }
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_requests')) {
            $indexes = Schema::connection('business')->getIndexes('bs_requests');
            $indexNames = array_map(fn($ix) => $ix['name'], $indexes);

            if (in_array('bs_requests_fulfillment_status_index', $indexNames, true)) {
                $schema->table('bs_requests', function (Blueprint $table) {
                    $table->dropIndex('bs_requests_fulfillment_status_index');
                });
            }

            $columns = Schema::connection('business')->getColumnListing('bs_requests');

            if (array_intersect(['fulfillment', 'assigned_at', 'assigned_by'], $columns)) {
                $schema->table('bs_requests', function (Blueprint $table) use ($columns) {
                    foreach (['fulfillment', 'assigned_at', 'assigned_by'] as $col) {
                        if (in_array($col, $columns, true)) {
                            $table->dropColumn($col);
                        }
                    }
                });
            }
        }
    }
};