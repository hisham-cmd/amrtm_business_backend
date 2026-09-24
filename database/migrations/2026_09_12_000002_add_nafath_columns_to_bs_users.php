<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if (!$schema->hasColumn('bs_users', 'nafath_verified_at')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->timestamp('nafath_verified_at')->nullable()->after('phone');
            });
        }
    }

    public function down(): void
    {
        Schema::connection('business')->table('bs_users', function (Blueprint $table) {
            $table->dropColumn('nafath_verified_at');
        });
    }
};