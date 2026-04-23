<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PayoutRequest; // Import Model vừa tạo

class PayoutController extends Controller
{
    public function index()
    {
        // 1. Kéo danh sách Yêu cầu đang chờ duyệt (kèm thông tin user)
        $pendingPayouts = PayoutRequest::with('user')
                            ->where('status', 'pending')
                            ->orderBy('created_at', 'desc')
                            ->get();

        // 2. Kéo danh sách Lịch sử đã thanh toán
        $completedPayouts = PayoutRequest::with('user')
                            ->where('status', 'completed')
                            ->orderBy('updated_at', 'desc')
                            ->get();

        // 3. Truyền cả 2 biến này ra giao diện Blade
        return view('admin.payouts.index', compact('pendingPayouts', 'completedPayouts'));
    }

    public function approve(Request $request, $id)
    {
        // 1. Tìm yêu cầu rút tiền trong Database
        $payout = \App\Models\PayoutRequest::find($id);
        
        if (!$payout) {
            return back()->with('error', 'Không tìm thấy yêu cầu rút tiền này!');
        }

        // 2. Xử lý Upload file ảnh biên lai
        if ($request->hasFile('receipt_image')) {
            // Tự động lưu ảnh vào thư mục storage/app/public/receipts
            $imagePath = $request->file('receipt_image')->store('receipts', 'public');
            $payout->receipt_image = $imagePath;
        }

        // 3. Cập nhật Trạng thái và Ghi chú
        $payout->admin_note = $request->input('admin_note');
        $payout->status = 'completed'; // Chuyển từ chờ duyệt -> Đã hoàn thành
        $payout->save();

        // 4. Trả về thông báo thành công
        return back()->with('success', 'Đã duyệt tiền và lưu biên lai thành công!');
    }
}