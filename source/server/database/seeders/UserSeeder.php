<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Tài khoản cố định để test đăng nhập nhanh ──────────────────────────

        // Admin
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@studyhub.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // 3 Giảng viên cố định
        User::create([
            'name'     => 'Nguyễn Văn Giảng',
            'email'    => 'lecturer1@studyhub.com',
            'password' => Hash::make('password'),
            'role'     => 'lecturer',
        ]);
        User::create([
            'name'     => 'Trần Thị Lan',
            'email'    => 'lecturer2@studyhub.com',
            'password' => Hash::make('password'),
            'role'     => 'lecturer',
        ]);
        User::create([
            'name'     => 'Lê Minh Tuấn',
            'email'    => 'lecturer3@studyhub.com',
            'password' => Hash::make('password'),
            'role'     => 'lecturer',
        ]);

        // 2 Học viên cố định
        User::create([
            'name'     => 'Học Viên A',
            'email'    => 'student1@studyhub.com',
            'password' => Hash::make('password'),
            'role'     => 'student',
        ]);
        User::create([
            'name'     => 'Học Viên B',
            'email'    => 'student2@studyhub.com',
            'password' => Hash::make('password'),
            'role'     => 'student',
        ]);

        // ── Dữ liệu ngẫu nhiên ─────────────────────────────────────────────────
        User::factory(5)->lecturer()->create();   // 5 giảng viên random
        User::factory(20)->create();              // 20 học viên random
    }
}
