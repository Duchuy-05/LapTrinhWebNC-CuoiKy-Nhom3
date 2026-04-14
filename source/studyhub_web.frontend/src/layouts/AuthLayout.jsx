import React from 'react';
import { Outlet } from 'react-router-dom';

export default function AuthLayout() {
  return (
    <div className="relative flex items-center justify-center min-h-screen overflow-hidden bg-slate-900 font-sans">
      {/* Các khối cầu phát sáng (thay thế cho animation cũ) */}
      <div className="absolute w-[300px] h-[300px] bg-indigo-600/40 rounded-full blur-[100px] -top-20 -left-20 animate-pulse"></div>
      <div className="absolute w-[250px] h-[250px] bg-pink-500/30 rounded-full blur-[90px] bottom-10 -right-10"></div>
      <div className="absolute w-[200px] h-[200px] bg-cyan-500/20 rounded-full blur-[80px] top-1/3 left-1/3"></div>

      {/* Khung kính Glassmorphism */}
      <div className="relative z-10 w-full max-w-md p-10 mx-4 bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl shadow-[0_25px_50px_-12px_rgba(0,0,0,0.5)]">
        <Outlet />
      </div>
    </div>
  );
}