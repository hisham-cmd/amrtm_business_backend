<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_contract_types') && !$schema->hasColumn('bs_contract_types', 'category')) {
            $schema->table('bs_contract_types', function (Blueprint $table) {
                $table->string('category')->nullable()->after('price');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_contract_types') && $schema->hasColumn('bs_contract_types', 'category')) {
            $schema->table('bs_contract_types', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }
};
