<?php

namespace Database\Seeders;

use App\Models\Business\BusinessUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(AmrtmServiceSeeder::class);

        $accounts = [
            ['name' => 'مشرف النظام', 'email' => 'supervisor@test.com', 'phone' => '0500000001', 'role' => 'supervisor'],
            ['name' => 'مدير النظام', 'email' => 'admin@test.com',      'phone' => '0500000002', 'role' => 'admin'],
            ['name' => 'عميل تجريبي', 'email' => 'client@test.com',     'phone' => '0500000003', 'role' => 'user'],
        ];

        foreach ($accounts as $acc) {
            BusinessUser::updateOrCreate(
                ['email' => $acc['email']],
                [
                    'name'              => $acc['name'],
                    'phone'             => $acc['phone'],
                    'role'              => $acc['role'],
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_active'         => true,
                ]
            );
        }

        DB::connection('business')->statement('SET FOREIGN_KEY_CHECKS=0');
        DB::connection('business')->statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
