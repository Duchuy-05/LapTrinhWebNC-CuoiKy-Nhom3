<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Khai báo thư viện Hash ở đây

class DatabaseSeeder extends Seeder
{
    // Bỏ comment dòng dưới nếu bạn muốn tắt các event model khi chạy seed
    // use WithoutModelEvents; 

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Super',
            'email' => 'test@gmail.com', // Bạn có thể đổi lại thành admin@gmail.com nếu cần
            'password' => Hash::make('123456'),
            'role' => 'admin'
        ]);
    }
}