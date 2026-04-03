@extends('layouts.admin')

@section('title', 'Quản lý thông báo | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Quản trị</span><h1>Quản lý thông báo</h1></div>
        <a class="button" href="{{ route('admin.announcements.create') }}">Thêm thông báo</a>
    </section>

    <section class="surface-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Bắt đầu</th>
                        <th>Kết thúc</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($announcements as $announcement)
                        <tr>
                            <td>{{ $announcement->title }}</td>
                            <td>{{ $announcement->starts_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $announcement->ends_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $announcement->is_active ? 'Đang bật' : 'Đã tắt' }}</td>
                            <td class="table-actions">
                                <a class="text-link" href="{{ route('admin.announcements.edit', $announcement) }}">Sửa</a>
                                <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}">
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
        <div class="pagination-wrap">@include('partials.pager', ['paginator' => $announcements])</div>
    </section>
@endsection