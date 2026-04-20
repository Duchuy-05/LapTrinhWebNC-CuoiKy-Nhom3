import React, { useState, useRef, useEffect } from 'react';

export default function VerifyEmail({ email, onSuccess, onBack }) {
  const [otp, setOtp] = useState(['', '', '', '', '', '']);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const [resendLoading, setResendLoading] = useState(false);
  const [resendCooldown, setResendCooldown] = useState(60);
  const [successMsg, setSuccessMsg] = useState('');
  const inputs = useRef([]);

  // Đếm ngược thời gian gửi lại
  useEffect(() => {
    if (resendCooldown <= 0) return;
    const timer = setInterval(() => {
      setResendCooldown(prev => prev - 1);
    }, 1000);
    return () => clearInterval(timer);
  }, [resendCooldown]);

  const handleChange = (index, value) => {
    if (!/^\d*$/.test(value)) return; // Chỉ nhận số
    const newOtp = [...otp];
    newOtp[index] = value.slice(-1); // Chỉ lấy ký tự cuối
    setOtp(newOtp);
    setError('');
    // Tự động chuyển ô tiếp theo
    if (value && index < 5) {
      inputs.current[index + 1]?.focus();
    }
  };

  const handleKeyDown = (index, e) => {
    if (e.key === 'Backspace' && !otp[index] && index > 0) {
      inputs.current[index - 1]?.focus();
    }
    if (e.key === 'Enter') handleVerify();
  };

  const handlePaste = (e) => {
    e.preventDefault();
    const paste = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
    if (paste.length === 6) {
      setOtp(paste.split(''));
      inputs.current[5]?.focus();
    }
  };

  const handleVerify = async () => {
    const code = otp.join('');
    if (code.length !== 6) {
      setError('Vui lòng nhập đủ 6 chữ số.');
      return;
    }
    setLoading(true);
    setError('');
    try {
      const response = await fetch('http://localhost:8000/api/register/verify-email', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ email, code }),
      });
      const data = await response.json();
      if (!response.ok) {
        setError(data.message || 'Xác thực thất bại!');
        return;
      }
      // Thành công: lưu token và gọi callback
      localStorage.setItem('token', data.token);
      localStorage.setItem('user_data', JSON.stringify(data.user));
      onSuccess(data);
    } catch {
      setError('Không thể kết nối đến Server.');
    } finally {
      setLoading(false);
    }
  };

  const handleResend = async () => {
    if (resendCooldown > 0 || resendLoading) return;
    setResendLoading(true);
    setError('');
    setSuccessMsg('');
    try {
      const response = await fetch('http://localhost:8000/api/register/resend-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ email }),
      });
      const data = await response.json();
      if (!response.ok) {
        setError(data.message || 'Gửi lại thất bại!');
      } else {
        setSuccessMsg('Mã mới đã được gửi!');
        setResendCooldown(60);
        setOtp(['', '', '', '', '', '']);
        inputs.current[0]?.focus();
      }
    } catch {
      setError('Không thể kết nối đến Server.');
    } finally {
      setResendLoading(false);
    }
  };

  // Che một phần email để bảo mật: abc***@gmail.com
  const maskedEmail = email.replace(/(.{2}).+(@.+)/, '$1***$2');

  return (
    <div className="relative flex items-center justify-center min-h-screen overflow-hidden bg-slate-900 font-sans">
      {/* Background blobs */}
      <div className="absolute w-[300px] h-[300px] bg-indigo-600/40 rounded-full blur-[100px] -top-20 -left-20 animate-pulse"></div>
      <div className="absolute w-[250px] h-[250px] bg-pink-500/30 rounded-full blur-[90px] bottom-10 -right-10"></div>

      <div className="relative z-10 w-full max-w-md p-10 mx-4 bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl shadow-[0_25px_50px_-12px_rgba(0,0,0,0.5)]">
        {/* Icon */}
        <div className="flex justify-center mb-6">
          <div className="w-16 h-16 bg-indigo-500/20 rounded-2xl flex items-center justify-center">
            <svg className="w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
        </div>

        <h2 className="text-2xl font-bold text-center text-white mb-2">Xác nhận email</h2>
        <p className="text-sm text-center text-slate-400 mb-8">
          Chúng tôi đã gửi mã 6 chữ số đến<br />
          <span className="text-indigo-400 font-medium">{maskedEmail}</span>
        </p>

        {/* Messages */}
        {error && (
          <div className="p-3 mb-5 text-sm text-center text-red-200 bg-red-500/20 rounded-xl border border-red-500/20">
            {error}
          </div>
        )}
        {successMsg && (
          <div className="p-3 mb-5 text-sm text-center text-green-200 bg-green-500/20 rounded-xl border border-green-500/20">
            {successMsg}
          </div>
        )}

        {/* OTP Input boxes */}
        <div className="flex justify-center gap-3 mb-8" onPaste={handlePaste}>
          {otp.map((digit, index) => (
            <input
              key={index}
              ref={el => inputs.current[index] = el}
              type="text"
              inputMode="numeric"
              maxLength={1}
              value={digit}
              onChange={e => handleChange(index, e.target.value)}
              onKeyDown={e => handleKeyDown(index, e)}
              className={`w-12 h-14 text-center text-2xl font-bold text-white bg-slate-800/60 border-2 rounded-xl outline-none transition-all
                ${digit ? 'border-indigo-500 shadow-[0_0_12px_rgba(99,102,241,0.4)]' : 'border-white/10'}
                focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/30`}
            />
          ))}
        </div>

        {/* Verify button */}
        <button
          onClick={handleVerify}
          disabled={loading}
          className="w-full py-3 font-bold text-white bg-gradient-to-r from-indigo-600 to-indigo-500 rounded-xl hover:scale-[1.02] hover:shadow-lg hover:shadow-indigo-500/30 transition-all disabled:opacity-60 disabled:cursor-not-allowed disabled:scale-100"
        >
          {loading ? (
            <span className="flex items-center justify-center gap-2">
              <svg className="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"/>
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              Đang xác thực...
            </span>
          ) : 'Xác nhận'}
        </button>

        {/* Resend */}
        <div className="text-center mt-5">
          <span className="text-sm text-slate-400">Không nhận được mã? </span>
          <button
            onClick={handleResend}
            disabled={resendCooldown > 0 || resendLoading}
            className="text-sm font-semibold text-indigo-400 hover:underline disabled:text-slate-500 disabled:no-underline disabled:cursor-not-allowed"
          >
            {resendCooldown > 0 ? `Gửi lại sau (${resendCooldown}s)` : resendLoading ? 'Đang gửi...' : 'Gửi lại mã'}
          </button>
        </div>

        {/* Back button */}
        <button
          onClick={onBack}
          className="flex items-center justify-center gap-1 w-full mt-5 text-sm text-slate-500 hover:text-slate-300 transition-colors"
        >
          <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
          </svg>
          Quay lại đăng ký
        </button>
      </div>
    </div>
  );
}