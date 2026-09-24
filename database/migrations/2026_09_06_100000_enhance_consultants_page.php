<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * تعزيز صفحة المستشارين:
 * - عداد زوار لكل مكتب/مستشار
 * - دعم استشارات الفيديو
 * - نوع الاستشارة (standard / video) في الطلبات
 * - رابط اجتماع الفيديو
 * - تاريخ انتهاء حفظ البيانات (سياسة 5 سنوات)
 */
return new class extends Migration {

    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        // ── bs_offices: عداد زوار + دعم فيديو ──────────────────────────────
        if ($schema->hasTable('bs_offices')) {
            $schema->table('bs_offices', function (Blueprint $table) use ($schema) {
                if (! $schema->hasColumn('bs_offices', 'views_count')) {
                    $table->unsignedBigInteger('views_count')->default(0)->after('is_verified');
                }
                if (! $schema->hasColumn('bs_offices', 'video_consultation_enabled')) {
                    $table->boolean('video_consultation_enabled')->default(false)->after('views_count');
                }
            });
        }

        // ── bs_office_requests: نوع الاستشارة + فيديو + حفظ 5 سنوات ──────
        if ($schema->hasTable('bs_office_requests')) {
            $schema->table('bs_office_requests', function (Blueprint $table) use ($schema) {
                if (! $schema->hasColumn('bs_office_requests', 'consultation_type')) {
                    $table->string('consultation_type', 20)->default('standard')->after('status');
                }
                if (! $schema->hasColumn('bs_office_requests', 'video_meeting_url')) {
                    $table->string('video_meeting_url')->nullable()->after('consultation_type');
                }
                if (! $schema->hasColumn('bs_office_requests', 'data_retention_until')) {
                    $table->timestamp('data_retention_until')->nullable()->after('completed_at');
                }
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_office_requests')) {
            $schema->table('bs_office_requests', function (Blueprint $table) use ($schema) {
                $cols = [];
                if ($schema->hasColumn('bs_office_requests', 'consultation_type')) {
                    $cols[] = 'consultation_type';
                }
                if ($schema->hasColumn('bs_office_requests', 'video_meeting_url')) {
                    $cols[] = 'video_meeting_url';
                }
                if ($schema->hasColumn('bs_office_requests', 'data_retention_until')) {
                    $cols[] = 'data_retention_until';
                }
                if ($cols) {
                    $table->dropColumn($cols);
                }
            });
        }

        if ($schema->hasTable('bs_offices')) {
            $schema->table('bs_offices', function (Blueprint $table) use ($schema) {
                $cols = [];
                if ($schema->hasColumn('bs_offices', 'views_count')) {
                    $cols[] = 'views_count';
                }
                if ($schema->hasColumn('bs_offices', 'video_consultation_enabled')) {
                    $cols[] = 'video_consultation_enabled';
                }
                if ($cols) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
