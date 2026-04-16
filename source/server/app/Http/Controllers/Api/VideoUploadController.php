<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoUploadController extends Controller
{
    /**
     * Hàm __invoke tự động được gọi khi route trỏ đến Controller này
     */
    public function __invoke(Request $request)
    {
        // 1. Kiểm tra dữ liệu đầu vào
        $request->validate([
            'video' => 'required|file|mimes:mp4,webm,mov|max:204800', 
        ]);

        try {
            if ($request->hasFile('video')) {
                $file = $request->file('video');

                // 2. Tạo tên file độc nhất
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());

                // 3. Lưu file vào disk 'public', thư mục 'videos'
                $path = $file->storeAs('videos', $filename, 'public');

                // 4. Tạo URL công khai
                $videoUrl = asset('storage/' . $path);

                // 5. Trả về kết quả
                return response()->json([
                    'message' => 'Upload video thành công',
                    'videoUrl' => $videoUrl
                ], 200);
            }

            return response()->json(['message' => 'Không tìm thấy file video'], 400);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi server khi upload: ' . $e->getMessage()
            ], 500);
        }
    }
}