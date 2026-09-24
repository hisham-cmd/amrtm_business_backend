<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if (!$schema->hasTable('bs_office_services')) {
            return;
        }

        if (!$schema->hasColumn('bs_office_services', 'custom_fields')) {
            $schema->table('bs_office_services', function (Blueprint $table) {
                $table->json('custom_fields')->nullable()->after('requirements');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_office_services') && $schema->hasColumn('bs_office_services', 'custom_fields')) {
            $schema->table('bs_office_services', function (Blueprint $table) {
                $table->dropColumn('custom_fields');
            });
        }
    }
};