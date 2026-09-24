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

        if (! $schema->hasColumn('bs_offices', 'subscription_type')) {
            $schema->table('bs_offices', function (Blueprint $table) {
                $table->enum('subscription_type', ['commission', 'subscription'])
                    ->default('commission')
                    ->after('commission_rate');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_offices') && $schema->hasColumn('bs_offices', 'subscription_type')) {
            $schema->table('bs_offices', function (Blueprint $table) {
                $table->dropColumn('subscription_type');
            });
        }
    }
};
