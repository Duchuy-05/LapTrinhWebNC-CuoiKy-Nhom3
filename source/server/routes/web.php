<?php

use Illuminate\Support\Facades\Route;

// ==========================================
// KHO IMPORT MODELS & MIDDLEWARES
// ==========================================
use App\Models\User;
use App\Models\Category;
use App\Models\Course;
use App\Models\Order;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckTeacher;

// ==========================================
// KHO IMPORT CONTROLLERS
// ==========================================
// 1. Admin Controllers
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PayoutController;

// 2. Frontend & Student Controllers
use App\Http\Controllers\StudentController;
use App\Http\Controllers\FrontendAuthController;
use App\Http\Controllers\FrontendApiController;  


// ==========================================
// ROUTE MẶC ĐỊNH
// ==========================================
Route::get('/', function () { 
    return view('welcome'); 
});


// ==========================================
// VÙNG CÔNG CỘNG (KHÔNG CẦN ĐĂNG NHẬP)
// ==========================================
// 1. Admin Login
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// 2. Học viên Đăng nhập / Đăng ký
Route::get('/login', [FrontendAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [FrontendAuthController::class, 'login'])->name('login.post');
Route::get('/register', [FrontendAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [FrontendAuthController::class, 'register'])->name('register.post');
Route::post('/logout', [FrontendAuthController::class, 'logout'])->name('logout');


// ==========================================
// VÙNG BẢO VỆ DÀNH CHO ADMIN
// ==========================================
Route::prefix('admin')->name('admin.')->middleware(['auth', CheckAdmin::class])->group(function () {
    
    Route::get('/dashboard', function () {
        $totalUsers      = User::count();
        $totalCategories = Category::count();
        $totalCourses    = Course::count();
        $completedOrders = Order::where('status', 'completed')->count(); 
        
        return view('admin.dashboard', compact('totalUsers', 'totalCategories', 'totalCourses', 'completedOrders'));
    })->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{id}/edit', [CourseController::class, 'edit'])->name('courses.edit');
    Route::put('/courses/{id}', [CourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{id}', [CourseController::class, 'destroy'])->name('courses.destroy');
    
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

    Route::get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts/{id}/approve', [PayoutController::class, 'approve'])->name('payouts.approve');
});


// ==========================================
// VÙNG BẢO VỆ DÀNH CHO GIẢNG VIÊN
// ==========================================
Route::prefix('instructor')->name('instructor.')->middleware(['auth', CheckTeacher::class])->group(function () {
    Route::get('/dashboard', function () {
        return view('instructor.dashboard');
    })->name('dashboard');
});


// ==========================================
// VÙNG BẢO VỆ DÀNH CHO HỌC VIÊN
// ==========================================
Route::middleware(['auth'])->prefix('student')->name('student.')->group(function () {
    Route::post('/course/{id}/enroll', [StudentController::class, 'enroll'])->name('enroll');
    Route::get('/my-learning', [StudentController::class, 'myLearning'])->name('my_learning');
});


// ==========================================
// VÙNG API CHO ỨNG DỤNG REACT/VUE
// ==========================================

// Gom toàn bộ API vào một nhóm và tắt triệt để tính năng bảo mật CSRF (Tránh lỗi 419)
Route::withoutMiddleware([
    \App\Http\Middleware\VerifyCsrfToken::class, 
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
])->group(function () {
    
    // 1. Xử lý lỗi CORS (Cho phép React đi qua)
    Route::options('/api/{any}', function () {
        return response('', 204)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    })->where('any', '.*');

    // 2. Các API của hệ thống
    Route::get('/api/courses', function () {
        $courses = \App\Models\Course::where('status', 'published')->get();
        return response()->json($courses)
                ->header('Access-Control-Allow-Origin', '*') 
                ->header('Access-Control-Allow-Methods', 'GET');
    });

    Route::post('/api/login', [FrontendApiController::class, 'login']);
    Route::post('/api/register', [FrontendApiController::class, 'register']);
    Route::post('/api/create-order', [FrontendApiController::class, 'createOrder']);
    Route::post('/api/webhook', [FrontendApiController::class, 'bankingWebhook']);
    Route::post('/api/my-courses', [FrontendApiController::class, 'myCourses']);
    Route::get('/api/check-order/{id}', [FrontendApiController::class, 'checkOrder']);
    
    // ==========================================
    // 3. API xử lý việc Giảng viên gửi yêu cầu rút tiền
    Route::post('/api/request-payout', [FrontendApiController::class, 'requestPayout']);

    // API Lấy lịch sử rút tiền của Giảng viên
    Route::get('/api/my-payouts', [FrontendApiController::class, 'myPayouts']);

    // API Quản lý thông tin Ngân hàng của Giảng viên
    Route::post('/api/update-bank-info', [FrontendApiController::class, 'updateBankInfo']);
    Route::get('/api/get-bank-info', [FrontendApiController::class, 'getBankInfo']);
    // API Hủy yêu cầu rút tiền
    Route::post('/api/cancel-payout', [FrontendApiController::class, 'cancelPayout']);
    // API Lấy danh sách Học viên của Giảng viên
    Route::get('/api/lecturer/my-students', [FrontendApiController::class, 'getMyStudents']);
    
});