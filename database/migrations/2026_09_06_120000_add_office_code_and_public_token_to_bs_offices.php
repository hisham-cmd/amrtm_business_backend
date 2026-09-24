<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| إضافة office_code و public_token إلى bs_offices
|--------------------------------------------------------------------------
|
| نموذج Office يُنشئ تلقائياً office_code و public_token عند إنشاء أي سجل
| (عبر booted)، كما يكتبهما مسار تسجيل ProviderAccountController — لكن هذين
| العمودين لم يكونا موجودين في المخطط، فكانت قاعدة البيانات الحقيقية مختلقة
| عن ملفات الهجرة. هذه الهجرة توازن المخطط مع النموذج.
|
*/

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if (! $schema->hasTable('bs_offices')) {
            return;
        }

        if (! $schema->hasColumn('bs_offices', 'office_code')) {
            $schema->table('bs_offices', function (Blueprint $table) {
                $table->string('office_code', 50)->nullable()->index()->after('id');
            });
        }

        if (! $schema->hasColumn('bs_offices', 'public_token')) {
            $schema->table('bs_offices', function (Blueprint $table) {
                $table->string('public_token', 64)->nullable()->index()->after('office_code');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if (! $schema->hasTable('bs_offices')) {
            return;
        }

        $schema->table('bs_offices', function (Blueprint $table) {
            $table->dropColumn(['public_token', 'office_code']);
        });
    }
};