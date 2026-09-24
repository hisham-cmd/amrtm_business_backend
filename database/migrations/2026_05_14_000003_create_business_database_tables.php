<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Business Sector Platform — dedicated database tables.
 * Run against the 'business' connection:
 *   php artisan migrate --database=business --path=database/migrations/2026_05_14_000003_create_business_database_tables.php
 *
 * Note: user_id columns are plain integers (no FK) because users live
 * in the main database (cross-database FKs are fragile in MySQL).
 */
return new class extends Migration {

    protected $connection = 'business';

    public function up(): void
    {
        // ── Categories ────────────────────────────────────────────────────────
        if (!Schema::connection('business')->hasTable('bs_categories')) {
            Schema::connection('business')->create('bs_categories', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('name_ar');
                $table->string('name_en');
                $table->string('icon')->default('ti-building');
                $table->string('color')->default('#1A237E');
                $table->string('bg')->default('rgba(26,35,126,.1)');
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // ── Entities ──────────────────────────────────────────────────────────
        if (!Schema::connection('business')->hasTable('bs_entities')) {
            Schema::connection('business')->create('bs_entities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id');
                $table->string('name_ar');
                $table->string('name_en');
                $table->string('icon')->default('ti-building');
                $table->string('color')->default('#1A237E');
                $table->string('bg')->default('rgba(26,35,126,.1)');
                $table->string('tag_ar')->nullable();
                $table->string('tag_en')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('category_id')->references('id')->on('bs_categories')->cascadeOnDelete();
            });
        }

        // ── Government / Business Services ────────────────────────────────────
        if (!Schema::connection('business')->hasTable('bs_services')) {
            Schema::connection('business')->create('bs_services', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('entity_id');
                $table->string('name_ar');
                $table->string('name_en');
                $table->string('icon')->default('ti-file-text');
                $table->decimal('price', 10, 2)->default(0);
                $table->text('description_ar')->nullable();
                $table->text('description_en')->nullable();
                $table->integer('estimated_days')->default(3);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('entity_id')->references('id')->on('bs_entities')->cascadeOnDelete();
            });
        }

        // ── Service Requests ──────────────────────────────────────────────────
        if (!Schema::connection('business')->hasTable('bs_requests')) {
            Schema::connection('business')->create('bs_requests', function (Blueprint $table) {
                $table->id();
                $table->string('ref_number')->unique();
                $table->unsignedBigInteger('user_id');           // references main DB users — no FK
                $table->unsignedBigInteger('service_id');
                $table->unsignedBigInteger('entity_id');

                $table->string('client_name');
                $table->string('client_email');
                $table->string('client_phone');
                $table->string('client_id_number');
                $table->string('company_name')->nullable();
                $table->string('company_cr')->nullable();
                $table->text('notes')->nullable();
                $table->json('attachments')->nullable();

                $table->decimal('price', 10, 2)->default(0);
                $table->enum('status', ['pending', 'processing', 'in_progress', 'done', 'rejected'])->default('pending');
                $table->text('reject_reason')->nullable();
                $table->string('estimated_completion')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->unsignedBigInteger('handled_by')->nullable();   // references main DB users — no FK
                $table->timestamps();

                $table->foreign('service_id')->references('id')->on('bs_services')->cascadeOnDelete();
                $table->foreign('entity_id')->references('id')->on('bs_entities')->cascadeOnDelete();
            });
        }

        // ── Request Logs ──────────────────────────────────────────────────────
        if (!Schema::connection('business')->hasTable('bs_request_logs')) {
            Schema::connection('business')->create('bs_request_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('request_id');
                $table->unsignedBigInteger('user_id')->nullable();   // no cross-DB FK
                $table->string('status');
                $table->string('log_type')->default('status_change');
                $table->text('note')->nullable();
                $table->timestamps();

                $table->foreign('request_id')->references('id')->on('bs_requests')->cascadeOnDelete();
            });
        }

        // ── Payments ──────────────────────────────────────────────────────────
        if (!Schema::connection('business')->hasTable('bs_payments')) {
            Schema::connection('business')->create('bs_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');              // no cross-DB FK
                $table->unsignedBigInteger('request_id')->nullable();
                $table->decimal('amount', 10, 2);
                $table->enum('type', ['charge', 'payment', 'refund'])->default('charge');
                $table->string('description_ar')->nullable();
                $table->string('description_en')->nullable();
                $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
                $table->string('transaction_ref')->nullable();
                $table->timestamps();

                $table->foreign('request_id')->references('id')->on('bs_requests')->nullOnDelete();
            });
        }

        // ── Notifications ─────────────────────────────────────────────────────
        if (!Schema::connection('business')->hasTable('bs_notifications')) {
            Schema::connection('business')->create('bs_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');              // no cross-DB FK
                $table->unsignedBigInteger('sender_id')->nullable();
                $table->string('type')->default('info');
                $table->string('title');
                $table->text('body');
                $table->unsignedBigInteger('request_id')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();

                $table->foreign('request_id')->references('id')->on('bs_requests')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('business')->dropIfExists('bs_notifications');
        Schema::connection('business')->dropIfExists('bs_payments');
        Schema::connection('business')->dropIfExists('bs_request_logs');
        Schema::connection('business')->dropIfExists('bs_requests');
        Schema::connection('business')->dropIfExists('bs_services');
        Schema::connection('business')->dropIfExists('bs_entities');
        Schema::connection('business')->dropIfExists('bs_categories');
    }
};
