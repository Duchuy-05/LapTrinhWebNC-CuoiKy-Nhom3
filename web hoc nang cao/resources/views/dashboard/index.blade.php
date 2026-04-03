@extends('layouts.app')

@section('title', 'Bảng học tập | StudyHub')

@section('content')
    <section class="section-block compact-top">
        <div class="container section-heading">
            <div>
                <span class="eyebrow">Bảng điều khiển học viên</span>
                <h1>Tiến độ học tập của bạn</h1>
            </div>
        </div>

        <div class="container card-grid card-grid-3 compact-cards">
            <article class="info-card stat-card-inline">
                <strong>{{ $enrollments->count() }}</strong>
                <span>Khóa học đang tham gia</span>
            </article>
            <article class="info-card stat-card-inline">
                <strong>{{ $completedCourses }}</strong>
                <span>Khóa học đã hoàn thành</span>
            </article>
            <article class="info-card stat-card-inline">
                <strong>{{ $averageProgress }}%</strong>
                <span>Tiến độ trung bình</span>
            </article>
        </div>
    </section>

    <section class="section-block compact-top">
        <div class="container dashboard-list">
            @forelse ($enrollments as $enrollment)
                @php
                    $firstLesson = $enrollment->course->lessons->sortBy('sort_order')->first();
                @endphp
                <article class="surface-card dashboard-course-card">
                    <div>
                        <div class="meta-row">
                            <span class="pill">{{ $enrollment->course->category->name }}</span>
                            <span class="muted">{{ $enrollment->course->level }}</span>
                        </div>
                        <h2>{{ $enrollment->course->title }}</h2>
                        <p>{{ $enrollment->course->short_description }}</p>
                    </div>
                    <div class="dashboard-course-meta">
                        <div class="progress-head">
                            <span>Tiến độ</span>
                            <strong>{{ $enrollment->progress_percentage }}%</strong>
                        </div>
                        <div class="progress-bar"><span style="width: {{ $enrollment->progress_percentage }}%"></span></div>
                        @if ($firstLesson)
                            <a class="button button-small" href="{{ route('lessons.show', [$enrollment->course, $firstLesson]) }}">Tiếp tục học</a>
                        @endif
                    </div>
                </article>
            @empty
                <article class="empty-state wide-card">
                    <h3>Bạn chưa tham gia khóa học nào</h3>
                    <p>Hãy bắt đầu bằng một khóa học mẫu trong hệ thống để kiểm tra toàn bộ quy trình học tập.</p>
                    <a class="button button-small" href="{{ route('courses.index') }}">Khám phá khóa học</a>
                </article>
            @endforelse
        </div>
    </section>
@endsection