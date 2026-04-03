@extends('layouts.admin')

@section('title', 'Quản lý danh mục | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Quản trị</span><h1>Quản lý danh mục</h1></div>
        <a class="button" href="{{ route('admin.categories.create') }}">Thêm danh mục</a>
    </section>

    <section class="surface-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tên</th>
                        <th>Slug</th>
                        <th>Số khóa học</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->slug }}</td>
                            <td>{{ $category->courses_count }}</td>
                            <td>{{ $category->is_active ? 'Đang hiển thị' : 'Tạm ẩn' }}</td>
                            <td class="table-actions">
                                <a class="text-link" href="{{ route('admin.categories.edit', $category) }}">Sửa</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
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
    </section>
@endsection