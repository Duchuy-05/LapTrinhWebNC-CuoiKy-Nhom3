import React, { useState, useEffect } from 'react';
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell,
  AreaChart, Area
} from 'recharts';
import CourseAPI from '../services/courseApi';
import Swal from 'sweetalert2';

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
  const currentUser = JSON.parse(localStorage.getItem('user_data')) || {};
  const currentUserId = currentUser.id || currentUser._id;

  const [isCustomBank, setIsCustomBank] = useState(false);
  const predefinedBanks = ['Vietcombank', 'Techcombank', 'MBBank', 'Agribank', 'BIDV', 'VietinBank', 'TPBank', 'VIB'];

  const [dailyData, setDailyData] = useState([]);
  const [statusData, setStatusData] = useState([]);
  const [monthlyRevenue, setMonthlyRevenue] = useState([]);
  const [summary, setSummary] = useState({ total_students: 0, total_revenue: 0 });
  const [isLoading, setIsLoading] = useState(true);

  const [showSettingsModal, setShowSettingsModal] = useState(false);
  const [activeSettingTab, setActiveSettingTab] = useState('bank'); 
  
  const [bankInfo, setBankInfo] = useState(null);
  const [withdrawalStatus, setWithdrawalStatus] = useState('idle');
  const [bankForm, setBankForm] = useState({ bankName: 'Vietcombank', accountName: '', accountNumber: '' });
  const [payoutHistory, setPayoutHistory] = useState([]);
  const [totalWithdrawn, setTotalWithdrawn] = useState(0);

  // === STATE MỚI CHO MODAL DANH SÁCH HỌC VIÊN ===
  const [showStudentsModal, setShowStudentsModal] = useState(false);
  const [studentsList, setStudentsList] = useState([]);
  const [isLoadingStudents, setIsLoadingStudents] = useState(false);
  const [studentSearchTerm, setStudentSearchTerm] = useState('');

  useEffect(() => {
    fetchStatistics();
    if (currentUserId) {
        fetchPayoutHistory();
        checkBankInformation();
        fetchMyStudents(); // Lấy sẵn danh sách học viên ngầm
    }
  }, [currentUserId]);

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

  const fetchMyStudents = async () => {
    try {
      setIsLoadingStudents(true);
      const response = await fetch(`http://127.0.0.1:8000/api/lecturer/my-students?user_id=${currentUserId}`);
      const data = await response.json();
      if (data.success) {
        setStudentsList(data.data);
        // Ép con số Tổng Học Viên bên ngoài khớp 100% với danh sách thực tế của Giảng viên này
        setSummary(prev => ({ ...prev, total_students: data.data.length })); 
      }
    } catch (error) {
      console.error("Lỗi tải danh sách học viên", error);
    } finally {
      setIsLoadingStudents(false);
    }
  };

  const fetchPayoutHistory = async () => {
    try {
        const response = await fetch(`http://127.0.0.1:8000/api/my-payouts?user_id=${currentUserId}`);
        const data = await response.json();
        
        if (data.success) {
            setPayoutHistory(data.payouts);
            let withdrawn = 0;
            let hasPending = false;
            
            data.payouts.forEach(p => {
                if (p.status === 'completed') withdrawn += parseFloat(p.amount);
                if (p.status === 'pending') hasPending = true;
            });

            setTotalWithdrawn(withdrawn);
            setWithdrawalStatus(hasPending ? 'pending' : 'idle'); 
        }
    } catch (error) {
        console.error("Chưa kết nối được API Lịch sử rút tiền");
    }
  };

  const checkBankInformation = async () => {
    try {
        const response = await fetch(`http://127.0.0.1:8000/api/get-bank-info?user_id=${currentUserId}`);
        const data = await response.json();
        if (data.success && data.bankInfo) {
            setBankInfo(data.bankInfo);
            setBankForm(data.bankInfo);
            if (!predefinedBanks.includes(data.bankInfo.bankName)) setIsCustomBank(true);
            else setIsCustomBank(false);
        } else {
            setBankInfo(null);
            setBankForm({ bankName: 'Vietcombank', accountName: '', accountNumber: '' });
            setIsCustomBank(false);
        }
    } catch (error) {
        console.error("Lỗi lấy thông tin ngân hàng từ CSDL");
    }
  };

  const handleNameChange = (e) => {
    let val = e.target.value.toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/Đ/g, "D");
    setBankForm({ ...bankForm, accountName: val });
  };

  const handleSaveBankInfo = async (e) => {
    e.preventDefault();
    if(!bankForm.accountName || !bankForm.accountNumber) {
        Swal.fire({ icon: 'warning', title: 'Thiếu thông tin', text: 'Vui lòng điền đầy đủ Tên chủ tài khoản và Số tài khoản!'});
        return;
    }
    try {
        const response = await fetch('http://127.0.0.1:8000/api/update-bank-info', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: currentUserId,
                bank_name: bankForm.bankName,
                account_name: bankForm.accountName,
                account_number: bankForm.accountNumber
            })
        });
        const data = await response.json();
        if (data.success) {
            setBankInfo(bankForm);
            Swal.fire({ icon: 'success', title: 'Thành công', text: 'Đã lưu thông tin ngân hàng vào CSDL an toàn!', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000});
        } else {
            Swal.fire('Lỗi', data.message, 'error');
        }
    } catch (error) {
        Swal.fire('Lỗi mạng', 'Không thể kết nối đến máy chủ', 'error');
    }
  };

  const totalEarned = summary.total_revenue * 0.6;
  const walletBalance = Math.max(0, totalEarned - totalWithdrawn); 

  const handleWithdrawRequest = () => {
    if (!bankInfo) {
        Swal.fire({ icon: 'warning', title: 'Chưa có tài khoản', text: 'Vui lòng cài đặt tài khoản ngân hàng trước khi rút tiền.' });
        setShowSettingsModal(true);
        setActiveSettingTab('bank');
        return;
    }
    if (walletBalance <= 0) {
        Swal.fire({ icon: 'error', title: 'Không đủ số dư', text: 'Ví của bạn hiện không có số dư khả dụng!' });
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
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch('http://127.0.0.1:8000/api/request-payout', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ amount: walletBalance, bankInfo: bankInfo, user_id: currentUserId })
                });
                const data = await response.json();
                if (data.status === 'success') {
                    Swal.fire('Đã gửi!', 'Yêu cầu rút tiền đang chờ Admin xử lý.', 'success');
                    fetchPayoutHistory(); 
                } else {
                    Swal.fire('Lỗi', data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Lỗi mạng', 'Không thể kết nối', 'error');
            }
        }
    });
  };

  const handleCancelRequest = () => {
      Swal.fire({
          title: 'Hủy yêu cầu?',
          text: 'Bạn có chắc chắn muốn hủy yêu cầu rút tiền đang chờ duyệt không?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#94a3b8',
          confirmButtonText: 'Có, hủy ngay!',
          cancelButtonText: 'Không'
      }).then(async (result) => {
          if (result.isConfirmed) {
              try {
                  const response = await fetch('http://127.0.0.1:8000/api/cancel-payout', {
                      method: 'POST',
                      headers: { 'Content-Type': 'application/json' },
                      body: JSON.stringify({ user_id: currentUserId })
                  });
                  const data = await response.json();
                  if (data.success) {
                      Swal.fire('Đã hủy!', 'Yêu cầu rút tiền của bạn đã bị hủy.', 'success');
                      fetchPayoutHistory();
                  } else {
                      Swal.fire('Lỗi', data.message, 'error');
                  }
              } catch (error) {
                  Swal.fire('Lỗi mạng', 'Không thể kết nối', 'error');
              }
          }
      });
  };

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

          {/* SỬA ĐỔI: Biến thẻ Tổng Học Viên thành Nút bấm hiển thị Modal */}
          <div 
             onClick={() => setShowStudentsModal(true)}
             className="bg-white border border-slate-100 px-6 py-4 rounded-2xl shadow-sm flex items-center gap-4 flex-1 min-w-[200px] cursor-pointer hover:shadow-lg transition-all border-l-4 border-l-indigo-500 group"
          >
             <div className="w-12 h-12 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center text-xl group-hover:scale-110 transition-transform">👨‍🎓</div>
             <div>
               <p className="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Tổng học viên</p>
               <p className="text-2xl font-black text-slate-800">{summary.total_students.toLocaleString()}</p>
               <p className="text-[10px] text-indigo-500 mt-1 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity font-bold">
                  Bấm xem danh sách <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path></svg>
               </p>
             </div>
          </div>

          <div className="bg-gradient-to-br from-green-500 to-emerald-600 text-white px-6 py-4 rounded-2xl shadow-lg flex items-center gap-4 flex-1 min-w-[200px]">
             <div className="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-xl">💰</div>
             <div>
               <p className="text-[10px] uppercase font-bold text-green-100 tracking-wider">Tổng doanh thu</p>
               <p className="text-2xl font-black">{totalEarned.toLocaleString()} đ</p>
             </div>
          </div>

          <div className="bg-white border border-slate-200 border-l-4 border-l-amber-400 px-4 py-3 rounded-2xl shadow-md flex flex-col justify-center flex-1 min-w-[220px]">
             <div className="flex justify-between items-start mb-1">
                 <div>
                    <p className="text-[10px] uppercase font-bold text-slate-400 tracking-wider flex items-center gap-1">
                        <span className="text-amber-500 text-sm">💳</span> Số dư ví khả dụng
                    </p>
                    <p className="text-xl font-black text-slate-800">{walletBalance.toLocaleString()} đ</p>
                 </div>
                 <button onClick={() => setShowSettingsModal(true)} title="Cài đặt thanh toán" className="text-slate-400 hover:text-blue-600 transition-colors p-1 rounded-full hover:bg-blue-50 cursor-pointer">
                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" strokeWidth="2">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                 </button>
             </div>

             {withdrawalStatus === 'pending' ? (
                 <div className="mt-2 flex flex-col gap-1.5">
                     <button disabled className="w-full py-1.5 bg-slate-100 text-slate-500 text-xs font-bold rounded-lg border border-slate-200 cursor-not-allowed">
                         ⏳ Đang chờ Admin duyệt...
                     </button>
                     <button onClick={handleCancelRequest} className="w-full py-1.5 bg-red-50 text-red-600 hover:bg-red-500 hover:text-white transition-colors text-xs font-bold rounded-lg border border-red-100 cursor-pointer">
                         ❌ Hủy yêu cầu rút
                     </button>
                 </div>
             ) : (
                 <button onClick={handleWithdrawRequest} disabled={walletBalance <= 0} className={`w-full mt-2 py-1.5 text-xs font-bold rounded-lg transition-colors ${walletBalance > 0 ? 'bg-amber-100 text-amber-700 hover:bg-amber-500 hover:text-white cursor-pointer' : 'bg-slate-100 text-slate-400 cursor-not-allowed'}`}>
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

      {showSettingsModal && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4">
          <div className="bg-white rounded-3xl shadow-2xl max-w-2xl w-full border border-slate-100 overflow-hidden flex flex-col md:flex-row min-h-[400px]">
            
            <div className="w-full md:w-1/3 bg-slate-50 p-6 border-r border-slate-200">
                <div className="flex justify-between items-center mb-6">
                    <h3 className="text-xl font-black text-slate-800">Cài đặt</h3>
                    <button onClick={() => setShowSettingsModal(false)} className="md:hidden text-slate-400 hover:text-red-500">
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div className="flex flex-col gap-2">
                    <button onClick={() => setActiveSettingTab('bank')} className={`text-left px-4 py-3 rounded-xl font-bold text-sm transition-colors ${activeSettingTab === 'bank' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-200'}`}>
                        🏦 Ngân hàng nhận
                    </button>
                    <button onClick={() => setActiveSettingTab('history')} className={`text-left px-4 py-3 rounded-xl font-bold text-sm transition-colors ${activeSettingTab === 'history' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-200'}`}>
                        📜 Lịch sử rút tiền
                    </button>
                </div>
            </div>

            <div className="w-full md:w-2/3 p-6 md:p-8 relative">
                <button onClick={() => setShowSettingsModal(false)} className="absolute top-4 right-4 hidden md:block text-slate-400 hover:text-red-500">
                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>

                {activeSettingTab === 'bank' && (
                    <div className="animate-fadeIn">
                        <h4 className="text-lg font-bold text-slate-800 mb-4">Cập nhật tài khoản</h4>
                        <form onSubmit={handleSaveBankInfo} className="flex flex-col gap-4">
                            <div>
                                <label className="block text-xs font-bold text-slate-700 mb-1">Ngân hàng</label>
                                <select 
                                    className="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-500 focus:bg-white" 
                                    value={isCustomBank ? 'other' : bankForm.bankName} 
                                    onChange={(e) => {
                                        if (e.target.value === 'other') {
                                            setIsCustomBank(true);
                                            setBankForm({...bankForm, bankName: ''}); 
                                        } else {
                                            setIsCustomBank(false);
                                            setBankForm({...bankForm, bankName: e.target.value});
                                        }
                                    }}
                                >
                                    {predefinedBanks.map(bank => (
                                        <option key={bank} value={bank}>{bank}</option>
                                    ))}
                                    <option value="other" className="font-bold text-blue-600">Ngân hàng khác (Nhập tay)...</option>
                                </select>
                                
                                {isCustomBank && (
                                    <input 
                                        type="text" 
                                        placeholder="Vui lòng nhập tên Ngân hàng / Ví điện tử..." 
                                        className="w-full mt-2 p-3 bg-blue-50 border border-blue-200 rounded-xl outline-none focus:border-blue-500" 
                                        value={bankForm.bankName} 
                                        onChange={(e) => setBankForm({...bankForm, bankName: e.target.value})} 
                                        required 
                                    />
                                )}
                            </div>
                            <div>
                                <label className="block text-xs font-bold text-slate-700 mb-1">Tên chủ tài khoản (In hoa không dấu)</label>
                                <input type="text" placeholder="NGUYEN VAN A" className="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-500 focus:bg-white" value={bankForm.accountName} onChange={handleNameChange} required />
                            </div>
                            <div>
                                <label className="block text-xs font-bold text-slate-700 mb-1">Số tài khoản</label>
                                <input type="text" placeholder="Nhập số tài khoản hợp lệ..." className="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-500 focus:bg-white" value={bankForm.accountNumber} onChange={(e) => setBankForm({...bankForm, accountNumber: e.target.value.replace(/\D/g, '')})} required />
                            </div>
                            <button type="submit" className="w-full mt-2 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 transition-all cursor-pointer">
                                Xác nhận thông tin
                            </button>
                        </form>
                    </div>
                )}

                {activeSettingTab === 'history' && (
                    <div className="animate-fadeIn">
                        <h4 className="text-lg font-bold text-slate-800 mb-4">Lịch sử rút tiền</h4>
                        <div className="overflow-y-auto max-h-[300px] pr-2 custom-scrollbar">
                            {payoutHistory.length > 0 ? (
                                <ul className="flex flex-col gap-3">
                                    {payoutHistory.map((item, idx) => (
                                        <li key={idx} className="p-4 rounded-xl border border-slate-100 bg-slate-50 flex justify-between items-center">
                                            <div>
                                                <p className="font-bold text-slate-800">{Number(item.amount).toLocaleString()} đ</p>
                                                <p className="text-xs text-slate-500 mt-1">{new Date(item.created_at).toLocaleString()}</p>
                                            </div>
                                            <div>
                                                {item.status === 'completed' ? (
                                                    <span className="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Đã nhận</span>
                                                ) : (
                                                    <span className="px-3 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded-full">Chờ duyệt</span>
                                                )}
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                            ) : (
                                <div className="text-center py-8 text-slate-400">
                                    <p className="text-4xl mb-2">💸</p>
                                    <p className="text-sm font-bold">Chưa có giao dịch nào</p>
                                </div>
                            )}
                        </div>
                    </div>
                )}

            </div>
          </div>
        </div>
      )}

      {/* MODAL MỚI: DANH SÁCH HỌC VIÊN */}
      {showStudentsModal && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4">
          <div className="bg-white rounded-3xl shadow-2xl max-w-4xl w-full border border-slate-100 overflow-hidden flex flex-col max-h-[85vh]">
            
            {/* Header Modal */}
            <div className="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
              <div className="flex items-center gap-3">
                <div className="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-xl">👨‍🎓</div>
                <div>
                  <h3 className="text-xl font-black text-slate-800">Danh sách học viên</h3>
                  <p className="text-xs text-slate-500 font-bold mt-1">Tổng cộng: {studentsList.length} người đang học khóa của bạn</p>
                </div>
              </div>
              <button onClick={() => setShowStudentsModal(false)} className="text-slate-400 hover:text-red-500 transition-colors p-2 rounded-full hover:bg-red-50">
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>
            </div>

            {/* Body Modal (Bảng danh sách) */}
            <div className="p-6 flex-1 overflow-hidden flex flex-col gap-4">
              
              <div className="relative">
                <input 
                  type="text" 
                  placeholder="Tìm kiếm theo tên học viên, email hoặc tên khóa học..." 
                  className="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-xl outline-none focus:border-indigo-500 transition-colors"
                  value={studentSearchTerm}
                  onChange={(e) => setStudentSearchTerm(e.target.value)}
                />
                <span className="absolute left-3 top-3.5 text-slate-400">🔍</span>
              </div>

              <div className="overflow-y-auto flex-1 custom-scrollbar border border-slate-100 rounded-xl bg-white">
                {isLoadingStudents ? (
                  <div className="flex justify-center items-center py-20">
                    <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
                  </div>
                ) : (
                  <table className="w-full text-left border-collapse">
                    <thead className="sticky top-0 bg-slate-100 z-10 shadow-sm">
                      <tr className="text-xs uppercase tracking-wider text-slate-500 font-bold">
                        <th className="p-4 pl-6">Học viên</th>
                        <th className="p-4">Khóa học đăng ký</th>
                        <th className="p-4 pr-6">Ngày tham gia</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                      {studentsList.filter(s => 
                        s.student_name.toLowerCase().includes(studentSearchTerm.toLowerCase()) || 
                        s.student_email.toLowerCase().includes(studentSearchTerm.toLowerCase()) ||
                        s.course_name.toLowerCase().includes(studentSearchTerm.toLowerCase())
                      ).length > 0 ? (
                        studentsList.filter(s => 
                          s.student_name.toLowerCase().includes(studentSearchTerm.toLowerCase()) || 
                          s.student_email.toLowerCase().includes(studentSearchTerm.toLowerCase()) ||
                          s.course_name.toLowerCase().includes(studentSearchTerm.toLowerCase())
                        ).map((student, idx) => (
                          <tr key={idx} className="hover:bg-indigo-50/50 transition-colors">
                            <td className="p-4 pl-6">
                                <p className="font-bold text-slate-800">{student.student_name}</p>
                                <p className="text-xs text-slate-500 mt-1">{student.student_email}</p>
                            </td>
                            <td className="p-4">
                                <span className="px-3 py-1.5 bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-bold rounded-lg block w-max max-w-[250px] truncate" title={student.course_name}>
                                    {student.course_name}
                                </span>
                            </td>
                            <td className="p-4 pr-6 text-sm text-slate-500 font-medium">
                                {new Date(student.enrolled_at).toLocaleDateString('vi-VN', { year: 'numeric', month: 'long', day: 'numeric' })}
                            </td>
                          </tr>
                        ))
                      ) : (
                        <tr>
                          <td colSpan="3" className="text-center py-20">
                            <p className="text-4xl mb-3">📭</p>
                            <p className="text-slate-500 font-bold">
                                {studentSearchTerm ? 'Không tìm thấy dữ liệu phù hợp với từ khóa.' : 'Chưa có học viên nào.'}
                            </p>
                          </td>
                        </tr>
                      )}
                    </tbody>
                  </table>
                )}
              </div>
            </div>

          </div>
        </div>
      )}

    </div>
  );
}