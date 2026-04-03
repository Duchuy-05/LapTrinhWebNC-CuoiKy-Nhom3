@extends('layouts.app')

@section('title', $course->title . ' | StudyHub')

@section('content')
    <section class="section-block compact-top">
        <div class="container detail-grid detail-grid-wide">
            <div class="detail-main">
                <div class="meta-row">
                    <span class="pill">{{ $course->category->name }}</span>
                    <span class="muted">{{ $course->formattedPrice() }}</span>
                </div>
                <h1>{{ $course->title }}</h1>
                <p class="lead-text">{{ $course->short_description }}</p>

                <div class="stats-inline stats-inline-wrap">
                    <span>{{ $course->lessons->count() }} bài học</span>
                    <span>{{ $course->duration_minutes }} phút</span>
                    <span>{{ $course->enrollments_count }} học viên</span>
                    <span>Trình độ {{ $course->level }}</span>
                    <span>{{ $course->quizzes->count() }} bài kiểm tra</span>
                </div>

                <div class="surface-card prose-block">
                    <h2>Mô tả khóa học</h2>
                    <p>{!! nl2br(e($course->description)) !!}</p>
                </div>

                <div class="surface-card">
                    <h2>Nội dung bài học</h2>
                    <div class="lesson-list">
                        @foreach ($course->lessons as $lesson)
                            @php
                                $isCompleted = $completedLessonIds->contains($lesson->id);
                                $canAccess = $lesson->is_preview || $enrollment;
                            @endphp
                            <div class="lesson-row">
                                <div>
                                    <strong>{{ $lesson->sort_order }}. {{ $lesson->title }}</strong>
                                    <p>{{ $lesson->excerpt ?: 'Bài học trong khóa ' . $course->title }}</p>
                                </div>
                                <div class="lesson-row__actions">
                                    @if ($lesson->is_preview)
                                        <span class="badge badge-success">Học thử</span>
                                    @endif
                                    @if ($isCompleted)
                                        <span class="badge badge-info">Đã hoàn thành</span>
                                    @endif
                                    @if ($canAccess)
                                        <a class="button button-small button-ghost" href="{{ route('lessons.show', [$course, $lesson]) }}">Mở bài học</a>
                                    @else
                                        <span class="badge badge-muted">Cần tham gia khóa học</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="surface-card">
                    <h2>Bài đăng từ giảng viên</h2>
                    <div class="stack-list">
                        @forelse ($course->posts as $post)
                            <article class="surface-subcard">
                                <div class="meta-row">
                                    <span class="pill">{{ $post->author->name }}</span>
                                    <span class="muted">{{ optional($post->published_at)->format('d/m/Y H:i') }}</span>
                                </div>
                                <h3>{{ $post->title }}</h3>
                                <p>{{ $post->excerpt ?: $post->body }}</p>
                            </article>
                        @empty
                            <article class="surface-subcard">
                                <h3>Chưa có bài đăng nào</h3>
                                <p>Giảng viên sẽ cập nhật thông báo, ghi chú học tập hoặc hướng dẫn bổ sung cho khóa học tại đây.</p>
                            </article>
                        @endforelse
                    </div>
                </div>

                <div class="surface-card">
                    <h2>Bài kiểm tra trong khóa học</h2>
                    <div class="stack-list">
                        @forelse ($course->quizzes as $quiz)
                            <article class="surface-subcard">
                                <div class="meta-row">
                                    <span class="pill">{{ $quiz->questions_count }} câu hỏi</span>
                                    <span class="muted">Điểm đạt: {{ $quiz->passing_score }}%</span>
                                </div>
                                <h3>{{ $quiz->title }}</h3>
                                <p>{{ $quiz->description ?: 'Bài kiểm tra này do giảng viên quản lý và có thể gắn với một bài học cụ thể.' }}</p>
                                @if ($quiz->lesson)
                                    <small>Liên kết với bài học: {{ $quiz->lesson->title }}</small>
                                @endif
                            </article>
                        @empty
                            <article class="surface-subcard">
                                <h3>Chưa có bài kiểm tra nào</h3>
                                <p>Giảng viên có thể thêm bài kiểm tra và câu hỏi từ cổng giảng viên để đánh giá mức độ tiếp thu của học viên.</p>
                            </article>
                        @endforelse
                    </div>
                </div>
            </div>

            <aside class="detail-sidebar">
                <div class="surface-card sticky-card highlight-card">
                    <h2>Thông tin nhanh</h2>
                    <p><strong>Giảng viên:</strong> {{ $course->instructor?->name ?? 'Đang cập nhật' }}</p>
                    <p><strong>Danh mục:</strong> {{ $course->category->name }}</p>
                    <p><strong>Trạng thái:</strong> {{ $course->is_published ? 'Đang mở' : 'Tạm ẩn' }}</p>

                    @if ($enrollment)
                        <div class="progress-block">
                            <div class="progress-head">
                                <span>Tiến độ của bạn</span>
                                <strong>{{ $enrollment->progress_percentage }}%</strong>
                            </div>
                            <div class="progress-bar"><span style="width: {{ $enrollment->progress_percentage }}%"></span></div>
                        </div>
                    @endif

                    <div class="stack-actions">
                        @auth
                            @if ($enrollment && $startLesson)
                                <a class="button button-block" href="{{ route('lessons.show', [$course, $startLesson]) }}">Tiếp tục học</a>
                            @elseif (! $enrollment)
                                <form method="POST" action="{{ route('courses.enroll', $course) }}">
                                    @csrf
                                    <button class="button button-block" type="submit">Tham gia khóa học</button>
                                </form>
                            @endif
                        @else
                            <a class="button button-block" href="{{ route('login', ['role' => 'student']) }}">Đăng nhập để tham gia</a>
                        @endauth

                        @if (! $enrollment && $startLesson && $startLesson->is_preview)
                            <a class="button button-muted button-block" href="{{ route('lessons.show', [$course, $startLesson]) }}">Xem bài học thử</a>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <section class="section-block compact-top">
        <div class="container section-heading">
            <div>
                <span class="eyebrow">Khóa học liên quan</span>
                <h2>Có thể bạn cũng quan tâm</h2>
            </div>
        </div>
        <div class="container card-grid card-grid-3">
            @forelse ($relatedCourses as $relatedCourse)
                @include('partials.course-card', ['course' => $relatedCourse])
            @empty
                <article class="info-card">
                    <h3>Chưa có khóa học liên quan</h3>
                    <p>Quản trị viên có thể bổ sung thêm nội dung trong cùng danh mục tại khu quản trị.</p>
                </article>
            @endforelse
        </div>
    </section>
@endsection