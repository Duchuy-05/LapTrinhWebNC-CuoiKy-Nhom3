@extends('layouts.admin')

@section('title', 'Quản lý bài học | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Quản trị</span><h1>Quản lý bài học</h1></div>
        <a class="button" href="{{ route('admin.lessons.create') }}">Thêm bài học</a>
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
                                <a class="text-link" href="{{ route('admin.lessons.edit', $lesson) }}">Sửa</a>
                                <form method="POST" action="{{ route('admin.lessons.destroy', $lesson) }}">
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