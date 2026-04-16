import React from 'react';
import { Outlet } from 'react-router-dom';
import Header from '../components/Header';
import Sidebar from '../components/Sidebar';

export default function MainLayout({ mode }) {
  // Menu dành cho việc Học
  const learnMenu = [
    { to: '/student/home', icon: '🏠', label: 'Trang chủ' },
    { to: '/student/my-courses', icon: '📖', label: 'Khóa học của tôi' },
  ];

  // Menu dành cho việc Dạy
  const teachMenu = [
    { to: '/lecturer/dashboard', icon: '📊', label: 'Thống kê' },
    { to: '/lecturer/courses', icon: '📘', label: 'Khóa học' },
    { to: '/lecturer/published-courses', icon: '📚', label: 'Khóa học đã xuất bản' },
    { to: '/lecturer/students', icon: '👥', label: 'Học viên' },
  ];

  return (
    <div className="min-h-screen bg-slate-50">
      {/* Truyền mode vào Header để nó biết đang ở chế độ nào */}
      <Header mode={mode} />
      
      {/* Nạp đúng menu vào Sidebar */}
      <Sidebar menuItems={mode === 'lecturer' ? teachMenu : learnMenu} />

      {/* Nội dung chính */}
      <main className="pl-20 md:pl-64 pt-16 transition-all min-h-screen">
        <Outlet />
      </main>
    </div>
  );
}