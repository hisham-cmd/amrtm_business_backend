<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        Schema::connection('business')->table('bs_requests', function (Blueprint $table) {
            if (Schema::connection('business')->hasColumn('bs_requests', 'client_id_number')) {
                $table->string('client_id_number')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::connection('business')->table('bs_requests', function (Blueprint $table) {
            $table->string('client_id_number')->nullable(false)->change();
        });
    }
};