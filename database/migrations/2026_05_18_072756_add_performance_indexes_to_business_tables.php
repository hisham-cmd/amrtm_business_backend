<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // These tables live in the 'business' database connection
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        try {
            $schema->table('bs_requests', function (Blueprint $table) {
                $sm = Schema::connection('business')->getConnection()->getDoctrineSchemaManager();
                $indexes = array_keys($sm->listTableIndexes('bs_requests'));
                if (!in_array('bs_requests_user_id_index', $indexes)) $table->index('user_id');
                if (!in_array('bs_requests_status_index', $indexes)) $table->index('status');
                if (!in_array('bs_requests_user_id_status_index', $indexes)) $table->index(['user_id', 'status']);
                if (!in_array('bs_requests_status_created_at_index', $indexes)) $table->index(['status', 'created_at']);
            });
        } catch (\Throwable $e) {}

        try {
            $schema->table('bs_payments', function (Blueprint $table) {
                $table->index('user_id');
                $table->index(['user_id', 'type']);
            });
        } catch (\Throwable $e) {}

        try {
            $schema->table('bs_notifications', function (Blueprint $table) {
                $table->index(['user_id', 'is_read']);
            });
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        Schema::connection('business')->table('bs_requests', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::connection('business')->table('bs_payments', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['user_id', 'type']);
        });

        Schema::connection('business')->table('bs_notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_read']);
        });
    }
};
