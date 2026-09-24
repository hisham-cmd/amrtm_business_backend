<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * مصفوفة صلاحيات الواجهات لكل نوع حساب (عميل فردي / منشأة / مدير / مشرف
     * / منشأة مكاتب مساندة / منشأة استشارية).
     * المنشأة قد تمتلك أكثر من نوع حساب في نفس الوقت فتظهر لها واجهات الأنواع
     * المتاحة مجتمعة.
     */
    public function up(): void
    {
        if (! Schema::connection('business')->hasTable('bs_type_interfaces')) {
            Schema::connection('business')->create('bs_type_interfaces', function (Blueprint $table) {
                $table->id();
                $table->string('type_key', 50)->index();
                $table->string('interface_key', 100)->index();
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();

                $table->unique(['type_key', 'interface_key']);
            });
        }
    }

    public function down(): void
    {
        Schema::connection('business')->dropIfExists('bs_type_interfaces');
    }
};
