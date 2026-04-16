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
            // Thêm dòng này để đánh dấu khóa học thuộc về giảng viên nào
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
     * 2. Lấy thông tin bản nháp để soạn thảo (Dùng cho trang CourseEditor)
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
        // Tìm bản Nháp hiện tại
        $draft = Course::where('courseGroupId', $courseGroupId)->where('status', 'DRAFT')->first();

        if (!$draft) {
            return response()->json(['message' => 'Không tìm thấy bản nháp để xuất bản'], 404);
        }

        // BƯỚC A: Tìm bản Published cũ (nếu có) và đổi thành ARCHIVED
        Course::where('courseGroupId', $courseGroupId)
              ->where('status', 'PUBLISHED')
              ->update(['status' => 'ARCHIVED']);

        // BƯỚC B: Clone (Nhân bản) bản Draft thành bản Published mới
        $published = $draft->replicate(); // Lệnh replicate() của Laravel tạo ra bản sao mới
        $published->status = 'PUBLISHED';
        $published->save();

        // BƯỚC C: Tăng version của bản Draft lên 1 cho lần chỉnh sửa tiếp theo
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
}