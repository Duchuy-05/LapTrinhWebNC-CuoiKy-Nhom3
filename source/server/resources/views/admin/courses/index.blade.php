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
                    <th>Người tạo</th> <th>Giá (VNĐ)</th>
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
                        // Dò tìm ID người tạo (ưu tiên authorId)
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
                        // Lấy giá khuyến mãi, nếu là chuỗi rỗng thì cho về null
                        $discountPrice = (isset($course->discountPrice) && $course->discountPrice !== '') ? (int) $course->discountPrice : null;
                        
                        $hasDiscount = ($discountPrice !== null);
                        $finalPrice = $hasDiscount ? $discountPrice : $originalPrice;
                    @endphp
                    <td class="align-middle">
                        @if($finalPrice == 0)
                            <span class="badge badge-success px-2 py-1" style="font-size: 13px;">Miễn phí</span>
                        @else
                            <span class="text-primary font-weight-bold" style="font-size: 15px;">{{ number_format($finalPrice) }} đ</span>
                            
                            {{-- Nếu có khuyến mãi và giá KM rẻ hơn giá gốc thì gạch ngang giá gốc --}}
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
        
        if(searchInput) {
            searchInput.addEventListener('keyup', function() {
                let filter = this.value.toLowerCase();
                let rows = document.querySelectorAll('table tbody tr');

                rows.forEach(row => {
                    let text = row.innerText.toLowerCase();
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