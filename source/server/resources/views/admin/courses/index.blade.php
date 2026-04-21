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
        <table class="table table-bordered table-hover">
            <thead class="bg-light">
                <tr>
                    <th>Ảnh</th>
                    <th>Tên khóa học</th>
                    <th>Người tạo</th> 
                    <th>Giá (VNĐ)</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($courses as $course)
                <tr>
                    <td class="align-middle text-center">
                        @if($course->thumbnail)
                            <img src="{{ asset($course->thumbnail) }}" width="80" class="rounded shadow-sm" alt="Thumbnail">
                        @else
                            <span class="text-muted" style="font-size: 12px;">Chưa có ảnh</span>
                        @endif
                    </td>
                    
                    <td class="align-middle font-weight-bold">{{ $course->title }}</td>
                    
                    @php
                        $authorName = 'Admin';
                        $userId = $course->authorId ?? $course->user_id ?? $course->author_id ?? null;
                        
                        if ($userId) {
                            $user = \App\Models\User::find($userId);
                            if ($user) {
                                $authorName = $user->name;
                            }
                        }
                    @endphp
                    <td class="align-middle text-secondary font-weight-bold">{{ $authorName }}</td>

                    @php
                        $originalPrice = (int) ($course->price ?? 0);
                        $discountPrice = (isset($course->discountPrice) && $course->discountPrice !== '') ? (int) $course->discountPrice : null;
                        
                        $hasDiscount = ($discountPrice !== null);
                        $finalPrice = $hasDiscount ? $discountPrice : $originalPrice;
                    @endphp
                    <td class="align-middle">
                        @if($finalPrice == 0)
                            <span class="badge badge-success px-2 py-1" style="font-size: 13px;">Miễn phí</span>
                        @else
                            <span class="text-primary font-weight-bold" style="font-size: 15px;">{{ number_format($finalPrice) }} đ</span>
                            
                            @if($hasDiscount && $originalPrice > $finalPrice)
                                <br><small class="text-muted" style="text-decoration: line-through;">{{ number_format($originalPrice) }} đ</small>
                            @endif
                        @endif
                    </td>

                    <td class="align-middle">
                        @if(strtoupper($course->status) == 'PUBLISHED')
                            <span class="badge badge-success">Đã xuất bản (LIVE)</span>
                        @elseif(strtoupper($course->status) == 'DRAFT')
                            <span class="badge badge-warning">Bản nháp</span>
                        @else
                            <span class="badge badge-secondary">{{ strtoupper($course->status) }}</span>
                        @endif
                    </td>

                    <td class="align-middle">
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{ route('admin.courses.edit', $course->id) }}">
                                    <i class="fas fa-edit text-primary mr-2"></i> Sửa
                                </a>
                                
                                <div class="dropdown-divider"></div>
                                
                                {{-- LOGIC MỚI: Khóa nút xóa nếu đã xuất bản --}}
                                @if(strtoupper($course->status) == 'PUBLISHED')
                                    <span class="dropdown-item text-muted" title="Không thể xóa khóa học đang xuất bản" style="cursor: not-allowed; background-color: #f8f9fa;">
                                        <i class="fas fa-lock text-secondary mr-2"></i> Xóa (Đã khóa)
                                    </span>
                                @else
                                    <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" class="form-delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="dropdown-item btn-delete">
                                            <i class="fas fa-trash text-danger mr-2"></i> Xóa
                                        </button>
                                    </form>
                                @endif

                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // 1. Logic tìm kiếm nhanh
        const searchInput = document.getElementById('searchInput');
        if(searchInput) {
            searchInput.addEventListener('keyup', function() {
                let filter = this.value.toLowerCase();
                let rows = document.querySelectorAll('table tbody tr');

                rows.forEach(row => {
                    let text = row.innerText.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });
        }

        // 2. Thông báo góc màn hình khi Sửa/Thêm thành công
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Tuyệt vời!',
                text: "{{ session('success') }}",
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: "{{ session('error') }}",
            });
        @endif

        // 3. Hộp thoại xác nhận xóa xịn sò
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                let form = this.closest('form');
                
                Swal.fire({
                    title: 'Bạn có chắc chắn?',
                    text: "Khóa học nháp này và hình ảnh sẽ bị xóa vĩnh viễn!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Vâng, xóa ngay!',
                    cancelButtonText: 'Hủy bỏ'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                })
            });
        });

    });
</script>
@endsection