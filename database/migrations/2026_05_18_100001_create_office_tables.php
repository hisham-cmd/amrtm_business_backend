<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        // ── Offices (law firms, service offices, customs clearance) ───────────
        if (!$schema->hasTable('bs_offices')) {
            $schema->create('bs_offices', function (Blueprint $table) {
                $table->id();
                $table->enum('type', ['law', 'services', 'customs', 'accounting', 'engineering', 'freelance', 'contracts']);
                $table->string('name_ar');
                $table->string('name_en');
                $table->text('description_ar')->nullable();
                $table->text('description_en')->nullable();
                $table->string('phone');
                $table->string('email')->unique();
                $table->string('city')->nullable();
                $table->string('cr_number')->nullable();
                $table->string('logo')->nullable();
                $table->json('specialties')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_verified')->default(false);
                $table->timestamps();
            });
        }

        // ── Office User Accounts (login accounts for office staff) ────────────
        if (!$schema->hasTable('bs_office_users')) {
            $schema->create('bs_office_users', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('office_id');
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->enum('role', ['owner', 'manager', 'staff'])->default('owner');
                $table->boolean('is_active')->default(true);
                $table->rememberToken();
                $table->timestamps();

                $table->foreign('office_id')->references('id')->on('bs_offices')->cascadeOnDelete();
            });
        }

        // ── Office ↔ Client Messages ──────────────────────────────────────────
        if (!$schema->hasTable('bs_office_messages')) {
            $schema->create('bs_office_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('request_id');
                $table->unsignedBigInteger('office_id');
                $table->enum('sender_type', ['office', 'client']);
                $table->unsignedBigInteger('sender_id')->nullable();
                $table->text('message');
                $table->json('attachments')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();

                $table->foreign('request_id')->references('id')->on('bs_requests')->cascadeOnDelete();
                $table->foreign('office_id')->references('id')->on('bs_offices')->cascadeOnDelete();
            });
        }

        // ── Add office_id to bs_requests ──────────────────────────────────────
        if ($schema->hasTable('bs_requests') && !$schema->hasColumn('bs_requests', 'office_id')) {
            $schema->table('bs_requests', function (Blueprint $table) {
                $table->unsignedBigInteger('office_id')->nullable()->after('handled_by');
                $table->enum('office_status', ['pending','accepted','in_progress','waiting_docs','done','rejected'])
                      ->nullable()->after('office_id');

                $table->foreign('office_id')->references('id')->on('bs_offices')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('business')->table('bs_requests', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
            $table->dropColumn(['office_id', 'office_status']);
        });
        Schema::connection('business')->dropIfExists('bs_office_messages');
        Schema::connection('business')->dropIfExists('bs_office_users');
        Schema::connection('business')->dropIfExists('bs_offices');
    }
};