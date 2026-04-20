<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $students        = User::where('role', 'student')->get();
        $publishedCourses = Course::where('status', 'PUBLISHED')->get();

        if ($students->isEmpty() || $publishedCourses->isEmpty()) {
            $this->command->warn('Thiếu student hoặc course. Hãy chạy UserSeeder và CourseSeeder trước.');
            return;
        }

        $count = 0;

        // Cố định: student1 đã mua 3 khóa đầu tiên → dùng để test ngay
        $student1   = User::where('email', 'student1@studyhub.com')->first();
        $firstThree = $publishedCourses->take(3);

        if ($student1) {
            foreach ($firstThree as $course) {
                $effectivePrice = $course->discountPrice ?? $course->price;
                Order::create([
                    'user_id'        => (string) $student1->_id,
                    'course_id'      => $course->courseGroupId,
                    'price_paid'     => $effectivePrice,
                    'payment_method' => $effectivePrice == 0 ? 'FREE' : 'payos',
                    'status'         => 'SUCCESS',
                    'progress'       => rand(10, 90),
                ]);
                $count++;
            }
        }

        // Ngẫu nhiên: mỗi student mua 1-3 khóa bất kỳ
        foreach ($students->take(15) as $student) {
            $bought = $publishedCourses->random(rand(1, 3));

            foreach ($bought as $course) {
                // Tránh tạo trùng
                $exists = Order::where('user_id', (string) $student->_id)
                               ->where('course_id', $course->courseGroupId)
                               ->exists();
                if ($exists) continue;

                $effectivePrice = $course->discountPrice ?? $course->price;
                Order::create([
                    'user_id'        => (string) $student->_id,
                    'course_id'      => $course->courseGroupId,
                    'price_paid'     => $effectivePrice,
                    'payment_method' => $effectivePrice == 0 ? 'FREE' : 'payos',
                    'status'         => 'SUCCESS',
                    'progress'       => rand(0, 100),
                ]);
                $count++;
            }
        }

        $this->command->info("Đã tạo {$count} đơn hàng.");
    }
}
