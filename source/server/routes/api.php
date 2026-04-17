<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FrontendApiController;
use App\Http\Controllers\Api\CourseController; 
use App\Http\Controllers\Api\VideoUploadController;
use App\Http\Controllers\Api\ImageUploadController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\OderController;
use App\Http\Controllers\Api\LearnController;
// ==========================================
// VÙNG API CÔNG CỘNG (Không cần đăng nhập)
// ==========================================
Route::get('/courses', function () {
    $courses = \App\Models\Course::where('status', 'published')->get();
    return response()->json($courses);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [FrontendApiController::class, 'register']);
Route::get('/student/home', [StudentController::class, 'Home']);
Route::get('/student/courses/{courseGroupId}/learn', [LearnController::class, 'showCourseContent']);
// ==========================================
// VÙNG API ĐƯỢC BẢO VỆ (Bắt buộc phải có Token)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('lecturer/courses')->group(function () {
        Route::get('/', [CourseController::class, 'index']);
        Route::post('/', [CourseController::class, 'store']);
        Route::get('/{courseGroupId}/draft', [CourseController::class, 'showDraft']);
        Route::put('/{courseGroupId}/draft', [CourseController::class, 'updateDraft']);
        Route::post('/{courseGroupId}/publish', [CourseController::class, 'publish']);
        Route::post('/{courseGroupId}/unpublish', [CourseController::class, 'unpublish']);
        Route::post('/upload-video', VideoUploadController::class);
        Route::post('/upload-image', ImageUploadController::class);
        Route::put('/lecturer/courses/{courseGroupId}/price', [\App\Http\Controllers\Api\CourseController::class, 'updatePrice']);
    });

    Route::prefix('student')->group(function () {
        Route::get('/my-courses', [OderController::class, 'myCourses']);
        Route::post('/enroll/{courseGroupId}', [StudentController::class, 'enroll']);
    });
});