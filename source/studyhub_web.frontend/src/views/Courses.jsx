import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi'; 

const Courses = () => {
  const navigate = useNavigate();
  const [courses, setCourses] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    fetchCourses();
  }, []);

  const fetchCourses = async () => {
    try {
      setIsLoading(true);
      const response = await CourseAPI.getLecturerCourses();
      const drafts = (response.data.data || []).filter(c => c.status !== 'PUBLISHED');
      setCourses(drafts);
    } catch (error) {
      console.error("Lỗi:", error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleAddNewCourse = async () => {
    const title = window.prompt("Nhập tên khóa học mới:");
    if (title && title.trim() !== "") {
      try {
        const response = await CourseAPI.createDraft(title.trim());
        const newCourseGroupId = response.data.data.courseGroupId;
        navigate(`/lecturer/courses/${newCourseGroupId}/edit`);
      } catch (error) {
        alert("Có lỗi xảy ra khi tạo khóa học!");
        console.error(error);
      }
    }
  };

  const handleEditCourse = (courseGroupId) => {
    navigate(`/lecturer/courses/${courseGroupId}/edit`);
  };

  const handlePublish = async (courseGroupId) => {
    if(window.confirm("Bạn có chắc chắn muốn xuất bản khóa học này?")) {
      try {
        await CourseAPI.publishCourse(courseGroupId);
        alert("Xuất bản thành công!");
        fetchCourses(); 
      } catch (error) {
        alert("Lỗi khi xuất bản!");
        console.error(error);
      }
    }
  };

  return (
    <div className="w-full">
      <div className="flex mb-8 border-b-2 border-slate-200">
        <button className="px-6 py-3 font-bold text-red-500 border-b-2 border-red-500 -mb-[2px]">
          Danh sách khóa học ({courses.length})
        </button>
      </div>

      <div className="flex items-center justify-between mb-8">
        <div className="relative w-80">
          <span className="absolute left-4 top-2.5 text-slate-400">🔍</span>
          <input type="text" placeholder="Tìm kiếm khóa học..." className="w-full py-2 pl-10 pr-4 transition-shadow border rounded-lg border-slate-300 outline-none focus:ring-2 focus:ring-blue-500/50" />
        </div>
        <button onClick={handleAddNewCourse} className="px-6 py-2.5 font-bold text-white bg-red-500 rounded-lg shadow-md hover:bg-red-600 transition-colors cursor-pointer">
          + Thêm khóa học mới
        </button>
      </div>

      {isLoading ? (
        <p className="text-slate-500">Đang tải dữ liệu...</p>
      ) : (
        <div className="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          {courses.map(course => (
            <div key={course.id || course.courseGroupId} className="flex flex-col overflow-hidden transition-all bg-white border border-gray-100 shadow-sm rounded-2xl hover:shadow-lg">
              {/* Hình ảnh */}
              <div className="relative h-44 overflow-hidden bg-slate-100 shrink-0">
                <img src={course.image || course.thumbnail || 'https://via.placeholder.com/300x200'} alt={course.title} className="object-cover w-full h-full" />
                <span className={`absolute top-4 -right-10 px-10 py-1.5 text-xs font-bold text-white rotate-45 shadow-sm ${course.status === 'PUBLISHED' ? 'bg-green-500' : 'bg-slate-700'}`}>
                  {course.status}
                </span>
              </div>
              
              {/* Nội dung thẻ */}
              <div className="p-5 flex flex-col flex-1">
                
                {/* HEADER: Tiêu đề (Trái) - Giá tiền (Phải) */}
                <div className="flex justify-between items-start mb-3 gap-3">
                  <h3 className="text-lg font-bold text-gray-800 line-clamp-2 flex-1" title={course.title}>
                    {course.title}
                  </h3>
                  <div className="flex flex-col items-end shrink-0 text-right">
                    {course.price > 0 ? (
                      <>
                        <span className="text-lg font-extrabold text-blue-600 leading-tight">
                          {/* Logic mới: Giá bán = Giá gốc - Giá giảm */}
                          {Number(course.price - (course.discountPrice || 0)).toLocaleString()} đ
                        </span>
                        {course.discountPrice > 0 && course.discountPrice < course.price && (
                          <span className="text-xs line-through text-slate-400 mt-0.5">
                            {Number(course.price).toLocaleString()} đ
                          </span>
                        )}
                      </>
                    ) : (
                      <span className="text-lg font-extrabold text-green-500 leading-tight">Miễn phí</span>
                    )}
                  </div>
                </div>

                {/* Mô tả */}
                <p className="text-sm text-slate-500 line-clamp-2 flex-1 mb-4">
                  {course.description || "Chưa có mô tả tổng quan cho khóa học này."}
                </p>
                
                {/* FOOTER: Số lượng Unit + Nút bấm */}
                <div className="mt-auto pt-4 border-t border-slate-100">
                  
                  {/* HIỂN THỊ SỐ UNIT Ở ĐÂY */}
                <div className="w-max inline-flex items-center gap-1.5 text-[11px] font-semibold text-blue-500 mb-4 bg-blue-50 py-1.5 px-3 rounded-md">
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                  </svg>
                  {course.unit_count || 0} Units
                </div>

                  <div className="space-y-2">
                    <button 
                      onClick={() => handleEditCourse(course.courseGroupId)}
                      className="w-full py-2.5 font-bold text-slate-700 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors cursor-pointer"
                    >
                      Chỉnh sửa
                    </button>

                    {course.status !== 'PUBLISHED' && (
                      <button 
                        onClick={() => handlePublish(course.courseGroupId)}
                        className="w-full py-2.5 font-bold text-white bg-red-500 rounded-xl hover:bg-red-600 transition-colors cursor-pointer shadow-sm shadow-red-200"
                      >
                        Xuất bản
                      </button>
                    )}
                  </div>
                </div>

              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default Courses;