// File: src/components/CourseCard.jsx
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi';

const CourseCard = ({ course, badge }) => {
  const navigate = useNavigate();
  const [showAuthModal, setShowAuthModal] = useState(false);
  
  // Thêm State để điều khiển Modal Thành công
  const [showSuccessModal, setShowSuccessModal] = useState(false); 
  const [isEnrolling, setIsEnrolling] = useState(false);

  // ── Tính giá ────────────────────────────────────────────────────────────────
  const originalPrice = Number(course.price || 0);
  const isOnSale = course.discountPrice !== null && course.discountPrice !== undefined;
  const effectivePrice = isOnSale ? Number(course.discountPrice) : originalPrice;

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
      
      // THAY ĐỔI: Tắt alert() và mở Modal Xịn
      setShowSuccessModal(true); 
      
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

    return (
      <span className="text-indigo-600 font-black text-lg">
        {originalPrice.toLocaleString('vi-VN')}đ
      </span>
    );
  };

  return (
    <>
      <div className="bg-white rounded-2xl overflow-hidden border border-slate-200 hover:shadow-xl transition-all group flex flex-col h-full relative">

        {badge && (
          <span className="absolute top-3 left-3 z-10 bg-indigo-600 text-white text-[10px] font-bold px-2 py-1 rounded-lg shadow-sm">
            {badge}
          </span>
        )}

        {isOnSale && (
          <span className="absolute top-3 right-3 z-10 bg-red-500 text-white text-[10px] font-black px-2 py-1 rounded-lg shadow-md flex items-center gap-0.5">
            SALE
          </span>
        )}

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
          <div className="flex items-center gap-2 mb-4 text-xs text-slate-500">
            <div className="flex items-center" title={`${course.rating_count || 0} đánh giá`}>
              {/* Icon Ngôi sao */}
              <svg className="w-3.5 h-3.5 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              <span className="font-bold text-slate-700">
                {course.rating_score ? Number(course.rating_score).toFixed(1) : '0.0'}
              </span>
              <span className="text-slate-400 ml-1">
                ({course.rating_count || 0})
              </span>
            </div>
            
            <span className="text-slate-300">•</span>
            
            <span className="flex items-center gap-1">
              👤 {course.student_count || 0} học viên
            </span>
          </div>

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
            <h3 className="text-xl font-black text-slate-800 mb-2 mt-4">Bạn chưa đăng nhập</h3>
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

      {/* MODAL ĐĂNG KÝ THÀNH CÔNG (XỊN) */}
      {showSuccessModal && (
        <div className="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4 transition-all duration-300">
          <div className="bg-white p-8 rounded-3xl shadow-2xl max-w-sm w-full border border-slate-100 text-center transform scale-100">
            
            {/* Icon Checkmark mượt mà */}
            <div className="w-20 h-20 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
              <svg className="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
              </svg>
            </div>
            
            <h3 className="text-2xl font-black text-slate-800 mb-2">Tuyệt vời! 🎉</h3>
            <p className="text-sm text-slate-500 mb-8 leading-relaxed">
              Bạn đã đăng ký thành công khóa học <br/>
              <strong className="text-indigo-600 text-base">{course.title}</strong>.<br/>
              Khóa học đã nằm trong thư viện của bạn.
            </p>
            
            <div className="flex flex-col gap-3">
              {/* Nút điều hướng vào học ngay */}
              <button
                onClick={() => {
                  setShowSuccessModal(false);
                  navigate(`/student/courses/${course.courseGroupId}/learn`);
                }}
                className="w-full py-3.5 bg-green-500 text-white font-bold rounded-xl hover:bg-green-600 shadow-lg shadow-green-200 transition-all cursor-pointer"
              >
                Vào học ngay
              </button>
              
              {/* Nút đóng */}
              <button
                onClick={() => setShowSuccessModal(false)}
                className="w-full py-2.5 text-sm font-bold text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl hover:bg-slate-100 transition-all cursor-pointer"
              >
                Để học sau
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
};

export default CourseCard;