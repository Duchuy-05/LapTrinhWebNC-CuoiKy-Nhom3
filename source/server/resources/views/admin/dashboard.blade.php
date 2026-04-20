    {{-- Phần Danh mục đã được ẩn an toàn --}}
    {{-- 
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
    --}}

@extends('layouts.admin')

@section('title', 'Bảng Điều Khiển')

@section('content')

{{-- NHÚNG THƯ VIỆN CHART.JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* Banner chào mừng */
    .welcome-banner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 30px 40px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(118, 75, 162, 0.2);
        position: relative;
        overflow: hidden;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .welcome-banner::after {
        content: '';
        position: absolute;
        right: -5%;
        top: -50%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
    }

    /* Thẻ thống kê nhỏ phía trên */
    .modern-stat-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 25px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0,0,0,0.02);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        overflow: hidden;
        margin-bottom: 25px;
        text-decoration: none !important;
    }

    .modern-stat-card:hover {
        transform: translateY(-7px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
    }

    .stat-info h3 {
        font-size: 2rem;
        font-weight: 800;
        color: #2d3748;
        margin: 0 0 5px 0;
        letter-spacing: -1px;
    }

    .stat-info p {
        font-size: 0.95rem;
        font-weight: 600;
        color: #718096;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .icon-wrapper {
        width: 70px;
        height: 70px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: white;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .gradient-blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); box-shadow: 0 10px 20px rgba(0, 242, 254, 0.3); }
    .gradient-orange { background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); box-shadow: 0 10px 20px rgba(253, 160, 133, 0.3); }
    .gradient-red { background: linear-gradient(135deg, #ff0844 0%, #ffb199 100%); box-shadow: 0 10px 20px rgba(255, 8, 68, 0.3); }

    /* Thẻ chứa Biểu đồ lớn bên dưới */
    .chart-box {
        background: #ffffff;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0,0,0,0.02);
        margin-bottom: 30px;
    }

    .chart-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #4a5568;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chart-container {
        position: relative;
        height: 250px;
        width: 100%;
    }
</style>

{{-- LẤY DỮ LIỆU TỪ DATABASE --}}
@php
    $countUsers = $totalUsers ?? \App\Models\User::count();
    $countCourses = $totalCourses ?? \App\Models\Course::count();
    $countOrders = \App\Models\Order::whereIn('status', ['SUCCESS', 'completed'])->count();
@endphp

{{-- BANNER CHÀO MỪNG --}}
<div class="row">
    <div class="col-12">
        <div class="welcome-banner">
            <div>
                <h2 style="font-weight: 800; margin-bottom: 5px; font-size: 1.8rem;">Xin chào, Admin! 👋</h2>
                <p style="margin: 0; opacity: 0.9; font-size: 1rem;">Chào mừng bạn trở lại. Dưới đây là tổng quan tình hình kinh doanh hôm nay.</p>
            </div>
            <div class="d-none d-md-block">
                <div style="background: rgba(255,255,255,0.2); padding: 10px 20px; border-radius: 12px; font-weight: bold; backdrop-filter: blur(5px);">
                    <i class="far fa-calendar-alt mr-2"></i> {{ \Carbon\Carbon::now()->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- HÀNG 1: CÁC THẺ THỐNG KÊ NHỎ --}}
<div class="row">
    <div class="col-lg-4 col-md-6">
        <a href="{{ route('admin.users.index') }}" class="modern-stat-card">
            <div class="stat-info">
                <h3>{{ $countUsers }}</h3>
                <p>Tổng Người Dùng</p>
            </div>
            <div class="icon-wrapper gradient-blue">
                <i class="fas fa-users"></i>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6">
        <a href="{{ route('admin.courses.index') }}" class="modern-stat-card">
            <div class="stat-info">
                <h3>{{ $countCourses }}</h3>
                <p>Tổng Khóa Học</p>
            </div>
            <div class="icon-wrapper gradient-orange">
                <i class="fas fa-book-open"></i>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6">
        <a href="{{ route('admin.orders.index') }}" class="modern-stat-card">
            <div class="stat-info">
                <h3>{{ $countOrders }}</h3>
                <p>GD Thành Công</p>
            </div>
            <div class="icon-wrapper gradient-red">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </a>
    </div>
</div>

{{-- HÀNG 2: 3 BIỂU ĐỒ LỚN ĐỘC LẬP --}}
<div class="row">
    
    <div class="col-lg-4">
        <div class="chart-box">
            <div class="chart-title">
                <i class="fas fa-chart-area text-info"></i> Tăng trưởng Người dùng
            </div>
            <div class="chart-container">
                <canvas id="usersChartMain"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="chart-box">
            <div class="chart-title">
                <i class="fas fa-chart-bar text-warning"></i> Khóa học mới (7 ngày)
            </div>
            <div class="chart-container">
                <canvas id="coursesChartMain"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="chart-box">
            <div class="chart-title">
                <i class="fas fa-chart-line text-danger"></i> Lượt giao dịch
            </div>
            <div class="chart-container">
                <canvas id="ordersChartMain"></canvas>
            </div>
        </div>
    </div>

</div>

{{-- SCRIPT KHỞI TẠO BIỂU ĐỒ LỚN --}}
<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // Lấy dữ liệu thực tế hiện tại
    let finalUsers = {{ $countUsers }};
    let finalCourses = {{ $countCourses }};
    let finalOrders = {{ $countOrders }};

    // Các nhãn trục X (Mô phỏng 7 ngày)
    const labels = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];

    // Cấu hình chung cho lưới (Grid) hiển thị đẹp
    const gridOptions = {
        color: 'rgba(0, 0, 0, 0.05)',
        drawBorder: false,
    };

    // 1. Biểu đồ Người dùng (Vùng Area Chart - Xanh lam)
    new Chart(document.getElementById('usersChartMain').getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Tổng người dùng',
                data: [Math.max(0, finalUsers-12), Math.max(0, finalUsers-9), Math.max(0, finalUsers-8), Math.max(0, finalUsers-5), Math.max(0, finalUsers-3), Math.max(0, finalUsers-1), finalUsers],
                borderColor: '#4facfe',
                backgroundColor: 'rgba(79, 172, 254, 0.15)',
                borderWidth: 3,
                fill: true,
                tension: 0.4 // Làm cong đường nối
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { grid: gridOptions, beginAtZero: true }
            }
        }
    });

    // 2. Biểu đồ Khóa học (Cột Bar Chart - Màu Cam)
    new Chart(document.getElementById('coursesChartMain').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Khóa học',
                data: [Math.max(0, finalCourses-6), Math.max(0, finalCourses-5), Math.max(0, finalCourses-4), Math.max(0, finalCourses-3), Math.max(0, finalCourses-2), Math.max(0, finalCourses-1), finalCourses],
                backgroundColor: 'rgba(253, 160, 133, 0.8)',
                borderRadius: 6 // Bo góc cột
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { grid: gridOptions, beginAtZero: true }
            }
        }
    });

    // 3. Biểu đồ Đơn hàng (Đường Line Chart - Màu Đỏ)
    new Chart(document.getElementById('ordersChartMain').getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Đơn hàng',
                data: [Math.max(0, finalOrders-8), Math.max(0, finalOrders-7), Math.max(0, finalOrders-4), Math.max(0, finalOrders-3), Math.max(0, finalOrders-2), Math.max(0, finalOrders-1), finalOrders],
                borderColor: '#ff0844',
                backgroundColor: '#ffffff',
                pointBackgroundColor: '#ff0844',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                borderWidth: 3,
                fill: false,
                tension: 0.1 // Đường gấp khúc nhẹ
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { grid: gridOptions, beginAtZero: true }
            }
        }
    });
});
</script>

@endsection