import React from 'react';
import { Outlet } from 'react-router-dom';
import Header from '../components/Header';
import Sidebar from '../components/Sidebar';

export default function MainLayout({ mode }) {
  const teachMenu = [
    { to: '/lecturer/dashboard', icon: '📊', label: 'Thống kê' },
    { to: '/lecturer/courses', icon: '📘', label: 'Khóa học' },
    { to: '/lecturer/published-courses', icon: '📚', label: 'Khóa học đã xuất bản' },
    { to: '/lecturer/students', icon: '👥', label: 'Học viên' },
  ];

  return (
    <div className="min-h-screen bg-slate-50">
      {/* Header luôn hiển thị */}
      <Header mode={mode} />
      
      {/* CHỈ hiển thị Sidebar nếu là chế độ Giảng viên */}
      {mode === 'lecturer' && (
        <Sidebar menuItems={teachMenu} />
      )}

      <main className={`pt-16 transition-all min-h-screen ${mode === 'lecturer' ? 'pl-20 md:pl-64' : 'w-full'}`}>
        <div className={mode === 'student' ? 'max-w-7xl mx-auto px-4 py-8' : ''}>
           <Outlet />
        </div>
      </main>
    </div>
  );
}