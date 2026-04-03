@extends('layouts.instructor')

@section('title', 'Bài đăng giảng viên | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Giảng viên</span><h1>Bài đăng của tôi</h1></div>
        <a class="button" href="{{ route('instructor.posts.create') }}">Đăng bài mới</a>
    </section>

    <section class="surface-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Khóa học</th>
                        <th>Ngày đăng</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($posts as $post)
                        <tr>
                            <td>{{ $post->title }}</td>
                            <td>{{ $post->course->title }}</td>
                            <td>{{ optional($post->published_at)->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $post->is_published ? 'Đang hiển thị' : 'Bản nháp' }}</td>
                            <td class="table-actions">
                                <a class="text-link" href="{{ route('instructor.posts.edit', $post) }}">Sửa</a>
                                <form method="POST" action="{{ route('instructor.posts.destroy', $post) }}">
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
        <div class="pagination-wrap">@include('partials.pager', ['paginator' => $posts])</div>
    </section>
@endsection