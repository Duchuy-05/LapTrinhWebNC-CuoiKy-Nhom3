<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>@yield('title', 'Bảng Điều Khiển') | Admin System</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4 d-flex flex-column" style="height: 100vh;">
        
        <a href="{{ route('admin.dashboard') ?? url('admin/dashboard') }}" class="brand-link text-center">
            <span class="brand-text font-weight-bold">ADMIN SYSTEM</span>
        </a>

        <div class="sidebar flex-grow-1" style="overflow-y: auto;">
            <nav class="mt-3 pb-3">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') ?? url('admin/dashboard') }}" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Bảng Điều Khiển</p>
                        </a>
                    </li>

                    <li class="nav-header mt-2 text-uppercase" style="font-size: 0.75rem; color: #a9a9a9;">Quản lý hệ thống</li>

                    <li class="nav-item">
                        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Quản lý Người dùng</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="{{ route('admin.courses.index') }}" class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-book"></i>
                            <p>Quản lý Khóa học</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Quản lý Đơn hàng</p>
                        </a>
                    </li>

                    @php
                        // Logic đếm yêu cầu chưa duyệt để hiện chấm đỏ
                        $pendingPayoutsCount = \App\Models\PayoutRequest::where('status', 'pending')->count();
                    @endphp
                    <li class="nav-item">
                        <a href="{{ route('admin.payouts.index') }}" class="nav-link {{ request()->routeIs('admin.payouts.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-money-check-alt"></i>
                            <p>
                                Thanh toán Giảng viên
                                @if($pendingPayoutsCount > 0)
                                    <span class="badge badge-danger right" style="min-width: 10px; height: 10px; border-radius: 50%; padding: 0; margin-top: 5px;" title="Có {{ $pendingPayoutsCount }} yêu cầu mới">&nbsp;</span>
                                @endif
                            </p>
                        </a>
                    </li>

                </ul>
            </nav>
        </div>

        <div class="mt-auto border-top border-secondary" style="background-color: #343a40;">
            <ul class="nav nav-pills nav-sidebar flex-column p-2">
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="nav-icon fas fa-sign-out-alt text-danger"></i>
                        <p class="text-danger font-weight-bold">Đăng xuất</p>
                    </a>
                    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
        
    </aside>

    <div class="content-wrapper bg-light">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold">@yield('title')</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="content pb-4">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

@stack('scripts')

</body>
</html>