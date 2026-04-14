<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Sử dụng Factory để tự động điền các trường còn thiếu (như email_verified_at, remember_token)
        // và ghi đè các trường quan trọng bằng thông tin Admin của bạn
        User::factory()->create([
            'name' => 'System Admin', 
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456'),
            'role' => 'admin',
        ]);
    }
}
