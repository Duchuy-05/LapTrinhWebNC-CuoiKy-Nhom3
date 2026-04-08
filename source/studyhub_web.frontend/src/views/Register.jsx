import React, { useState } from 'react';
import googleLogo from '../assets/images/logo_google.png'; // Giữ nguyên đường dẫn ảnh của bạn
import '../Auth.css'; // lấy trang CSS 
import { Link, useNavigate } from 'react-router-dom';

export default function Register() {
  // 1. Tạo state lưu dữ liệu
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [errorMessage, setErrorMessage] = useState('');
  
  const navigate = useNavigate();

  // 2. Hàm xử lý Đăng ký
  const handleRegister = async (e) => {
    e.preventDefault();
    setErrorMessage('');

    // Kiểm tra sơ bộ ở Frontend
    if (password !== passwordConfirmation) {
      setErrorMessage('Mật khẩu nhập lại không khớp!');
      return;
    }

    try {
      // Gọi API sang Laravel
      const response = await fetch('http://localhost:8000/api/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ 
          email: email, 
          password: password,
          password_confirmation: passwordConfirmation // Phải giống tên biến Laravel yêu cầu
        })
      });

      const data = await response.json();

      if (!response.ok) {
        // Lấy thông báo lỗi đầu tiên từ Laravel trả về (ví dụ: Trùng email)
        const errorMsg = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Đăng ký thất bại!');
        setErrorMessage(errorMsg);
        return;
      }

      // Thành công
      alert('Đăng ký tài khoản thành công!');
      navigate('/login'); // Chuyển hướng người dùng về trang Đăng nhập

    } catch (error) {
      setErrorMessage('Không thể kết nối đến Server.');
    }
  };

  return (
    <>
      <div className="animate-item delay-1">
        <h2 className="auth-title">Tạo tài khoản</h2>
      </div>
      {/* Hiển thị lỗi nếu có */}
      {errorMessage && (
        <div style={{ color: '#ff4d4f', backgroundColor: 'rgba(255, 77, 79, 0.1)', padding: '10px', borderRadius: '8px', marginBottom: '15px', textAlign: 'center', fontSize: '0.9rem' }}>
          {errorMessage}
        </div>
      )}
      <form onSubmit={handleRegister}>
        <div className="form-group animate-item delay-3">
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

        <div className="form-group animate-item delay-4">
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

        <div className="form-group animate-item delay-5">
          <label className="form-label">Xác nhận mật khẩu</label>
          <input 
            type="password" 
            required
            value={passwordConfirmation}
            onChange={(e) => setPasswordConfirmation(e.target.value)}
            className="form-input"
            placeholder="••••••••"
          />
        </div>

        <div className="animate-item delay-6">
          <button type="submit" className="btn-primary">Đăng ký ngay</button>
        </div>
      </form>

      <div className="animate-item delay-6">
        <div className="divider"><span>HOẶC</span></div>
        <button className="btn-google">
          <img src={googleLogo} alt="Google" className="google-icon" />
          Đăng ký bằng Google
        </button>
        
        <p className="auth-footer-text">
          Đã có tài khoản? <Link to="/login" className="auth-link font-bold">Đăng nhập</Link>
        </p>
      </div>
    </>
  );
}