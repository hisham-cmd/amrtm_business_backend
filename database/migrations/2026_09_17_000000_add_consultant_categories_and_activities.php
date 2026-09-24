<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Support\ConsultantCatalog;

/*
|--------------------------------------------------------------------------
| إضافة الفئة والنشاط التجاري لتخصصات المستشارين
|--------------------------------------------------------------------------
|
| يضيف عمودَي:
|   - category        VARCHAR(64)  — الفئة الاستشارية (management, finance …)
|   - business_activity VARCHAR(64)  — النشاط التجاري المستهدف (financial, tech …)
|
| ويعمّمهما استناداً إلى كتالوج ConsultantCatalog للمخصصات الاستشارية (150 تخصّصاً)
| التي تحمل is_consultant = true.
|
*/

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if (! $schema->hasTable('bs_specialties')) {
            return;
        }

        if (! $schema->hasColumn('bs_specialties', 'category')) {
            $schema->table('bs_specialties', function (Blueprint $table) {
                $table->string('category', 64)->nullable()->after('is_consultant');
            });
        }

        if (! $schema->hasColumn('bs_specialties', 'business_activity')) {
            $schema->table('bs_specialties', function (Blueprint $table) {
                $table->string('business_activity', 64)->nullable()->after('category');
            });
        }

        $db = DB::connection('business');
        $catalog = ConsultantCatalog::catalog();

        $specialties = $db->table('bs_specialties')
            ->where('is_consultant', true)
            ->whereNull('category')
            ->get(['id', 'name_ar']);

        foreach ($specialties as $spec) {
            $meta = $catalog[trim((string) $spec->name_ar)] ?? null;

            if (! $meta) {
                continue;
            }

            $db->table('bs_specialties')
                ->where('id', $spec->id)
                ->update([
                    'category'          => $meta['category'],
                    'business_activity' => $meta['business_activity'],
                ]);
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if (! $schema->hasTable('bs_specialties')) {
            return;
        }

        $cols = $schema->getColumns('bs_specialties');
        $columnNames = array_column($cols, 'name');

        if (in_array('business_activity', $columnNames)) {
            $schema->table('bs_specialties', function (Blueprint $table) {
                $table->dropColumn('business_activity');
            });
        }

        if (in_array('category', $columnNames)) {
            $schema->table('bs_specialties', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }
};