<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| إزالة عمود النشاط التجاري الحر (activity)
|--------------------------------------------------------------------------
|
| أصبح «النشاط التجاري» يمثل قائمة النشاطات المعتمدة (office_type: مكاتب
| المحاماة/الخدمات/التخليص الجمركي/...) — حقل بديل عن عمود activity النصي
| الذي أُضيف مؤقتاً ثم أُلغي قبل أي نشر، فيُزال من bs_offices و bs_users
| بنمط الحماية الثنائي على اتصال business مع إبقاء entity_type.
|
*/

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_offices') && $schema->hasColumn('bs_offices', 'activity')) {
            $schema->table('bs_offices', function (Blueprint $table) {
                $table->dropColumn('activity');
            });
        }

        if ($schema->hasTable('bs_users') && $schema->hasColumn('bs_users', 'activity')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->dropColumn('activity');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_offices') && ! $schema->hasColumn('bs_offices', 'activity')) {
            $schema->table('bs_offices', function (Blueprint $table) {
                $table->string('activity', 191)->nullable()->after('name_en');
            });
        }

        if ($schema->hasTable('bs_users') && ! $schema->hasColumn('bs_users', 'activity')) {
            $schema->table('bs_users', function (Blueprint $table) {
                $table->string('activity', 191)->nullable()->after('legal_name');
            });
        }
    }
};