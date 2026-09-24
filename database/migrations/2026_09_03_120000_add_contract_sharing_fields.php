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

        if (! $schema->hasColumn('bs_contracts', 'view_token')) {
            $schema->table('bs_contracts', function (Blueprint $table) {
                $table->string('view_token', 64)->nullable()->unique()->after('status');
                $table->timestamp('second_party_email_sent_at')->nullable()->after('view_token');
                $table->string('pdf_path')->nullable()->after('second_party_email_sent_at');
                $table->timestamp('second_party_viewed_at')->nullable()->after('pdf_path');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        foreach (['view_token', 'second_party_email_sent_at', 'pdf_path', 'second_party_viewed_at'] as $column) {
            if ($schema->hasColumn('bs_contracts', $column)) {
                $schema->table('bs_contracts', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
