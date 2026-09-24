<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| توحيد طلبات المكاتب: دمج bs_office_requests في bs_requests
|--------------------------------------------------------------------------
|
| القرار المعماري:
|   - كل الطلبات (كتالوج + مباشر من عميل لمكتب) تعيش الآن في bs_requests.
|   - عمود origin يميّز المصدر: catalog (كتالوج المنصة) / office (مباشر).
|   - bs_office_requests تُجمد كأرشيف تاريخي للقراءة فقط ولا تُكتب بعد الآن.
|
| الأعمدة الجديدة في bs_requests:
|   origin, office_service_id, commission_amount, consultation_type,
|   video_meeting_url, data_retention_until, legacy_source_id
|
| تحويل service_id / entity_id إلى nullable لأن الطلبات المباشرة قد لا
| تتوافق مع خدمة/جهة من كتالوج المنصة.
|
| خريطة الحالات عند الترحيل:
|   pending     -> office_status=pending      status=processing
|   accepted    -> office_status=accepted     status=processing
|   in_progress -> office_status=in_progress  status=in_progress
|   waiting_docs-> office_status=waiting_docs status=in_progress
|   done        -> office_status=done         status=done
|   rejected    -> office_status=rejected     status=rejected
|
*/

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');
        $driver = DB::connection('business')->getDriverName();

        if (! $schema->hasTable('bs_requests')) {
            return;
        }

        $schema->table('bs_requests', function (Blueprint $table) use ($schema) {
            $cols = fn () => $schema->getColumnListing('bs_requests');

            if (! in_array('origin', $cols(), true)) {
                $table->enum('origin', ['catalog', 'office'])->default('catalog');
            }
            if (! in_array('office_service_id', $cols(), true)) {
                $table->unsignedBigInteger('office_service_id')->nullable();
            }
            if (! in_array('commission_amount', $cols(), true)) {
                $table->decimal('commission_amount', 10, 2)->default(0);
            }
            if (! in_array('consultation_type', $cols(), true)) {
                $table->string('consultation_type')->nullable();
            }
            if (! in_array('video_meeting_url', $cols(), true)) {
                $table->string('video_meeting_url')->nullable();
            }
            if (! in_array('data_retention_until', $cols(), true)) {
                $table->timestamp('data_retention_until')->nullable();
            }
            if (! in_array('legacy_source_id', $cols(), true)) {
                $table->unsignedBigInteger('legacy_source_id')->nullable();
            }
            if (! in_array('office_note', $cols(), true)) {
                $table->text('office_note')->nullable();
            }
        });

        // service_id / entity_id تصبح قابلة للـ nullable.
        if ($driver === 'mysql') {
            $schema->table('bs_requests', function (Blueprint $table) use ($schema) {
                $cols = $schema->getColumnListing('bs_requests');
                if (in_array('service_id', $cols, true)) {
                    $table->unsignedBigInteger('service_id')->nullable()->change();
                }
                if (in_array('entity_id', $cols, true)) {
                    $table->unsignedBigInteger('entity_id')->nullable()->change();
                }
            });
        } else {
            $schema->table('bs_requests', function (Blueprint $table) use ($schema) {
                $cols = $schema->getColumnListing('bs_requests');
                if (in_array('service_id', $cols, true)) {
                    $table->unsignedBigInteger('service_id')->nullable()->change();
                }
                if (in_array('entity_id', $cols, true)) {
                    $table->unsignedBigInteger('entity_id')->nullable()->change();
                }
            });
        }

        // فهارس الأداء على المصدر
        $this->ensureIndex($schema, ['origin', 'user_id'], 'bs_requests_origin_user_index');
        $this->ensureIndex($schema, ['origin', 'office_id'], 'bs_requests_origin_office_index');

        $this->migrateLegacyRows($schema);
    }

    protected function ensureIndex($schema, array $columns, string $name): void
    {
        if (! $schema->hasTable('bs_requests')) {
            return;
        }
        try {
            $names = array_map(fn ($ix) => $ix['name'], $schema->getIndexes('bs_requests'));
        } catch (\Throwable $e) {
            return; // SQLite لا يقفل قراءة الفهارس (بنفس نمط migration الأداء)
        }
        if (in_array($name, $names, true)) {
            return;
        }
        $schema->table('bs_requests', function (Blueprint $table) use ($columns, $name) {
            $table->index($columns, $name);
        });
    }

    protected function migrateLegacyRows($schema): void
    {
        if (! $schema->hasTable('bs_office_requests')) {
            return;
        }

        $business = DB::connection('business');

        if ($business->table('bs_office_requests')->count() === 0) {
            return;
        }

        $serviceMap = $business->table('bs_office_services')->get()->keyBy('id');

        $statusMap = [
            'pending'      => ['office_status' => 'pending',      'status' => 'processing'],
            'accepted'     => ['office_status' => 'accepted',     'status' => 'processing'],
            'in_progress'  => ['office_status' => 'in_progress',  'status' => 'in_progress'],
            'waiting_docs' => ['office_status' => 'waiting_docs', 'status' => 'in_progress'],
            'done'         => ['office_status' => 'done',         'status' => 'done'],
            'rejected'     => ['office_status' => 'rejected',     'status' => 'rejected'],
        ];

        $business->table('bs_office_requests')->orderBy('id')->chunkById(100, function ($rows) use ($business, $serviceMap, $statusMap) {
            foreach ($rows as $row) {
                if ($business->table('bs_requests')->where('ref_number', $row->ref_number)->exists()) {
                    continue;
                }

                $svc = $serviceMap[$row->office_service_id] ?? null;
                $mapped = $statusMap[$row->status] ?? ['office_status' => 'pending', 'status' => 'processing'];

                $id = $business->table('bs_requests')->insertGetId([
                    'ref_number'            => $row->ref_number,
                    'origin'                => 'office',
                    'user_id'               => $row->user_id,
                    'service_id'            => $svc?->source_service_id ?? null,
                    'entity_id'             => $svc?->entity_id ?? null,
                    'office_service_id'     => $row->office_service_id,
                    'client_name'           => $row->client_name,
                    'client_email'          => $row->client_email,
                    'client_phone'          => $row->client_phone,
                    'client_id_number'      => $row->client_id_number,
                    'company_name'          => null,
                    'company_cr'            => null,
                    'notes'                 => $row->notes,
                    'attachments'           => $row->attachments,
                    'custom_field_values'   => null,
                    'price'                 => $row->price,
                    'commission_amount'     => $row->commission_amount,
                    'status'                => $mapped['status'],
                    'reject_reason'         => $row->status === 'rejected' ? $row->office_note : null,
                    'office_status'         => $mapped['office_status'],
                    'office_note'           => $row->office_note,
                    'fulfillment'           => 'assigned',
                    'office_id'             => $row->office_id,
                    'assigned_at'           => $row->created_at,
                    'assigned_by'           => null,
                    'handled_by'            => null,
                    'estimated_completion'  => null,
                    'completed_at'          => $row->completed_at,
                    'consultation_type'     => $row->consultation_type ?? 'standard',
                    'video_meeting_url'     => $row->video_meeting_url ?? null,
                    'data_retention_until'  => $row->data_retention_until ?? null,
                    'legacy_source_id'      => $row->id,
                    'created_at'            => $row->created_at,
                    'updated_at'            => $row->updated_at,
                ]);

                if ($business->table('bs_request_logs')->where('request_id', $id)->exists()) {
                    continue;
                }

                $business->table('bs_request_logs')->insert([
                    'request_id' => $id,
                    'user_id'    => $row->user_id,
                    'status'     => $mapped['status'],
                    'log_type'   => 'status_change',
                    'note'       => 'تم تحويل الطلب المباشر إلى النظام الموحد',
                    'created_at' => $row->created_at,
                    'updated_at' => $row->created_at,
                ]);
            }
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        if (! $schema->hasTable('bs_requests')) {
            return;
        }

        foreach (['bs_requests_origin_user_index', 'bs_requests_origin_office_index'] as $index) {
            try {
                $names = array_map(fn ($ix) => $ix['name'], $schema->getIndexes('bs_requests'));
            } catch (\Throwable $e) {
                break;
            }
            if (in_array($index, $names, true)) {
                $schema->table('bs_requests', function (Blueprint $table) use ($index) {
                    $table->dropIndex($index);
                });
            }
        }

        $schema->table('bs_requests', function (Blueprint $table) use ($schema) {
            $cols = $schema->getColumnListing('bs_requests');
            foreach (['origin', 'office_service_id', 'commission_amount', 'consultation_type', 'video_meeting_url', 'data_retention_until', 'legacy_source_id', 'office_note'] as $col) {
                if (in_array($col, $cols, true)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};