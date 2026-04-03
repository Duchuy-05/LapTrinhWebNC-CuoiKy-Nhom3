@extends('layouts.instructor')

@section('title', 'Khóa học của tôi | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Giảng viên</span><h1>Khóa học của tôi</h1></div>
    </section>

    <section class="card-grid card-grid-2">
        @forelse ($courses as $course)
            <article class="surface-card course-manage-card">
                <div class="meta-row">
                    <span class="pill">{{ $course->category->name }}</span>
                    <span class="muted">{{ $course->is_published ? 'Đang mở' : 'Bản nháp' }}</span>
                </div>
                <h2>{{ $course->title }}</h2>
                <p>{{ $course->short_description }}</p>
                <div class="course-meta-grid">
                    <span>{{ $course->lessons_count }} bài học</span>
                    <span>{{ $course->quizzes_count }} bài kiểm tra</span>
                    <span>{{ $course->posts_count }} bài đăng</span>
                    <span>{{ $course->enrollments_count }} học viên</span>
                </div>
                <div class="button-row">
                    <a class="button button-small" href="{{ route('instructor.courses.edit', $course) }}">Chỉnh sửa khóa học</a>
                    <a class="button button-small button-ghost" href="{{ route('courses.show', $course) }}">Xem ngoài website</a>
                </div>
            </article>
        @empty
            <article class="empty-state wide-card">
                <h3>Chưa có khóa học được giao</h3>
                <p>Quản trị viên cần tạo khóa học và phân công bạn làm giảng viên trước khi bạn có thể quản lý nội dung.</p>
            </article>
        @endforelse
    </section>

    <div class="pagination-wrap">@include('partials.pager', ['paginator' => $courses])</div>
@endsection