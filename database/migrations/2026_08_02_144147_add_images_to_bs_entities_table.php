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
        if (Schema::connection('business')->hasTable('bs_entities')) {
            if (!Schema::connection('business')->hasColumn('bs_entities', 'images')) {
                Schema::connection('business')->table('bs_entities', function (Blueprint $table) {
                    $table->string('images')->nullable()->after('bg');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
 Schema::connection('business')->table('bs_entities', function (Blueprint $table) {
        $table->dropColumn('images');
    });
    }
};
