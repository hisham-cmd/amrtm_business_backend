<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'business';

    public function up(): void
    {
        Schema::connection('business')->table('bs_services', function (Blueprint $table) {
            if (!Schema::connection('business')->hasColumn('bs_services', 'custom_fields')) {
                $table->json('custom_fields')->nullable()->after('estimated_days');
            }
        });

        Schema::connection('business')->table('bs_requests', function (Blueprint $table) {
            if (!Schema::connection('business')->hasColumn('bs_requests', 'custom_field_values')) {
                $table->json('custom_field_values')->nullable()->after('attachments');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('business')->table('bs_requests', function (Blueprint $table) {
            $table->dropColumn('custom_field_values');
        });

        Schema::connection('business')->table('bs_services', function (Blueprint $table) {
            $table->dropColumn('custom_fields');
        });
    }
};
