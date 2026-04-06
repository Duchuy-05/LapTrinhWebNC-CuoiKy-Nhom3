@extends('layouts.admin')
@section('title', 'Quản lý Đơn hàng')
@section('content')
<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Mã ĐH</th>
                    <th>Học viên</th>
                    <th>Khóa học</th>
                    <th>Số tiền</th>
                    <th>Trạng thái</th>
                    <th>Thao tác (Duyệt)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>#{{ substr($order->id, -6) }}</td> <td>{{ $order->user->name ?? 'Lỗi User' }}</td>
                    <td>{{ $order->course->title ?? 'Lỗi Course' }}</td>
                    <td class="text-danger font-weight-bold">{{ number_format($order->amount) }} đ</td>
                    <td>
                        @if($order->status == 'pending')
                            <span class="badge badge-warning">Chờ duyệt</span>
                        @elseif($order->status == 'completed')
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
                                    <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                                    <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Thành công</option>
                                    <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Hủy đơn</option>
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
@endsection