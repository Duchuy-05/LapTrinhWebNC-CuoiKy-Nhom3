import React from 'react';
import { Outlet } from 'react-router-dom';
import AppHeader from '../components/AppHeader';

export default function AppLayout() {
  return (
    <div className="flex bg-gray-50 min-h-screen">
      {/* Thanh Sidebar bên trái */}
      <AppHeader />
      
      {/* NỘI DUNG CHÍNH (Có ml-20 để không bị Sidebar đè) */}
      <main className="flex-1 ml-20 p-8 w-full overflow-hidden"> 
         {/* <Outlet /> chính là nơi React Router sẽ nhúng file Courses.jsx vào */}
         <Outlet />
      </main>
    </div>
  );
}