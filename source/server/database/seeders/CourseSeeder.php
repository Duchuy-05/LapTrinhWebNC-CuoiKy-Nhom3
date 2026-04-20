<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $lecturers = User::where('role', 'lecturer')->get();

        if ($lecturers->isEmpty()) {
            $this->command->warn('Không có giảng viên nào! Hãy chạy UserSeeder trước.');
            return;
        }

        $courseCatalog = [
            [
                'title'       => 'Lập trình Web với ReactJS từ A đến Z',
                'description' => 'Học ReactJS từ cơ bản đến nâng cao: hooks, context, redux, và xây dựng dự án thực tế.',
                'tags'        => 'reactjs, javascript, frontend, web',
                'price'       => 599000,
                'discountPrice' => 299000,
            ],
            [
                'title'       => 'Laravel & PHP - Xây dựng REST API',
                'description' => 'Xây dựng REST API chuyên nghiệp với Laravel, Sanctum, MongoDB và các kỹ thuật bảo mật.',
                'tags'        => 'laravel, php, backend, api, mongodb',
                'price'       => 499000,
                'discountPrice' => null,
            ],
            [
                'title'       => 'Thiết kế UI/UX với Figma',
                'description' => 'Nắm vững Figma để thiết kế giao diện chuyên nghiệp: wireframe, prototype, design system.',
                'tags'        => 'figma, ui, ux, design',
                'price'       => 399000,
                'discountPrice' => 199000,
            ],
            [
                'title'       => 'Python cho Khoa học dữ liệu',
                'description' => 'Pandas, NumPy, Matplotlib và Machine Learning cơ bản. Làm việc với dataset thực tế.',
                'tags'        => 'python, data science, machine learning, pandas',
                'price'       => 699000,
                'discountPrice' => null,
            ],
            [
                'title'       => 'Docker & CI/CD cho Developer',
                'description' => 'Container hóa ứng dụng với Docker, Docker Compose và tự động hóa deploy với GitHub Actions.',
                'tags'        => 'docker, devops, cicd, linux',
                'price'       => 449000,
                'discountPrice' => 349000,
            ],
            [
                'title'       => 'Flutter - Lập trình App di động đa nền tảng',
                'description' => 'Xây dựng ứng dụng iOS và Android chỉ với một codebase Flutter/Dart.',
                'tags'        => 'flutter, dart, mobile, ios, android',
                'price'       => 0,
                'discountPrice' => null,
            ],
            [
                'title'       => 'SQL & Thiết kế cơ sở dữ liệu',
                'description' => 'Từ SQL cơ bản đến tối ưu query, indexing, và thiết kế schema cho hệ thống lớn.',
                'tags'        => 'sql, database, mysql, postgresql',
                'price'       => 349000,
                'discountPrice' => null,
            ],
            [
                'title'       => 'Node.js & Express - Backend hiệu năng cao',
                'description' => 'Xây dựng server Node.js với Express, JWT auth, WebSocket, và deploy lên cloud.',
                'tags'        => 'nodejs, express, javascript, backend',
                'price'       => 499000,
                'discountPrice' => 199000,
            ],
        ];

        foreach ($courseCatalog as $i => $data) {
            $lecturer      = $lecturers[$i % $lecturers->count()];
            $courseGroupId = (string) Str::uuid();

            // Cấu trúc bài học mẫu
            $courseData = [
                [
                    'id'    => (string) Str::uuid(),
                    'title' => 'Chương 1: Giới thiệu & Cài đặt môi trường',
                    'items' => [
                        ['id' => (string) Str::uuid(), 'title' => 'Giới thiệu khóa học',    'type' => 'lesson', 'isPreview' => true],
                        ['id' => (string) Str::uuid(), 'title' => 'Cài đặt môi trường',     'type' => 'lesson', 'isPreview' => true],
                        ['id' => (string) Str::uuid(), 'title' => 'Kiểm tra chương 1',      'type' => 'quiz',   'isPreview' => false],
                    ],
                ],
                [
                    'id'    => (string) Str::uuid(),
                    'title' => 'Chương 2: Kiến thức nền tảng',
                    'items' => [
                        ['id' => (string) Str::uuid(), 'title' => 'Bài học 2.1', 'type' => 'lesson', 'isPreview' => false],
                        ['id' => (string) Str::uuid(), 'title' => 'Bài học 2.2', 'type' => 'lesson', 'isPreview' => false],
                        ['id' => (string) Str::uuid(), 'title' => 'Bài học 2.3', 'type' => 'lesson', 'isPreview' => false],
                    ],
                ],
                [
                    'id'    => (string) Str::uuid(),
                    'title' => 'Chương 3: Dự án thực tế',
                    'items' => [
                        ['id' => (string) Str::uuid(), 'title' => 'Lên kế hoạch dự án',    'type' => 'lesson', 'isPreview' => false],
                        ['id' => (string) Str::uuid(), 'title' => 'Xây dựng chức năng',    'type' => 'lesson', 'isPreview' => false],
                        ['id' => (string) Str::uuid(), 'title' => 'Kiểm tra tổng kết',     'type' => 'quiz',   'isPreview' => false],
                    ],
                ],
            ];

            Course::create([
                'courseGroupId' => $courseGroupId,
                'status'        => 'PUBLISHED',
                'version'       => 1,
                'title'         => $data['title'],
                'description'   => $data['description'],
                'thumbnail'     => "https://picsum.photos/seed/{$courseGroupId}/600/400",
                'tags'          => $data['tags'],
                'authorId'      => (string) $lecturer->_id,
                'price'         => $data['price'],
                'discountPrice' => $data['discountPrice'],
                'courseData'    => $courseData,
                'blocks'        => [],
                'student_count' => rand(10, 800),
                'rating_count'  => rand(5, 200),
                'rating_score'  => round(rand(35, 50) / 10, 1), // 3.5 → 5.0
            ]);
        }

        $this->command->info('Đã tạo ' . count($courseCatalog) . ' khóa học.');
    }
}
