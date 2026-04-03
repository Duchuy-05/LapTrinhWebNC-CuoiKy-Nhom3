<?php

use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\CourseCategoryController as AdminCourseCategoryController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LessonController as AdminLessonController;
use App\Http\Controllers\Admin\SiteContentController as AdminSiteContentController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Instructor\CourseController as InstructorCourseController;
use App\Http\Controllers\Instructor\CoursePostController as InstructorCoursePostController;
use App\Http\Controllers\Instructor\DashboardController as InstructorDashboardController;
use App\Http\Controllers\Instructor\LessonController as InstructorLessonController;
use App\Http\Controllers\Instructor\QuizController as InstructorQuizController;
use App\Http\Controllers\Instructor\QuizQuestionController as InstructorQuizQuestionController;
use App\Http\Controllers\Instructor\StudentController as InstructorStudentController;
use App\Http\Controllers\LessonController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/courses/{course:slug}/lessons/{lesson:slug}', [LessonController::class, 'show'])->name('lessons.show');
Route::get('/pages/{key}', [HomeController::class, 'page'])->name('pages.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::post('/courses/{course:slug}/enroll', [EnrollmentController::class, 'store'])->name('courses.enroll');
    Route::post('/courses/{course:slug}/lessons/{lesson:slug}/complete', [EnrollmentController::class, 'completeLesson'])->name('lessons.complete');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('categories', AdminCourseCategoryController::class)->except(['show']);
        Route::resource('courses', AdminCourseController::class)->except(['show']);
        Route::resource('lessons', AdminLessonController::class)->except(['show']);
        Route::resource('users', AdminUserController::class)->except(['show']);
        Route::resource('announcements', AdminAnnouncementController::class)->except(['show']);
        Route::resource('site-contents', AdminSiteContentController::class)->except(['show']);
    });

Route::prefix('instructor')
    ->name('instructor.')
    ->middleware(['auth', 'role:instructor'])
    ->group(function (): void {
        Route::get('/', [InstructorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/courses', [InstructorCourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/{course}/edit', [InstructorCourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{course}', [InstructorCourseController::class, 'update'])->name('courses.update');

        Route::resource('lessons', InstructorLessonController::class)->except(['show']);
        Route::resource('posts', InstructorCoursePostController::class)->except(['show']);
        Route::resource('quizzes', InstructorQuizController::class)->except(['show']);
        Route::get('/students', [InstructorStudentController::class, 'index'])->name('students.index');

        Route::get('/quizzes/{quiz}/questions', [InstructorQuizQuestionController::class, 'index'])->name('questions.index');
        Route::get('/quizzes/{quiz}/questions/create', [InstructorQuizQuestionController::class, 'create'])->name('questions.create');
        Route::post('/quizzes/{quiz}/questions', [InstructorQuizQuestionController::class, 'store'])->name('questions.store');
        Route::get('/quizzes/{quiz}/questions/{question}/edit', [InstructorQuizQuestionController::class, 'edit'])->name('questions.edit');
        Route::match(['put', 'patch'], '/quizzes/{quiz}/questions/{question}', [InstructorQuizQuestionController::class, 'update'])->name('questions.update');
        Route::delete('/quizzes/{quiz}/questions/{question}', [InstructorQuizQuestionController::class, 'destroy'])->name('questions.destroy');
    });