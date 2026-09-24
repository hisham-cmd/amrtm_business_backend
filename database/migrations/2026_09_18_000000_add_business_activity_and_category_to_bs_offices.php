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
            if (! $schema->hasColumn('bs_offices', 'business_activity')) {
                $table->string('business_activity', 64)->nullable()->after('type');
            }
            if (! $schema->hasColumn('bs_offices', 'category')) {
                $table->string('category', 64)->nullable()->after('business_activity');
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
            if ($schema->hasColumn('bs_offices', 'category')) {
                $table->dropColumn('category');
            }
            if ($schema->hasColumn('bs_offices', 'business_activity')) {
                $table->dropColumn('business_activity');
            }
        });
    }
};
