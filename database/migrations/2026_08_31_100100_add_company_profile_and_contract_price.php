<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        // ── Add price to contract types ───────────────────────────────────────
        if (
            $schema->hasTable('bs_contract_types')
            && ! $schema->hasColumn('bs_contract_types', 'price')
        ) {
            $schema->table('bs_contract_types', function (Blueprint $table) {
                $table->decimal('price', 10, 2)->default(0)->after('name');
            });
        }

        // ── Company profile (الطرف الأول — مؤسسة آمر تم) ──────────────────────
        if (! $schema->hasTable('bs_company_profiles')) {
            $schema->create('bs_company_profiles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('commercial_registration')->nullable();
                $table->string('address')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('manager_name')->nullable();
                $table->timestamps();
            });

            DB::connection('business')->table('bs_company_profiles')->insert([
                'name'                    => 'مؤسسة آمر تم لخدمات الأعمال',
                'commercial_registration' => null,
                'address'                 => null,
                'email'                   => 'info@amrtm.com.sa',
                'phone'                   => '0504915222',
                'manager_name'            => null,
                'created_at'              => now(),
                'updated_at'              => now(),
            ]);
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasColumn('bs_contract_types', 'price')) {
            $schema->table('bs_contract_types', function (Blueprint $table) {
                $table->dropColumn('price');
            });
        }

        $schema->dropIfExists('bs_company_profiles');
    }
};