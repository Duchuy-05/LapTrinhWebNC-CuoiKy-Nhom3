@extends('layouts.instructor')

@section('title', 'Bài kiểm tra | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Giảng viên</span><h1>Quản lý bài kiểm tra</h1></div>
        <a class="button" href="{{ route('instructor.quizzes.create') }}">Tạo bài kiểm tra</a>
    </section>

    <section class="surface-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Bài kiểm tra</th>
                        <th>Khóa học</th>
                        <th>Bài học liên kết</th>
                        <th>Câu hỏi</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($quizzes as $quiz)
                        <tr>
                            <td>{{ $quiz->title }}</td>
                            <td>{{ $quiz->course->title }}</td>
                            <td>{{ $quiz->lesson?->title ?? 'Không gắn' }}</td>
                            <td>{{ $quiz->questions_count }}</td>
                            <td>{{ $quiz->is_published ? 'Đang hiển thị' : 'Bản nháp' }}</td>
                            <td class="table-actions">
                                <a class="text-link" href="{{ route('instructor.questions.index', $quiz) }}">Câu hỏi</a>
                                <a class="text-link" href="{{ route('instructor.quizzes.edit', $quiz) }}">Sửa</a>
                                <form method="POST" action="{{ route('instructor.quizzes.destroy', $quiz) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button-link danger" type="submit">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">@include('partials.pager', ['paginator' => $quizzes])</div>
    </section>
@endsection