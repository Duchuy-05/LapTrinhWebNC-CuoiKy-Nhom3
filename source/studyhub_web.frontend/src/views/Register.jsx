import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useGoogleLogin } from '@react-oauth/google';
import googleLogo from '../assets/images/logo_google.png';
import VerifyEmail from './VerifyEmail';

export default function Register() {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [errorMessage, setErrorMessage] = useState('');
  const [passwordError, setPasswordError] = useState('');
  const [loading, setLoading] = useState(false);

  // Trạng thái chờ xác thực OTP
  const [needsVerification, setNeedsVerification] = useState(false);
  const [pendingEmail, setPendingEmail] = useState('');

  const navigate = useNavigate();

  // ⚠️ Hook phải khai báo trước mọi conditional return (Rules of Hooks)
  const registerWithGoogle = useGoogleLogin({
    onSuccess: async (tokenResponse) => {
      try {
        const response = await fetch('http://localhost:8000/api/login/google', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ token: tokenResponse.access_token })
        });
        const data = await response.json();
        if (!response.ok) {
          setErrorMessage(data.message || 'Lỗi xác thực Google từ Server!');
          return;
        }
        localStorage.setItem('token', data.token);
        localStorage.setItem('user_data', JSON.stringify(data.user));
        navigate('/student/home');
      } catch {
        setErrorMessage('Không thể kết nối đến Server.');
      }
    },
    onError: () => setErrorMessage('Đăng ký Google thất bại trên trình duyệt.')
  });

  const handleRegister = async (e) => {
    e.preventDefault();
    setErrorMessage('');
    setPasswordError('');

    if (
      password.length < 6 ||
      !/[A-Z]/.test(password) ||
      !/[!@#$%^&*(),.?":{}|<>\-_]/.test(password)
    ) {
      setPasswordError('Mật khẩu phải có ít nhất 6 ký tự, chứa 1 chữ in hoa và 1 ký tự đặc biệt!');
      return;
    }
    if (password !== passwordConfirmation) {
      setErrorMessage('Mật khẩu nhập lại không khớp!');
      return;
    }

    setLoading(true);
    try {
      const response = await fetch('http://localhost:8000/api/register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ name, email, password, password_confirmation: passwordConfirmation })
      });
      const data = await response.json();

      if (!response.ok) {
        const errorMsg = data.errors
          ? Object.values(data.errors)[0][0]
          : (data.message || 'Đăng ký thất bại!');
        setErrorMessage(errorMsg);
        return;
      }

      // Backend trả về needs_verification → chuyển sang màn OTP
      if (data.status === 'needs_verification') {
        setPendingEmail(data.email);
        setNeedsVerification(true);
      }
    } catch {
      setErrorMessage('Không thể kết nối đến Server.');
    } finally {
      setLoading(false);
    }
  };

  // Xác thực OTP thành công
  const handleVerifySuccess = () => {
    navigate('/student/home');
  };

  // Quay lại form đăng ký
  const handleBackToRegister = () => {
    setNeedsVerification(false);
    setPendingEmail('');
  };

  // Đang ở bước nhập OTP → render VerifyEmail
  if (needsVerification) {
    return (
      <VerifyEmail
        email={pendingEmail}
        onSuccess={handleVerifySuccess}
        onBack={handleBackToRegister}
      />
    );
  }

  return (
    <div className="relative flex items-center justify-center min-h-screen overflow-hidden bg-slate-900 font-sans">
      <div className="absolute w-[300px] h-[300px] bg-indigo-600/40 rounded-full blur-[100px] -top-20 -left-20 animate-pulse"></div>
      <div className="absolute w-[250px] h-[250px] bg-pink-500/30 rounded-full blur-[90px] bottom-10 -right-10"></div>
      <div className="absolute w-[200px] h-[200px] bg-cyan-500/20 rounded-full blur-[80px] top-1/3 left-1/3"></div>

      <div className="relative z-10 w-full max-w-md p-10 mx-4 bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl shadow-[0_25px_50px_-12px_rgba(0,0,0,0.5)]">
        <h2 className="mb-8 text-3xl font-bold text-center text-white">Tạo tài khoản</h2>
        {errorMessage && <div className="p-3 mb-6 text-sm text-center text-red-200 bg-red-500/20 rounded-xl">{errorMessage}</div>}

        <form onSubmit={handleRegister} className="flex flex-col gap-5">
          <div>
            <label className="block mb-2 text-sm font-medium text-slate-300">Họ và tên</label>
            <input type="text" required value={name} onChange={e => setName(e.target.value)}
              className="w-full px-4 py-3 text-white transition-all border outline-none bg-slate-800/50 border-white/10 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/50"
              placeholder="Tên của bạn" />
          </div>
          <div>
            <label className="block mb-2 text-sm font-medium text-slate-300">Email</label>
            <input type="email" required value={email} onChange={e => setEmail(e.target.value)}
              className="w-full px-4 py-3 text-white transition-all border outline-none bg-slate-800/50 border-white/10 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/50"
              placeholder="nhapemail@epu.edu.vn" />
          </div>
          <div>
            <label className="block mb-2 text-sm font-medium text-slate-300">Mật khẩu</label>
            <input type="password" required value={password} onChange={e => setPassword(e.target.value)}
              className="w-full px-4 py-3 text-white transition-all border outline-none bg-slate-800/50 border-white/10 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/50"
              placeholder="••••••••" />
            {passwordError && <p className="mt-2 text-xs italic text-red-400">{passwordError}</p>}
          </div>
          <div>
            <label className="block mb-2 text-sm font-medium text-slate-300">Xác nhận mật khẩu</label>
            <input type="password" required value={passwordConfirmation} onChange={e => setPasswordConfirmation(e.target.value)}
              className="w-full px-4 py-3 text-white transition-all border outline-none bg-slate-800/50 border-white/10 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/50"
              placeholder="••••••••" />
          </div>

          <button type="submit" disabled={loading}
            className="w-full py-3 mt-2 font-bold text-white transition-all bg-gradient-to-r from-indigo-600 to-indigo-500 rounded-xl hover:scale-[1.02] hover:shadow-lg hover:shadow-indigo-500/30 disabled:opacity-60 disabled:cursor-not-allowed disabled:scale-100">
            {loading ? 'Đang gửi mã...' : 'Đăng ký ngay'}
          </button>
        </form>

        <div className="flex items-center gap-3 my-6 text-sm text-slate-500">
          <div className="flex-1 h-px bg-white/10"></div><span>HOẶC</span><div className="flex-1 h-px bg-white/10"></div>
        </div>

        <button type="button" onClick={() => registerWithGoogle()}
          className="flex items-center justify-center w-full gap-3 py-3 font-semibold transition-all bg-white text-slate-900 rounded-xl hover:bg-slate-50 hover:scale-[1.02] cursor-pointer">
          <img src={googleLogo} alt="Google" className="w-5 h-5" /> Đăng nhập với Google
        </button>

        <p className="mt-6 text-sm text-center text-slate-400">
          Đã có tài khoản? <Link to="/login" className="font-bold text-indigo-400 hover:underline">Đăng nhập</Link>
        </p>
      </div>
    </div>
  );
}