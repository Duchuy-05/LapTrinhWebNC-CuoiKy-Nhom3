import React, { useState, useEffect } from 'react';

export default function CoursePricingEditor({ 
  initialPrice = 0, 
  initialDiscountPrice = 0, 
  isPublished = false, 
  onSave, 
  isSaving = false 
}) {
  const [price, setPrice] = useState(initialPrice);
  const [discountAmount, setDiscountAmount] = useState(initialDiscountPrice);
  const [error, setError] = useState('');

  useEffect(() => {
    setPrice(initialPrice || 0);
    setDiscountAmount(initialDiscountPrice || 0);
  }, [initialPrice, initialDiscountPrice]);

  const validateAndSync = (p, d) => {
    if (d > p) {
      setError('Số tiền giảm không được lớn hơn giá gốc!');
      return false;
    }
    setError('');
    return true;
  };

  const handleSave = () => {
    if (validateAndSync(price, discountAmount)) {
      onSave({ price: Number(price), discountPrice: Number(discountAmount) });
    }
  };

  // Logic trọng tâm: Giá thực tế = Giá gốc - Số tiền giảm
  const finalPrice = price - discountAmount;
  const isFree = finalPrice === 0 || price === 0;

  return (
    <div className="bg-slate-50 p-4 rounded-2xl border border-slate-200 shadow-sm">
      <div className="flex items-center justify-between mb-4">
        <div className="flex items-center gap-2">
            <span className="text-lg">🏷️</span>
            <h3 className="font-bold text-slate-800 text-sm">Chính sách giá</h3>
        </div>
        {isFree && <span className="px-2 py-1 bg-green-100 text-green-600 text-[10px] font-black rounded-full shadow-sm">MIỄN PHÍ</span>}
      </div>

      <div className="space-y-3">
        {/* GIÁ GỐC - Bị khóa nếu đã xuất bản */}
        <div className={`flex items-center justify-between bg-white px-3 py-2 rounded-xl border ${isPublished ? 'bg-slate-100 border-slate-200' : 'border-slate-200 focus-within:border-blue-400'}`}>
          <label className="text-[10px] font-extrabold text-slate-400 uppercase w-20">Giá gốc {isPublished && '🔒'}</label>
          <input 
            type="number" 
            disabled={isPublished} 
            className="flex-1 text-right text-sm font-bold text-slate-700 bg-transparent outline-none disabled:cursor-not-allowed" 
            value={price} 
            onChange={(e) => { setPrice(Number(e.target.value)); validateAndSync(Number(e.target.value), discountAmount); }} 
          />
          <span className="text-xs font-bold text-slate-400 ml-1">đ</span>
        </div>

        {/* SỐ TIỀN GIẢM */}
        <div className={`flex items-center justify-between bg-white px-3 py-2 rounded-xl border ${error ? 'border-red-400' : 'border-slate-200 focus-within:border-green-400'}`}>
          <label className="text-[10px] font-extrabold text-green-500 uppercase w-20">Số tiền giảm</label>
          <input 
            type="number" 
            className="flex-1 text-right text-sm font-bold text-green-600 bg-transparent outline-none" 
            value={discountAmount} 
            placeholder="Nhập bằng giá gốc để Free"
            onChange={(e) => { setDiscountAmount(Number(e.target.value)); validateAndSync(price, Number(e.target.value)); }} 
          />
          <span className="text-xs font-bold text-slate-400 ml-1">đ</span>
        </div>

        {error && <p className="text-[10px] font-bold text-red-500 text-right">{error}</p>}

        <button 
          onClick={handleSave} 
          disabled={isSaving || !!error} 
          className="w-full py-2.5 mt-1 text-xs font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-all active:scale-95 disabled:bg-slate-300 cursor-pointer"
        >
          {isSaving ? 'ĐANG LƯU...' : 'CẬP NHẬT GIÁ'}
        </button>
      </div>
    </div>
  );
}