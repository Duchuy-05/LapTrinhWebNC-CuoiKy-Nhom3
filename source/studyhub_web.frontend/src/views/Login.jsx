import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import googleLogo from '../assets/images/logo_google.png'; 
import { useGoogleLogin } from '@react-oauth/google';

export default function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [errorMessage, setErrorMessage] = useState('');
  const navigate = useNavigate();

  // Khởi tạo hook useGoogleLogin
  const loginWithGoogle = useGoogleLogin({
    onSuccess: async (tokenResponse) => {
      try {
        // Lưu ý: Dùng tokenResponse.access_token thay vì credential
        const response = await fetch('http://localhost:8000/api/login/google', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ token: tokenResponse.access_token })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
          setErrorMessage(data.message || 'Lỗi đăng nhập Google từ Server!');
          return;
        }

        localStorage.setItem('token', data.token);
        localStorage.setItem('user_data', JSON.stringify(data.user));
        navigate('/student/home'); 
        
      } catch (error) {
        setErrorMessage('Không thể kết nối đến Server.');
      }
    },
    onError: () => {
      setErrorMessage('Đăng nhập Google thất bại trên trình duyệt.');
    }
  });

  const handleLogin = async (e) => {
    // ... (Giữ nguyên logic đăng nhập bằng form của bạn)
    e.preventDefault(); 
    setErrorMessage(''); 
    try {
      const response = await fetch('http://localhost:8000/api/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ email, password })
      });
      const data = await response.json();
      if (!response.ok) {
        setErrorMessage(data.message || 'Email hoặc mật khẩu không chính xác!');
        return;
      }
      localStorage.setItem('token', data.token);
      localStorage.setItem('user_data', JSON.stringify(data.user));
      navigate('/student/home'); 
    } catch (error) {
      setErrorMessage('Không thể kết nối đến Server.');
    }
  };

  return (
    <div className="relative flex items-center justify-center min-h-screen overflow-hidden bg-slate-900 font-sans">
      {/* Khối cầu phát sáng */}
      <div className="absolute w-[300px] h-[300px] bg-indigo-600/40 rounded-full blur-[100px] -top-20 -left-20 animate-pulse"></div>
      <div className="absolute w-[250px] h-[250px] bg-pink-500/30 rounded-full blur-[90px] bottom-10 -right-10"></div>
      <div className="absolute w-[200px] h-[200px] bg-cyan-500/20 rounded-full blur-[80px] top-1/3 left-1/3"></div>

      {/* Box Đăng nhập */}
      <div className="relative z-10 w-full max-w-md p-10 mx-4 bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl shadow-[0_25px_50px_-12px_rgba(0,0,0,0.5)]">
        <h2 className="mb-8 text-3xl font-bold text-center text-white">Chào mừng quay trở lại!</h2>
        {errorMessage && <div className="p-3 mb-6 text-sm text-center text-red-200 bg-red-500/20 rounded-xl">{errorMessage}</div>}
        
        <form onSubmit={handleLogin} className="flex flex-col gap-5">
          {/* ... (Giữ nguyên các input email/password của bạn) ... */}
          <div>
            <label className="block mb-2 text-sm font-medium text-slate-300">Email</label>
            <input type="email" required value={email} onChange={(e) => setEmail(e.target.value)}
              className="w-full px-4 py-3 text-white transition-all border outline-none bg-slate-800/50 border-white/10 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/50"
              placeholder="nhapemail@epu.edu.vn" />
          </div>

          <div>
            <label className="block mb-2 text-sm font-medium text-slate-300">Mật khẩu</label>
            <input type="password" required value={password} onChange={(e) => setPassword(e.target.value)}
              className="w-full px-4 py-3 text-white transition-all border outline-none bg-slate-800/50 border-white/10 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/50"
              placeholder="••••••••" />
          </div>

          <div className="flex items-center justify-between text-sm">
            <label className="flex items-center gap-2 cursor-pointer text-slate-400">
              <input type="checkbox" className="w-4 h-4 accent-indigo-500" /> Ghi nhớ
            </label>
            <a href="#" className="text-indigo-400 hover:text-indigo-300 hover:underline">Quên mật khẩu?</a>
          </div>

          <button type="submit" className="w-full py-3 mt-2 font-bold text-white transition-all bg-gradient-to-r from-indigo-600 to-indigo-500 rounded-xl hover:scale-[1.02] hover:shadow-lg hover:shadow-indigo-500/30">
            Đăng nhập
          </button>
        </form>

        <div className="flex items-center gap-3 my-6 text-sm text-slate-500">
          <div className="flex-1 h-px bg-white/10"></div><span>HOẶC</span><div className="flex-1 h-px bg-white/10"></div>
        </div>
        
        {/* Nút Đăng nhập Google đã được gắn sự kiện onClick */}
        <button 
          type="button" 
          onClick={() => loginWithGoogle()} 
          className="flex items-center justify-center w-full gap-3 py-3 font-semibold transition-all bg-white text-slate-900 rounded-xl hover:bg-slate-50 hover:scale-[1.02] cursor-pointer"
        >
          <img src={googleLogo} alt="Google" className="w-5 h-5" /> Đăng nhập với Google
        </button>

        <p className="mt-6 text-sm text-center text-slate-400">
          Chưa có tài khoản? <Link to="/register" className="font-bold text-indigo-400 hover:underline">Đăng ký ngay</Link>
        </p>
      </div>
    </div>
  );
}