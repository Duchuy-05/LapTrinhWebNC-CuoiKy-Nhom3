@extends('layouts.instructor')

@section('title', 'Câu hỏi bài kiểm tra | StudyHub')

@section('content')
    <section class="portal-header">
        <div>
            <span class="eyebrow">Giảng viên</span>
            <h1>Quản lý câu hỏi: {{ $quiz->title }}</h1>
        </div>
        <a class="button" href="{{ route('instructor.questions.create', $quiz) }}">Thêm câu hỏi</a>
    </section>

    <section class="surface-card">
        <div class="stack-list">
            @forelse ($questions as $question)
                <article class="surface-subcard">
                    <div class="meta-row">
                        <span class="pill">Câu {{ $question->sort_order }}</span>
                        <span class="muted">Đáp án đúng: {{ $question->correct_option }}</span>
                    </div>
                    <h3>{{ $question->question }}</h3>
                    <div class="question-grid">
                        <span>A. {{ $question->option_a }}</span>
                        <span>B. {{ $question->option_b }}</span>
                        <span>C. {{ $question->option_c }}</span>
                        <span>D. {{ $question->option_d }}</span>
                    </div>
                    @if ($question->explanation)
                        <p>{{ $question->explanation }}</p>
                    @endif
                    <div class="table-actions">
                        <a class="text-link" href="{{ route('instructor.questions.edit', [$quiz, $question]) }}">Sửa</a>
                        <form method="POST" action="{{ route('instructor.questions.destroy', [$quiz, $question]) }}">
                            @csrf
                            @method('DELETE')
                            <button class="button-link danger" type="submit">Xóa</button>
                        </form>
                    </div>
                </article>
            @empty
                <article class="empty-state wide-card">
                    <h3>Chưa có câu hỏi nào</h3>
                    <p>Hãy thêm câu hỏi đầu tiên để hoàn thiện bài kiểm tra này.</p>
                </article>
            @endforelse
        </div>
    </section>
@endsection