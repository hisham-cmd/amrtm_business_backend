<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if (! $schema->hasTable('bs_contract_verification_codes')) {
            $schema->create('bs_contract_verification_codes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('contract_id');
                $table->string('email');
                $table->string('code', 6);
                $table->string('type'); // party1_sign, party2_sign
                $table->timestamp('expires_at');
                $table->boolean('used')->default(false);
                $table->integer('attempts')->default(0);
                $table->timestamps();

                $table->foreign('contract_id')
                    ->references('id')->on('bs_contracts')
                    ->cascadeOnDelete();

                $table->index(['contract_id', 'type', 'used']);
            });
        }
    }

    public function down(): void
    {
        Schema::connection('business')->dropIfExists('bs_contract_verification_codes');
    }
};
