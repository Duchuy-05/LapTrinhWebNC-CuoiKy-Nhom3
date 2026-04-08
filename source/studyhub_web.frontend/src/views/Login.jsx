import React, { useState } from 'react';
import googleLogo from '../assets/images/logo_google.png'; 
import '../Auth.css';
import { GoogleLogin } from '@react-oauth/google';
import { Link, useNavigate } from 'react-router-dom';

export default function Login() {
  // 1. Tạo các state để lưu trữ dữ liệu người dùng gõ vào
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [errorMessage, setErrorMessage] = useState(''); // State lưu câu báo lỗi
  const navigate = useNavigate();

  // 2. Viết hàm xử lý khi bấm nút Đăng nhập
  const handleLogin = async (e) => {
    e.preventDefault(); // Ngăn trang web bị reload
    setErrorMessage(''); // Xóa lỗi cũ (nếu có) trước khi gửi yêu cầu mới

    try {
      // Gửi API sang Laravel
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
        // Nếu Backend trả về lỗi (401, 404...), hiển thị lỗi đó lên
        setErrorMessage(data.message || 'Email hoặc mật khẩu không chính xác!');
        return;
      }

      // Nếu thành công: Lưu Token và thông tin user vào trình duyệt
      localStorage.setItem('token', data.token);
      localStorage.setItem('user_data', JSON.stringify(data.user));
      navigate('/courses'); // Chuyển về trang Courses

    } catch (error) {
      setErrorMessage('Không thể kết nối đến Server. Vui lòng bật XAMPP và chạy Laravel!');
    }
  };

  return (
    <>
      <div className="animate-item delay-1">
        <h2 className="auth-title">Chào mừng quay trở lại!</h2>
      </div>

      <form onSubmit={handleLogin}>
        <div className="form-group animate-item delay-2">
          <label className="form-label">Email</label>
          <input 
            type="email" 
            required
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            className="form-input"
            placeholder="nhapemail@epu.edu.vn"
          />
        </div>

        <div className="form-group animate-item delay-3">
          <label className="form-label">Mật khẩu</label>
          <input 
            type="password" 
            required
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            className="form-input"
            placeholder="••••••••"
          />
        </div>

        <div className="auth-options animate-item delay-4">
          <label className="remember-me">
            <input type="checkbox" className="custom-checkbox" /> 
            <span>Ghi nhớ</span>
          </label>
          <a href="#" className="auth-link">Quên mật khẩu?</a>
        </div>

        <div className="animate-item delay-5">
          <button type="submit" className="btn-primary">Đăng nhập</button>
        </div>
      </form>

      <div className="animate-item delay-6">
        <div className="divider"><span>HOẶC</span></div>
        <button className="btn-google">
          <img src={googleLogo} alt="Google" className="google-icon" />
          Đăng nhập với Google
        </button>
        <p className="auth-footer-text">
          Chưa có tài khoản? <Link to="/register" className="auth-link font-bold">Đăng ký ngay</Link>
        </p>
      </div>
    </>
  );
}