@extends('layouts.admin')

@section('title', 'Quản lý Khóa học')

@section('content')
<div class="card">
    <div class="card-header">
        <a href="{{ route('admin.courses.create') }}" class="btn btn-sm btn-success">
            <i class="fas fa-plus"></i> Thêm khóa học
        </a>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-end align-items-center p-3 pb-2">
    <div class="input-group" style="width: 300px;">
        <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm nhanh...">
        <div class="input-group-append">
            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
        </div>
    </div>
</div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Ảnh</th>
                    <th>Tên khóa học</th>
                    <th>Giá (VNĐ)</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($courses as $course)
                <tr>
                    <td>
                        @if($course->thumbnail)
                            <img src="{{ asset($course->thumbnail) }}" width="80" alt="Thumbnail">
                        @else
                            <span class="text-muted">Chưa có ảnh</span>
                        @endif
                    </td>
                    <td>{{ $course->title }}</td>
                    <td>{{ number_format($course->price) }} đ</td>
                    <td>
                        @if($course->status == 'published')
                            <span class="badge badge-success">Đã xuất bản</span>
                        @else
                            <span class="badge badge-warning">Bản nháp</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{ route('admin.courses.edit', $course->id) }}">
                                    <i class="fas fa-edit text-primary mr-2"></i> Sửa
                                </a>
                                
                                <div class="dropdown-divider"></div>
                                
                                <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khóa học này cùng toàn bộ hình ảnh của nó?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-trash text-danger mr-2"></i> Xóa
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('searchInput');
        
        // Kiểm tra xem trang có ô tìm kiếm không rồi mới chạy
        if(searchInput) {
            searchInput.addEventListener('keyup', function() {
                let filter = this.value.toLowerCase();
                let rows = document.querySelectorAll('table tbody tr');

                rows.forEach(row => {
                    let text = row.innerText.toLowerCase();
                    // Ẩn hoặc hiện dòng nếu có chứa từ khóa
                    if(text.includes(filter)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
@endsection