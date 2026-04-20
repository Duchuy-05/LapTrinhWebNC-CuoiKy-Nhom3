import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import StudyHubLogo from '../assets/images/logo_Studyhub.jpg';

export default function CommonHeader({ mode }) {
  const [user, setUser] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    const storedUser = localStorage.getItem('user_data');
    if (storedUser) setUser(JSON.parse(storedUser));
  }, []);

  const toggleMode = () => {
    if (mode === 'student') navigate('/lecturer/dashboard');
    else navigate('/student/home');
  };

  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user_data');
    setUser(null);
    navigate('/student/home');
  };

  return (
    <header className="fixed top-0 right-0 left-0 h-16 bg-white border-b border-slate-200 z-50 flex items-center justify-between px-6 shadow-sm">
      {/* TRÁI: Logo (link điều hướng về /student/dashboard) */}
      <div className="flex items-center gap-3 cursor-pointer" onClick={() => navigate('/student/dashboard')}>
      {/* TRÁI: Logo
    </div>  <div className="flex items-center gap-3 cursor-pointer" onClick={() => navigate('/student/home')}> */}
        <img src={StudyHubLogo} alt="Logo" className="w-10 h-10 rounded-lg" />
        <span className="text-xl font-black text-blue-600 tracking-tight">StudyHub</span>
      </div>

      {/* PHẢI: Khu vực người dùng */}
      <div className="flex items-center gap-4">
        
        {user ? (
          /* TRƯỜNG HỢP: ĐÃ ĐĂNG NHẬP */
          <>
            
            <div className="relative group flex items-center justify-center py-2">
              <div className="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold shadow-md cursor-pointer group-hover:ring-2 ring-blue-500/50 transition-all">
                {user?.name?.charAt(0).toUpperCase() || 'H'}
              </div>

              <div className="absolute right-0 top-full mt-1 w-72 bg-white rounded-2xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 border border-slate-100 origin-top-right transform scale-95 group-hover:scale-100">
                <div className="p-5 border-b border-slate-100">
                  <p className="font-bold text-slate-800 text-lg truncate">{user?.name}</p>
                  <p className="text-sm text-slate-500 truncate mt-0.5">{user?.email}</p>
                </div>
                
                <div className="p-3 space-y-1">
                  {/* CHỈ HIỆN "Khóa học của tôi" ở chế độ Học viên */}
                  {mode === 'student' && (
                    <button 
                      onClick={() => navigate('/student/my-courses')}
                      className="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-blue-50 text-blue-700 font-bold text-sm transition-colors cursor-pointer"
                    >
                      <span>📖 Khóa học của tôi</span>
                    </button>
                  )}

                  <button 
                    onClick={toggleMode} 
                    className="w-full flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors text-slate-700 font-medium text-sm cursor-pointer"
                  >
                    <span>Chế độ hiển thị:</span>
                    <span className={`px-2.5 py-1.5 rounded-lg text-xs font-bold ${mode === 'lecturer' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'}`}>
                      {mode === 'lecturer' ? '👨‍🏫 Giảng viên' : '👨‍🎓 Học viên'}
                    </span>
                  </button>
                </div>
                
                <div className="p-3 border-t border-slate-100">
                  <button onClick={handleLogout} className="w-full text-left p-3 rounded-xl hover:bg-red-50 text-red-600 font-bold text-sm transition-colors cursor-pointer flex gap-2 items-center">
                    🚪 Đăng xuất
                  </button>
                </div>
              </div>
            </div>
          </>
        ) : (
          /* TRƯỜNG HỢP: CHƯA ĐĂNG NHẬP */
          <div className="flex items-center gap-3">
            <button 
              onClick={() => navigate('/login')}
              className="px-4 py-2 text-sm font-bold text-slate-600 hover:text-blue-600 transition-colors cursor-pointer"
            >
              Đăng nhập
            </button>
            <button 
              onClick={() => navigate('/register')}
              className="px-6 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-full hover:bg-blue-700 shadow-md shadow-blue-100 transition-all active:scale-95 cursor-pointer"
            >
              Đăng ký
            </button>
          </div>
        )}
      </div>
    </header>
  );
}