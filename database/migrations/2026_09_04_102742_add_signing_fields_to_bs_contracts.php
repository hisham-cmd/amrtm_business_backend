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

        if (! in_array('party1_signed_at', $columns, true)) {
            $schema->table('bs_contracts', function (Blueprint $table) {
                $table->timestamp('party1_signed_at')->nullable()->after('second_party_viewed_at');
            });
        }

        if (! in_array('party2_signed_at', $columns, true)) {
            $schema->table('bs_contracts', function (Blueprint $table) {
                $table->timestamp('party2_signed_at')->nullable()->after('party1_signed_at');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        foreach (['party1_signed_at', 'party2_signed_at'] as $col) {
            if ($schema->hasColumn('bs_contracts', $col)) {
                $schema->table('bs_contracts', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }
    }
};
