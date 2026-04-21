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
        // Tạm thời trả về thông báo, tính năng up ảnh sẽ hoàn thiện sau
        return back()->with('success', 'Đã duyệt yêu cầu thành công!');
    }
}