import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi';

export default function MyCourses() {
  const navigate = useNavigate();
  const [courses, setCourses] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchMyCourses = async () => {
      try {
        setIsLoading(true);
        const response = await CourseAPI.getMyCourses();
        // Giả sử API trả về mảng khóa học có kèm trường 'progress'
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
    return <div className="p-8 text-center text-slate-500 font-medium">Đang tải thư viện khóa học...</div>;
  }

  return (
    <div className="w-full max-w-7xl mx-auto p-6 pb-24">
      <div className="flex items-center justify-between mb-8 border-b pb-4">
        <div>
          <h1 className="text-3xl font-bold text-slate-800 tracking-tight">Khóa học của tôi</h1>
          <p className="text-slate-500 mt-1">Bạn đang tham gia {courses.length} khóa học.</p>
        </div>
      </div>

      {courses.length === 0 ? (
        <div className="flex flex-col items-center justify-center py-20 bg-white rounded-3xl border-2 border-dashed border-slate-200">
          <span className="text-6xl mb-4">📚</span>
          <h2 className="text-xl font-bold text-slate-700">Thư viện trống</h2>
          <p className="text-slate-500 mt-2 mb-6">Bạn chưa đăng ký khóa học nào.</p>
          <button 
            onClick={() => navigate('/student/home')}
            className="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-100 cursor-pointer"
          >
            Khám phá ngay
          </button>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
          {courses.map(course => (
            <div key={course.courseGroupId} className="flex flex-col bg-white border border-slate-200 rounded-2xl overflow-hidden hover:shadow-xl transition-all group">
              
              {/* THUMBNAIL: Đã thêm onClick và cursor-pointer */}
              <div 
                onClick={() => navigate(`/student/courses/${course.courseGroupId}`)}
                className="relative h-44 overflow-hidden bg-slate-100 cursor-pointer"
                title="Xem chi tiết khóa học"
              >
                <img 
                  src={course.thumbnail || 'https://via.placeholder.com/500x300'} 
                  alt={course.title} 
                  className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                />
                <div className="absolute bottom-0 left-0 right-0 h-1.5 bg-slate-200/80">
                  <div 
                    className="h-full bg-green-500 transition-all duration-700 ease-out"
                    style={{ width: `${course.progress || 0}%` }}
                  ></div>
                </div>
              </div>

              <div className="p-5 flex-1 flex flex-col">
                {/* TÊN KHÓA HỌC: Đã thêm onClick và cursor-pointer */}
                <h3 
                  onClick={() => navigate(`/student/courses/${course.courseGroupId}`)}
                  className="font-bold text-slate-800 line-clamp-2 mb-2 group-hover:text-indigo-600 transition-colors cursor-pointer"
                  title="Xem chi tiết khóa học"
                >
                  {course.title}
                </h3>
                
                <p className="text-xs text-slate-400 mb-4 line-clamp-1">Giảng viên: {course.author_name || "StudyHub Instructor"}</p>
                
                <div className="flex items-center justify-between mt-auto pt-4 border-t border-slate-50">
                   <div className="flex flex-col gap-1">
                      <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tiến độ</span>
                      <span className="text-sm font-bold text-slate-700">{course.progress || 0}%</span>
                   </div>
                   {course.progress === 100 && (
                     <span className="text-xl" title="Đã hoàn thành">🏆</span>
                   )}
                </div>

                {/* NÚT HỌC: Dẫn thẳng vào trang học (CoursePlayer) */}
                <button 
                  onClick={() => navigate(`/student/courses/${course.courseGroupId}/learn`)}
                  className="w-full mt-4 py-3 bg-indigo-50 text-indigo-600 font-bold rounded-xl hover:bg-indigo-600 hover:text-white transition-all shadow-sm active:scale-95 cursor-pointer"
                >
                  {course.progress > 0 ? 'TIẾP TỤC HỌC' : 'BẮT ĐẦU HỌC'}
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}