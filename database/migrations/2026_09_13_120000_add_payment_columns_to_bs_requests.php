<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if (!$schema->hasTable('bs_requests')) {
            return;
        }

        $add = [
            // null = unpaid/مؤجل (المستشارون) — prepaid = مدفوع مسبقاً (المكاتب المساندة والكتالوج).
            'payment_status' => fn(Blueprint $t) => $t->string('payment_status', 20)->nullable()->after('price'),
            'payment_method' => fn(Blueprint $t) => $t->string('payment_method', 20)->nullable()->after('payment_status'),
            'payment_ref'    => fn(Blueprint $t) => $t->string('payment_ref', 191)->nullable()->after('payment_method'),
            'paid_at'        => fn(Blueprint $t) => $t->timestamp('paid_at')->nullable()->after('payment_ref'),
            'settled_at'     => fn(Blueprint $t) => $t->timestamp('settled_at')->nullable()->after('paid_at'),
        ];

        $schema->table('bs_requests', function (Blueprint $table) use ($schema, $add) {
            foreach ($add as $column => $builder) {
                if (!$schema->hasColumn('bs_requests', $column)) {
                    $builder($table);
                }
            }
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if (!$schema->hasTable('bs_requests')) {
            return;
        }

        $columns = ['settled_at', 'paid_at', 'payment_ref', 'payment_method', 'payment_status'];
        foreach ($columns as $column) {
            if ($schema->hasColumn('bs_requests', $column)) {
                $schema->table('bs_requests', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};