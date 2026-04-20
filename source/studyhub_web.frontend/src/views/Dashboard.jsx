import React from 'react';
import { Link } from 'react-router-dom'; 

const Dashboard = () => {
  // Đã sửa lại toàn bộ 'path' để khớp với Sidebar trong MainLayout
  const features = [
    { id: 1, title: 'Search', icon: '🔍'}, 
    { id: 2, title: 'Khóa học', icon: '📘', path: '/lecturer/courses' },
    { id: 3, title: 'Khóa học đã xuất bản', icon: '📚', path: '/lecturer/published-courses' },
    { id: 4, title: 'Thống kê', icon: '📊', path: '/lecturer/statistics' },
    { id: 5, title: 'Học viên', icon: '👥', path: '/lecturer/students' },
  ];

  return (
    <div className="flex flex-col gap-8 w-full max-w-6xl mx-auto">
      <h1 className="text-3xl font-bold text-center text-gray-800">Home screen</h1>
      
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        {features.map((item) => (
          <Link 
            key={item.id} 
            to={item.path} 
            className={`bg-white rounded-2xl p-8 flex items-center justify-center gap-4 shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all cursor-pointer ${item.isWide ? 'col-span-2 md:col-span-2 flex-row justify-start pl-12' : 'flex-col'}`}
          >
            <div className="text-4xl">{item.icon}</div>
            <div className="text-lg font-semibold text-gray-700">{item.title}</div>
          </Link>
        ))}
      </div>
    </div>
  );
};

export default Dashboard;