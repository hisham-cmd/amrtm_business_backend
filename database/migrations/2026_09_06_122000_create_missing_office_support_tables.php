<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| إنشاء جداول التخصصات والمستندات الناقصة
|--------------------------------------------------------------------------
|
| bs_specialties (كتالوج التخصصات)، bs_office_specialties (الرابط بين المكتب
| والتخصصات)، و bs_office_documents (مستندات التسجيل/التراخيص) — لم تكن أي
| منها موجودة في الهجرات رغم استخدامها في مسار تسجيل المكتب ودليل المكاتب،
| وكانت موجودة في قاعدة البيانات الحقيقية فقط بشكل يدوي.
|
*/

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if (! $schema->hasTable('bs_specialties')) {
            $schema->create('bs_specialties', function (Blueprint $table) {
                $table->id();
                $table->string('office_type');
                $table->string('name_ar');
                $table->string('name_en')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['office_type', 'is_active']);
            });
        }

        if (! $schema->hasTable('bs_office_specialties')) {
            $schema->create('bs_office_specialties', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('office_id');
                $table->unsignedBigInteger('specialty_id');
                $table->timestamps();

                $table->foreign('office_id')->references('id')->on('bs_offices')->cascadeOnDelete();
                $table->foreign('specialty_id')->references('id')->on('bs_specialties')->cascadeOnDelete();
                $table->unique(['office_id', 'specialty_id']);
            });
        }

        if (! $schema->hasTable('bs_office_documents')) {
            $schema->create('bs_office_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('office_id');
                $table->string('document_type');
                $table->string('file');
                $table->string('file_name')->nullable();
                $table->boolean('is_verified')->default(false);
                $table->timestamps();

                $table->foreign('office_id')->references('id')->on('bs_offices')->cascadeOnDelete();
                $table->index(['office_id', 'document_type']);
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('business');

        $schema->dropIfExists('bs_office_documents');
        $schema->dropIfExists('bs_office_specialties');
        $schema->dropIfExists('bs_specialties');
    }
};