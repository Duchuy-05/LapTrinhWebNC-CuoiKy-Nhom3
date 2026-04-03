<article class="course-card">
    <div class="course-card__media">
        @if ($course->thumbnail)
            <img src="{{ $course->thumbnail }}" alt="{{ $course->title }}">
        @else
            <div class="course-card__placeholder">
                <span>{{ strtoupper(substr($course->title, 0, 1)) }}</span>
            </div>
        @endif
    </div>
    <div class="course-card__body">
        <div class="meta-row">
            <span class="pill">{{ $course->category->name }}</span>
            <span class="muted">{{ $course->formattedPrice() }}</span>
        </div>
        <h3>{{ $course->title }}</h3>
        <p>{{ \Illuminate\Support\Str::limit($course->short_description, 120) }}</p>
        <div class="course-meta-grid">
            <span>{{ $course->lessons_count ?? $course->lessons->count() }} bài học</span>
            <span>{{ $course->duration_minutes }} phút</span>
            <span>{{ $course->enrollments_count ?? $course->enrollments->count() }} học viên</span>
            <span>{{ $course->quizzes_count ?? $course->quizzes->count() ?? 0 }} bài kiểm tra</span>
        </div>
        <a class="button button-small" href="{{ route('courses.show', $course) }}">Xem chi tiết</a>
    </div>
</article>