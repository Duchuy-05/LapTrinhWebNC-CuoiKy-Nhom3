<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    // Hiển thị danh sách Đơn hàng
    public function index()
    {
        // Lấy tất cả đơn hàng, sắp xếp mới nhất lên đầu, và nạp sẵn thông tin User, Course đi kèm
        $orders = Order::with(['user', 'course'])->orderBy('created_at', 'desc')->get();
        return view('admin.orders.index', compact('orders'));
    }

    // Cập nhật trạng thái đơn hàng (Duyệt hoặc Hủy)
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return redirect()->back(); // Cập nhật xong thì load lại trang
    }
}