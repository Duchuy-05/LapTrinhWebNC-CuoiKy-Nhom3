// layout để tái sử dụng cho trang dashboard (bao gồm Header, Sidebar....)
import React from 'react';
import AppHeader from '../components/AppHeader';
import './MainLayout.css';

const MainLayout = ({ children }) => {
  return (
    <div className="main-layout">
      <AppHeader />
      <main className="main-content">
        {children}
      </main>
    </div>
  );
};

export default MainLayout;