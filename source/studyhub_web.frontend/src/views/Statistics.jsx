import React, { useState, useEffect } from 'react';
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell,
  AreaChart, Area
} from 'recharts';
import CourseAPI from '../services/courseApi';
import Swal from 'sweetalert2'; // Thêm thư viện thông báo xịn

const CustomTooltip = ({ active, payload, label }) => {
  if (active && payload && payload.length) {
    const dataKey = payload[0].dataKey;
    let valueStr = payload[0].value;
    let unitStr = "khóa học";

    if (dataKey === "revenue" || dataKey === "Doanh thu") {
       valueStr = Number(payload[0].value).toLocaleString() + ' đ';
       unitStr = "";
    }

    return (
      <div className="bg-white p-4 rounded-xl shadow-xl border border-slate-100 transform transition-all z-50">
        <p className="font-bold text-slate-700 mb-1">{label}</p>
        <p className="text-sm font-bold flex items-center gap-2" style={{ color: payload[0].payload.color || '#3b82f6' }}>
          <span className="w-3 h-3 rounded-full" style={{ backgroundColor: payload[0].payload.color || '#3b82f6' }}></span>
          {payload[0].name}: <span className="text-lg">{valueStr}</span> {unitStr}
        </p>
      </div>
    );
  }
  return null;
};

export default function Statistics() {
  const [dailyData, setDailyData] = useState([]);
  const [statusData, setStatusData] = useState([]);
  const [monthlyRevenue, setMonthlyRevenue] = useState([]);
  const [summary, setSummary] = useState({ total_students: 0, total_revenue: 0 });
  const [isLoading, setIsLoading] = useState(true);

  const [showBankModal, setShowBankModal] = useState(false);
  const [bankInfo, setBankInfo] = useState(null);
  const [withdrawalStatus, setWithdrawalStatus] = useState('idle');
  const [bankForm, setBankForm] = useState({ bankName: 'Vietcombank', accountName: '', accountNumber: '' });

  useEffect(() => {
    fetchStatistics();
    checkBankInformation();
  }, []);

  const fetchStatistics = async () => {
    try {
      setIsLoading(true);
      const response = await CourseAPI.getStatistics();
      const stats = response.data.data;

      setSummary(stats.summary || { total_students: 0, total_revenue: 0 });

      const processedDailyData = stats.daily_published.map(item => {
        const [year, month, day] = item.date.split('-');
        return { date: `${day}/${month}/${year}`, 'Số lượng': item.total };
      });
      setDailyData(processedDailyData);

      setStatusData([
        { name: 'Bản nháp', 'Số lượng': stats.status_counts.DRAFT, color: '#94a3b8' },
        { name: 'Đã xuất bản', 'Số lượng': stats.status_counts.PUBLISHED, color: '#22c55e' },
        { name: 'Ngừng xuất bản', 'Số lượng': stats.status_counts.UNPUBLISHED, color: '#f59e0b' }
      ]);

      const processedRevenue = (stats.monthly_revenue || []).map(item => ({
         month: item.month,
         'Doanh thu': item.revenue
      }));
      setMonthlyRevenue(processedRevenue);

    } catch (error) {
      console.error("Lỗi khi tải dữ liệu thống kê:", error);
    } finally {
      setIsLoading(false);
    }
  };

  const checkBankInformation = () => {
    const storedBank = localStorage.getItem('lecturer_bank_info');
    const storedWithdrawStatus = localStorage.getItem('lecturer_withdraw_status') || 'idle';

    setWithdrawalStatus(storedWithdrawStatus);

    if (storedBank) {
      const parsedBank = JSON.parse(storedBank);
      if (!parsedBank.bankName || !parsedBank.accountName || !parsedBank.accountNumber) {
        setShowBankModal(true);
      } else {
        setBankInfo(parsedBank);
        setBankForm(parsedBank);
      }
    } else {
      setShowBankModal(true);
    }
  };

  const handleNameChange = (e) => {
    let val = e.target.value;
    val = val.toUpperCase();
    val = val.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    val = val.replace(/Đ/g, "D");
    val = val.replace(/đ/g, "d");
    setBankForm({ ...bankForm, accountName: val });
  };

  const handleSaveBankInfo = (e) => {
    e.preventDefault();
    if(!bankForm.accountName || !bankForm.accountNumber) {
        Swal.fire({
            icon: 'warning',
            title: 'Thiếu thông tin',
            text: 'Vui lòng điền đầy đủ Tên chủ tài khoản và Số tài khoản!',
        });
        return;
    }

    localStorage.setItem('lecturer_bank_info', JSON.stringify(bankForm));
    setBankInfo(bankForm);
    setShowBankModal(false);
    
    Swal.fire({
        icon: 'success',
        title: 'Thành công',
        text: 'Đã cập nhật thông tin ngân hàng!',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
  };

  const handleWithdrawRequest = () => {
    if (walletBalance <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Không thể rút tiền',
            text: 'Ví của bạn hiện chưa có số dư!',
        });
        return;
    }
    
    Swal.fire({
        title: 'Xác nhận rút tiền?',
        html: `Bạn đang yêu cầu rút <strong>${walletBalance.toLocaleString()} đ</strong> về tài khoản <strong>${bankInfo.bankName}</strong>.`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Vâng, gửi yêu cầu!',
        cancelButtonText: 'Hủy bỏ'
    }).then((result) => {
        Swal.fire({
        title: 'Xác nhận rút tiền?',
        html: `Bạn đang yêu cầu rút <strong>${walletBalance.toLocaleString()} đ</strong> về tài khoản <strong>${bankInfo.bankName}</strong>.`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Vâng, gửi yêu cầu!',
        cancelButtonText: 'Hủy bỏ'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                // 1. Gọi API gửi sang Laravel
                const response = await fetch('http://127.0.0.1:8000/api/request-payout', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        amount: walletBalance,
                        bankInfo: bankInfo
                    })
                });

                // 2. ĐỌC KẾT QUẢ TỪ BACKEND
                const data = await response.json();

                // 3. Xử lý theo đúng Dữ liệu thật
                if (data.status === 'success') {
                    // CHỈ KHI BACKEND BÁO THÀNH CÔNG MỚI LƯU TRẠNG THÁI
                    localStorage.setItem('lecturer_withdraw_status', 'pending');
                    setWithdrawalStatus('pending');
                    Swal.fire('Đã gửi!', 'Yêu cầu rút tiền của bạn đã được chuyển tới Admin.', 'success');
                } else {
                    // NẾU BACKEND BÁO LỖI (VD: Thiếu Model, Lỗi DB...) -> Hiển thị lỗi thật
                    Swal.fire('Lỗi từ Backend', data.message, 'error');
                }

            } catch (error) {
                Swal.fire('Lỗi mạng', 'Không thể kết nối đến máy chủ Laravel', 'error');
            }
        }
    });
    });
  };

  const handleCancelWithdraw = () => {
    Swal.fire({
        title: 'Hủy yêu cầu?',
        text: "Bạn có chắc chắn muốn hủy bỏ yêu cầu rút tiền này không?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Đồng ý hủy',
        cancelButtonText: 'Giữ lại yêu cầu'
    }).then((result) => {
        if (result.isConfirmed) {
            localStorage.setItem('lecturer_withdraw_status', 'idle');
            setWithdrawalStatus('idle');
            Swal.fire(
              'Đã hủy!',
              'Yêu cầu rút tiền của bạn đã được gỡ bỏ.',
              'success'
            );
        }
    });
  };

  const walletBalance = summary.total_revenue * 0.6;

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-full">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div className="w-full p-2 font-sans animate-fadeIn pb-10 relative">

      <div className="mb-8 flex flex-col xl:flex-row justify-between xl:items-end gap-4">
        <div>
          <h1 className="text-3xl font-extrabold text-slate-800 tracking-tight">Thống kê tổng quan</h1>
          <p className="text-slate-500 mt-2">Báo cáo chi tiết về hiệu suất và doanh thu của bạn.</p>
        </div>

        <div className="flex flex-wrap gap-4">

          <div className="bg-white border border-slate-100 px-6 py-4 rounded-2xl shadow-sm flex items-center gap-4 flex-1 min-w-[200px]">
             <div className="w-12 h-12 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center text-xl">👨‍🎓</div>
             <div>
               <p className="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Tổng học viên</p>
               <p className="text-2xl font-black text-slate-800">{summary.total_students.toLocaleString()}</p>
             </div>
          </div>

          <div className="bg-gradient-to-br from-green-500 to-emerald-600 text-white px-6 py-4 rounded-2xl shadow-lg flex items-center gap-4 flex-1 min-w-[200px]">
             <div className="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-xl">💰</div>
             <div>
               <p className="text-[10px] uppercase font-bold text-green-100 tracking-wider">Tổng doanh thu</p>
               <p className="text-2xl font-black">{summary.total_revenue.toLocaleString()} đ</p>
             </div>
          </div>

          <div className="bg-white border border-slate-200 border-l-4 border-l-amber-400 px-4 py-3 rounded-2xl shadow-md flex flex-col justify-center flex-1 min-w-[220px]">
             <div className="flex justify-between items-start mb-1">
                 <div>
                    <p className="text-[10px] uppercase font-bold text-slate-400 tracking-wider flex items-center gap-1">
                        <span className="text-amber-500 text-sm">💳</span> Số dư ví (60%)
                    </p>
                    <p className="text-xl font-black text-slate-800">{walletBalance.toLocaleString()} đ</p>
                 </div>
                 <button onClick={() => setShowBankModal(true)} className="text-blue-500 hover:text-blue-700 text-xs font-bold underline cursor-pointer">
                     Thay đổi tài khoản
                 </button>
             </div>

             {withdrawalStatus === 'pending' ? (
                 <div className="flex flex-col gap-2 mt-2">
                     <button disabled className="w-full py-1.5 bg-slate-100 text-slate-400 text-xs font-bold rounded-lg border border-slate-200 cursor-not-allowed">
                         ⏳ Đang chờ Admin duyệt...
                     </button>
                     <button onClick={handleCancelWithdraw} className="w-full py-1.5 bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors text-xs font-bold rounded-lg border border-red-200 cursor-pointer">
                         ❌ Hủy yêu cầu rút
                     </button>
                 </div>
             ) : (
                 <button onClick={handleWithdrawRequest} className="w-full mt-2 py-1.5 bg-amber-100 text-amber-700 hover:bg-amber-500 hover:text-white transition-colors text-xs font-bold rounded-lg cursor-pointer">
                     💸 Yêu cầu rút doanh thu
                 </button>
             )}
          </div>

        </div>
      </div>

      <div className="bg-white p-6 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 flex flex-col mb-8">
          <div className="mb-6">
            <h2 className="text-lg font-bold text-slate-800 flex items-center gap-2">
              <span className="p-2 bg-green-50 text-green-600 rounded-lg">💹</span>
              Dòng tiền doanh thu theo tháng
            </h2>
            <p className="text-xs text-slate-400 mt-1">Dựa trên các hóa đơn thanh toán thành công của học viên</p>
          </div>

          <div className="w-full min-h-[350px]">
            {monthlyRevenue.length > 0 ? (
              <ResponsiveContainer width="100%" height={350}>
                <AreaChart data={monthlyRevenue} margin={{ top: 10, right: 10, left: 10, bottom: 0 }}>
                  <defs>
                    <linearGradient id="colorRevenue" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="#10b981" stopOpacity={0.4}/>
                      <stop offset="95%" stopColor="#10b981" stopOpacity={0}/>
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f1f5f9" />
                  <XAxis dataKey="month" axisLine={false} tickLine={false} tick={{ fill: '#94a3b8', fontSize: 12, fontWeight: 'bold' }} dy={10} />
                  <YAxis axisLine={false} tickLine={false} tick={{ fill: '#94a3b8', fontSize: 12 }} tickFormatter={(val) => (val/1000).toLocaleString() + 'k'} />
                  <Tooltip content={<CustomTooltip />} />
                  <Area type="monotone" dataKey="Doanh thu" stroke="#10b981" strokeWidth={3} fillOpacity={1} fill="url(#colorRevenue)" animationDuration={2000} />
                </AreaChart>
              </ResponsiveContainer>
            ) : (
              <div className="h-full flex items-center justify-center text-slate-400 italic border-2 border-dashed border-slate-100 rounded-xl">
                Chưa phát sinh doanh thu từ hệ thống
              </div>
            )}
          </div>
      </div>

      <div className="grid grid-cols-1 xl:grid-cols-2 gap-8">

        <div className="bg-white p-6 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 flex flex-col group hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-shadow duration-500">
          <div className="mb-6">
            <h2 className="text-lg font-bold text-slate-800 flex items-center gap-2">
              <span className="p-2 bg-blue-50 text-blue-600 rounded-lg">📈</span>
              Khóa học đã xuất bản theo ngày
            </h2>
          </div>
          <div className="flex-1 w-full min-h-[300px]">
            {dailyData.length > 0 ? (
              <ResponsiveContainer width="100%" height={300}>
                <BarChart data={dailyData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                  <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f1f5f9" />
                  <XAxis dataKey="date" axisLine={false} tickLine={false} tick={{ fill: '#94a3b8', fontSize: 12 }} dy={10} />
                  <YAxis axisLine={false} tickLine={false} tick={{ fill: '#94a3b8', fontSize: 12 }} allowDecimals={false} />
                  <Tooltip cursor={{ fill: '#f8fafc' }} content={<CustomTooltip />} />
                  <Bar dataKey="Số lượng" fill="url(#colorBlue)" radius={[6, 6, 0, 0]} animationDuration={1500} barSize={40} />
                  <defs>
                    <linearGradient id="colorBlue" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stopColor="#2100f7" stopOpacity={1}/>
                      <stop offset="100%" stopColor="#818cf8" stopOpacity={0.6}/>
                    </linearGradient>
                  </defs>
                </BarChart>
              </ResponsiveContainer>
            ) : (
              <div className="h-full flex items-center justify-center text-slate-400 italic border-2 border-dashed border-slate-100 rounded-xl">
                Không có dữ liệu
              </div>
            )}
          </div>
        </div>

        <div className="bg-white p-6 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 flex flex-col group hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-shadow duration-500">
          <div className="mb-6 flex justify-between items-start">
            <div>
              <h2 className="text-lg font-bold text-slate-800 flex items-center gap-2">
                <span className="p-2 bg-amber-50 text-amber-600 rounded-lg">📊</span>
                Phân bổ trạng thái
              </h2>
            </div>
            <div className="flex flex-col gap-2 text-[10px] font-bold text-slate-500">
               <div className="flex items-center gap-1"><span className="w-3 h-3 rounded-sm bg-[#94a3b8]"></span> Nháp</div>
               <div className="flex items-center gap-1"><span className="w-3 h-3 rounded-sm bg-[#22c55e]"></span> Đã xuất bản</div>
               <div className="flex items-center gap-1"><span className="w-3 h-3 rounded-sm bg-[#f59e0b]"></span> Thu hồi</div>
            </div>
          </div>
          <div className="flex-1 w-full min-h-[300px]">
            <ResponsiveContainer width="100%" height={300}>
              <BarChart data={statusData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f1f5f9" />
                <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fill: '#94a3b8', fontSize: 12, fontWeight: 'bold' }} dy={10} />
                <YAxis axisLine={false} tickLine={false} tick={{ fill: '#94a3b8', fontSize: 12 }} allowDecimals={false} />
                <Tooltip cursor={{ fill: '#f8fafc', opacity: 0.4 }} content={<CustomTooltip />} />
                <Bar dataKey="Số lượng" radius={[8, 8, 0, 0]} animationDuration={1500} barSize={60}>
                  {statusData.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={entry.color} />
                  ))}
                </Bar>
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
      </div>

      {showBankModal && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4">
          <div className="bg-white p-6 md:p-8 rounded-3xl shadow-2xl max-w-md w-full border border-slate-100">
            <div className="w-16 h-16 bg-amber-100 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
              🏦
            </div>
            <h3 className="text-2xl font-black text-slate-800 text-center mb-2">Cập nhật tài khoản</h3>
            <p className="text-sm text-slate-500 text-center mb-6">
              Bạn cần cung cấp thông tin ngân hàng để nhận thanh toán doanh thu từ StudyHub.
            </p>

            <form onSubmit={handleSaveBankInfo} className="flex flex-col gap-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Ngân hàng</label>
                <select
                  className="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-500 focus:bg-white transition-colors"
                  value={bankForm.bankName}
                  onChange={(e) => setBankForm({...bankForm, bankName: e.target.value})}
                >
                  <option value="Vietcombank">Vietcombank</option>
                  <option value="Techcombank">Techcombank</option>
                  <option value="MBBank">MB Bank (Quân Đội)</option>
                  <option value="Agribank">Agribank</option>
                  <option value="BIDV">BIDV</option>
                  <option value="VietinBank">VietinBank</option>
                  <option value="TPBank">TPBank</option>
                  <option value="VIB">VIB</option>
                </select>
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Tên chủ tài khoản (In hoa không dấu)</label>
                <input
                  type="text"
                  placeholder="NGUYEN VAN A"
                  className="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-500 focus:bg-white transition-colors"
                  value={bankForm.accountName}
                  onChange={handleNameChange}
                  required
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Số tài khoản</label>
                <input
                  type="text"
                  placeholder="Nhập số tài khoản hợp lệ..."
                  className="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-500 focus:bg-white transition-colors"
                  value={bankForm.accountNumber}
                  onChange={(e) => setBankForm({...bankForm, accountNumber: e.target.value.replace(/\D/g, '')})}
                  required
                />
              </div>

              <button
                type="submit"
                className="w-full mt-2 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 transition-all cursor-pointer"
              >
                Xác nhận thông tin
              </button>
              {bankInfo && (
                 <button
                   type="button"
                   onClick={() => setShowBankModal(false)}
                   className="w-full py-2 text-sm font-semibold text-slate-400 hover:text-slate-600 cursor-pointer"
                 >
                   Hủy bỏ
                 </button>
              )}
            </form>
          </div>
        </div>
      )}

    </div>
  );
}