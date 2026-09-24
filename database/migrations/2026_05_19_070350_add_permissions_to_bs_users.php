<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $schema = Schema::connection('business');
        if ($schema->hasTable('bs_users')) {
            $schema->table('bs_users', function (Blueprint $table) use ($schema) {
                if (!$schema->hasColumn('bs_users', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('role');
                }
                if (!$schema->hasColumn('bs_users', 'permissions')) {
                    $table->json('permissions')->nullable()->after('is_active');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('business')->table('bs_users', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'permissions']);
        });
    }
};
