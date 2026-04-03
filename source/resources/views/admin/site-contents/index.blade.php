@extends('layouts.admin')

@section('title', 'Quản lý nội dung tĩnh | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Quản trị</span><h1>Quản lý nội dung tĩnh</h1></div>
        <a class="button" href="{{ route('admin.site-contents.create') }}">Thêm nội dung</a>
    </section>

    <section class="surface-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Tiêu đề</th>
                        <th>Cập nhật bởi</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($siteContents as $siteContent)
                        <tr>
                            <td>{{ $siteContent->key }}</td>
                            <td>{{ $siteContent->title }}</td>
                            <td>{{ $siteContent->updatedBy?->name ?? 'Hệ thống' }}</td>
                            <td>{{ $siteContent->is_published ? 'Đang hiển thị' : 'Tạm ẩn' }}</td>
                            <td class="table-actions">
                                <a class="text-link" href="{{ route('admin.site-contents.edit', $siteContent) }}">Sửa</a>
                                <form method="POST" action="{{ route('admin.site-contents.destroy', $siteContent) }}">
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