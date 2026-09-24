<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| حقول العميل الفردي
|--------------------------------------------------------------------------
|
| عند اختيار «عميل مفرد» يتم عرض حقول خاصة: اسم الأب والجد والعائلة ورقم
| الهوية، إضافة إلى القطاع الوظيفي (حكومي/خاص) والحالة الوظيفية
| (متقاعد/منتسب). هذه الهجرة تضيف الأعمدة إلى bs_users بنفس نمط
| الحماية الثنائي للـ Schema المستخدم في بقية هجرات العملاء.
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

        if (! $schema->hasColumn('bs_users', 'father_name')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->string('father_name', 100)->nullable()->after('name');
            });
        }

        if (! $schema->hasColumn('bs_users', 'grandfather_name')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->string('grandfather_name', 100)->nullable()->after('father_name');
            });
        }

        if (! $schema->hasColumn('bs_users', 'family_name')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->string('family_name', 100)->nullable()->after('grandfather_name');
            });
        }

        if (! $schema->hasColumn('bs_users', 'id_number')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->string('id_number', 20)->nullable()->index()->after('family_name');
            });
        }

        if (! $schema->hasColumn('bs_users', 'job_sector')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->string('job_sector', 20)->nullable()->after('id_number');
            });
        }

        if (! $schema->hasColumn('bs_users', 'employment_status')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->string('employment_status', 20)->nullable()->after('job_sector');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if (! $schema->hasTable('bs_users')) {
            return;
        }

        $columns = [
            'father_name', 'grandfather_name', 'family_name',
            'id_number', 'job_sector', 'employment_status',
        ];

        $schema->table('bs_users', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};