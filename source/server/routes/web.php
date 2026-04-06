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

// --- VÙNG KHÔNG CẦN BẢO VỆ ---
// Trang đăng nhập (đặt tên là 'login' để Laravel tự động chuyển hướng khi chưa đăng nhập)
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');


// --- VÙNG BẢO VỆ BỞI MIDDLEWARE 'AUTH' ---
// Bất cứ ai muốn vào nhóm này đều phải qua cửa ải Đăng Nhập
Route::prefix('admin')->name('admin.')->middleware(['auth', CheckAdmin::class])->group(function () {
    
    Route::get('/dashboard', function () {
        $totalUsers = User::count();           // Đếm tổng số user
        $totalCategories = Category::count();  // Đếm tổng số danh mục
        $totalCourses = Course::count();       // Đếm tổng số khóa học
        
        return view('admin.dashboard', compact('totalUsers', 'totalCategories', 'totalCourses'));
    })->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    // Nhóm Route Quản lý Danh mục
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    // Thêm 3 dòng này cho chức năng Sửa và Xóa:
    Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    // Nhóm Route Quản lý Khóa học
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    // Thêm 3 dòng này cho chức năng Sửa và Xóa khóa học:
    Route::get('/courses/{id}/edit', [CourseController::class, 'edit'])->name('courses.edit');
    Route::put('/courses/{id}', [CourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{id}', [CourseController::class, 'destroy'])->name('courses.destroy');
    // Nhóm Route Quản lý Đơn hàng
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

    Route::get('/dashboard', function () {
        $totalUsers = User::count();
        $totalCategories = Category::count();
        $totalCourses = Course::count();
        
        // Đếm số đơn hàng đang có trạng thái là 'pending' (Chờ duyệt)
        $newOrders = Order::where('status', 'pending')->count(); 
        
        return view('admin.dashboard', compact('totalUsers', 'totalCategories', 'totalCourses', 'newOrders'));
    })->name('dashboard');

    // Nhóm Route Đăng nhập / Đăng ký cho Học viên
    Route::get('/login', [FrontendAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [FrontendAuthController::class, 'login'])->name('login.post');

    Route::get('/register', [FrontendAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [FrontendAuthController::class, 'register'])->name('register.post');

    Route::post('/logout', [FrontendAuthController::class, 'logout'])->name('logout');
});

// Nhóm dành riêng cho Giảng viên
Route::prefix('instructor')->name('instructor.')->middleware(['auth', CheckTeacher::class])->group(function () {
    Route::get('/dashboard', function () {
        return view('instructor.dashboard'); // Bạn sẽ tạo view này sau
    })->name('dashboard');
    
    // Giảng viên chỉ quản lý khóa học của họ
    // Route::get('/courses', [CourseController::class, 'myCourses'])->name('courses');
});

// Nhóm Route dành cho Học viên (Phải đăng nhập mới được mua/học)
Route::middleware(['auth'])->prefix('student')->name('student.')->group(function () {
    
    // Nút bấm đăng ký mua khóa học
    Route::post('/course/{id}/enroll', [StudentController::class, 'enroll'])->name('enroll');
    
    // Trang danh sách khóa học đã mua
    Route::get('/my-learning', [StudentController::class, 'myLearning'])->name('my_learning');
    
});

// API LẤY DANH SÁCH KHÓA HỌC CHO FRONTEND BÊN NGOÀI
Route::get('/api/courses', function () {
    $courses = \App\Models\Course::where('status', 'published')->get();
    
    return response()->json($courses)
            ->header('Access-Control-Allow-Origin', '*') // Cho phép file bên ngoài gọi vào
            ->header('Access-Control-Allow-Methods', 'GET');
});

// API CHO FRONTEND (Lưu ý: withoutMiddleware để cho phép file html bên ngoài gửi data vào)
Route::post('/api/login', [FrontendApiController::class, 'login'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

Route::post('/api/register', [FrontendApiController::class, 'register'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

// Route để tạo đơn hàng (đang bị thiếu hoặc sai ở đây)
Route::post('/api/create-order', [App\Http\Controllers\FrontendApiController::class, 'createOrder'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

// Route đón Webhook của SePay (bài trước mình đã làm)
Route::post('/api/webhook', [App\Http\Controllers\FrontendApiController::class, 'bankingWebhook'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);