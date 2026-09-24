<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if (!$schema->hasTable('bs_payments')) {
            return;
        }

        if (!$schema->hasColumn('bs_payments', 'method')) {
            $schema->table('bs_payments', function (Blueprint $table) {
                $table->string('method', 20)->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_payments') && $schema->hasColumn('bs_payments', 'method')) {
            $schema->table('bs_payments', function (Blueprint $table) {
                $table->dropColumn('method');
            });
        }
    }
};