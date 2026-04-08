@extends('layouts.admin')

@section('title', 'Bảng Điều Khiển')

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalUsers ?? 0 }}</h3>
                <p>Tổng số Người dùng</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ route('admin.users.index') }}" class="small-box-footer">Xem chi tiết <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $totalCategories ?? 0 }}</h3>
                <p>Danh mục Khóa học</p>
            </div>
            <div class="icon">
                <i class="fas fa-tags"></i>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="small-box-footer">Xem chi tiết <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $totalCourses ?? 0 }}</h3>
                <p>Tổng số Khóa học</p>
            </div>
            <div class="icon">
                <i class="fas fa-book"></i>
            </div>
            <a href="{{ route('admin.courses.index') }}" class="small-box-footer" style="color: #fff !important;">Xem chi tiết <i class="fas fa-arrow-circle-right" style="color: #fff;"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
              <div class="inner">
                <h3>{{ $completedOrders }}</h3>
                <p>Giao dịch thành công</p>
              </div>
              <div class="icon">
                <i class="fas fa-shopping-cart"></i>
              </div>
              <a href="{{ route('admin.orders.index') }}" class="small-box-footer">Xem chi tiết <i class="fas fa-arrow-circle-right"></i></a>
         </div>
    </div>
</div>
@endsection