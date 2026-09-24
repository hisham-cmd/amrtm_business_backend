<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_office_settlements')) {
            return;
        }

        $schema->create('bs_office_settlements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('office_id');
            $table->unsignedBigInteger('request_id');
            $table->decimal('amount', 10, 2);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->string('transaction_ref', 191)->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->unique('request_id');
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_office_settlements')) {
            $schema->dropIfExists('bs_office_settlements');
        }
    }
};