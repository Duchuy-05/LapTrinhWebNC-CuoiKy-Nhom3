import React, { useState, useEffect } from 'react';
import CourseAPI from '../services/courseApi';

export default function MyCourses() {
  const [courses, setCourses] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchMyCourses = async () => {
      try {
        setIsLoading(true);
        const response = await CourseAPI.getMyCourses();
        setCourses(response.data.data || []);
      } catch (error) {
        console.error("Lỗi khi tải khóa học của tôi:", error);
      } finally {
        setIsLoading(false);
      }
    };
    fetchMyCourses();
  }, []);

  if (isLoading) {
    return <div className="p-8 text-center text-slate-500">Đang tải thư viện khóa học...</div>;
  }

  return (
    <div className="w-full max-w-7xl mx-auto p-6 pb-24">
      <div className="flex items-center justify-between mb-8 border-b pb-4">
        <div>
          <h1 className="text-3xl font-bold text-slate-800">Khóa học của tôi</h1>
          <p className="text-slate-500 mt-1">Bạn đã tham gia {courses.length} khóa học.</p>
        </div>
      </div>

      {courses.length === 0 ? (
        <div className="flex flex-col items-center justify-center py-20 bg-white rounded-3xl border-2 border-dashed border-slate-200">
          <span className="text-6xl mb-4">📚</span>
          <h2 className="text-xl font-bold text-slate-700">Thư viện trống</h2>
          <p className="text-slate-500 mt-2 mb-6">Bạn chưa đăng ký khóa học nào.</p>
          <button 
            onClick={() => window.location.href = '/student/home'}
            className="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-colors"
          >
            Khám phá ngay
          </button>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
          {courses.map(course => (
            <div key={course.courseGroupId} className="flex flex-col bg-white border border-slate-200 rounded-2xl overflow-hidden hover:shadow-xl transition-all group">
              {/* Thumbnail với thanh tiến độ giả định */}
              <div className="relative h-44 overflow-hidden">
                <img 
                  src={course.thumbnail || 'https://via.placeholder.com/500x300'} 
                  alt={course.title} 
                  className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                />
                <div className="absolute bottom-0 left-0 right-0 h-1.5 bg-slate-200">
                  <div className="h-full bg-green-500 w-[45%]"></div> {/* Tỉ lệ phần trăm học tập */}
                </div>
              </div>

              <div className="p-5 flex-1 flex flex-col">
                <h3 className="font-bold text-slate-800 line-clamp-2 mb-2 group-hover:text-indigo-600">
                  {course.title}
                </h3>
                <p className="text-xs text-slate-500 mb-4 line-clamp-1">Giảng viên: {course.author_name || "StudyHub Instructor"}</p>
                
                <div className="flex items-center justify-between mt-auto pt-4 border-t border-slate-50">
                   <span className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Đã hoàn thành 45%</span>
                </div>

                <button 
                  onClick={() => navigate(`/student/courses/${course.courseGroupId}/learn`)}
                  className="w-full mt-4 py-3 bg-indigo-50 text-indigo-600 font-bold rounded-xl hover:bg-indigo-600 hover:text-white transition-all"
                >
                  TIẾP TỤC HỌC
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}