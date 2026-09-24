<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| جعل user_id في bs_notifications اختيارياً
|--------------------------------------------------------------------------
|
| إشعارات المكتب (`recipient_type = office`) تُرسل عبر office_id فقط،
| لكن العمود user_id في قاعدة البيانات الفعلية ما زال NOT NULL (من
| الترحيل القديم 2026_05_14_000003)، فيفشل الإدراج بـ SQLSTATE 1364
| عند إسناد طلب مكتب مساند (assign). هذه المهاجرة تعالج عدم التطابق.
|
*/

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if (! $schema->hasTable('bs_notifications')) {
            return;
        }

        foreach ($schema->getColumns('bs_notifications') as $column) {
            if ($column['name'] === 'user_id' && $column['nullable'] === false) {
                $schema->table('bs_notifications', function (Blueprint $table) {
                    $table->unsignedBigInteger('user_id')->nullable()->change();
                });
                break;
            }
        }
    }

    public function down(): void
    {
        // تعمّداً فارغ: العودة إلى NOT NULL قد تكسر إشعارات المكتب القائمة.
    }
};