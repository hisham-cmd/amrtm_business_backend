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

        if (! in_array('created_by_office_id', $columns, true)) {
            $schema->table('bs_contracts', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by_office_id')->nullable()->after('contract_type_id');
            });
        }

        if (! in_array('party_office_id', $columns, true)) {
            $schema->table('bs_contracts', function (Blueprint $table) {
                $table->unsignedBigInteger('party_office_id')->nullable()->after('created_by_office_id');
            });
        }

        if (! in_array('party_email', $columns, true)) {
            $schema->table('bs_contracts', function (Blueprint $table) {
                $table->string('party_email')->nullable()->after('party_name');
            });
        }

        if (! in_array('description', $columns, true)) {
            $schema->table('bs_contracts', function (Blueprint $table) {
                $table->text('description')->nullable()->after('status');
            });
        }

        if (! in_array('category', $columns, true)) {
            $schema->table('bs_contracts', function (Blueprint $table) {
                $table->string('category')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if (! $schema->hasTable('bs_contracts')) {
            return;
        }

        $columns = $schema->getColumnListing('bs_contracts');

        foreach (['created_by_office_id', 'party_office_id', 'party_email', 'description', 'category'] as $col) {
            if (in_array($col, $columns, true)) {
                $schema->table('bs_contracts', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }
    }
};
