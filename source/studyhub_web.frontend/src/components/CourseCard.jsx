// File: src/components/CourseCard.jsx
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi';

const CourseCard = ({ course, badge }) => {
  const navigate = useNavigate();
  // State để điều khiển cửa sổ Popup yêu cầu đăng nhập
  const [showAuthModal, setShowAuthModal] = useState(false);
  
  const currentPrice = Number(course.price || 0);
  const isDiscountActive = course.discountPrice !== null && course.discountPrice !== undefined;
  const currentDiscountPrice = isDiscountActive ? Number(course.discountPrice) : currentPrice;
  const isFree = currentPrice === 0 || (isDiscountActive && currentDiscountPrice === 0);
  const isPurchased = course.is_purchased;

  // Hàm kiểm tra trạng thái đăng nhập
  const checkAuth = () => {
    const token = localStorage.getItem('token');
    if (!token) {
      setShowAuthModal(true); // Mở popup nếu chưa đăng nhập
      return false;
    }
    return true;
  };

  const handleEnroll = (e) => {
    e.stopPropagation();
    if (!checkAuth()) return;
    // Chuyển hướng sang trang chi tiết thanh toán
    navigate(`/student/courses/${course.courseGroupId}/checkout`);
  };

  const handleGoToLearn = (e) => {
    e.stopPropagation();
    navigate(`/student/courses/${course.courseGroupId}/learn`);
  };

  // Hàm chuyển hướng đến trang Chi tiết khóa học
  const handleGoToDetail = (e) => {
    e.stopPropagation();
    navigate(`/student/courses/${course.courseGroupId}`);
  };

  // LOGIC HIỂN THỊ GIÁ TIỀN THÔNG MINH
  const renderPrice = () => {
    // TH1: Khóa học hoàn toàn miễn phí từ đầu
    if (currentPrice === 0) {
      return <span className="text-green-600 font-bold text-lg">Miễn phí</span>;
    }

    // TH2: Có giảm giá (Giảm xuống 1 mức nào đó, hoặc giảm hẳn xuống 0đ)
    if (isDiscountActive && currentDiscountPrice < currentPrice) {
      return (
        <div className="flex items-center gap-2">
          <span className="text-indigo-600 font-black text-lg">
            {currentDiscountPrice === 0 ? "Miễn phí" : `${currentDiscountPrice.toLocaleString()}đ`}
          </span>
          <span className="text-slate-400 text-xs line-through">
            {currentPrice.toLocaleString()}đ
          </span>
        </div>
      );
    }

    // TH3: Không giảm giá (Bán đúng giá gốc)
    return <span className="text-indigo-600 font-black text-lg">{currentPrice.toLocaleString()}đ</span>
  };

  return (
    <>
      {/* THẺ KHÓA HỌC CHÍNH */}
      <div className="bg-white rounded-2xl overflow-hidden border border-slate-200 hover:shadow-xl transition-all group flex flex-col h-full relative">
        {badge && (
          <span className="absolute top-3 left-3 z-10 bg-indigo-600 text-white text-[10px] font-bold px-2 py-1 rounded-lg shadow-sm">
            {badge}
          </span>
        )}
        
        {/* THUMBNAIL */}
        <div 
          onClick={handleGoToDetail}
          className="aspect-video overflow-hidden bg-slate-100 relative cursor-pointer"
          title="Xem chi tiết khóa học"
        >
          <img 
            src={course.thumbnail || 'https://via.placeholder.com/350x200?text=StudyHub'} 
            alt={course.title} 
            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
          />
          {isPurchased && (
             <div className="absolute inset-0 bg-green-600/10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                <span className="bg-green-600 text-white text-xs font-bold px-3 py-1 rounded-full">Đã sở hữu</span>
             </div>
          )}
        </div>

        <div className="p-5 flex flex-col flex-1">
          {/* TÊN KHÓA HỌC */}
          <h3 
            onClick={handleGoToDetail}
            className="font-bold text-slate-800 line-clamp-2 mb-2 group-hover:text-indigo-600 h-12 cursor-pointer transition-colors"
            title="Xem chi tiết khóa học"
          >
            {course.title}
          </h3>
          <p className="text-xs text-slate-500 mb-4 flex items-center gap-1">
            👤 {course.student_count || 0} học viên
          </p>

          {/* VÙNG HIỂN THỊ GIÁ (Đã cập nhật gọi hàm renderPrice) */}
          <div className="flex items-center gap-2 mb-5">
            {renderPrice()}
          </div>

          <div className="mt-auto pt-4 border-t border-slate-50">
            {(isFree || isPurchased) ? (
              <button 
                onClick={handleGoToLearn}
                className="w-full py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-all flex justify-center items-center gap-2 shadow-md shadow-green-100 cursor-pointer"
              >
                📖 Vào học ngay
              </button>
            ) : (
              <div className="grid grid-cols-2 gap-2">
                <button 
                  onClick={handleGoToLearn} 
                  className="py-2.5 bg-orange-50 text-orange-600 font-bold rounded-xl hover:bg-orange-100 border border-orange-200 transition-all text-sm cursor-pointer"
                >
                  Học thử
                </button>
                <button 
                  onClick={handleEnroll}
                  className="py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all text-sm shadow-md shadow-indigo-100 cursor-pointer"
                >
                  Đăng ký
                </button>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* POPUP YÊU CẦU ĐĂNG NHẬP */}
      {showAuthModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm px-4">
          <div className="bg-white p-6 rounded-2xl shadow-2xl max-w-sm w-full animate-fadeIn border border-slate-100">
            <div className="text-center mb-6">
              <div className="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <span className="text-3xl">🔒</span>
              </div>
              <h3 className="text-xl font-black text-slate-800 mb-2">Bạn chưa đăng nhập</h3>
              <p className="text-sm text-slate-500">
                Vui lòng đăng nhập hoặc tạo tài khoản để đăng ký và lưu trữ tiến độ học tập của bạn nhé!
              </p>
            </div>
            
            <div className="flex flex-col gap-3">
              <button 
                onClick={() => navigate('/login')}
                className="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all shadow-md shadow-blue-200 cursor-pointer"
              >
                Đăng nhập ngay
              </button>
              <button 
                onClick={() => navigate('/register')}
                className="w-full py-3 bg-white text-blue-600 border border-blue-200 font-bold rounded-xl hover:bg-blue-50 transition-all cursor-pointer"
              >
                Tạo tài khoản mới
              </button>
              <button 
                onClick={() => setShowAuthModal(false)}
                className="w-full py-2 text-sm font-semibold text-slate-400 hover:text-slate-600 transition-colors mt-2 cursor-pointer"
              >
                Đóng lại
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
};

export default CourseCard;