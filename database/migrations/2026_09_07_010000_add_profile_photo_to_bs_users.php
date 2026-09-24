<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| الصورة الشخصية للعميل
|--------------------------------------------------------------------------
|
| عند تسجيل «عميل مفرد» يمكن رفع صورة شخصية (JPG/PNG/WebP). هذه الهجرة
| تضيف عمود profile_photo إلى bs_users ويخزن مسار الملف النسبي في
| storage/app/public. نفس نمط الحماية الثنائي للـ Schema المستخدم
| في بقية هجرات العملاء.
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

        if (! $schema->hasColumn('bs_users', 'profile_photo')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->string('profile_photo', 255)->nullable()->after('employment_status');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if (! $schema->hasTable('bs_users')) {
            return;
        }

        if ($schema->hasColumn('bs_users', 'profile_photo')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->dropColumn('profile_photo');
            });
        }
    }
};