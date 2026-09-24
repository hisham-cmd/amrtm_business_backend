<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'business';

    public function up(): void
    {
        if (Schema::connection('business')->hasTable('bs_notifications')) {
            return;
        }

        Schema::connection('business')->create('bs_notifications', function (Blueprint $table) {
            $table->id();
            $table->enum('recipient_type', ['user', 'office', 'admin'])->default('user')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('office_id')->nullable()->index();
            $table->string('type', 60)->default('info');
            $table->string('title', 250);
            $table->text('body')->nullable();
            $table->json('data')->nullable();
            $table->unsignedBigInteger('request_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['recipient_type', 'user_id', 'is_read']);
            $table->index(['recipient_type', 'office_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::connection('business')->dropIfExists('bs_notifications');
    }
};