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

        if (! $schema->hasTable('bs_contracts')) {
            return;
        }

        $columns = $schema->getColumnListing('bs_contracts');

        if (! in_array('price', $columns, true)) {
            $schema->table('bs_contracts', function (Blueprint $table) {
                $table->decimal('price', 12, 2)->default(0)->after('contract_type_id');
            });
        }

        // لقطة البنود وقت إنشاء العقد — JSON: [{name, description, sort_order}, ...]
        if (! in_array('clauses_json', $columns, true)) {
            $schema->table('bs_contracts', function (Blueprint $table) {
                $table->json('clauses_json')->nullable()->after('price');
            });
        }

        // اسم الطرف الثاني (المكتب/المنشأة) وقت الإنشاء
        if (! in_array('party_name', $columns, true)) {
            $schema->table('bs_contracts', function (Blueprint $table) {
                $table->string('party_name')->nullable()->after('clauses_json');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        $columns = $schema->getColumnListing('bs_contracts');

        foreach (['price', 'clauses_json', 'party_name'] as $col) {
            if (in_array($col, $columns, true)) {
                $schema->table('bs_contracts', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }
    }
};
