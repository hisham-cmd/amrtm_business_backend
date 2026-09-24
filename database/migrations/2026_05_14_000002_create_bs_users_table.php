<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'business';

    public function up(): void
    {
        if (!Schema::connection('business')->hasTable('bs_users')) {
            Schema::connection('business')->create('bs_users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->enum('role', ['admin', 'supervisor', 'user'])->default('user');
                $table->rememberToken();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('business')->dropIfExists('bs_users');
    }
};
