import React, { useState, useEffect } from 'react';

export default function CoursePricingEditor({ 
  initialPrice = 0, 
  initialDiscountPrice = 0, 
  isPublished = false, 
  onSave, 
  isSaving = false 
}) {
  const [price, setPrice] = useState(initialPrice);
  const [discountPrice, setDiscountPrice] = useState(initialDiscountPrice);
  const [error, setError] = useState('');

  useEffect(() => {
    setPrice(initialPrice || 0);
    // Nếu có giá giảm thì hiển thị, nếu không thì để trống ô input
    setDiscountPrice(initialDiscountPrice !== null && initialDiscountPrice !== undefined ? initialDiscountPrice : '');
  }, [initialPrice, initialDiscountPrice]);

  const validateAndSync = (p, d) => {
    if (d !== '' && Number(d) > Number(p)) {
      setError('Giá sau giảm không được lớn hơn giá gốc!');
      return false;
    }
    setError('');
    return true;
  };

  const handleSave = () => {
    if (validateAndSync(price, discountPrice)) {
      onSave({ 
        price: Number(price), 
        // Nếu để trống thì ngầm hiểu là KHÔNG GIẢM (bằng giá gốc)
        discountPrice: discountPrice === '' ? Number(price) : Number(discountPrice) 
      });
    }
  };

  // Logic hiển thị real-time trên component
  const pNum = Number(price) || 0;
  const dNum = discountPrice === '' ? pNum : Number(discountPrice);
  const finalPrice = dNum < pNum ? dNum : pNum;
  const isFree = finalPrice === 0;

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
        {/* GIÁ GỐC */}
        <div className={`flex items-center justify-between bg-white px-3 py-2 rounded-xl border ${isPublished ? 'bg-slate-100 border-slate-200' : 'border-slate-200 focus-within:border-blue-400'}`}>
          <label className="text-[10px] font-extrabold text-slate-400 uppercase w-20">Giá gốc {isPublished && '🔒'}</label>
          <input 
            type="number" 
            disabled={isPublished} 
            className="flex-1 text-right text-sm font-bold text-slate-700 bg-transparent outline-none disabled:cursor-not-allowed" 
            value={price} 
            onChange={(e) => { setPrice(Number(e.target.value)); validateAndSync(Number(e.target.value), discountPrice); }} 
          />
          <span className="text-xs font-bold text-slate-400 ml-1">đ</span>
        </div>

        {/* GIÁ SAU GIẢM */}
        <div className={`flex items-center justify-between bg-white px-3 py-2 rounded-xl border ${error ? 'border-red-400' : 'border-slate-200 focus-within:border-green-400'}`}>
          <label className="text-[10px] font-extrabold text-green-500 uppercase w-20">Giá sau giảm</label>
          <input 
            type="number" 
            className="flex-1 text-right text-sm font-bold text-green-600 bg-transparent outline-none" 
            value={discountPrice} 
            placeholder="Nhập 0 để Free"
            onChange={(e) => { setDiscountPrice(e.target.value); validateAndSync(price, e.target.value); }} 
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