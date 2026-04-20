@extends('layouts.admin')
@section('title', 'Quản lý Đơn hàng & Doanh thu')
@section('content')

{{-- ĐOẠN CODE TÍNH TOÁN DOANH THU 40 - 60 (ĐÃ FIX CHUẨN authorId) --}}
@php
    $successfulOrdersForIncome = \App\Models\Order::with('course')
        ->whereIn('status', ['SUCCESS', 'completed'])
        ->get();

    $totalRevenue = 0;
    $userIncomes = [];

    foreach($successfulOrdersForIncome as $ord) {
        $price = (int) ($ord->price_paid ?? 0);
        $totalRevenue += $price;

        $userShare = $price * 0.6; // 60% cho Người dùng (tác giả)

        if ($userShare > 0) {
            $authorName = 'Không xác định';
            
            if ($ord->course) {
                $courseData = $ord->course->toArray();
                
                // ĐÃ FIX: Nhận diện chính xác cột authorId của Database
                $userId = $courseData['authorId'] ?? $courseData['user_id'] ?? $courseData['author_id'] ?? null;
                
                if ($userId) {
                    $user = \App\Models\User::find($userId);
                    if ($user) {
                        $authorName = $user->name;
                    } else {
                        $authorName = 'Tài khoản đã xóa (ID: ' . $userId . ')';
                    }
                }
            } else {
                $authorName = 'Khóa học đã bị xóa';
            }

            if(!isset($userIncomes[$authorName])) {
                $userIncomes[$authorName] = 0;
            }
            $userIncomes[$authorName] += $userShare;
        }
    }

    $adminIncome = $totalRevenue * 0.4; // 40% cho Admin

    arsort($userIncomes);
@endphp
{{-- KẾT THÚC TÍNH TOÁN --}}

{{-- KHỐI HIỂN THỊ DOANH THU ADMIN & NGƯỜI DÙNG --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm h-100" style="border-top: 4px solid #6f42c1; border-radius: 10px;">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <h5 class="text-muted text-uppercase font-weight-bold" style="font-size: 14px;">Thu nhập của Admin (40%)</h5>
                <h2 class="font-weight-bold my-3" style="color: #6f42c1;">
                    {{ number_format($adminIncome) }} <small>đ</small>
                </h2>
                <p class="text-muted mb-0" style="font-size: 13px;">Từ tổng doanh thu: <strong>{{ number_format($totalRevenue) }} đ</strong></p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm h-100" style="border-top: 4px solid #28a745; border-radius: 10px;">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="text-success font-weight-bold mb-0" style="font-size: 15px;">
                    <i class="fas fa-hand-holding-usd mr-2"></i> Thu nhập của Người dùng (60%)
                </h5>
            </div>
            <div class="card-body p-0 mt-2" style="max-height: 180px; overflow-y: auto;">
                <table class="table table-hover table-striped mb-0 text-sm">
                    <thead class="bg-light" style="position: sticky; top: 0; z-index: 1;">
                        <tr>
                            <th width="10%">STT</th>
                            <th>Tên Người dùng (Tác giả)</th>
                            <th class="text-right">Tổng thu nhập kiếm được</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userIncomes as $author => $income)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="font-weight-bold text-dark">{{ $author }}</td>
                                <td class="text-right text-primary font-weight-bold">+ {{ number_format($income) }} đ</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">Chưa có người dùng nào phát sinh thu nhập.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- KHỐI DANH SÁCH ĐƠN HÀNG --}}
<div class="card shadow-sm" style="border-radius: 10px;">
    <div class="card-body table-responsive p-0">
        <div class="d-flex justify-content-end align-items-center p-3 pb-2 border-bottom">
            <h5 class="mr-auto mb-0 font-weight-bold text-secondary">Lịch sử giao dịch</h5>
            <div class="input-group" style="width: 300px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm nhanh...">
                <div class="input-group-append">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                </div>
            </div>
        </div>
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Mã ĐH</th>
                    <th>Người mua</th>
                    <th>Khóa học</th>
                    <th>Số tiền</th>
                    <th>Thời gian thanh toán</th> 
                    <th>Trạng thái</th>
                    <th>Thao tác (Duyệt)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>#{{ substr($order->id, -6) }}</td> 
                    
                    @php
                        $buyerName = 'Khách ẩn danh';
                        if ($order->user_id) {
                            $buyer = \App\Models\User::find($order->user_id);
                            if ($buyer) $buyerName = $buyer->name;
                        }
                    @endphp
                    <td>{{ $buyerName }}</td>
                    
                    <td>{{ optional($order->course)->title ?? 'Khóa học đã xóa' }}</td>
                    
                    <td class="text-danger font-weight-bold">{{ number_format($order->price_paid ?? 0) }} đ</td>
                    
                    <td>
                        @if($order->paid_at)
                            {{ \Carbon\Carbon::parse($order->paid_at)->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}
                        @else
                            <span class="text-muted">Chưa thanh toán</span>
                        @endif
                    </td>

                    <td>
                        @if(in_array($order->status, ['pending', 'PENDING']))
                            <span class="badge badge-warning">Chờ duyệt</span>
                        @elseif(in_array($order->status, ['completed', 'SUCCESS']))
                            <span class="badge badge-success">Thành công</span>
                        @else
                            <span class="badge badge-danger">Đã hủy</span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                            @csrf
                            <div class="input-group input-group-sm" style="width: 150px;">
                                <select name="status" class="form-control">
                                    <option value="PENDING" {{ in_array($order->status, ['pending', 'PENDING']) ? 'selected' : '' }}>Chờ duyệt</option>
                                    <option value="SUCCESS" {{ in_array($order->status, ['completed', 'SUCCESS']) ? 'selected' : '' }}>Thành công</option>
                                    <option value="CANCELED" {{ in_array($order->status, ['cancelled', 'CANCELED']) ? 'selected' : '' }}>Hủy đơn</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i></button>
                                </div>
                            </div>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($orders->count() == 0)
            <div class="text-center p-4 text-muted">Chưa có đơn hàng nào phát sinh!</div>
        @endif
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