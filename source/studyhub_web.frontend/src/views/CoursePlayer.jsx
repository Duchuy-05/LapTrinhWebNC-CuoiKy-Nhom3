import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi';

export default function CoursePlayer() {
  const { courseId } = useParams();
  const navigate = useNavigate();
  const [course, setCourse] = useState(null);
  const [activeItem, setActiveItem] = useState(null);
  const [accessMode, setAccessMode] = useState('loading');

  useEffect(() => {
    const fetchContent = async () => {
      try {
        const response = await CourseAPI.getCourseLearningContent(courseId);
        const { data, access } = response.data;
        setCourse(data);
        setAccessMode(access);
        
        // Tự động chọn bài học đầu tiên
        if (data.courseData?.[0]?.items?.[0]) {
          setActiveItem(data.courseData[0].items[0]);
        }
      } catch (err) {
        console.error("Lỗi tải nội dung học:", err);
      }
    };
    fetchContent();
  }, [courseId]);

  if (!course) return <div className="p-10 text-center">Đang chuẩn bị bài học...</div>;

  return (
    <div className="flex h-screen bg-white">
      {/* Sidebar danh sách bài học */}
      <div className="w-80 border-r flex flex-col bg-slate-50">
        <div className="p-4 border-b font-bold text-lg text-slate-800">
          Nội dung khóa học
          {accessMode === 'trial' && (
            <span className="ml-2 text-xs bg-orange-100 text-orange-600 px-2 py-0.5 rounded">Học thử</span>
          )}
        </div>
        <div className="flex-1 overflow-y-auto">
          {course.courseData.map(unit => (
            <div key={unit.id}>
              <div className="p-3 bg-slate-200/50 text-sm font-bold text-slate-600">{unit.title}</div>
              {unit.items.map(item => (
                <div 
                  key={item.id}
                  onClick={() => setActiveItem(item)}
                  className={`p-4 text-sm cursor-pointer border-b transition-colors ${activeItem?.id === item.id ? 'bg-blue-50 text-blue-600 border-l-4 border-l-blue-600' : 'hover:bg-slate-100 text-slate-700'}`}
                >
                  {item.title}
                </div>
              ))}
            </div>
          ))}
        </div>
      </div>

      <div className="flex-1 overflow-y-auto bg-white flex flex-col">
        {accessMode === 'trial' && (
          <div className="bg-indigo-600 text-white p-3 text-center text-sm font-medium flex justify-center items-center gap-4">
            <span>Bạn đang xem các bài giảng học thử. Đăng ký ngay để mở khóa toàn bộ nội dung!</span>
            <button className="bg-white text-indigo-600 px-4 py-1 rounded-full font-bold">Mua ngay</button>
          </div>
        )}

        <div className="max-w-4xl mx-auto w-full p-8">
          <h1 className="text-2xl font-bold mb-8">{activeItem?.title}</h1>
          <div className="space-y-6">
            {(course.blocks[activeItem?.id] || []).map(block => (
              <div key={block.id}>
                {/* Tái sử dụng logic render từ PublishedCourseViewer */}
                {block.type === 'text' && <div className="prose max-w-none">{block.content}</div>}
                {block.type === 'image' && <img src={block.content} className="rounded-xl shadow-sm" />}
                {block.type === 'video' && (
                  <div className="aspect-video bg-black rounded-xl overflow-hidden shadow-lg">
                    {/* Logic iframe Youtube hoặc HTML5 video */}
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}