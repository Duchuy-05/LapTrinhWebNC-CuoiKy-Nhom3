<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckTeacher;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CourseController;
use App\Models\User;
use App\Models\Category;
use App\Models\Course;
use App\Http\Controllers\Admin\OrderController;
use App\Models\Order;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\FrontendAuthController;
use App\Http\Controllers\FrontendApiController;

Route::get('/', function () { return view('welcome'); });

// ==========================================
// VÙNG CÔNG CỘNG (AI CŨNG VÀO ĐƯỢC)
// ==========================================

// 1. Đăng nhập dành cho ADMIN
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// 2. Đăng nhập / Đăng ký dành cho HỌC VIÊN (Dùng Blade View)
// Đặt tên là 'login' để Laravel tự hiểu đây là trang đăng nhập mặc định
Route::get('/login', [FrontendAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [FrontendAuthController::class, 'login'])->name('login.post');
Route::get('/register', [FrontendAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [FrontendAuthController::class, 'register'])->name('register.post');
Route::post('/logout', [FrontendAuthController::class, 'logout'])->name('logout');


// ==========================================
// VÙNG BẢO VỆ (CHỈ ADMIN MỚI ĐƯỢC VÀO)
// ==========================================
Route::prefix('admin')->name('admin.')->middleware(['auth', CheckAdmin::class])->group(function () {
    
    Route::get('/dashboard', function () {
        $totalUsers = User::count();
        $totalCategories = Category::count();
        $totalCourses = Course::count();
        
        // ĐỔI CODE Ở ĐÂY: Đếm số đơn hàng có trạng thái là 'completed' (Thành công)
        $completedOrders = Order::where('status', 'completed')->count(); 
        
        // Truyền biến $completedOrders ra ngoài View
        return view('admin.dashboard', compact('totalUsers', 'totalCategories', 'totalCourses', 'completedOrders'));
    })->name('dashboard');

    // Quản lý Users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    
    // Quản lý Danh mục
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    
    // Quản lý Khóa học
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{id}/edit', [CourseController::class, 'edit'])->name('courses.edit');
    Route::put('/courses/{id}', [CourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{id}', [CourseController::class, 'destroy'])->name('courses.destroy');
    
    // Quản lý Đơn hàng
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
});


// ==========================================
// VÙNG BẢO VỆ (CHỈ GIẢNG VIÊN MỚI ĐƯỢC VÀO)
// ==========================================
Route::prefix('instructor')->name('instructor.')->middleware(['auth', CheckTeacher::class])->group(function () {
    Route::get('/dashboard', function () {
        return view('instructor.dashboard');
    })->name('dashboard');
});


// ==========================================
// VÙNG BẢO VỆ (HỌC VIÊN PHẢI ĐĂNG NHẬP MỚI ĐƯỢC MUA/HỌC)
// ==========================================
Route::middleware(['auth'])->prefix('student')->name('student.')->group(function () {
    Route::post('/course/{id}/enroll', [StudentController::class, 'enroll'])->name('enroll');
    Route::get('/my-learning', [StudentController::class, 'myLearning'])->name('my_learning');
});


// ==========================================
// VÙNG API CHO TRANG TEST (HTML BÊN NGOÀI)
// ==========================================
Route::get('/api/courses', function () {
    $courses = \App\Models\Course::where('status', 'published')->get();
    return response()->json($courses)
            ->header('Access-Control-Allow-Origin', '*') 
            ->header('Access-Control-Allow-Methods', 'GET');
});

Route::post('/api/login', [FrontendApiController::class, 'login'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

Route::post('/api/register', [FrontendApiController::class, 'register'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

Route::post('/api/create-order', [FrontendApiController::class, 'createOrder'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

Route::post('/api/webhook', [FrontendApiController::class, 'bankingWebhook'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

Route::post('/api/my-courses', [FrontendApiController::class, 'myCourses'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

// Route để kiểm tra xem đơn hàng đã được Webhook cập nhật thành công chưa
Route::get('/api/check-order/{id}', [App\Http\Controllers\FrontendApiController::class, 'checkOrder'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);