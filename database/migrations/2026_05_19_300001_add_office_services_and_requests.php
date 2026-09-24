<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        // ── Office's own service catalog ──────────────────────────────────────
        if (!$schema->hasTable('bs_office_services')) {
            $schema->create('bs_office_services', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('office_id');
                $table->string('name_ar');
                $table->string('name_en');
                $table->text('description_ar')->nullable();
                $table->text('description_en')->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->string('duration')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('office_id')->references('id')->on('bs_offices')->cascadeOnDelete();
            });
        }

        // ── Direct client → office requests ──────────────────────────────────
        if (!$schema->hasTable('bs_office_requests')) {
            $schema->create('bs_office_requests', function (Blueprint $table) {
                $table->id();
                $table->string('ref_number')->unique();
                $table->unsignedBigInteger('user_id');               // main DB user — no cross-DB FK
                $table->unsignedBigInteger('office_id');
                $table->unsignedBigInteger('office_service_id');

                $table->string('client_name');
                $table->string('client_phone');
                $table->string('client_email');
                $table->string('client_id_number')->nullable();
                $table->text('notes')->nullable();
                $table->json('attachments')->nullable();

                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('commission_amount', 10, 2)->default(0);

                $table->enum('status', ['pending', 'accepted', 'in_progress', 'waiting_docs', 'done', 'rejected'])
                      ->default('pending');
                $table->text('office_note')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->foreign('office_id')->references('id')->on('bs_offices')->cascadeOnDelete();
                $table->foreign('office_service_id')->references('id')->on('bs_office_services')->cascadeOnDelete();
            });
        }

        // ── Commission rate on offices ─────────────────────────────────────────
        if ($schema->hasTable('bs_offices') && !$schema->hasColumn('bs_offices', 'commission_rate')) {
            $schema->table('bs_offices', function (Blueprint $table) {
                $table->decimal('commission_rate', 5, 2)->default(0)->after('is_verified');
            });
        }
    }

    public function down(): void
    {
        Schema::connection('business')->table('bs_offices', function (Blueprint $table) {
            $table->dropColumn('commission_rate');
        });
        Schema::connection('business')->dropIfExists('bs_office_requests');
        Schema::connection('business')->dropIfExists('bs_office_services');
    }
};