import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi';
import CoursePricingEditor from '../components/CoursePricingEditor';

const PublishedCourses = () => {
  const navigate = useNavigate();
  const [courses, setCourses] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [savingId, setSavingId] = useState(null);

  useEffect(() => {
    fetchPublishedCourses();
  }, []);

  const fetchPublishedCourses = async () => {
    try {
      setIsLoading(true);
      const response = await CourseAPI.getLecturerCourses();
      const published = (response.data.data || []).filter(c => c.status === 'PUBLISHED');
      setCourses(published);
    } catch (error) {
      console.error('Lỗi khi tải khóa học:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleUnpublish = async (courseGroupId) => {
    if (window.confirm('Bạn có chắc chắn muốn ngừng xuất bản? Học viên sẽ không thể tìm thấy khóa học này nữa.')) {
      try {
        await CourseAPI.unpublishCourse(courseGroupId);
        alert('Đã ngừng xuất bản!');
        fetchPublishedCourses();
      } catch (error) {
        alert('Lỗi khi thực hiện!');
      }
    }
  };

  const handleSavePrice = async (courseGroupId, { discountPrice }) => {
    setSavingId(courseGroupId);
    try {
      await CourseAPI.updateCoursePrice(courseGroupId, { discountPrice });
      setCourses(prev =>
        prev.map(c =>
          c.courseGroupId === courseGroupId
            ? { ...c, discountPrice: discountPrice ?? null }
            : c
        )
      );
      alert(discountPrice !== null ? 'Đã áp dụng khuyến mãi!' : 'Đã xóa khuyến mãi!');
    } catch (error) {
      const msg = error.response?.data?.message || 'Cập nhật thất bại!';
      alert(msg);
    } finally {
      setSavingId(null);
    }
  };

  const getEffectivePrice = (course) => {
    if (course.discountPrice !== null && course.discountPrice !== undefined) {
      return course.discountPrice;
    }
    return course.price || 0;
  };

  return (
    <div className="w-full">
      <div className="flex mb-8 border-b-2 border-slate-200">
        <button className="px-6 py-3 font-bold text-blue-600 border-b-2 border-blue-600 -mb-[2px]">
          Khóa học đã xuất bản ({courses.length})
        </button>
      </div>

      {isLoading ? (
        <p className="text-slate-500">Đang tải dữ liệu...</p>
      ) : courses.length === 0 ? (
        <div className="text-center py-20 bg-white rounded-2xl border border-dashed border-slate-300">
          <p className="text-slate-400">Bạn chưa có khóa học nào đang hoạt động.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          {courses.map(course => {
            const originalPrice = course.price || 0;
            const discountPrice = course.discountPrice ?? null;
            const effectivePrice = getEffectivePrice(course);
            const isOnSale = discountPrice !== null;
            const isFree = effectivePrice === 0;

            return (
              <div key={course.courseGroupId} className="flex flex-col overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl transition-all hover:shadow-lg">
                
                {/* Hình ảnh */}
                <div className="relative h-44 overflow-hidden bg-slate-100 shrink-0">
                  <img src={course.thumbnail || 'https://via.placeholder.com/300x200'} alt={course.title} className="object-cover w-full h-full" />
                  <span className="absolute top-3 right-3 px-2.5 py-1 text-[10px] font-bold text-white bg-green-500 rounded-full shadow-sm">
                    LIVE
                  </span>
                  {isOnSale && (
                    <span className="absolute top-3 left-3 px-2.5 py-1 text-[10px] font-bold text-white bg-red-500 rounded-full shadow-sm flex items-center gap-1">
                      🔥 SALE
                    </span>
                  )}
                </div>

                {/* Nội dung thẻ */}
                <div className="p-5 flex flex-col flex-1">
                  
                  <div className="flex justify-between items-start mb-3 gap-3">
                    <h3 className="text-base font-bold text-gray-800 line-clamp-2 flex-1" title={course.title}>
                      {course.title}
                    </h3>
                    <div className="flex flex-col items-end shrink-0 text-right mt-0.5">
                      {isFree ? (
                        <>
                          <span className="text-base font-extrabold text-green-500 leading-tight uppercase">Miễn phí</span>
                          {originalPrice > 0 && (
                            <span className="text-xs line-through text-slate-400 mt-0.5">
                              {originalPrice.toLocaleString('vi-VN')}đ
                            </span>
                          )}
                        </>
                      ) : (
                        <>
                          <span className="text-base font-extrabold text-blue-600 leading-tight">
                            {effectivePrice.toLocaleString('vi-VN')}đ
                          </span>
                          {isOnSale && (
                            <span className="text-xs line-through text-slate-400 mt-0.5">
                              {originalPrice.toLocaleString('vi-VN')}đ
                            </span>
                          )}
                        </>
                      )}
                    </div>
                  </div>

                  <p className="text-sm text-slate-500 line-clamp-2 mb-4">
                    {course.description || 'Không có mô tả'}
                  </p>

                  <div className="w-max inline-flex items-center gap-1.5 text-[11px] font-semibold text-blue-500 mb-4 bg-blue-50 py-1.5 px-3 rounded-md">
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    {course.unit_count || 0} Units
                  </div>

                  <div className="mb-4">
                    <CoursePricingEditor
                      initialPrice={originalPrice}
                      initialDiscountPrice={discountPrice}
                      isSaving={savingId === course.courseGroupId}
                      onSave={(data) => handleSavePrice(course.courseGroupId, data)}
                    />
                  </div>

                  <div className="mt-auto pt-4 border-t border-slate-100 space-y-2">
                    <button
                      className="w-full py-2.5 font-bold text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors cursor-pointer"
                      onClick={() => navigate(`/lecturer/published-courses/${course.courseGroupId}/view`)}
                    >
                      Xem trang học tập
                    </button>
                    <button
                      onClick={() => handleUnpublish(course.courseGroupId)}
                      className="w-full py-2.5 font-bold text-red-500 hover:underline text-sm cursor-pointer"
                    >
                      Ngừng xuất bản
                    </button>
                  </div>

                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
};

export default PublishedCourses;