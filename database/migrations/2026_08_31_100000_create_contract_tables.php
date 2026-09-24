<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        // ── Contract types (أنواع العقود) ─────────────────────────────────────
        if (! $schema->hasTable('bs_contract_types')) {
            $schema->create('bs_contract_types', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // ── Contract clauses (بنود كل نوع عقد) ────────────────────────────────
        if (! $schema->hasTable('bs_contract_clauses')) {
            $schema->create('bs_contract_clauses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('contract_type_id');
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('contract_type_id')
                    ->references('id')->on('bs_contract_types')
                    ->cascadeOnDelete();
            });
        }

        // ── Contracts (العقود الفعلية) ────────────────────────────────────────
        if (! $schema->hasTable('bs_contracts')) {
            $schema->create('bs_contracts', function (Blueprint $table) {
                $table->id();
                $table->string('number');
                $table->unsignedBigInteger('contract_type_id')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();

                $table->foreign('contract_type_id')
                    ->references('id')->on('bs_contract_types')
                    ->nullOnDelete();
            });
        }

        $this->seedTypes();
    }

    private function seedTypes(): void
    {
        $db = DB::connection('business');

        if ($db->table('bs_contract_types')->exists()) {
            return;
        }

        $types = [
            'عقد خدمات فورية' => [
                ['name' => 'التمهيد', 'desc' => 'يوضح خلفية التعاقد بين الطرفين والغرض العام منه.'],
                ['name' => 'غرض العقد', 'desc' => 'تقديم خدمات فورية عبر منصة آمرتم للمستفيدين.'],
                ['name' => 'مدة العقد', 'desc' => 'سارية اعتباراً من تاريخ التوقيع وحتى نهاية المدة المحددة.'],
            ],
            'عقد خدمات آمر تم السنوية' => [
                ['name' => 'التمهيد', 'desc' => 'يوضح خلفية التعاقد السنوي بين الطرفين والغرض العام منه.'],
                ['name' => 'غرض العقد', 'desc' => 'تقديم خدمات آمر تم على أساس سنوي متجدد.'],
                ['name' => 'مدة العقد', 'desc' => 'سنة واحدة قابلة للتجديد التلقائي ما لم يُخطر أحد الطرفين بخلاف ذلك.'],
            ],
            'عقد الاشتراكات في المنصة' => [
                ['name' => 'التمهيد', 'desc' => 'يوضح آلية اشتراك المكاتب في المنصة وحقوق الطرفين.'],
                ['name' => 'غرض العقد', 'desc' => 'تفعيل اشتراك المكتب في منصة آمرتم مقابل رسوم دورية.'],
            ],
            'عقد الوساطة' => [
                ['name' => 'التمهيد', 'desc' => 'يوضح دور المنصة كوسيط بين مقدمي الخدمة والمستفيدين.'],
                ['name' => 'غرض العقد', 'desc' => 'تنظيم عملية الوساطة بين الأطراف عبر المنصة.'],
            ],
        ];

        $now   = now();
        $order = 0;

        foreach ($types as $name => $clauses) {
            $order++;

            $typeId = $db->table('bs_contract_types')->insertGetId([
                'name'       => $name,
                'sort_order' => $order,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $clauseOrder = 0;
            foreach ($clauses as $clause) {
                $clauseOrder++;

                $db->table('bs_contract_clauses')->insert([
                    'contract_type_id' => $typeId,
                    'name'             => $clause['name'],
                    'description'      => $clause['desc'],
                    'sort_order'       => $clauseOrder,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        $schema->dropIfExists('bs_contract_clauses');
        $schema->dropIfExists('bs_contracts');
        $schema->dropIfExists('bs_contract_types');
    }
};