@extends('layouts.instructor')

@section('title', 'Bảng điều khiển giảng viên | StudyHub')

@section('content')
    <section class="portal-header">
        <div>
            <span class="eyebrow">Cổng giảng viên</span>
            <h1>Tổng quan giảng dạy</h1>
        </div>
    </section>

    <section class="card-grid card-grid-5 compact-cards">
        <article class="info-card stat-card-inline"><strong>{{ $stats['courses'] }}</strong><span>Khóa học phụ trách</span></article>
        <article class="info-card stat-card-inline"><strong>{{ $stats['students'] }}</strong><span>Học viên đang học</span></article>
        <article class="info-card stat-card-inline"><strong>{{ $stats['lessons'] }}</strong><span>Bài học</span></article>
        <article class="info-card stat-card-inline"><strong>{{ $stats['quizzes'] }}</strong><span>Bài kiểm tra</span></article>
        <article class="info-card stat-card-inline"><strong>{{ $stats['posts'] }}</strong><span>Bài đăng</span></article>
    </section>

    <section class="card-grid card-grid-2 admin-panel-grid">
        <article class="surface-card">
            <div class="section-heading section-heading-inline">
                <div><h2>Khóa học của tôi</h2></div>
                <a class="text-link" href="{{ route('instructor.courses.index') }}">Xem tất cả</a>
            </div>
            <div class="simple-list">
                @forelse ($courses as $course)
                    <div class="simple-list__item">
                        <strong>{{ $course->title }}</strong>
                        <span>{{ $course->enrollments_count }} học viên · {{ $course->lessons_count }} bài học · {{ $course->quizzes_count }} bài kiểm tra</span>
                    </div>
                @empty
                    <p>Bạn chưa được phân công khóa học nào.</p>
                @endforelse
            </div>
        </article>
        <article class="surface-card">
            <div class="section-heading section-heading-inline">
                <div><h2>Đăng ký mới gần đây</h2></div>
                <a class="text-link" href="{{ route('instructor.students.index') }}">Xem học viên</a>
            </div>
            <div class="simple-list">
                @forelse ($recentEnrollments as $enrollment)
                    <div class="simple-list__item">
                        <strong>{{ $enrollment->user->name }}</strong>
                        <span>{{ $enrollment->course->title }} · {{ $enrollment->progress_percentage }}% tiến độ</span>
                    </div>
                @empty
                    <p>Chưa có học viên mới tham gia khóa học của bạn.</p>
                @endforelse
            </div>
        </article>
    </section>
@endsection