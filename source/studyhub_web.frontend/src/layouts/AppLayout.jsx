import React from 'react';
import { Outlet, Navigate } from 'react-router-dom';
import AppHeader from '../components/AppHeader';

const AppLayout = () => {
  const token = localStorage.getItem('token');
  
  if (!token) {
    return <Navigate to="/login" replace />;
  }

  // Nếu đã đăng nhập, hiển thị layout chính
  return (
    <div className="min-h-screen flex flex-col bg-gray-50">
      
      {/* AppHeader chung */}
     

      {/* Vùng chứa nội dung động (View) */}
      <Outlet />

    </div>
  );
};

export default AppLayout;