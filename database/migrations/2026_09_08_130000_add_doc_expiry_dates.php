<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| إضافة تواريخ انتهاء السجل التجاري وترخيص المزاولة
|--------------------------------------------------------------------------
|
| تُضاف cr_expiry_date و license_expiry_date ضمن بطاقات «رقم السجل التجاري»
| و «رقم ترخيص المزاولة» في نماذج التسجيل (مكتب / عميل منشأة).
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('business')->hasTable('bs_office_profiles')) {
            if (! Schema::connection('business')->hasColumn('bs_office_profiles', 'cr_expiry_date')) {
                Schema::connection('business')->table('bs_office_profiles', function (Blueprint $table) {
                    $table->date('cr_expiry_date')->nullable()->after('cr_number');
                });
            }

            if (! Schema::connection('business')->hasColumn('bs_office_profiles', 'license_expiry_date')) {
                Schema::connection('business')->table('bs_office_profiles', function (Blueprint $table) {
                    $table->date('license_expiry_date')->nullable()->after('license_number');
                });
            }
        }

        if (Schema::connection('business')->hasTable('bs_users')) {
            if (! Schema::connection('business')->hasColumn('bs_users', 'cr_expiry_date')) {
                Schema::connection('business')->table('bs_users', function (Blueprint $table) {
                    $table->date('cr_expiry_date')->nullable()->after('cr_number');
                });
            }

            if (! Schema::connection('business')->hasColumn('bs_users', 'license_expiry_date')) {
                Schema::connection('business')->table('bs_users', function (Blueprint $table) {
                    $table->date('license_expiry_date')->nullable()->after('cr_expiry_date');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['bs_office_profiles', 'bs_users'] as $table) {
            if (! Schema::connection('business')->hasTable($table)) {
                continue;
            }

            if (Schema::connection('business')->hasColumn($table, 'license_expiry_date')) {
                Schema::connection('business')->table($table, function (Blueprint $table) {
                    $table->dropColumn('license_expiry_date');
                });
            }

            if (Schema::connection('business')->hasColumn($table, 'cr_expiry_date')) {
                Schema::connection('business')->table($table, function (Blueprint $table) {
                    $table->dropColumn('cr_expiry_date');
                });
            }
        }
    }
};
