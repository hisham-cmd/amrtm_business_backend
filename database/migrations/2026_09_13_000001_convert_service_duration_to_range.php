<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * تحويل "مدة الخدمة" من قيمة ثابتة إلى نطاق (من...إلى):
 * - bs_services:      estimated_days (int)  → duration_min/duration_max/duration_unit
 * - bs_office_services: duration (string)    → duration_min/duration_max/duration_unit
 * مع backfill من القيم الموجودة وإسقاط الأعمدة القديمة.
 */
return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        // ── bs_services ──
        if ($schema->hasTable('bs_services')) {
            if (!$schema->hasColumn('bs_services', 'duration_min')) {
                $schema->table('bs_services', function (Blueprint $table) {
                    $table->unsignedInteger('duration_min')->nullable()->after('price');
                    $table->unsignedInteger('duration_max')->nullable()->after('duration_min');
                    $table->string('duration_unit', 20)->nullable()->after('duration_max');
                });
            }

            if ($schema->hasColumn('bs_services', 'estimated_days')) {
                DB::connection('business')->table('bs_services')
                    ->whereNotNull('estimated_days')
                    ->update([
                        'duration_min'  => DB::raw('estimated_days'),
                        'duration_max'  => DB::raw('estimated_days'),
                        'duration_unit' => 'day',
                    ]);
            }

            if ($schema->hasColumn('bs_services', 'estimated_days')) {
                $schema->table('bs_services', function (Blueprint $table) {
                    $table->dropColumn('estimated_days');
                });
            }
        }

        // ── bs_office_services ──
        if ($schema->hasTable('bs_office_services')) {
            if (!$schema->hasColumn('bs_office_services', 'duration_min')) {
                $schema->table('bs_office_services', function (Blueprint $table) {
                    $table->unsignedInteger('duration_min')->nullable()->after('price');
                    $table->unsignedInteger('duration_max')->nullable()->after('duration_min');
                    $table->string('duration_unit', 20)->nullable()->after('duration_max');
                });
            }

            if ($schema->hasColumn('bs_office_services', 'duration')) {
                $rows = DB::connection('business')->table('bs_office_services')
                    ->whereNotNull('duration')
                    ->where('duration', '!=', '')
                    ->get(['id', 'duration']);

                foreach ($rows as $row) {
                    $parsed = $this->parseDuration((string) $row->duration);
                    DB::connection('business')->table('bs_office_services')
                        ->where('id', $row->id)
                        ->update($parsed);
                }

                $schema->table('bs_office_services', function (Blueprint $table) {
                    $table->dropColumn('duration');
                });
            }
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        // إعادة estimated_days من النطاق (قيمة واحدة)
        if ($schema->hasTable('bs_services')) {
            if (!$schema->hasColumn('bs_services', 'estimated_days')) {
                $schema->table('bs_services', function (Blueprint $table) {
                    $table->integer('estimated_days')->default(3)->after('price');
                });
            }

            DB::connection('business')->table('bs_services')
                ->whereNotNull('duration_min')
                ->update(['estimated_days' => DB::raw('duration_min')]);

            $this->dropRangeColumns($schema, 'bs_services');
        }

        // إعادة duration كنص حر
        if ($schema->hasTable('bs_office_services')) {
            if (!$schema->hasColumn('bs_office_services', 'duration')) {
                $schema->table('bs_office_services', function (Blueprint $table) {
                    $table->string('duration')->nullable()->after('price');
                });
            }

            $rows = DB::connection('business')->table('bs_office_services')
                ->whereNotNull('duration_min')
                ->get(['id', 'duration_min', 'duration_max', 'duration_unit']);

            foreach ($rows as $row) {
                DB::connection('business')->table('bs_office_services')
                    ->where('id', $row->id)
                    ->update(['duration' => $this->formatDuration((int) $row->duration_min, (int) $row->duration_max, (string) $row->duration_unit)]);
            }

            $this->dropRangeColumns($schema, 'bs_office_services');
        }
    }

    private function dropRangeColumns($schema, string $table): void
    {
        foreach (['duration_min', 'duration_max', 'duration_unit'] as $column) {
            if ($schema->hasColumn($table, $column)) {
                $schema->table($table, function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    private function parseDuration(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return ['duration_min' => null, 'duration_max' => null, 'duration_unit' => null];
        }

        $map = ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9'];
        $text = strtr($text, $map);

        $unit = null;
        if (preg_match('/ساعة|hour/i', $text)) $unit = 'hour';
        elseif (preg_match('/أسبوع|اسبوع|week/i', $text)) $unit = 'week';
        elseif (preg_match('/شهر|month/i', $text)) $unit = 'month';
        elseif (preg_match('/يوم|يومان|أيام|day/i', $text)) $unit = 'day';

        preg_match_all('/\d+/', $text, $matches);
        $numbers = array_map('intval', $matches[0] ?? []);

        if (count($numbers) === 0) {
            return [
                'duration_min'  => $unit ? 1 : null,
                'duration_max'  => $unit ? 1 : null,
                'duration_unit' => $unit,
            ];
        }

        return [
            'duration_min'  => min($numbers[0], $numbers[1] ?? $numbers[0]),
            'duration_max'  => max($numbers[0], $numbers[1] ?? $numbers[0]),
            'duration_unit' => $unit ?? 'day',
        ];
    }

    private function formatDuration(int $min, int $max, string $unit): ?string
    {
        $labels = [
            'day'   => ['1' => 'يوم', '2' => 'يومان', 'plural' => 'أيام'],
            'hour'  => ['1' => 'ساعة', '2' => 'ساعتين', 'plural' => 'ساعات'],
            'week'  => ['1' => 'أسبوع', '2' => 'أسبوعين', 'plural' => 'أسابيع'],
            'month' => ['1' => 'شهر', '2' => 'شهران', 'plural' => 'أشهر'],
        ];

        $unit = array_key_exists($unit, $labels) ? $unit : 'day';
        $units = $labels[$unit];

        if ($min === $max) {
            $label = $min === 1 ? $units['1'] : ($min === 2 ? $units['2'] : $units['plural']);
            return $min . ' ' . $label;
        }

        return $min . ' – ' . $max . ' ' . $units['plural'];
    }
};