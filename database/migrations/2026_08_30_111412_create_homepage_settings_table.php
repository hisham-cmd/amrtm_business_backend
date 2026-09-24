<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('homepage_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $defaults = [
            'site_title'             => 'منصة آمر تم لخدمات قطاع الأعمال',
            'site_tagline'           => 'أختر الخدمة المطلوبة من خلال الجهات التالية',
            'site_subtitle'          => 'منصة تعمل وفق مفهوم النافذة الواحدة لاستقبال طلبات العملاء وإنجاز معاملاتهم عبر شبكة من الشركاء والمتخصصين.',
            'contract_button_text'   => 'عقود نظامية متاحة حسب النشاط',
            'contact_phone'          => '966920002164',
            'contact_whatsapp'       => '966504915222',
            'contact_address'        => '',
            'main_office_label'      => 'المكتب الرئيسي',
            'video_file'             => 'videos/0829.mp4',
            'video_poster'           => 'images/logo2.jpg',
        ];

        $now = now();
        foreach ($defaults as $key => $value) {
            DB::table('homepage_settings')->insert([
                'key'        => $key,
                'value'      => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_settings');
    }
};
