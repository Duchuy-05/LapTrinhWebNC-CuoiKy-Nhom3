import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi'; // Import API Class

const Courses = () => {
  const navigate = useNavigate();
  const [courses, setCourses] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  // 1. Gọi API lấy danh sách khóa học khi vừa vào trang
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

  // 2. Gọi API Tạo bản nháp mới
  const handleAddNewCourse = async () => {
    const title = window.prompt("Nhập tên khóa học mới:");
    
    if (title && title.trim() !== "") {
      try {
        const response = await CourseAPI.createDraft(title.trim());
        // Lấy ID gốc (courseGroupId) từ backend trả về để chuyển hướng
        const newCourseGroupId = response.data.data.courseGroupId;
        navigate(`/lecturer/courses/${newCourseGroupId}/edit`);
      } catch (error) {
        alert("Có lỗi xảy ra khi tạo khóa học!");
        console.error(error);
      }
    }
  };

  // Chuyển hướng sang trang Edit (Dùng courseGroupId)
  const handleEditCourse = (courseGroupId) => {
    navigate(`/lecturer/courses/${courseGroupId}/edit`);
  };

  // 3. Gọi API Xuất bản khóa học từ ngoài danh sách
  const handlePublish = async (courseGroupId) => {
    if(window.confirm("Bạn có chắc chắn muốn xuất bản khóa học này?")) {
      try {
        await CourseAPI.publishCourse(courseGroupId);
        alert("Xuất bản thành công!");
        fetchCourses(); // Load lại danh sách để cập nhật trạng thái
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
          List course ({courses.length} Courses)
        </button>
      </div>

      <div className="flex items-center justify-between mb-8">
        <div className="relative w-80">
          <span className="absolute left-4 top-2.5 text-slate-400">🔍</span>
          <input type="text" placeholder="Search" className="w-full py-2 pl-10 pr-4 transition-shadow border rounded-lg border-slate-300 outline-none focus:ring-2 focus:ring-blue-500/50" />
        </div>
        <button onClick={handleAddNewCourse} className="px-6 py-2.5 font-bold text-white bg-red-500 rounded-lg shadow-md hover:bg-red-600 transition-colors">
          + Add new course
        </button>
      </div>

      {isLoading ? (
        <p>Đang tải dữ liệu...</p>
      ) : (
        <div className="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          {courses.map(course => (
            <div key={course.id || course.courseGroupId} className="overflow-hidden transition-shadow bg-white border border-gray-100 shadow-sm rounded-2xl hover:shadow-lg">
              <div className="relative h-48 overflow-hidden bg-slate-100">
                <img src={course.image || course.thumbnail} alt={course.title} className="object-cover w-full h-full" />
                <span className={`absolute top-4 -right-10 px-10 py-1.5 text-xs font-bold text-white rotate-45 shadow-sm ${course.status === 'PUBLISHED' ? 'bg-green-500' : 'bg-slate-700'}`}>
                  {course.status}
                </span>
              </div>
              <div className="p-5">
                <h3 className="mb-2 text-lg font-bold text-gray-800 line-clamp-1">{course.title}</h3>
                <p className="mb-6 text-sm text-slate-500 line-clamp-2">{course.description || "Chưa có mô tả"}</p>
                
                <button 
                  onClick={() => handleEditCourse(course.courseGroupId)}
                  className="w-full py-2.5 font-bold text-slate-700 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors"
                >
                  Chỉnh sửa
                </button>

                {course.status !== 'PUBLISHED' && (
                  <button 
                    onClick={() => handlePublish(course.courseGroupId)}
                    className="w-full mt-2 py-2.5 font-bold text-white bg-red-500 rounded-xl hover:bg-red-600 transition-colors"
                  >
                    Xuất bản
                  </button>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default Courses;