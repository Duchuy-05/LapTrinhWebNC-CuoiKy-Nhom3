<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Category; // Gọi Model Category để lấy danh sách Thể loại
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth; // Để lấy ID của người đang đăng nhập

class CourseController extends Controller
{
    // 1. Hiển thị danh sách khóa học
    public function index()
    {
        $courses = Course::all();
        return view('admin.courses.index', compact('courses'));
    }

    // 2. Hiển thị form Thêm mới
    public function create()
    {
        // Lấy các danh mục đang 'Hiển thị' (status = 1) để đưa vào thẻ <select>
        $categories = Category::where('status', '1')->get(); 
        return view('admin.courses.create', compact('categories'));
    }

    // 3. Nhận dữ liệu từ Form và lưu vào MongoDB
    public function store(Request $request)
    {
        $course = new Course();
        $course->title = $request->title;
        $course->slug = Str::slug($request->title);
        $course->category_id = $request->category_id;
        $course->price = $request->price;
        $course->description = $request->description;
        $course->status = $request->status;
        
        // Gán người tạo khóa học chính là tài khoản đang đăng nhập
        $course->teacher_id = Auth::id(); 

        // XỬ LÝ UPLOAD ẢNH THUMBNAIL
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            // Đổi tên file để không bị trùng (thêm thời gian vào trước tên)
            $filename = time() . '_' . $file->getClientOriginalName();
            // Lưu file vào thư mục public/uploads/courses
            $file->move(public_path('uploads/courses'), $filename);
            // Lưu đường dẫn vào Database
            $course->thumbnail = 'uploads/courses/' . $filename; 
        }

        $course->save();

        return redirect()->route('admin.courses.index');
    }
    // 1. Hiển thị form Sửa khóa học
    public function edit($id)
    {
        $course = Course::findOrFail($id);
        $categories = Category::where('status', '1')->get(); // Lấy danh sách thể loại để chọn lại
        return view('admin.courses.edit', compact('course', 'categories'));
    }

    // 2. Nhận dữ liệu Cập nhật (Có xử lý thay ảnh)
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $course->title = $request->title;
        $course->slug = Str::slug($request->title);
        $course->category_id = $request->category_id;
        $course->price = $request->price;
        $course->description = $request->description;
        $course->status = $request->status;

        // Nếu người dùng có chọn upload ảnh mới
        if ($request->hasFile('thumbnail')) {
            // Xóa file ảnh cũ đi cho nhẹ server (nếu có ảnh cũ)
            if ($course->thumbnail && file_exists(public_path($course->thumbnail))) {
                unlink(public_path($course->thumbnail));
            }

            // Lưu ảnh mới
            $file = $request->file('thumbnail');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/courses'), $filename);
            $course->thumbnail = 'uploads/courses/' . $filename;
        }

        $course->save();
        return redirect()->route('admin.courses.index');
    }

    // 3. Xóa khóa học
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        
        // Xóa luôn file ảnh của khóa học này trong thư mục uploads
        if ($course->thumbnail && file_exists(public_path($course->thumbnail))) {
            unlink(public_path($course->thumbnail));
        }

        $course->delete();
        return redirect()->route('admin.courses.index');
    }
}