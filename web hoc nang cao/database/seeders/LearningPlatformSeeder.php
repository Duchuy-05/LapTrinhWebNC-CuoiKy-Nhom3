<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CoursePost;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\SiteContent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LearningPlatformSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@studyhub.local'],
            [
                'name' => 'Quản trị viên StudyHub',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'bio' => 'Quản lý hệ thống học tập trực tuyến và tạo tài khoản giảng viên.',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $instructor = User::query()->updateOrCreate(
            ['email' => 'giangvien@studyhub.local'],
            [
                'name' => 'Giảng viên Demo',
                'password' => Hash::make('password123'),
                'role' => 'instructor',
                'bio' => 'Phụ trách các khóa học thực hành về PHP, Laravel và triển khai website.',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $student = User::query()->updateOrCreate(
            ['email' => 'hocvien@studyhub.local'],
            [
                'name' => 'Học viên Demo',
                'password' => Hash::make('password123'),
                'role' => 'student',
                'bio' => 'Tài khoản học viên dùng để kiểm thử quá trình học tập.',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $studentTwo = User::query()->updateOrCreate(
            ['email' => 'hocvien2@studyhub.local'],
            [
                'name' => 'Trần Minh Anh',
                'password' => Hash::make('password123'),
                'role' => 'student',
                'bio' => 'Học viên đang theo học lộ trình xây dựng web với Laravel.',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $studentThree = User::query()->updateOrCreate(
            ['email' => 'hocvien3@studyhub.local'],
            [
                'name' => 'Lê Khánh Vy',
                'password' => Hash::make('password123'),
                'role' => 'student',
                'bio' => 'Học viên mới tham gia các khóa nền tảng frontend.',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $categories = collect([
            [
                'name' => 'Lập trình web',
                'slug' => 'lap-trinh-web',
                'description' => 'Danh mục tổng hợp các khóa học về xây dựng website hiện đại.',
            ],
            [
                'name' => 'Frontend',
                'slug' => 'frontend',
                'description' => 'HTML, CSS, JavaScript và các kỹ thuật thiết kế giao diện.',
            ],
            [
                'name' => 'Backend PHP',
                'slug' => 'backend-php',
                'description' => 'Xây dựng logic hệ thống, API và quản lý dữ liệu với PHP.',
            ],
        ])->map(function (array $category) {
            return CourseCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_active' => true,
                ]
            );
        })->keyBy('slug');

        $courses = [
            [
                'slug' => 'xay-dung-lms-voi-laravel',
                'title' => 'Xây dựng LMS với Laravel',
                'category_id' => $categories['backend-php']->id,
                'short_description' => 'Học cách xây dựng một website học tập bằng Laravel theo mô hình MVC.',
                'description' => 'Khóa học giúp bạn làm quen với routing, migration, Eloquent, Blade và các module cốt lõi để xây dựng hệ thống học tập trực tuyến.',
                'level' => 'Trung bình',
                'duration_minutes' => 360,
                'price' => 0,
                'is_featured' => true,
                'is_published' => true,
            ],
            [
                'slug' => 'nen-tang-html-css-javascript',
                'title' => 'Nền tảng HTML, CSS và JavaScript',
                'category_id' => $categories['frontend']->id,
                'short_description' => 'Xây nền tảng giao diện cho người mới bắt đầu học lập trình web.',
                'description' => 'Nội dung tập trung vào xây dựng layout, tương tác và responsive để học viên có thể tạo được trang web hoàn chỉnh.',
                'level' => 'Cơ bản',
                'duration_minutes' => 280,
                'price' => 0,
                'is_featured' => true,
                'is_published' => true,
            ],
            [
                'slug' => 'du-an-php-xampp-thuc-te',
                'title' => 'Dự án PHP với XAMPP thực tế',
                'category_id' => $categories['lap-trinh-web']->id,
                'short_description' => 'Tổng hợp cách cấu hình môi trường XAMPP, cơ sở dữ liệu và triển khai nội bộ.',
                'description' => 'Bạn sẽ được hướng dẫn cách chạy dự án PHP với Apache, MySQL, tạo virtual host và tổ chức mã nguồn để dễ bảo trì.',
                'level' => 'Cơ bản',
                'duration_minutes' => 220,
                'price' => 0,
                'is_featured' => false,
                'is_published' => true,
            ],
        ];

        foreach ($courses as $courseData) {
            $course = Course::query()->updateOrCreate(
                ['slug' => $courseData['slug']],
                array_merge($courseData, [
                    'instructor_id' => $instructor->id,
                    'thumbnail' => null,
                ])
            );

            $this->seedLessonsForCourse($course);
            $this->seedPostsForCourse($course, $instructor);
            $this->seedQuizzesForCourse($course, $instructor);
        }

        $laravelCourse = Course::query()->where('slug', 'xay-dung-lms-voi-laravel')->firstOrFail();
        $frontendCourse = Course::query()->where('slug', 'nen-tang-html-css-javascript')->firstOrFail();
        $xamppCourse = Course::query()->where('slug', 'du-an-php-xampp-thuc-te')->firstOrFail();

        $this->enrollStudentWithProgress($student, $laravelCourse, [1]);
        $this->enrollStudentWithProgress($student, $xamppCourse, []);
        $this->enrollStudentWithProgress($studentTwo, $laravelCourse, [1, 2]);
        $this->enrollStudentWithProgress($studentThree, $frontendCourse, [1]);

        Announcement::query()->updateOrCreate(
            ['title' => 'Thông báo mở cổng học tập'],
            [
                'body' => 'Nền tảng đã sẵn sàng với các khóa học mẫu, khu quản trị, khu giảng viên và tính năng theo dõi tiến độ học tập.',
                'cta_label' => 'Xem khóa học',
                'cta_url' => '/courses',
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(2),
                'is_active' => true,
            ]
        );

        SiteContent::query()->updateOrCreate(
            ['key' => 'guidelines'],
            [
                'title' => 'Hướng dẫn học tập',
                'summary' => 'Tổng hợp các bước để học viên bắt đầu nhanh với website.',
                'body' => "1. Chọn đúng vai trò khi đăng nhập hoặc đăng ký tài khoản học viên.\n2. Tìm khóa học phù hợp rồi bấm tham gia.\n3. Học lần lượt các bài giảng, bài đăng hướng dẫn và bài kiểm tra.\n4. Theo dõi tiến độ của bạn tại trang bảng điều khiển.",
                'is_published' => true,
                'updated_by' => $admin->id,
            ]
        );

        SiteContent::query()->updateOrCreate(
            ['key' => 'regulations'],
            [
                'title' => 'Quy định sử dụng',
                'summary' => 'Những lưu ý khi học tập và quản lý nội dung trên hệ thống.',
                'body' => "- Không chia sẻ tài khoản cho người khác.\n- Tôn trọng nội dung khóa học và giảng viên.\n- Tài khoản giảng viên phải do quản trị viên tạo.\n- Quản trị viên có quyền tạm khóa tài khoản nếu phát hiện vi phạm.",
                'is_published' => true,
                'updated_by' => $admin->id,
            ]
        );
    }

    private function seedLessonsForCourse(Course $course): void
    {
        $lessonsByCourse = [
            'xay-dung-lms-voi-laravel' => [
                [
                    'title' => 'Giới thiệu khóa học',
                    'excerpt' => 'Tổng quan mục tiêu, lộ trình và cách học hiệu quả.',
                    'content' => 'Bài học mở đầu giúp học viên nắm rõ lộ trình, cách sử dụng hệ thống và các đầu mục cần hoàn thành trong khóa.',
                    'sort_order' => 1,
                    'duration_minutes' => 20,
                    'is_preview' => true,
                ],
                [
                    'title' => 'Thiết kế cấu trúc dự án Laravel',
                    'excerpt' => 'Phân tích module, migration, model và route.',
                    'content' => 'Bài học trình bày cách phân tách các module khóa học, bài học, tiến độ, phân quyền và tổ chức mã nguồn dễ mở rộng.',
                    'sort_order' => 2,
                    'duration_minutes' => 45,
                    'is_preview' => false,
                ],
                [
                    'title' => 'Xây dựng giao diện và tiến độ học tập',
                    'excerpt' => 'Hoàn thiện dashboard, trang bài học và cập nhật progress.',
                    'content' => 'Học viên sẽ học cách xây trang danh sách khóa học, trang bài học và đồng bộ tiến độ hoàn thành theo từng bài.',
                    'sort_order' => 3,
                    'duration_minutes' => 60,
                    'is_preview' => false,
                ],
            ],
            'nen-tang-html-css-javascript' => [
                [
                    'title' => 'Làm quen với HTML ngữ nghĩa',
                    'excerpt' => 'Xây bố cục chuẩn và dễ bảo trì.',
                    'content' => 'Nội dung giúp học viên hiểu về thẻ ngữ nghĩa, cấu trúc trang và cách tổ chức nội dung rõ ràng.',
                    'sort_order' => 1,
                    'duration_minutes' => 25,
                    'is_preview' => true,
                ],
                [
                    'title' => 'Thiết kế giao diện hiện đại với CSS',
                    'excerpt' => 'Biến thể màu, khoảng trắng và responsive layout.',
                    'content' => 'Bài học tập trung vào grid, flexbox, typography và cách xây giao diện hiện đại trên cả desktop lẫn mobile.',
                    'sort_order' => 2,
                    'duration_minutes' => 50,
                    'is_preview' => false,
                ],
                [
                    'title' => 'Tương tác bằng JavaScript',
                    'excerpt' => 'Điều khiển trạng thái, form và thao tác DOM.',
                    'content' => 'Học viên thực hành hiển thị dữ liệu, xử lý sự kiện và cải thiện trải nghiệm sử dụng bằng JavaScript thuần.',
                    'sort_order' => 3,
                    'duration_minutes' => 55,
                    'is_preview' => false,
                ],
            ],
            'du-an-php-xampp-thuc-te' => [
                [
                    'title' => 'Chuẩn bị môi trường XAMPP',
                    'excerpt' => 'Khởi động Apache, MySQL và cấu hình dự án.',
                    'content' => 'Bài học hướng dẫn cài đặt, cấu hình cổng và cách tổ chức thư mục dự án PHP trong môi trường XAMPP.',
                    'sort_order' => 1,
                    'duration_minutes' => 25,
                    'is_preview' => true,
                ],
                [
                    'title' => 'Kết nối cơ sở dữ liệu MySQL',
                    'excerpt' => 'Tạo database, import dữ liệu và kiểm tra kết nối.',
                    'content' => 'Bạn sẽ thực hành tạo database, cấu hình tệp môi trường và xác nhận ứng dụng kết nối đúng với MySQL.',
                    'sort_order' => 2,
                    'duration_minutes' => 40,
                    'is_preview' => false,
                ],
                [
                    'title' => 'Tối ưu cấu trúc triển khai nội bộ',
                    'excerpt' => 'Virtual host, phân quyền thư mục và sao lưu.',
                    'content' => 'Bài học chia sẻ cách tạo virtual host, đặt thư mục lưu trữ hợp lý và chuẩn bị phương án sao lưu cho dự án.',
                    'sort_order' => 3,
                    'duration_minutes' => 45,
                    'is_preview' => false,
                ],
            ],
        ];

        foreach ($lessonsByCourse[$course->slug] ?? [] as $lessonData) {
            Lesson::query()->updateOrCreate(
                [
                    'course_id' => $course->id,
                    'sort_order' => $lessonData['sort_order'],
                ],
                [
                    'title' => $lessonData['title'],
                    'slug' => $course->slug.'-'.Str::slug($lessonData['title']),
                    'excerpt' => $lessonData['excerpt'],
                    'content' => $lessonData['content'],
                    'video_url' => null,
                    'document_url' => null,
                    'duration_minutes' => $lessonData['duration_minutes'],
                    'is_preview' => $lessonData['is_preview'],
                ]
            );
        }
    }

    private function seedPostsForCourse(Course $course, User $instructor): void
    {
        $postsByCourse = [
            'xay-dung-lms-voi-laravel' => [
                [
                    'title' => 'Lộ trình hoàn thành dự án LMS trong 4 tuần',
                    'excerpt' => 'Gợi ý cách chia nhỏ tiến độ để theo học đều đặn.',
                    'body' => 'Bài đăng này giúp học viên chia khóa học thành từng chặng nhỏ, mỗi tuần hoàn thành một nhóm tính năng như giao diện, quản lý bài học và kiểm tra tiến độ.',
                ],
                [
                    'title' => 'Cách chuẩn bị dữ liệu trước khi xây dựng chức năng khóa học',
                    'excerpt' => 'Danh sách bảng dữ liệu và quan hệ nên có ngay từ đầu.',
                    'body' => 'Giảng viên giới thiệu các bảng dữ liệu quan trọng như người dùng, khóa học, bài học, ghi danh, tiến độ học tập và quyền truy cập để tránh phải sửa cấu trúc nhiều lần.',
                ],
            ],
            'nen-tang-html-css-javascript' => [
                [
                    'title' => 'Checklist làm giao diện hiện đại cho người mới',
                    'excerpt' => 'Màu sắc, khoảng trắng, font chữ và bố cục nên ưu tiên.',
                    'body' => 'Bài đăng tổng hợp các nguyên tắc giúp giao diện trông hiện đại hơn, từ việc chọn typography, hệ màu đến cách tạo nhịp điệu khoảng trắng.',
                ],
            ],
            'du-an-php-xampp-thuc-te' => [
                [
                    'title' => 'Những lỗi thường gặp khi chạy dự án PHP trên XAMPP',
                    'excerpt' => 'Tổng hợp cách xử lý lỗi cổng, quyền ghi và cấu hình MySQL.',
                    'body' => 'Giảng viên chia sẻ cách kiểm tra cổng Apache, sửa xung đột dịch vụ, cấp quyền thư mục lưu trữ và xử lý lỗi kết nối MySQL khi chạy dự án cục bộ.',
                ],
            ],
        ];

        foreach ($postsByCourse[$course->slug] ?? [] as $index => $postData) {
            CoursePost::query()->updateOrCreate(
                ['slug' => $course->slug.'-'.Str::slug($postData['title'])],
                [
                    'course_id' => $course->id,
                    'author_id' => $instructor->id,
                    'title' => $postData['title'],
                    'excerpt' => $postData['excerpt'],
                    'body' => $postData['body'],
                    'is_published' => true,
                    'published_at' => now()->subDays($index + 1),
                ]
            );
        }
    }

    private function seedQuizzesForCourse(Course $course, User $instructor): void
    {
        $lessons = $course->lessons()->orderBy('sort_order')->get()->keyBy('sort_order');

        $quizzesByCourse = [
            'xay-dung-lms-voi-laravel' => [
                [
                    'title' => 'Kiểm tra kiến thức nền về kiến trúc LMS',
                    'description' => 'Ôn lại các thành phần quan trọng của một website học tập.',
                    'lesson_order' => 2,
                    'passing_score' => 70,
                    'time_limit_minutes' => 15,
                    'questions' => [
                        [
                            'question' => 'Trong một hệ thống LMS, bảng nào thường dùng để lưu việc học viên tham gia khóa học?',
                            'option_a' => 'course_categories',
                            'option_b' => 'enrollments',
                            'option_c' => 'site_contents',
                            'option_d' => 'announcements',
                            'correct_option' => 'B',
                            'explanation' => 'Bảng enrollments giúp lưu quan hệ giữa học viên và khóa học mà họ đã tham gia.',
                        ],
                        [
                            'question' => 'Blade trong Laravel thường được dùng cho mục đích nào?',
                            'option_a' => 'Tạo migration',
                            'option_b' => 'Quản lý queue',
                            'option_c' => 'Xây dựng giao diện hiển thị',
                            'option_d' => 'Biên dịch CSS',
                            'correct_option' => 'C',
                            'explanation' => 'Blade là template engine của Laravel, hỗ trợ xây dựng giao diện động.',
                        ],
                    ],
                ],
                [
                    'title' => 'Bài test theo dõi tiến độ học tập',
                    'description' => 'Đánh giá hiểu biết về cập nhật tiến độ và dashboard.',
                    'lesson_order' => 3,
                    'passing_score' => 75,
                    'time_limit_minutes' => 20,
                    'questions' => [
                        [
                            'question' => 'Tính năng nào giúp học viên thấy được mình đã học đến đâu?',
                            'option_a' => 'Bảng điều khiển tiến độ',
                            'option_b' => 'Trang quản trị hệ thống',
                            'option_c' => 'Trang đăng nhập',
                            'option_d' => 'Tệp cấu hình môi trường',
                            'correct_option' => 'A',
                            'explanation' => 'Bảng điều khiển tiến độ tổng hợp trạng thái học tập theo từng khóa và từng bài.',
                        ],
                    ],
                ],
            ],
            'nen-tang-html-css-javascript' => [
                [
                    'title' => 'Bài test giao diện responsive',
                    'description' => 'Kiểm tra kiến thức về bố cục linh hoạt và tương thích thiết bị.',
                    'lesson_order' => 2,
                    'passing_score' => 70,
                    'time_limit_minutes' => 15,
                    'questions' => [
                        [
                            'question' => 'Thuộc tính CSS nào thường dùng để tạo bố cục một chiều linh hoạt?',
                            'option_a' => 'display: flex',
                            'option_b' => 'position: fixed',
                            'option_c' => 'overflow: hidden',
                            'option_d' => 'font-style: italic',
                            'correct_option' => 'A',
                            'explanation' => 'Flexbox hỗ trợ bố cục một chiều rất hiệu quả trên cả desktop và mobile.',
                        ],
                    ],
                ],
            ],
            'du-an-php-xampp-thuc-te' => [
                [
                    'title' => 'Bài test cấu hình XAMPP và MySQL',
                    'description' => 'Đánh giá khả năng chuẩn bị môi trường chạy dự án PHP.',
                    'lesson_order' => 2,
                    'passing_score' => 70,
                    'time_limit_minutes' => 10,
                    'questions' => [
                        [
                            'question' => 'Thành phần nào trong XAMPP thường được dùng để chạy cơ sở dữ liệu cho dự án PHP?',
                            'option_a' => 'Apache',
                            'option_b' => 'FileZilla',
                            'option_c' => 'MySQL',
                            'option_d' => 'Mercury',
                            'correct_option' => 'C',
                            'explanation' => 'MySQL là dịch vụ cơ sở dữ liệu phổ biến đi kèm trong XAMPP.',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($quizzesByCourse[$course->slug] ?? [] as $quizData) {
            $quiz = Quiz::query()->updateOrCreate(
                ['slug' => $course->slug.'-'.Str::slug($quizData['title'])],
                [
                    'course_id' => $course->id,
                    'lesson_id' => $lessons[$quizData['lesson_order']]->id ?? null,
                    'created_by' => $instructor->id,
                    'title' => $quizData['title'],
                    'description' => $quizData['description'],
                    'passing_score' => $quizData['passing_score'],
                    'time_limit_minutes' => $quizData['time_limit_minutes'],
                    'is_published' => true,
                ]
            );

            foreach ($quizData['questions'] as $questionIndex => $questionData) {
                QuizQuestion::query()->updateOrCreate(
                    [
                        'quiz_id' => $quiz->id,
                        'sort_order' => $questionIndex + 1,
                    ],
                    [
                        'question' => $questionData['question'],
                        'option_a' => $questionData['option_a'],
                        'option_b' => $questionData['option_b'],
                        'option_c' => $questionData['option_c'],
                        'option_d' => $questionData['option_d'],
                        'correct_option' => $questionData['correct_option'],
                        'explanation' => $questionData['explanation'],
                    ]
                );
            }
        }
    }

    private function enrollStudentWithProgress(User $student, Course $course, array $completedLessonOrders): void
    {
        $enrollment = Enrollment::query()->updateOrCreate(
            [
                'user_id' => $student->id,
                'course_id' => $course->id,
            ],
            [
                'enrolled_at' => now(),
                'last_accessed_at' => now(),
                'progress_percentage' => 0,
            ]
        );

        $lessons = $course->lessons()->orderBy('sort_order')->get()->keyBy('sort_order');

        foreach ($completedLessonOrders as $lessonOrder) {
            if (! isset($lessons[$lessonOrder])) {
                continue;
            }

            LessonProgress::query()->updateOrCreate(
                [
                    'user_id' => $student->id,
                    'lesson_id' => $lessons[$lessonOrder]->id,
                ],
                [
                    'is_completed' => true,
                    'completed_at' => now()->subDays(max(1, count($completedLessonOrders) - $lessonOrder + 1)),
                ]
            );
        }

        $enrollment->refresh();
        $enrollment->load('course.lessons');
        $enrollment->syncProgress();
    }
}