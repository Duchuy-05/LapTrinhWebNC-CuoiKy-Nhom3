// File: src/components/CourseCard.jsx
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi';

const CourseCard = ({ course, badge }) => {
  const navigate = useNavigate();
  const [showAuthModal, setShowAuthModal] = useState(false);
  const [isEnrolling, setIsEnrolling] = useState(false);
  
  const currentPrice = Number(course.price || 0);
  const isDiscountActive = course.discountPrice !== null && course.discountPrice !== undefined;
  const currentDiscountPrice = isDiscountActive ? Number(course.discountPrice) : currentPrice;
  
  // LOGIC MỚI: Chỉ coi là "Miễn phí" nếu giá gốc của nó đã là 0đ ngay từ đầu.
  // Các khóa có giá gốc nhưng giảm về 0đ sẽ được coi là khóa "Có phí đang sale 100%".
  const isFree = currentPrice === 0;
  const isPurchased = course.is_purchased;

  const checkAuth = () => {
    const token = localStorage.getItem('token');
    if (!token) {
      setShowAuthModal(true);
      return false;
    }
    return true;
  };

  // Nút Đăng ký cho khóa có phí (bao gồm cả khóa sale về 0đ) -> Chuyển sang Checkout
  const handleEnrollPaid = (e) => {
    e.stopPropagation();
    if (!checkAuth()) return;
    navigate(`/student/courses/${course.courseGroupId}/checkout`);
  };

  // Nút Đăng ký cho khóa miễn phí từ đầu -> Gọi API đăng ký luôn
  const handleEnrollFree = async (e) => {
    e.stopPropagation();
    if (!checkAuth()) return;

    setIsEnrolling(true);
    try {
      await CourseAPI.enrollCourse(course.courseGroupId);
      alert("Đăng ký thành công! Khóa học đã được thêm vào thư viện của bạn.");
      navigate(`/student/courses/${course.courseGroupId}/learn`);
    } catch (error) {
      alert(error.response?.data?.message || "Có lỗi xảy ra khi đăng ký.");
    } finally {
      setIsEnrolling(false);
    }
  };

  const handleGoToLearn = (e) => {
    e.stopPropagation();
    navigate(`/student/courses/${course.courseGroupId}/learn`);
  };

  const handleGoToDetail = (e) => {
    e.stopPropagation();
    navigate(`/student/courses/${course.courseGroupId}`);
  };

  const renderPrice = () => {
    // TH1: Khóa học gốc 0đ -> Hiện chữ "Miễn phí" xanh lá
    if (currentPrice === 0) {
      return <span className="text-green-600 font-bold text-lg">Miễn phí</span>;
    }

    // TH2: Có giảm giá (kể cả giảm về 0đ) -> Hiện giá sau giảm và giá gốc bị gạch
    if (isDiscountActive && currentDiscountPrice < currentPrice) {
      return (
        <div className="flex items-center gap-2">
          <span className="text-indigo-600 font-black text-lg">{currentDiscountPrice.toLocaleString()}đ</span>
          <span className="text-slate-400 text-xs line-through">{currentPrice.toLocaleString()}đ</span>
        </div>
      );
    }

    // TH3: Giá bình thường không sale
    return <span className="text-indigo-600 font-black text-lg">{currentPrice.toLocaleString()}đ</span>;
  };

  return (
    <>
      <div className="bg-white rounded-2xl overflow-hidden border border-slate-200 hover:shadow-xl transition-all group flex flex-col h-full relative">
        {badge && (
          <span className="absolute top-3 left-3 z-10 bg-indigo-600 text-white text-[10px] font-bold px-2 py-1 rounded-lg shadow-sm">
            {badge}
          </span>
        )}
        
        <div onClick={handleGoToDetail} className="aspect-video overflow-hidden bg-slate-100 relative cursor-pointer">
          <img src={course.thumbnail || 'https://via.placeholder.com/350x200?text=StudyHub'} alt={course.title} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
          {isPurchased && (
             <div className="absolute inset-0 bg-green-600/10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                <span className="bg-green-600 text-white text-xs font-bold px-3 py-1 rounded-full">Đã sở hữu</span>
             </div>
          )}
        </div>

        <div className="p-5 flex flex-col flex-1">
          <h3 onClick={handleGoToDetail} className="font-bold text-slate-800 line-clamp-2 mb-2 group-hover:text-indigo-600 h-12 cursor-pointer transition-colors">
            {course.title}
          </h3>
          <p className="text-xs text-slate-500 mb-4 flex items-center gap-1">👤 {course.student_count || 0} học viên</p>

          <div className="flex items-center gap-2 mb-5">{renderPrice()}</div>

          <div className="mt-auto pt-4 border-t border-slate-50">
            {isPurchased ? (
              <button onClick={handleGoToLearn} className="w-full py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-all flex justify-center items-center gap-2 shadow-md cursor-pointer">
                📖 Vào học ngay
              </button>
            ) : (
              <div className="grid grid-cols-2 gap-2">
                <button 
                  onClick={handleGoToLearn} 
                  className="py-2.5 bg-orange-50 text-orange-600 font-bold rounded-xl hover:bg-orange-100 border border-orange-200 transition-all text-sm cursor-pointer"
                >
                  {isFree ? 'Học ngay' : 'Học thử'}
                </button>
                <button 
                  onClick={isFree ? handleEnrollFree : handleEnrollPaid}
                  disabled={isEnrolling}
                  className={`py-2.5 font-bold rounded-xl transition-all text-sm shadow-md cursor-pointer ${isEnrolling ? 'bg-slate-400 text-white' : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-indigo-100'}`}
                >
                  {isEnrolling ? '...' : 'Đăng ký'}
                </button>
              </div>
            )}
          </div>
        </div>
      </div>

      {showAuthModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm px-4">
          <div className="bg-white p-6 rounded-2xl shadow-2xl max-w-sm w-full animate-fadeIn border border-slate-100 text-center">
            <div className="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4"><span className="text-3xl">🔒</span></div>
            <h3 className="text-xl font-black text-slate-800 mb-2">Bạn chưa đăng nhập</h3>
            <p className="text-sm text-slate-500 mb-6">Vui lòng đăng nhập để đăng ký và bắt đầu học nhé!</p>
            <div className="flex flex-col gap-3">
              <button onClick={() => navigate('/login')} className="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-md cursor-pointer">Đăng nhập</button>
              <button onClick={() => setShowAuthModal(false)} className="w-full py-2 text-sm font-semibold text-slate-400 hover:text-slate-600 cursor-pointer">Đóng lại</button>
            </div>
          </div>
        </div>
      )}
    </>
  );
};

export default CourseCard;