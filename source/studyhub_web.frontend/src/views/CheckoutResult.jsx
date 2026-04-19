// File: src/views/CheckoutResult.jsx
// Trang này nhận redirect từ PayOS sau thanh toán.
// Poll API mỗi 3 giây đến khi đơn là SUCCESS hoặc CANCELED.
import React, { useEffect, useState, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi';

const MAX_POLLS = 20; // Tối đa 60 giây polling

export default function CheckoutResult() {
  const { courseId } = useParams();
  const navigate = useNavigate();
  const [status, setStatus] = useState('POLLING'); // POLLING | SUCCESS | CANCELED | TIMEOUT
  const pollCount = useRef(0);

  useEffect(() => {
    const interval = setInterval(async () => {
      pollCount.current += 1;

      try {
        const res = await CourseAPI.getOrderStatus(courseId);
        const orderStatus = res.data.status;

        if (orderStatus === 'SUCCESS') {
          clearInterval(interval);
          setStatus('SUCCESS');
        } else if (orderStatus === 'CANCELED') {
          clearInterval(interval);
          setStatus('CANCELED');
        }
        // Nếu PENDING tiếp tục poll
      } catch {
        // Lỗi mạng → bỏ qua, thử lại lần sau
      }

      if (pollCount.current >= MAX_POLLS) {
        clearInterval(interval);
        setStatus('TIMEOUT');
      }
    }, 3000);

    return () => clearInterval(interval);
  }, [courseId]);

  if (status === 'POLLING') {
    return (
      <div className="flex flex-col items-center justify-center min-h-screen bg-slate-50 gap-6 px-4">
        <div className="w-20 h-20 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin" />
        <h2 className="text-xl font-bold text-slate-700">Đang xác nhận thanh toán...</h2>
        <p className="text-sm text-slate-400">Vui lòng không đóng trang này. Quá trình thường mất dưới 10 giây.</p>
      </div>
    );
  }

  if (status === 'SUCCESS') {
    return (
      <div className="flex flex-col items-center justify-center min-h-screen bg-slate-50 gap-6 px-4 text-center">
        <div className="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center text-5xl shadow-sm">🎉</div>
        <h2 className="text-2xl font-black text-green-600">Thanh toán thành công!</h2>
        <p className="text-slate-500 max-w-sm">Khóa học đã được thêm vào thư viện của bạn. Bắt đầu học ngay thôi!</p>
        <div className="flex gap-3 mt-2">
          <button
            onClick={() => navigate(`/student/courses/${courseId}/learn`)}
            className="px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-md cursor-pointer"
          >
            📖 Vào học ngay
          </button>
          <button
            onClick={() => navigate('/student/my-courses')}
            className="px-6 py-3 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-slate-200 transition-all cursor-pointer"
          >
            Thư viện của tôi
          </button>
        </div>
      </div>
    );
  }

  if (status === 'CANCELED') {
    return (
      <div className="flex flex-col items-center justify-center min-h-screen bg-slate-50 gap-6 px-4 text-center">
        <div className="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center text-5xl">❌</div>
        <h2 className="text-2xl font-black text-red-500">Thanh toán bị hủy</h2>
        <p className="text-slate-500 max-w-sm">Giao dịch đã bị hủy. Tiền của bạn không bị trừ.</p>
        <button
          onClick={() => navigate(`/student/courses/${courseId}/checkout`)}
          className="px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-md cursor-pointer"
        >
          Thử lại
        </button>
      </div>
    );
  }

  // TIMEOUT
  return (
    <div className="flex flex-col items-center justify-center min-h-screen bg-slate-50 gap-6 px-4 text-center">
      <div className="w-24 h-24 bg-yellow-100 rounded-full flex items-center justify-center text-5xl">⏳</div>
      <h2 className="text-2xl font-black text-yellow-600">Đang chờ xác nhận</h2>
      <p className="text-slate-500 max-w-sm">
        Hệ thống chưa nhận được xác nhận từ ngân hàng. Nếu đã thanh toán thành công, khóa học sẽ xuất hiện trong thư viện sau vài phút.
      </p>
      <div className="flex gap-3 mt-2">
        <button
          onClick={() => navigate('/student/my-courses')}
          className="px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-md cursor-pointer"
        >
          Kiểm tra thư viện
        </button>
        <button
          onClick={() => navigate('/student/home')}
          className="px-6 py-3 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-slate-200 transition-all cursor-pointer"
        >
          Về trang chủ
        </button>
      </div>
    </div>
  );
}