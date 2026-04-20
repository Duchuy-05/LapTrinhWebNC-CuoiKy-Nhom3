<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // Bỏ comment dòng dưới nếu bạn muốn tắt các event model khi chạy seed
    // use WithoutModelEvents; 

    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'huy',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',

        ]);
    }
}