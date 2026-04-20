// File: src/components/CourseCard.jsx
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi';

const CourseCard = ({ course, badge }) => {
  const navigate = useNavigate();
  const [showAuthModal, setShowAuthModal] = useState(false);
  const [isEnrolling, setIsEnrolling] = useState(false);

  // ── Tính giá ────────────────────────────────────────────────────────────────
  // discountPrice = null  → chưa KM → học viên trả price gốc
  // discountPrice = <số>  → đang KM → học viên trả discountPrice
  const originalPrice = Number(course.price || 0);
  const isOnSale = course.discountPrice !== null && course.discountPrice !== undefined;
  const effectivePrice = isOnSale ? Number(course.discountPrice) : originalPrice;

  // Miễn phí = giá gốc là 0 (từ đầu không mất tiền)
  // Khóa sale về 0đ vẫn đi qua checkout (backend trả về 400 "dùng enroll")
  const isFree = originalPrice === 0;
  const isPurchased = course.is_purchased;

  // ── Auth check ───────────────────────────────────────────────────────────────
  const checkAuth = () => {
    const token = localStorage.getItem('token');
    if (!token) { setShowAuthModal(true); return false; }
    return true;
  };

  // ── Handlers ─────────────────────────────────────────────────────────────────
  const handleEnrollPaid = (e) => {
    e.stopPropagation();
    if (!checkAuth()) return;
    navigate(`/student/courses/${course.courseGroupId}/checkout`);
  };

  const handleEnrollFree = async (e) => {
    e.stopPropagation();
    if (!checkAuth()) return;
    setIsEnrolling(true);
    try {
      await CourseAPI.enrollCourse(course.courseGroupId);
      alert('Đăng ký thành công! Khóa học đã được thêm vào thư viện của bạn.');
      navigate(`/student/courses/${course.courseGroupId}/learn`);
    } catch (error) {
      alert(error.response?.data?.message || 'Có lỗi xảy ra khi đăng ký.');
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

  // ── Render giá ───────────────────────────────────────────────────────────────
  const renderPrice = () => {
    // Giá gốc = 0 → Miễn phí hoàn toàn
    if (originalPrice === 0) {
      return <span className="text-green-600 font-bold text-lg">Miễn phí</span>;
    }

    if (isOnSale) {
      return (
        <div className="flex items-center gap-2 flex-wrap">
          <span className="text-red-500 font-black text-lg">
            {effectivePrice === 0 ? 'Miễn phí' : `${effectivePrice.toLocaleString('vi-VN')}đ`}
          </span>
          <span className="text-slate-400 text-xs line-through">
            {originalPrice.toLocaleString('vi-VN')}đ
          </span>
          {effectivePrice > 0 && originalPrice > 0 && (
            <span className="text-[10px] font-black text-red-500 bg-red-50 px-1.5 py-0.5 rounded">
              -{Math.round(((originalPrice - effectivePrice) / originalPrice) * 100)}%
            </span>
          )}
        </div>
      );
    }

    // Giá bình thường
    return (
      <span className="text-indigo-600 font-black text-lg">
        {originalPrice.toLocaleString('vi-VN')}đ
      </span>
    );
  };

  return (
    <>
      <div className="bg-white rounded-2xl overflow-hidden border border-slate-200 hover:shadow-xl transition-all group flex flex-col h-full relative">

        {/* Badge do cha truyền vào (VD: "Mới", "Hot") */}
        {badge && (
          <span className="absolute top-3 left-3 z-10 bg-indigo-600 text-white text-[10px] font-bold px-2 py-1 rounded-lg shadow-sm">
            {badge}
          </span>
        )}

        {/* Cờ SALE — góc trên phải thumbnail */}
        {isOnSale && (
          <span className="absolute top-3 right-3 z-10 bg-red-500 text-white text-[10px] font-black px-2 py-1 rounded-lg shadow-md flex items-center gap-0.5">
            SALE
          </span>
        )}

        {/* Thumbnail */}
        <div onClick={handleGoToDetail} className="aspect-video overflow-hidden bg-slate-100 relative cursor-pointer">
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

        {/* Nội dung */}
        <div className="p-5 flex flex-col flex-1">
          <h3
            onClick={handleGoToDetail}
            className="font-bold text-slate-800 line-clamp-2 mb-1 group-hover:text-indigo-600 h-12 cursor-pointer transition-colors"
          >
            {course.title}
          </h3>
          <p className="text-xs text-indigo-400 font-semibold mb-2 truncate">
            {course.author_name || 'Giảng viên'}
          </p>
          <p className="text-xs text-slate-500 mb-4 flex items-center gap-1">
            👤 {course.student_count || 0} học viên
          </p>

          <div className="flex items-center gap-2 mb-5">{renderPrice()}</div>

          <div className="mt-auto pt-4 border-t border-slate-50">
            {isPurchased ? (
              <button
                onClick={handleGoToLearn}
                className="w-full py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-all flex justify-center items-center gap-2 shadow-md cursor-pointer"
              >
                Vào học ngay
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
                  className={`py-2.5 font-bold rounded-xl transition-all text-sm shadow-md cursor-pointer ${
                    isEnrolling
                      ? 'bg-slate-400 text-white'
                      : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-indigo-100'
                  }`}
                >
                  {isEnrolling ? '...' : 'Đăng ký'}
                </button>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Modal yêu cầu đăng nhập */}
      {showAuthModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm px-4">
          <div className="bg-white p-6 rounded-2xl shadow-2xl max-w-sm w-full border border-slate-100 text-center">
            <div className="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
            </div>
            <h3 className="text-xl font-black text-slate-800 mb-2">Bạn chưa đăng nhập</h3>
            <p className="text-sm text-slate-500 mb-6">Vui lòng đăng nhập để đăng ký và bắt đầu học nhé!</p>
            <div className="flex flex-col gap-3">
              <button
                onClick={() => navigate('/login')}
                className="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-md cursor-pointer"
              >
                Đăng nhập
              </button>
              <button
                onClick={() => setShowAuthModal(false)}
                className="w-full py-2 text-sm font-semibold text-slate-400 hover:text-slate-600 cursor-pointer"
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