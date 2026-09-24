<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| إضافة حقلي «رقم المبنى» و«رقم المكتب» لجدول bs_users
|--------------------------------------------------------------------------
|
| يكمل حقل العنوان في نماذج التسجيل (مكتب/عميل منشأة/عميل مفرد):
| الحي، الشارع، رقم المبنى، رقم المكتب. العمودان قابلا للإلغاء لأن
| بيانات أي نموذج قد لا تتضمنها، بنمط الحماية الثنائي على اتصال business.
|
*/

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if (! $schema->hasTable('bs_users')) {
            return;
        }

        if ($schema->hasColumn('bs_users', 'building_number') || $schema->hasColumn('bs_users', 'office_number')) {
            return;
        }

        $schema->table('bs_users', function (Blueprint $table) {
            $table->string('building_number', 191)->nullable()->after('street');
            $table->string('office_number', 191)->nullable()->after('building_number');
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_users') && $schema->hasColumn('bs_users', 'office_number')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->dropColumn('office_number');
            });
        }

        if ($schema->hasTable('bs_users') && $schema->hasColumn('bs_users', 'building_number')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->dropColumn('building_number');
            });
        }
    }
};