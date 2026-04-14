import React, { useState, useEffect } from 'react';
import CourseAPI from '../services/courseApi';

const PublishedCourses = () => {
  const [courses, setCourses] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    fetchPublishedCourses();
  }, []);

  const fetchPublishedCourses = async () => {
    try {
      setIsLoading(true);
      const response = await CourseAPI.getLecturerCourses();
      // CHỈ LỌC NHỮNG KHÓA ĐÃ XUẤT BẢN
      const published = (response.data.data || []).filter(c => c.status === 'PUBLISHED');
      setCourses(published);
    } catch (error) {
      console.error("Lỗi khi tải khóa học:", error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleUnpublish = async (courseGroupId) => {
    if (window.confirm("Bạn có chắc chắn muốn ngừng xuất bản? Học viên sẽ không thể tìm thấy khóa học này nữa.")) {
      try {
        await CourseAPI.unpublishCourse(courseGroupId);
        alert("Đã ngừng xuất bản!");
        fetchPublishedCourses(); // Tải lại danh sách
      } catch (error) {
        alert("Lỗi khi thực hiện!");
      }
    }
  };

  return (
    <div className="w-full">
      <div className="flex mb-8 border-b-2 border-slate-200">
        <button className="px-6 py-3 font-bold text-blue-600 border-b-2 border-blue-600 -mb-[2px]">
          Khóa học đã xuất bản ({courses.length})
        </button>
      </div>

      {isLoading ? (
        <p className="text-slate-500">Đang tải...</p>
      ) : courses.length === 0 ? (
        <div className="text-center py-20 bg-white rounded-2xl border border-dashed border-slate-300">
          <p className="text-slate-400">Bạn chưa có khóa học nào được xuất bản.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          {courses.map(course => (
            <div key={course.courseGroupId} className="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
              <div className="relative h-48 overflow-hidden bg-slate-100">
                <img src={course.thumbnail || 'https://via.placeholder.com/300x200'} alt={course.title} className="object-cover w-full h-full" />
                <span className="absolute top-4 right-4 px-3 py-1 text-[10px] font-bold text-white bg-green-500 rounded-full">
                  LIVE
                </span>
              </div>
              <div className="p-5">
                <h3 className="mb-2 text-lg font-bold text-gray-800 line-clamp-1">{course.title}</h3>
                <p className="mb-6 text-sm text-slate-500 line-clamp-2">{course.description || "Không có mô tả"}</p>
                
                <button 
                  className="w-full py-2.5 font-bold text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors mb-2"
                  onClick={() => alert("Xem trang chi tiết học viên (Coming soon)")}
                >
                  Xem trang học tập
                </button>

                <button 
                  onClick={() => handleUnpublish(course.courseGroupId)}
                  className="w-full py-2.5 font-bold text-red-500 hover:underline text-sm"
                >
                  Ngừng xuất bản
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default PublishedCourses;