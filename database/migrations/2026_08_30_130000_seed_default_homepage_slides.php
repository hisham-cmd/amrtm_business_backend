<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed the two default hero slides so the admin homepage manager and the
     * public homepage show the same slider content out of the box.
     */
    public function up(): void
    {
        if (DB::table('homepage_slides')->exists()) {
            return;
        }

        $now = now();

        DB::table('homepage_slides')->insert([
            [
                'title'      => 'قطاع الأعمال',
                'image_path' => 'images/slide-riyadh-business.jpg',
                'link_url'   => null,
                'is_active'  => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => 'خدمات الجهات',
                'image_path' => 'images/slide-kafd.jpg',
                'link_url'   => null,
                'is_active'  => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations — remove only the seeded default rows.
     */
    public function down(): void
    {
        DB::table('homepage_slides')
            ->whereIn('image_path', [
                'images/slide-riyadh-business.jpg',
                'images/slide-kafd.jpg',
            ])
            ->delete();
    }
};
