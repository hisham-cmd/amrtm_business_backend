<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| إنشاء جدول الربط بين تخصصات المكاتب والخدمات الحكومية
|--------------------------------------------------------------------------
|
| bs_specialty_services يربط تخصص المكتب (bs_specialties) بالخدمات الحكومية
| (bs_services) المضافة من واجهة الإدارة — بحيث عند اختيار التخصص في نموذج
| تسجيل المكتب تُعرض الخدمات المرتبطة به بدلاً من الكتالوج المرمّز.
|
*/

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if (! $schema->hasTable('bs_specialty_services')) {
            $schema->create('bs_specialty_services', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('specialty_id');
                $table->unsignedBigInteger('service_id');
                $table->timestamps();

                $table->foreign('specialty_id')->references('id')->on('bs_specialties')->cascadeOnDelete();
                $table->foreign('service_id')->references('id')->on('bs_services')->cascadeOnDelete();
                $table->unique(['specialty_id', 'service_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::connection('business')->dropIfExists('bs_specialty_services');
    }
};