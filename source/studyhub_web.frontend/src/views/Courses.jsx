import React, { useState } from 'react';
import CourseModal from './AddCourses';

const Courses = () => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [courses, setCourses] = useState([
    { id: 1, title: 'Lập trình C nâng cao', description: 'Bài tập này giúp học sinh biết từ cơ bản đến nâng cao', image: 'src/assets/images/logo_laptrinhC.jpg', status: 'Draft' }
  ]);

  return (
    <div className="w-full">
      {/* Tabs */}
      <div className="flex mb-8 border-b-2 border-slate-200">
        <button className="px-6 py-3 font-bold text-red-500 border-b-2 border-red-500 -mb-[2px]">
          List course ({courses.length} Courses)
        </button>
      </div>

      {/* Toolbar */}
      <div className="flex items-center justify-between mb-8">
        <div className="relative w-80">
          <span className="absolute left-4 top-2.5 text-slate-400">🔍</span>
          <input type="text" placeholder="Search" className="w-full py-2 pl-10 pr-4 transition-shadow border rounded-lg border-slate-300 outline-none focus:ring-2 focus:ring-blue-500/50" />
        </div>
        <button onClick={() => setIsModalOpen(true)} className="px-6 py-2.5 font-bold text-white bg-red-500 rounded-lg shadow-md hover:bg-red-600 transition-colors">
          + Add new course
        </button>
      </div>

      {/* Grid Course */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        {courses.map(course => (
          <div key={course.id} className="overflow-hidden transition-shadow bg-white border border-gray-100 shadow-sm rounded-2xl hover:shadow-lg">
            <div className="relative h-48 bg-slate-100 overflow-hidden">
              <img src={course.image} alt={course.title} className="object-cover w-full h-full" />
              <span className="absolute top-4 -right-10 px-10 py-1.5 text-xs font-bold text-white bg-slate-700 rotate-45 shadow-sm">
                {course.status}
              </span>
            </div>
            <div className="p-5">
              <h3 className="mb-2 text-lg font-bold text-gray-800">{course.title}</h3>
              <p className="mb-6 text-sm text-slate-500 line-clamp-2">{course.description}</p>
              <button className="w-full py-2.5 font-bold text-slate-700 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">
                View Lesson
              </button>
            </div>
          </div>
        ))}
      </div>

      {isModalOpen && <CourseModal onClose={() => setIsModalOpen(false)} />}
    </div>
  );
};

export default Courses;