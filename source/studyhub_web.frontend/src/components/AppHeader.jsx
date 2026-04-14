import React from 'react';
import { NavLink } from 'react-router-dom';
import StudyHubLogo from '../assets/images/logo_Studyhub.jpg';

const AppHeader = () => {
  // Hàm tạo style cho các nút bấm (chuẩn Tailwind)
  // - w-12 h-12: Kích thước nút (48x48px)
  // - group: Để bắt sự kiện hover hiện Tooltip
  const navItemClasses = ({ isActive }) =>
    `group relative flex items-center justify-center w-12 h-12 rounded-xl transition-all duration-300 cursor-pointer ${
      isActive
        ? 'bg-white/30 shadow-inner scale-105' // Style khi đang ở trang đó (Active)
        : 'text-white hover:bg-white/20 hover:scale-105' // Style khi hover
    }`;

  return (
    // THÂN SIDEBAR: Cố định bên trái, chiều rộng 80px (w-20), chiều cao full màn hình (h-screen)
    <aside className="fixed inset-y-0 left-0 z-50 flex flex-col items-center w-20 py-6 bg-gradient-to-b from-blue-600 to-purple-700 shadow-2xl text-xl">
      
      {/* 1. LOGO */}
      <div className="relative flex items-center justify-center mb-8 cursor-pointer group hover:scale-110 transition-transform">
        <img
          src={StudyHubLogo}
          alt="StudyHub Logo"
          className="w-12 h-12 object-cover bg-white rounded-xl shadow-md p-0.5"
        />
        {/* Tooltip Logo */}
        <span className="absolute left-16 px-3 py-1.5 ml-2 text-sm font-bold text-gray-800 bg-white rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 shadow-lg whitespace-nowrap z-50 origin-left scale-95 group-hover:scale-100">
          StudyHub
          <div className="absolute top-1/2 -left-1 -translate-y-1/2 border-[6px] border-transparent border-r-white"></div>
        </span>
      </div>

      {/* 2. MENU ĐIỀU HƯỚNG CHÍNH */}
      <nav className="flex flex-col items-center w-full gap-4">
        {/* Helper Component để render Nav Item gọn gàng */}
        <NavItem to="/" icon="🏠" label="Home" getClasses={navItemClasses} />
        <NavItem to="/search" icon="🔍" label="Search" getClasses={navItemClasses} />
        <NavItem to="/assignments" icon="📄" label="Assignments" getClasses={navItemClasses} />
        <NavItem to="/documents" icon="📁" label="Documents" getClasses={navItemClasses} />
        <NavItem to="/classes" icon="📚" label="Classes" getClasses={navItemClasses} />
        <NavItem to="/courses" icon="📘" label="Courses" getClasses={navItemClasses} />
      </nav>

      {/* 3. MENU CÁ NHÂN (Nằm dưới đáy) */}
      <div className="flex flex-col items-center w-full gap-5 mt-auto">
        
        {/* Nút Đổi Ngôn Ngữ */}
        <button className="text-sm font-extrabold text-white transition-all hover:text-yellow-300 hover:scale-110">
          EN
        </button>

        {/* Nút Thông Báo */}
        <button className="relative flex items-center justify-center w-12 h-12 text-white transition-all rounded-xl hover:bg-white/20 hover:scale-105">
          🔔
          {/* Chấm đỏ thông báo */}
          <span className="absolute top-2.5 right-2.5 w-2.5 h-2.5 bg-red-500 border-2 border-purple-600 rounded-full"></span>
        </button>

        {/* Avatar User */}
        <div className="flex items-center justify-center w-10 h-10 font-bold text-blue-700 transition-all bg-white rounded-full shadow-lg cursor-pointer hover:ring-4 ring-white/30 hover:scale-105">
          H
        </div>

        {/* Nút Cài đặt (Có đường viền ngăn cách bên trên) */}
        <a href="#" className="flex items-center justify-center w-full pt-4 mt-2 text-white transition-all border-t border-white/20 hover:text-gray-300 hover:rotate-90">
          ⚙️
        </a>
      </div>
    </aside>
  );
};

// Component con để tái sử dụng code hiển thị nút menu + tooltip
const NavItem = ({ to, icon, label, getClasses }) => (
  <NavLink to={to} className={getClasses}>
    {icon}
    {/* Tooltip */}
    <span className="absolute left-16 px-3 py-1.5 ml-2 text-sm font-semibold text-gray-800 bg-white rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 shadow-lg whitespace-nowrap z-50 origin-left scale-95 group-hover:scale-100">
      {label}
      {/* Hình tam giác chỉ vào nút */}
      <div className="absolute top-1/2 -left-1 -translate-y-1/2 border-[6px] border-transparent border-r-white"></div>
    </span>
  </NavLink>
);

export default AppHeader;