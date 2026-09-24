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

        if (! $schema->hasTable('bs_offices')) {
            return;
        }

        $schema->table('bs_offices', function (Blueprint $table) use ($schema) {
            if (! $schema->hasColumn('bs_offices', 'business_categories')) {
                $table->json('business_categories')->nullable()->after('category');
            }
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if (! $schema->hasTable('bs_offices')) {
            return;
        }

        $schema->table('bs_offices', function (Blueprint $table) use ($schema) {
            if ($schema->hasColumn('bs_offices', 'business_categories')) {
                $table->dropColumn('business_categories');
            }
        });
    }
};
