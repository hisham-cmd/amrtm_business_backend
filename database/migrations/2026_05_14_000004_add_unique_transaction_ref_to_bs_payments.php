<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'business';

    public function up(): void
    {
        try {
            Schema::connection('business')->table('bs_payments', function (Blueprint $table) {
                $table->unique('transaction_ref');
            });
        } catch (\Throwable $e) {
            // Already unique or index exists
        }
    }

    public function down(): void
    {
        Schema::connection('business')->table('bs_payments', function (Blueprint $table) {
            $table->dropUnique(['transaction_ref']);
        });
    }
};