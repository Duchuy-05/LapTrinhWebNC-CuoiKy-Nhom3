<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str; // Thư viện hỗ trợ tạo chuỗi slug tự động

class CategoryController extends Controller
{
    // Hiển thị danh sách
    public function index()
    {
        $categories = Category::all();
        return view('admin.categories.index', compact('categories'));
    }

    // Hiển thị form thêm mới
    public function create()
    {
        return view('admin.categories.create');
    }

    // Lưu danh mục mới vào MongoDB
    public function store(Request $request)
    {
        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name); // Tự động tạo slug từ tên
        $category->description = $request->description;
        $category->status = $request->status; // 1 là Hiện, 0 là Ẩn
        $category->save();

        return redirect()->route('admin.categories.index');
    }

    // 1. Hiển thị form Sửa
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    // 2. Nhận dữ liệu và Cập nhật vào MongoDB
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->name); // Cập nhật lại slug nếu đổi tên
        $category->description = $request->description;
        $category->status = $request->status;
        $category->save();

        return redirect()->route('admin.categories.index');
    }

    // 3. Xóa danh mục
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.categories.index');
    }
}