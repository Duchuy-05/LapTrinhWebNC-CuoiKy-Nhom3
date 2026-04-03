@extends('layouts.app')

@section('title', $lesson->title . ' | StudyHub')

@section('content')
    <section class="section-block compact-top">
        <div class="container learning-layout">
            <aside class="learning-sidebar">
                <div class="surface-card sticky-card">
                    <span class="eyebrow">Lộ trình khóa học</span>
                    <h2>{{ $course->title }}</h2>
                    <div class="lesson-outline">
                        @foreach ($course->lessons as $item)
                            @php
                                $canAccess = $item->is_preview || $enrollment;
                            @endphp
                            <a href="{{ $canAccess ? route('lessons.show', [$course, $item]) : route('courses.show', $course) }}" @class([
                                'outline-item',
                                'active' => $item->id === $lesson->id,
                                'done' => $completedLessonIds->contains($item->id),
                            ])>
                                <span>{{ $item->sort_order }}</span>
                                <strong>{{ $item->title }}</strong>
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>

            <div class="learning-content">
                <div class="surface-card">
                    <div class="meta-row">
                        <span class="pill">Bài {{ $lesson->sort_order }}</span>
                        @if ($lesson->is_preview)
                            <span class="badge badge-success">Học thử</span>
                        @endif
                        @if ($progressEntry?->is_completed)
                            <span class="badge badge-info">Đã hoàn thành</span>
                        @endif
                    </div>

                    <h1>{{ $lesson->title }}</h1>
                    <p class="lead-text">{{ $lesson->excerpt ?: 'Nội dung chi tiết của bài học trong khóa ' . $course->title }}</p>

                    @php
                        $embedUrl = null;
                        if ($lesson->video_url) {
                            if (str_contains($lesson->video_url, 'watch?v=')) {
                                $embedUrl = str_replace('watch?v=', 'embed/', $lesson->video_url);
                            } elseif (str_contains($lesson->video_url, 'youtu.be/')) {
                                $embedUrl = str_replace('youtu.be/', 'youtube.com/embed/', $lesson->video_url);
                            }
                        }
                    @endphp

                    @if ($embedUrl)
                        <div class="video-shell">
                            <iframe src="{{ $embedUrl }}" title="{{ $lesson->title }}" allowfullscreen></iframe>
                        </div>
                    @elseif ($lesson->video_url)
                        <div class="surface-subcard">
                            <p>Video bài giảng:</p>
                            <a class="text-link" href="{{ $lesson->video_url }}" target="_blank" rel="noreferrer">Mở video bài giảng</a>
                        </div>
                    @endif

                    @if ($lesson->document_url)
                        <div class="surface-subcard">
                            <p>Tài liệu đính kèm:</p>
                            <a class="text-link" href="{{ $lesson->document_url }}" target="_blank" rel="noreferrer">Xem tài liệu</a>
                        </div>
                    @endif

                    <div class="prose-block">
                        <p>{!! nl2br(e($lesson->content ?: 'Nội dung bài học đang được cập nhật.')) !!}</p>
                    </div>

                    @if ($relatedQuizzes->isNotEmpty())
                        <div class="surface-card nested-card">
                            <h2>Bài kiểm tra liên quan</h2>
                            <div class="stack-list">
                                @foreach ($relatedQuizzes as $quiz)
                                    <article class="surface-subcard">
                                        <div class="meta-row">
                                            <span class="pill">{{ $quiz->questions_count }} câu hỏi</span>
                                            <span class="muted">Điểm đạt {{ $quiz->passing_score }}%</span>
                                        </div>
                                        <h3>{{ $quiz->title }}</h3>
                                        <p>{{ $quiz->description ?: 'Bài kiểm tra do giảng viên tạo để hỗ trợ ôn tập sau bài học.' }}</p>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="lesson-bottom-bar">
                        <div class="button-row">
                            @if ($previousLesson)
                                <a class="button button-ghost" href="{{ route('lessons.show', [$course, $previousLesson]) }}">Bài trước</a>
                            @endif

                            @if ($nextLesson)
                                <a class="button button-muted" href="{{ route('lessons.show', [$course, $nextLesson]) }}">Bài tiếp theo</a>
                            @endif
                        </div>

                        @if ($enrollment && ! $progressEntry?->is_completed)
                            <form method="POST" action="{{ route('lessons.complete', [$course, $lesson]) }}">
                                @csrf
                                <button class="button" type="submit">Đánh dấu hoàn thành</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection