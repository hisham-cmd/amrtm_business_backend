<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'business';

    /*
    |--------------------------------------------------------------------------
    | توحيد أنواع الحسابات (مكتب مساند / مستشار) في حساب واحد
    |--------------------------------------------------------------------------
    |
    | - account_types على bs_offices: مصفوفة من الأنواع المفعلّة لنفس المنشأة.
    |   القيم: support_office (مكتب مساند)، consultant (مستشار).
    |
    | - على bs_contracts: ربط الطرف الأول (منشئ العقد) والطرف الثاني بالبريد
    |   لتظهر العقود للمنشأة المسجّلة كطرف ثانٍ.
    |
    */

    public function up(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_offices') && ! $schema->hasColumn('bs_offices', 'account_types')) {
            $schema->table('bs_offices', function (Blueprint $table) {
                $table->json('account_types')->nullable()->after('subscription_type');
            });
        }

        if ($schema->hasTable('bs_contracts')) {
            $columns = $schema->getColumnListing('bs_contracts');

            $additions = [
                // الطرف الأول — منشئ العقد (منشأة مسجلة)
                'party_1_office_id' => function (Blueprint $table) {
                    $table->unsignedBigInteger('party_1_office_id')->nullable()->after('id');
                },
                'party_1_name' => function (Blueprint $table) {
                    $table->string('party_1_name')->nullable()->after('party_1_office_id');
                },
                // الطرف الثاني — بالبريد الإلكتروني للدعوة والتسجيل
                'party_2_email' => function (Blueprint $table) {
                    $table->string('party_2_email')->nullable()->after('party_name');
                },
                'party_2_office_id' => function (Blueprint $table) {
                    $table->unsignedBigInteger('party_2_office_id')->nullable()->after('party_2_email');
                },
                'party_2_status' => function (Blueprint $table) {
                    $table->string('party_2_status')->default('pending')->after('party_2_office_id');
                },
            ];

            foreach ($additions as $col => $closure) {
                if (! in_array($col, $columns, true)) {
                    $schema->table('bs_contracts', function (Blueprint $table) use ($closure) {
                        $closure($table);
                    });
                }
            }
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_offices') && $schema->hasColumn('bs_offices', 'account_types')) {
            $schema->table('bs_offices', function (Blueprint $table) {
                $table->dropColumn('account_types');
            });
        }

        if ($schema->hasTable('bs_contracts')) {
            $schema->table('bs_contracts', function (Blueprint $table) {
                foreach (['party_1_office_id', 'party_1_name', 'party_2_email', 'party_2_office_id', 'party_2_status'] as $col) {
                    if ($schema->hasColumn('bs_contracts', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
