<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| إنشاء جدول bs_office_profiles
|--------------------------------------------------------------------------
|
| هذا الجدول كان إجبارياً في مسار تسجيل المكتب (ProviderAccountController::store)
| ونموذج OfficeProfile — لكنه لم يكن موجوداً في ملفات الهجرة إطلاقاً، فكانت
| قاعدة البيانات الحقيقية تعتمد على جدول مضاف يدوياً خارج الهجرات.
| هذه الهجرة تضيفه رسمياً وفق أعمدة النموذج ومسار الحفظ.
|
*/

return new class extends Migration
{
    protected $connection = 'business';

    public function up(): void
    {
        $schema = Schema::connection('business');

        if ($schema->hasTable('bs_office_profiles')) {
            return;
        }

        $schema->create('bs_office_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('office_id')->primary();
            $table->string('license_number')->nullable();
            $table->string('cr_number')->nullable();
            $table->string('mobile')->nullable();
            $table->string('country')->nullable();
            $table->string('governorate')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('street')->nullable();
            $table->string('building_number')->nullable();
            $table->string('office_number')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->unsignedInteger('handled_cases')->default(0);
            $table->string('custom_specialty')->nullable();
            $table->boolean('profile_completed')->default(true);
            $table->string('verification_status')->default('pending');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('office_code')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('trademark_registration_number', 191)->nullable();
            $table->timestamps();

            $table->foreign('office_id')->references('id')->on('bs_offices')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection('business')->dropIfExists('bs_office_profiles');
    }
};