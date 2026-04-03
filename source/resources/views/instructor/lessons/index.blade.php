@extends('layouts.instructor')

@section('title', 'Quản lý bài học | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Giảng viên</span><h1>Quản lý bài học</h1></div>
        <a class="button" href="{{ route('instructor.lessons.create') }}">Thêm bài học</a>
    </section>

    <section class="surface-card filter-card">
        <form method="GET" action="{{ route('instructor.lessons.index') }}" class="filter-grid">
            <div class="form-field">
                <label for="course_id">Khóa học</label>
                <select id="course_id" name="course_id">
                    <option value="">Tất cả khóa học</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected((string) $selectedCourse === (string) $course->id)>{{ $course->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <button class="button" type="submit">Lọc</button>
                <a class="button button-ghost" href="{{ route('instructor.lessons.index') }}">Đặt lại</a>
            </div>
        </form>
    </section>

    <section class="surface-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Bài học</th>
                        <th>Khóa học</th>
                        <th>Thứ tự</th>
                        <th>Học thử</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lessons as $lesson)
                        <tr>
                            <td>{{ $lesson->title }}</td>
                            <td>{{ $lesson->course->title }}</td>
                            <td>{{ $lesson->sort_order }}</td>
                            <td>{{ $lesson->is_preview ? 'Có' : 'Không' }}</td>
                            <td class="table-actions">
                                <a class="text-link" href="{{ route('instructor.lessons.edit', $lesson) }}">Sửa</a>
                                <form method="POST" action="{{ route('instructor.lessons.destroy', $lesson) }}">
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
        <div class="pagination-wrap">@include('partials.pager', ['paginator' => $lessons])</div>
    </section>
@endsection