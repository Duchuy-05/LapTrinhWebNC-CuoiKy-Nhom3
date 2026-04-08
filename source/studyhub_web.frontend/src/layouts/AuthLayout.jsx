import React from 'react';
import { Outlet } from 'react-router-dom';
import '../Auth.css';

export default function AuthLayout() {
  return (
    <div className="auth-wrapper">
      {/* Các khối cầu background chuyển động */}
      <div className="bg-shape shape-1"></div>
      <div className="bg-shape shape-2"></div>
      <div className="bg-shape shape-3"></div>

      <div className="auth-glass-container">
        {/* Render route con ở đây */}
        <Outlet />
      </div>
    </div>
  );
}