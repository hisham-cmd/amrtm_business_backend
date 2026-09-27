<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * تثبيت قيود المفاتيح الأجنبية على جداول نطاق الأعمال (bs_*)
 *
 * السبب: القيود كانت تُعرَّف داخل مايجريشنات لاحقة داخل شرط hasTable()،
 * فإذا كانت الجداول موجودة مسبقاً في القاعدة (وهو حال أي بيئة مطوّرة أو
 * الإنتاج) لم تُنفَّذ قيودها إطلاقاً. النتيجة بيانات يتيمة في bs_office_users
 * و bs_office_documents و bs_office_services و bs_office_specialties
 * و bs_contracts، وكل تصديرGun untuk الإنتاج يفشل بخطأ
 * #1452 أو errno 150.
 *
 * هذا المايجريشن:
 *   1. ينظّف البيانات اليتيمة مسبقاً (سلوك مطابق لما يطلبه كل قيد)
 *   2. يضيف القيود الناقصة فقط
 *
 * آمن للتشغيل المتكرر ولا يفعل شيئاً إن كانت القيود موجودة.
 */
return new class extends Migration
{
    protected $connection = 'business';

    /**
     * القيود المطلوبة: [جدول الابن، العمود، جدول الأب، عمود الأب، سلوك الحذف]
     * مستخرجة مباشرة من تعريفات المايجريشنز القائمة في المشروع.
     */
    private function constraints(): array
    {
        return [
            ['bs_entities', 'category_id', 'bs_categories', 'id', 'CASCADE'],
            ['bs_services', 'entity_id', 'bs_entities', 'id', 'CASCADE'],
            ['bs_requests', 'service_id', 'bs_services', 'id', 'CASCADE'],
            ['bs_requests', 'entity_id', 'bs_entities', 'id', 'CASCADE'],
            ['bs_requests', 'office_id', 'bs_offices', 'id', 'SET NULL'],
            ['bs_request_logs', 'request_id', 'bs_requests', 'id', 'CASCADE'],
            ['bs_payments', 'request_id', 'bs_requests', 'id', 'SET NULL'],
            ['bs_notifications', 'request_id', 'bs_requests', 'id', 'CASCADE'],
            ['bs_office_users', 'office_id', 'bs_offices', 'id', 'CASCADE'],
            ['bs_office_messages', 'request_id', 'bs_requests', 'id', 'CASCADE'],
            ['bs_office_messages', 'office_id', 'bs_offices', 'id', 'CASCADE'],
            ['bs_office_services', 'office_id', 'bs_offices', 'id', 'CASCADE'],
            ['bs_office_requests', 'office_id', 'bs_offices', 'id', 'CASCADE'],
            ['bs_office_requests', 'office_service_id', 'bs_office_services', 'id', 'CASCADE'],
            ['bs_office_profiles', 'office_id', 'bs_offices', 'id', 'CASCADE'],
            ['bs_office_specialties', 'office_id', 'bs_offices', 'id', 'CASCADE'],
            ['bs_office_specialties', 'specialty_id', 'bs_specialties', 'id', 'CASCADE'],
            ['bs_office_documents', 'office_id', 'bs_offices', 'id', 'CASCADE'],
            ['bs_contract_clauses', 'contract_type_id', 'bs_contract_types', 'id', 'CASCADE'],
            ['bs_contracts', 'contract_type_id', 'bs_contract_types', 'id', 'SET NULL'],
            ['bs_contract_verification_codes', 'contract_id', 'bs_contracts', 'id', 'CASCADE'],
            ['bs_specialty_services', 'specialty_id', 'bs_specialties', 'id', 'CASCADE'],
            ['bs_specialty_services', 'service_id', 'bs_services', 'id', 'CASCADE'],
        ];
    }

    public function up(): void
    {
        $db = DB::connection('business');
        $schema = Schema::connection('business');

        $db->statement('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($this->constraints() as [$child, $column, $parent, $parentColumn, $onDelete]) {
            $this->ensureTable($schema, $child, $parent);

            if (! $this->hasColumn($db, $child, $column)) {
                continue;
            }

            $this->cleanOrphans($db, $child, $column, $parent, $parentColumn, $onDelete);
            $this->addConstraint($db, $schema, $child, $column, $parent, $parentColumn, $onDelete);
        }

        $db->statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down(): void
    {
        $db = DB::connection('business');
        $db->statement('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($this->constraints() as [$child, $column]) {
            $name = "{$child}_{$column}_foreign";
            try {
                $schema = Schema::connection('business');
                if ($schema->hasTable($child) && $this->hasColumn($db, $child, $column)) {
                    $schema->table($child, function (Blueprint $table) use ($name) {
                        $table->dropForeign($name);
                    });
                }
            } catch (\Throwable $e) {
                // القيد غير موجود أصلاً
            }
        }

        $db->statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    // ── مساعدات ──────────────────────────────────────────────────────────

    private function ensureTable($schema, string $child, string $parent): void
    {
        if (! $schema->hasTable($child) || ! $schema->hasTable($parent)) {
            return;
        }
    }

    private function hasColumn($db, string $table, string $column): bool
    {
        return in_array($column, $db->getSchemaBuilder()->getColumnListing($table), true);
    }

    private function hasConstraint($db, string $table, string $column): bool
    {
        $exists = $db->selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$table, $column]
        );

        return (int) $exists->c > 0;
    }

    private function cleanOrphans($db, string $child, string $column, string $parent, string $parentColumn, string $onDelete): void
    {
        $count = (int) $db->selectOne(
            "SELECT COUNT(*) AS c FROM `{$child}` t
              LEFT JOIN `{$parent}` p ON t.`{$column}` = p.`{$parentColumn}`
              WHERE t.`{$column}` IS NOT NULL AND p.`{$parentColumn}` IS NULL"
        )->c;

        if ($count === 0) {
            return;
        }

        if ($onDelete === 'SET NULL') {
            if (! $this->isNullable($db, $child, $column)) {
                $db->statement("ALTER TABLE `{$child}` MODIFY `{$column}` BIGINT UNSIGNED NULL");
            }
            $db->statement(
                "UPDATE `{$child}` t
                  LEFT JOIN `{$parent}` p ON t.`{$column}` = p.`{$parentColumn}`
                  SET t.`{$column}` = NULL
                  WHERE t.`{$column}` IS NOT NULL AND p.`{$parentColumn}` IS NULL"
            );
        } else {
            $db->statement(
                "DELETE t FROM `{$child}` t
                  LEFT JOIN `{$parent}` p ON t.`{$column}` = p.`{$parentColumn}`
                  WHERE t.`{$column}` IS NOT NULL AND p.`{$parentColumn}` IS NULL"
            );
        }
    }

    private function isNullable($db, string $table, string $column): bool
    {
        $row = $db->selectOne(
            'SELECT IS_NULLABLE FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        );

        return $row && strtoupper($row->IS_NULLABLE) === 'YES';
    }

    private function addConstraint($db, $schema, string $child, string $column, string $parent, string $parentColumn, string $onDelete): void
    {
        if ($this->hasConstraint($db, $child, $column)) {
            return;
        }

        $name = "{$child}_{$column}_foreign";
        $db->statement(
            "ALTER TABLE `{$child}`
               ADD CONSTRAINT `{$name}`
               FOREIGN KEY (`{$column}`) REFERENCES `{$parent}` (`{$parentColumn}`)
               ON DELETE {$onDelete}"
        );
    }
};
