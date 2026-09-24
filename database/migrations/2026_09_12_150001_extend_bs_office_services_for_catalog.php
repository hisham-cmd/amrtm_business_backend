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

        $schema->table('bs_office_services', function (Blueprint $table) use ($schema) {
            if (!$schema->hasColumn('bs_office_services', 'specialty_id')) {
                $table->unsignedBigInteger('specialty_id')->nullable()->after('office_id');
            }
            if (!$schema->hasColumn('bs_office_services', 'entity_id')) {
                $table->unsignedBigInteger('entity_id')->nullable()->after('specialty_id');
            }
            if (!$schema->hasColumn('bs_office_services', 'source_service_id')) {
                $table->unsignedBigInteger('source_service_id')->nullable()->after('entity_id');
            }
            if (!$schema->hasColumn('bs_office_services', 'source_type')) {
                $table->enum('source_type', ['catalog', 'custom', 'manual'])->default('manual')->after('source_service_id');
            }
            if (!$schema->hasColumn('bs_office_services', 'requirements')) {
                $table->text('requirements')->nullable()->after('duration');
            }
            if (!$schema->hasColumn('bs_office_services', 'approval_status')) {
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('approved')->after('requirements');
            }
            if (!$schema->hasColumn('bs_office_services', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approval_status');
            }
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if (!$schema->hasTable('bs_office_services')) {
            return;
        }

        $schema->table('bs_office_services', function (Blueprint $table) {
            $table->dropColumn(['specialty_id', 'entity_id', 'source_service_id', 'source_type', 'requirements', 'approval_status', 'rejection_reason']);
        });
    }
};