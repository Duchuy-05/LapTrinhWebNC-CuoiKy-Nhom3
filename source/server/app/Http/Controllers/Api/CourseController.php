<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index()
    {
        // Lấy các khóa học do giảng viên này tạo ra
        $courses = Course::where('authorId', auth()->id())
                         ->orderBy('created_at', 'desc')
                         ->get();
        
        // Tối ưu hóa dữ liệu trả về cho danh sách
        $courses->transform(function ($course) {
            // Đếm số lượng Unit có trong khóa học
            $course->unit_count = is_array($course->courseData) ? count($course->courseData) : 0;
            
            // Xóa mảng blocks để tránh payload API bị quá nặng khi tải danh sách
            unset($course->blocks); 
            
            return $course;
        });
                         
        return response()->json(['data' => $courses]);
    }

    /**
     * 1. Khởi tạo một khóa học mới (Luôn tạo bản DRAFT đầu tiên)
     */
    public function store(Request $request)
    {
        $courseGroupId = (string) Str::uuid(); 

        $course = Course::create([
            'courseGroupId' => $courseGroupId,
            'status' => 'DRAFT',
            'version' => 1,
            'title' => $request->input('title', 'Khóa học mới'),
            'courseData' => [],
            'blocks' => [],
            'authorId' => auth()->id(),
            'price' => 0,
            'discountPrice' => 0
        ]);

        if (!$course) {
            return response()->json(['message' => 'Tạo bản nháp thất bại'], 500);
        }

        return response()->json(['message' => 'Tạo bản nháp thành công', 'data' => $course], 201);
    }

    /**
     * 2. Lấy thông tin bản nháp để soạn thảo
     */
    public function showDraft($courseGroupId)
    {
        $draft = Course::where('courseGroupId', $courseGroupId)
                       ->where('status', 'DRAFT')
                       ->first();

        if (!$draft) {
            return response()->json(['message' => 'Không tìm thấy bản nháp'], 404);
        }

        return response()->json(['data' => $draft]);
    }

    /**
     * 3. Lưu bản nháp (Ghi đè liên tục khi soạn thảo)
     */
    public function updateDraft(Request $request, $courseGroupId)
    {
        $draft = Course::where('courseGroupId', $courseGroupId)
                       ->where('status', 'DRAFT')
                       ->first();

        if (!$draft) {
            return response()->json(['message' => 'Không tìm thấy bản nháp'], 404);
        }

        $draft->update($request->only([
            'title', 'description', 'thumbnail', 'tags', 'courseData', 'blocks', 'price', 'discountPrice'
        ]));

        return response()->json(['message' => 'Đã lưu bản nháp', 'data' => $draft]);
    }

    /**
     * 4. XUẤT BẢN KHÓA HỌC (Logic Clone Document)
     */
    public function publish($courseGroupId)
    {
        $draft = Course::where('courseGroupId', $courseGroupId)->where('status', 'DRAFT')->first();

        if (!$draft) {
            return response()->json(['message' => 'Không tìm thấy bản nháp để xuất bản'], 404);
        }

        Course::where('courseGroupId', $courseGroupId)
              ->where('status', 'PUBLISHED')
              ->update(['status' => 'ARCHIVED']);

        $published = $draft->replicate(); 
        $published->status = 'PUBLISHED';
        $published->save();

        $draft->increment('version');

        return response()->json([
            'message' => 'Xuất bản thành công!', 
            'published_version' => $published->version
        ]);
    }

    /**
     * 5. Ngừng xuất bản (Hạ xuống)
     */
    public function unpublish($courseGroupId)
    {
        $updated = Course::where('courseGroupId', $courseGroupId)
              ->where('status', 'PUBLISHED')
              ->update(['status' => 'UNPUBLISHED']);

        return response()->json(['message' => 'Đã ngừng xuất bản khóa học']);
    }
    /**
     * 6. Cập nhật nhanh Giá bán cho khóa học (Cập nhật cả bản Live và Draft)
     */
    public function updatePrice(Request $request, $courseGroupId)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
            'discountPrice' => 'required|numeric|min:0',
        ]);

        // Cập nhật cho bản PUBLISHED (Để thay đổi hiển thị ngay lập tức cho học viên)
        $publishedCourse = Course::where('courseGroupId', $courseGroupId)
                                 ->where('status', 'PUBLISHED')
                                 ->first();
        if ($publishedCourse) {
            $publishedCourse->update([
                'price' => $request->price,
                'discountPrice' => $request->discountPrice
            ]);
        }

        // Cập nhật luôn cho bản DRAFT (Để lần xuất bản sau không bị đè mất giá mới)
        $draftCourse = Course::where('courseGroupId', $courseGroupId)
                             ->where('status', 'DRAFT')
                             ->first();
        if ($draftCourse) {
            $draftCourse->update([
                'price' => $request->price,
                'discountPrice' => $request->discountPrice
            ]);
        }

        return response()->json(['message' => 'Cập nhật giá thành công!']);
    }
}