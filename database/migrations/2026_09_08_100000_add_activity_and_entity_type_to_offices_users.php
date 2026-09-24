<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| النشاط التجاري + نوع المنشأة (شركة/مؤسسة)
|--------------------------------------------------------------------------
|
| حقل «النشاط التجاري» (activity) يصف النشاط الذي تمارسه المنشأة، وحقل
| «نوع المنشأة» (entity_type) يحدد الشكل القانوني (شركة company / مؤسسة
| institution). يُضافان إلى bs_offices (المزوّد) و bs_users (عميل المنشأة)
| بنفس نمط الحماية الثنائي للـ Schema على اتصال business.
|
*/

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_offices')) {
            if (! $schema->hasColumn('bs_offices', 'activity')) {
                $schema->table('bs_offices', function (Blueprint $table) {
                    $table->string('activity', 191)->nullable()->after('name_en');
                });
            }

            if (! $schema->hasColumn('bs_offices', 'entity_type')) {
                $schema->table('bs_offices', function (Blueprint $table) {
                    $table->string('entity_type', 20)->nullable()->after('activity');
                });
            }
        }

        if ($schema->hasTable('bs_users')) {
            if (! $schema->hasColumn('bs_users', 'activity')) {
                $schema->table('bs_users', function (Blueprint $table) {
                    $table->string('activity', 191)->nullable()->after('legal_name');
                });
            }

            if (! $schema->hasColumn('bs_users', 'entity_type')) {
                $schema->table('bs_users', function (Blueprint $table) {
                    $table->string('entity_type', 20)->nullable()->after('activity');
                });
            }
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_offices')) {
            $columns = array_filter(['activity', 'entity_type'], fn ($col) => $schema->hasColumn('bs_offices', $col));
            if ($columns) {
                $schema->table('bs_offices', function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }

        if ($schema->hasTable('bs_users')) {
            $columns = array_filter(['activity', 'entity_type'], fn ($col) => $schema->hasColumn('bs_users', $col));
            if ($columns) {
                $schema->table('bs_users', function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }
    }
};