@extends('layouts.instructor')

@section('title', 'Học viên của tôi | StudyHub')

@section('content')
    <section class="portal-header">
        <div>
            <span class="eyebrow">Giảng viên</span>
            <h1>Học viên đang học khóa của bạn</h1>
        </div>
        <div class="portal-header__meta">Tổng số học viên: {{ $studentCount }}</div>
    </section>

    <section class="surface-card filter-card">
        <form method="GET" action="{{ route('instructor.students.index') }}" class="filter-grid">
            <div class="form-field">
                <label for="course_id">Lọc theo khóa học</label>
                <select id="course_id" name="course_id">
                    <option value="">Tất cả khóa học</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected((string) $selectedCourse === (string) $course->id)>{{ $course->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <button class="button" type="submit">Áp dụng</button>
                <a class="button button-ghost" href="{{ route('instructor.students.index') }}">Đặt lại</a>
            </div>
        </form>
    </section>

    <section class="surface-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Học viên</th>
                        <th>Khóa học</th>
                        <th>Ngày tham gia</th>
                        <th>Tiến độ</th>
                        <th>Truy cập gần nhất</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($enrollments as $enrollment)
                        <tr>
                            <td>
                                <strong>{{ $enrollment->user->name }}</strong>
                                <div class="muted">{{ $enrollment->user->email }}</div>
                            </td>
                            <td>{{ $enrollment->course->title }}</td>
                            <td>{{ optional($enrollment->enrolled_at)->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $enrollment->progress_percentage }}%</td>
                            <td>{{ optional($enrollment->last_accessed_at)->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Chưa có học viên nào trong phạm vi đang lọc.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">@include('partials.pager', ['paginator' => $enrollments])</div>
    </section>
@endsection