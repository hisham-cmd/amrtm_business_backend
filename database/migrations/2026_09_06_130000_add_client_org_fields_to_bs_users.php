<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| حقول التسجيل للمنشآت (العملاء)
|--------------------------------------------------------------------------
|
| تسجيل العميل يدعم حالياً نوعين: فرد (الاسم فقط) ومنشأة/مؤسسة تحتاج
| الاسم التجاري والسجل التجاري والعنوان الكامل. هذه الهجرة تضيف الأعمدة
| إلى bs_users مع الحفاظ على نفس نمط الحماية الثنائي للـ Schema.
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

        if (! $schema->hasColumn('bs_users', 'legal_name')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->string('legal_name')->nullable()->after('name');
            });
        }

        if (! $schema->hasColumn('bs_users', 'cr_number')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->string('cr_number', 50)->nullable()->index()->after('legal_name');
            });
        }

        if (! $schema->hasColumn('bs_users', 'account_type')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->string('account_type', 30)->default('individual')->after('role');
            });
        }

        if (! $schema->hasColumn('bs_users', 'phone_dial')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->string('phone_dial', 10)->nullable()->after('phone');
            });
        }

        if (! $schema->hasColumn('bs_users', 'country')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->string('country', 191)->nullable()->after('phone_dial');
            });
        }

        if (! $schema->hasColumn('bs_users', 'region')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->string('region', 191)->nullable()->after('country');
            });
        }

        if (! $schema->hasColumn('bs_users', 'city')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->string('city', 191)->nullable()->after('region');
            });
        }

        if (! $schema->hasColumn('bs_users', 'district')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->string('district', 191)->nullable()->after('city');
            });
        }

        if (! $schema->hasColumn('bs_users', 'street')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->string('street', 191)->nullable()->after('district');
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
            'legal_name', 'cr_number', 'account_type', 'phone_dial',
            'country', 'region', 'city', 'district', 'street',
        ];

        $schema->table('bs_users', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};