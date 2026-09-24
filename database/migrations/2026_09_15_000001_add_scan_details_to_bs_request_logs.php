<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if (!$schema->hasTable('bs_request_logs')) {
            return;
        }

        if (!$schema->hasColumn('bs_request_logs', 'details')) {
            $schema->table('bs_request_logs', function (Blueprint $table) {
                $table->json('details')->nullable()->after('note');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_request_logs') && $schema->hasColumn('bs_request_logs', 'details')) {
            $schema->table('bs_request_logs', function (Blueprint $table) {
                $table->dropColumn('details');
            });
        }
    }
};