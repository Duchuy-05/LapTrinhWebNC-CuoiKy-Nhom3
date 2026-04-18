import React, { useState, useEffect } from 'react';
import { 
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell,
  AreaChart, Area // Import thêm AreaChart cho biểu đồ doanh thu
} from 'recharts';
import CourseAPI from '../services/courseApi';

// Tooltip dùng chung, tự phát hiện nếu là "revenue" thì format tiền VNĐ
const CustomTooltip = ({ active, payload, label }) => {
  if (active && payload && payload.length) {
    const dataKey = payload[0].dataKey;
    let valueStr = payload[0].value;
    let unitStr = "khóa học";

    // Nếu đang trỏ vào biểu đồ doanh thu
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

  useEffect(() => {
    fetchStatistics();
  }, []);

  const fetchStatistics = async () => {
    try {
      setIsLoading(true);
      const response = await CourseAPI.getStatistics();
      const stats = response.data.data;

      // 1. Dữ liệu tóm tắt
      setSummary(stats.summary || { total_students: 0, total_revenue: 0 });

      // 2. Map dữ liệu biểu đồ ngày
      const processedDailyData = stats.daily_published.map(item => {
        const [year, month, day] = item.date.split('-');
        return { date: `${day}/${month}/${year}`, 'Số lượng': item.total };
      });
      setDailyData(processedDailyData);

      // 3. Map dữ liệu biểu đồ trạng thái
      setStatusData([
        { name: 'Bản nháp', 'Số lượng': stats.status_counts.DRAFT, color: '#94a3b8' },
        { name: 'Đã xuất bản', 'Số lượng': stats.status_counts.PUBLISHED, color: '#22c55e' },
        { name: 'Ngừng xuất bản', 'Số lượng': stats.status_counts.UNPUBLISHED, color: '#f59e0b' }
      ]);

      // 4. Map dữ liệu doanh thu
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

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-full">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div className="w-full p-2 font-sans animate-fadeIn pb-10">
      
      {/* HEADER & TÓM TẮT */}
      <div className="mb-8 flex justify-between items-end">
        <div>
          <h1 className="text-3xl font-extrabold text-slate-800 tracking-tight">Thống kê tổng quan</h1>
          <p className="text-slate-500 mt-2">Báo cáo chi tiết về hiệu suất và doanh thu của bạn.</p>
        </div>
        
        {/* Thẻ Summary Cards */}
        <div className="flex gap-4">
          <div className="bg-white border border-slate-100 px-6 py-4 rounded-2xl shadow-sm flex items-center gap-4">
             <div className="w-12 h-12 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center text-xl">👨‍🎓</div>
             <div>
               <p className="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Tổng học viên</p>
               <p className="text-2xl font-black text-slate-800">{summary.total_students.toLocaleString()}</p>
             </div>
          </div>
          <div className="bg-gradient-to-br from-green-500 to-emerald-600 text-white px-6 py-4 rounded-2xl shadow-lg flex items-center gap-4">
             <div className="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-xl">💰</div>
             <div>
               <p className="text-[10px] uppercase font-bold text-green-100 tracking-wider">Tổng doanh thu</p>
               <p className="text-2xl font-black">{summary.total_revenue.toLocaleString()} đ</p>
             </div>
          </div>
        </div>
      </div>

      {/* ================= BIỂU ĐỒ 3: DOANH THU (Full Width) ================= */}
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

      {/* LƯỚI 2 BIỂU ĐỒ CŨ (Khóa học & Trạng thái) */}
      <div className="grid grid-cols-1 xl:grid-cols-2 gap-8">
        
        {/* ================= BIỂU ĐỒ 1 ================= */}
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

        {/* ================= BIỂU ĐỒ 2 ================= */}
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
    </div>
  );
}