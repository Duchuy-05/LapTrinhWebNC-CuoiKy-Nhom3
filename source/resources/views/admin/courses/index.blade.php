@extends('layouts.admin')

@section('title', 'Quản lý khóa học | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Quản trị</span><h1>Quản lý khóa học</h1></div>
        <a class="button" href="{{ route('admin.courses.create') }}">Thêm khóa học</a>
    </section>

    <section class="surface-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Danh mục</th>
                        <th>Giảng viên</th>
                        <th>Bài học</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($courses as $course)
                        <tr>
                            <td>{{ $course->title }}</td>
                            <td>{{ $course->category->name }}</td>
                            <td>{{ $course->instructor?->name ?? 'Chưa gán' }}</td>
                            <td>{{ $course->lessons_count }}</td>
                            <td>{{ $course->is_published ? 'Đang mở' : 'Bản nháp' }}</td>
                            <td class="table-actions">
                                <a class="text-link" href="{{ route('admin.courses.edit', $course) }}">Sửa</a>
                                <form method="POST" action="{{ route('admin.courses.destroy', $course) }}">
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
        <div class="pagination-wrap">@include('partials.pager', ['paginator' => $courses])</div>
    </section>
@endsection