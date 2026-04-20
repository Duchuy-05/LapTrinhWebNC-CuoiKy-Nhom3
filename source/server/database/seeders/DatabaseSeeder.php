<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Bắt đầu seed dữ liệu...');

        // Gọi các file Seeder con theo đúng thứ tự
        $this->call([
            UserSeeder::class,
            CourseSeeder::class,
            OrderSeeder::class,
        ]);

        $this->command->info('✅ Seed hoàn tất!');
        $this->command->newLine();
        $this->command->line('📋 Tài khoản test:');
        $this->command->line('  Admin      → admin@studyhub.com     / password');
        $this->command->line('  Giảng viên → lecturer1@studyhub.com / password');
        $this->command->line('  Học viên   → student1@studyhub.com  / password  (đã mua 3 khóa)');
        $this->command->line('  Học viên   → student2@studyhub.com  / password');
    }
}