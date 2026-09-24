<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'business';

    public function up(): void
    {
        Schema::connection('business')->table('bs_notifications', function (Blueprint $table) {
            $cols = Schema::connection('business')->getColumnListing('bs_notifications');

            if (!in_array('recipient_type', $cols)) {
                $table->enum('recipient_type', ['user', 'office', 'admin'])->default('user')->after('id')->index();
            }
            if (!in_array('office_id', $cols)) {
                $table->unsignedBigInteger('office_id')->nullable()->after('user_id')->index();
            }
            if (!in_array('data', $cols)) {
                $table->json('data')->nullable()->after('body');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('business')->table('bs_notifications', function (Blueprint $table) {
            $table->dropColumn(['recipient_type', 'office_id', 'data']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};