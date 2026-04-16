import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import StudyHubLogo from '../assets/images/logo_Studyhub.jpg';

export default function CommonHeader({ mode }) {
  const [user, setUser] = useState(null);
  const navigate = useNavigate();

  // Lấy thông tin user từ localStorage khi Header render
  useEffect(() => {
    const storedUser = localStorage.getItem('user_data');
    if (storedUser) setUser(JSON.parse(storedUser));
  }, []);

  // ĐÃ SỬA LỖI Ở ĐÂY: So sánh với 'student' thay vì 'learn'
  const toggleMode = () => {
    if (mode === 'student') {
      navigate('/lecturer/dashboard'); // Đang ở Học viên -> Chuyển sang Giảng viên
    } else {
      navigate('/student/home'); // Đang ở Giảng viên -> Chuyển về Học viên
    }
  };

  // Hàm Đăng xuất
  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user_data');
    navigate('/login');
  };

  return (
    <header className="fixed top-0 right-0 left-0 h-16 bg-white border-b border-slate-200 z-50 flex items-center justify-between px-6 shadow-sm">
      {/* TRÁI: Logo (ĐÃ SỬA link điều hướng về /student/dashboard) */}
      <div className="flex items-center gap-3 cursor-pointer" onClick={() => navigate('/student/dashboard')}>
        <img src={StudyHubLogo} alt="Logo" className="w-10 h-10 rounded-lg" />
        <span className="text-xl font-black text-blue-600 tracking-tight">StudyHub</span>
      </div>

      {/* PHẢI: Thông báo & Avatar (Chứa Dropdown) */}
      <div className="flex items-center gap-4 pl-6">
        
        {/* Nút thông báo */}
        <button className="text-slate-400 hover:text-slate-600 relative p-2">
          🔔 <span className="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
        </button>
        
        {/* KHU VỰC AVATAR + MENU DROPDOWN */}
        <div className="relative group flex items-center justify-center py-2">
          
          {/* Hình Avatar */}
          <div className="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold shadow-md cursor-pointer group-hover:ring-2 ring-blue-500/50 transition-all">
            {user?.name?.charAt(0).toUpperCase() || 'H'}
          </div>

          {/* Cửa sổ Popup (Dropdown Menu) */}
          <div className="absolute right-0 top-full mt-1 w-72 bg-white rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 border border-slate-100 origin-top-right transform scale-95 group-hover:scale-100">
            
            {/* Phần 1: Thông tin User */}
            <div className="p-5 border-b border-slate-100">
              <p className="font-bold text-slate-800 text-lg truncate">
                {user?.name || 'Người dùng StudyHub'}
              </p>
              <p className="text-sm text-slate-500 truncate mt-0.5">
                {user?.email || 'Đang cập nhật...'}
              </p>
            </div>
            
            {/* Phần 2: Nút chuyển đổi Mode */}
            <div className="p-3">
              <button 
                onClick={toggleMode} 
                className="w-full flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors text-slate-700 font-medium text-sm cursor-pointer"
              >
                <span>Chế độ hiển thị:</span>
                <span className={`px-2.5 py-1.5 rounded-lg text-xs font-bold transition-colors ${
                  mode === 'lecturer' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'
                }`}>
                  {mode === 'lecturer' ? '👨‍🏫 Giảng viên' : '👨‍🎓 Học viên'}
                </span>
              </button>
            </div>
            
            {/* Phần 3: Nút Đăng xuất */}
            <div className="p-3 border-t border-slate-100">
              <button 
                onClick={handleLogout} 
                className="w-full text-left p-3 rounded-xl hover:bg-red-50 text-red-600 font-bold text-sm transition-colors cursor-pointer flex gap-2 items-center"
              >
                🚪 Đăng xuất khỏi hệ thống
              </button>
            </div>
          </div>
          {/* Kết thúc Cửa sổ Popup */}

        </div>
      </div>
    </header>
  );
}