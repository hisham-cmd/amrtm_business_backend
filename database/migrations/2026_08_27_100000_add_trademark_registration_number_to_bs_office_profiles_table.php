<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::connection('business')->hasTable('bs_office_profiles')) {
            return;
        }

        // الفحص اليدوي SHOW COLUMNS خاص بـ MySQL فقط (إصلاح عمود مكسور غير
        // مكرس في المخطط). باقي قواعد البيانات لا تحتاج هذا الفحص.
        if (DB::connection('business')->getDriverName() === 'mysql') {
            $cols = DB::connection('business')->select("SHOW COLUMNS FROM `bs_office_profiles`");
            $hasTabCol = false;
            $hasNormalCol = false;

            foreach ($cols as $col) {
                if ($col->Field === "\ttrademark_registration_number") {
                    $hasTabCol = true;
                }
                if ($col->Field === 'trademark_registration_number') {
                    $hasNormalCol = true;
                }
            }

            if ($hasTabCol) {
                DB::connection('business')->statement("ALTER TABLE `bs_office_profiles` CHANGE `\ttrademark_registration_number` `trademark_registration_number` VARCHAR(191) NULL DEFAULT NULL");

                return;
            }
        }

        if (! Schema::connection('business')->hasColumn('bs_office_profiles', 'trademark_registration_number')) {
            Schema::connection('business')->table('bs_office_profiles', function (Blueprint $table) {
                $table->string('trademark_registration_number', 191)->nullable()->after('qr_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection('business')->hasTable('bs_office_profiles')) {
            if (Schema::connection('business')->hasColumn('bs_office_profiles', 'trademark_registration_number')) {
                Schema::connection('business')->table('bs_office_profiles', function (Blueprint $table) {
                    $table->dropColumn('trademark_registration_number');
                });
            }
        }
    }
};
