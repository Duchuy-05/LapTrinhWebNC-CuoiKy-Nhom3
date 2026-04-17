<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    /**
     * Xử lý tải ảnh lên cho các block bài giảng
     */
    public function __invoke(Request $request)
    {
        // 1. Kiểm tra dữ liệu (Giới hạn 2MB cho ảnh)
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048', 
        ]);

        try {
            if ($request->hasFile('image')) {
                $file = $request->file('image');

                // 2. Tạo tên file duy nhất
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                // 3. Lưu vào thư mục public/images
                $path = $file->storeAs('images', $filename, 'public');

                // 4. Trả về URL công khai
                $imageUrl = asset('storage/' . $path);

                return response()->json([
                    'message' => 'Tải ảnh lên thành công',
                    'imageUrl' => $imageUrl
                ], 200);
            }

            return response()->json(['message' => 'Không tìm thấy file ảnh'], 400);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi server khi upload ảnh: ' . $e->getMessage()
            ], 500);
        }
    }
}