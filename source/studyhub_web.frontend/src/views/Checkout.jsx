// File: src/views/Checkout.jsx
import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi';

export default function Checkout() {
  const { courseId } = useParams();
  const navigate = useNavigate();
  
  const [course, setCourse] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isProcessing, setIsProcessing] = useState(false);

  useEffect(() => {
    const fetchCourse = async () => {
      try {
        const response = await CourseAPI.getCourseDetail(courseId);
        setCourse(response.data.data || response.data);
      } catch (error) {
        alert("Không thể tải thông tin khóa học.");
        navigate('/student/home');
      } finally {
        setIsLoading(false);
      }
    };
    fetchCourse();
  }, [courseId, navigate]);

  if (isLoading) return <div className="p-20 text-center text-slate-500">Đang tải thông tin đơn hàng...</div>;
  if (!course) return <div className="p-20 text-center text-red-500">Không tìm thấy khóa học</div>;

  // Logic tính giá
  const currentPrice = Number(course.price || 0);
  const isDiscountActive = course.discountPrice !== null && course.discountPrice !== undefined;
  const currentDiscountPrice = isDiscountActive ? Number(course.discountPrice) : currentPrice;
  const isFree = currentPrice === 0 || (isDiscountActive && currentDiscountPrice === 0);

  const formatMoney = (amount) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);

  const handleCheckout = async () => {
    setIsProcessing(true);
    try {
      if (isFree) {
        // Nếu khóa học miễn phí, gọi API ghi danh luôn (enroll)
        await CourseAPI.enrollCourse(course.courseGroupId);
        alert("Đăng ký khóa học thành công!");
        navigate(`/student/courses/${course.courseGroupId}/learn`);
      } else {
        // Gắn cố định phương thức 'payos' vào đây vì chỉ dùng 1 loại
        const response = await CourseAPI.processCheckout(course.courseGroupId, 'payos');
        if (response.data.payUrl) {
          window.location.href = response.data.payUrl;
        }
      }
    } catch (error) {
      alert(error.response?.data?.message || "Có lỗi xảy ra khi khởi tạo thanh toán.");
      setIsProcessing(false);
    }
  };

  return (
    <div className="max-w-6xl mx-auto p-6 md:p-12 bg-slate-50 min-h-screen">
      <h1 className="text-3xl font-black text-slate-800 mb-8">Thanh toán an toàn</h1>
      
      <div className="flex flex-col lg:flex-row gap-8">
        
        {/* CỘT TRÁI: Phương thức thanh toán (Đã Fix cứng PayOS) */}
        <div className="space-y-4 lg:w-1/2">
          <h2 className="text-xl font-bold text-slate-800 mb-4">Phương thức thanh toán</h2>
          
          <div className="flex items-center p-4 border border-emerald-500 bg-emerald-50 rounded-xl cursor-default shadow-sm">
            {/* Input radio được check mặc định và không cho sửa */}
            <input 
              type="radio" 
              checked={true} 
              readOnly 
              className="w-5 h-5 text-emerald-600 focus:ring-emerald-500 cursor-default" 
            />
            <div className="ml-4 flex items-center gap-3">
              <div className="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600 font-bold text-xl">
                🏦
              </div>
              <div>
                <p className="font-bold text-slate-800">Chuyển khoản VietQR</p>
                <p className="text-xs text-slate-500">Hệ thống tự động xác nhận sau 3 giây (PayOS)</p>
              </div>
            </div>
          </div>
        </div>

        {/* CỘT PHẢI: Tóm tắt đơn hàng */}
        <div className="w-full lg:w-[400px]">
          <div className="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 sticky top-8">
            <h2 className="text-xl font-bold text-slate-800 mb-6">Tóm tắt đơn hàng</h2>
            
            {/* Thông tin khóa học */}
            <div className="flex gap-4 mb-6 pb-6 border-b border-slate-100">
              <img src={course.thumbnail || 'https://via.placeholder.com/100'} alt="thumbnail" className="w-24 h-16 object-cover rounded-lg border border-slate-200" />
              <div className="flex-1">
                <h3 className="font-bold text-sm text-slate-800 line-clamp-2">{course.title}</h3>
              </div>
            </div>

            {/* Chi tiết tính tiền */}
            <div className="space-y-3 text-sm mb-6">
              <div className="flex justify-between text-slate-600">
                <span>Giá gốc:</span>
                <span className="line-through">{formatMoney(currentPrice)}</span>
              </div>
              
              {isDiscountActive && currentDiscountPrice < currentPrice && (
                <div className="flex justify-between text-green-600">
                  <span>Khuyến mãi:</span>
                  <span>- {formatMoney(currentPrice - currentDiscountPrice)}</span>
                </div>
              )}
            </div>

            {/* Tổng cộng */}
            <div className="flex justify-between items-center mb-8 pt-4 border-t border-slate-200">
              <span className="font-bold text-slate-800">Tổng thanh toán:</span>
              <span className="text-2xl font-black text-indigo-600">
                {isFree ? "Miễn phí" : formatMoney(currentDiscountPrice)}
              </span>
            </div>

            {/* Nút thanh toán */}
            <button 
              onClick={handleCheckout}
              disabled={isProcessing}
              className={`w-full py-4 rounded-xl font-bold text-white transition-all shadow-lg ${isProcessing ? 'bg-slate-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700 active:scale-95'}`}
            >
              {isProcessing ? 'Đang khởi tạo...' : (isFree ? 'Đăng ký miễn phí' : 'Hoàn tất thanh toán')}
            </button>
            <p className="text-center text-xs text-slate-400 mt-4">
              Bằng việc hoàn tất thanh toán, bạn đồng ý với Điều khoản sử dụng của StudyHub.
            </p>
          </div>
        </div>

      </div>
    </div>
  );
}