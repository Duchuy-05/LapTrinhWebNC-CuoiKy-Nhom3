import React, { useState, useEffect } from 'react';

/**
 * CoursePricingEditor — chỉ dùng cho khóa học ĐÃ XUẤT BẢN.
 *
 * Quy ước giá:
 *   price          = Giá gốc (đã bị khóa, không thể đổi sau khi publish)
 *   discountPrice  = Giá khuyến mãi thực tế học viên trả (null = không KM)
 */
export default function CoursePricingEditor({
  initialPrice = 0,
  initialDiscountPrice = null,
  onSave,
  isSaving = false,
}) {
  const price = Number(initialPrice) || 0;

  const [discountInput, setDiscountInput] = useState(
    initialDiscountPrice !== null ? String(initialDiscountPrice) : ''
  );
  const [isDiscountEnabled, setIsDiscountEnabled] = useState(initialDiscountPrice !== null);
  const [error, setError] = useState('');

  useEffect(() => {
    setIsDiscountEnabled(initialDiscountPrice !== null);
    setDiscountInput(initialDiscountPrice !== null ? String(initialDiscountPrice) : '');
    setError('');
  }, [initialDiscountPrice]);

  const discountValue = discountInput === '' ? null : Number(discountInput);
  const effectivePrice = isDiscountEnabled && discountValue !== null ? discountValue : price;
  const isFree = effectivePrice === 0;
  const savingAmount = isDiscountEnabled && discountValue !== null ? price - discountValue : 0;
  const savingPercent =
    price > 0 && savingAmount > 0 ? Math.round((savingAmount / price) * 100) : 0;

  const validate = (val) => {
    if (!isDiscountEnabled) { setError(''); return true; }
    if (val === '' || val === null) { setError('Vui lòng nhập giá khuyến mãi.'); return false; }
    const n = Number(val);
    if (isNaN(n) || n < 0) { setError('Giá không hợp lệ.'); return false; }
    if (n >= price) { setError('Giá khuyến mãi phải nhỏ hơn giá gốc!'); return false; }
    setError('');
    return true;
  };

  const handleToggleDiscount = () => {
    const next = !isDiscountEnabled;
    setIsDiscountEnabled(next);
    if (!next) { setDiscountInput(''); setError(''); }
  };

  const handleDiscountChange = (e) => {
    setDiscountInput(e.target.value);
    validate(e.target.value);
  };

  const handleSave = () => {
    if (!validate(discountInput)) return;
    const finalDiscount = isDiscountEnabled && discountInput !== '' ? Number(discountInput) : null;
    onSave({ discountPrice: finalDiscount });
  };

  return (
    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3 bg-slate-50 border-b border-slate-100">
        <div className="flex items-center gap-2">
          <h3 className="text-sm font-bold text-slate-700">Sale</h3>
        </div>
        {isFree && (
          <span className="px-2 py-0.5 bg-green-100 text-green-600 text-[10px] font-black rounded-full">
            MIỄN PHÍ
          </span>
        )}
        {!isFree && isDiscountEnabled && savingPercent > 0 && (
          <span className="px-2 py-0.5 bg-red-100 text-red-500 text-[10px] font-black rounded-full">
            -{savingPercent}%
          </span>
        )}
      </div>

      <div className="p-4 space-y-3">
        {/* Giá gốc — chỉ đọc */}
        <div className="flex items-center justify-between bg-slate-50 px-3 py-2.5 rounded-xl border border-slate-200">
          <span className="text-[11px] font-extrabold text-slate-400 uppercase tracking-wide">
            Giá gốc
          </span>
          <span className="text-sm font-bold text-slate-600">
            {price === 0 ? 'Miễn phí' : `${price.toLocaleString('vi-VN')}đ`}
          </span>
        </div>

        {/* Toggle khuyến mãi */}
        <label className="flex items-center justify-between px-3 py-2.5 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition-colors">
          <span className="text-[11px] font-extrabold text-slate-500 uppercase tracking-wide">
            Bật khuyến mãi
          </span>
          <div className="relative">
            <input type="checkbox" className="sr-only peer" checked={isDiscountEnabled} onChange={handleToggleDiscount} />
            <div className="w-9 h-5 bg-slate-300 rounded-full peer peer-checked:bg-red-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all shadow-inner" />
          </div>
        </label>

        {/* Input giá khuyến mãi */}
        {isDiscountEnabled && (
          <div className={`flex items-center justify-between bg-white px-3 py-2.5 rounded-xl border transition-colors ${error ? 'border-red-400 bg-red-50' : 'border-red-200 focus-within:border-red-400'}`}>
            <label className="text-[11px] font-extrabold text-red-400 uppercase tracking-wide whitespace-nowrap mr-2">
              Giá KM
            </label>
            <input
              type="number"
              min="0"
              max={price - 1}
              className="flex-1 text-right text-sm font-bold text-red-500 bg-transparent outline-none"
              placeholder={`Tối đa ${(price - 1).toLocaleString('vi-VN')}`}
              value={discountInput}
              onChange={handleDiscountChange}
            />
            <span className="text-xs font-bold text-slate-400 ml-1">đ</span>
          </div>
        )}

        {/* Thông báo lỗi */}
        {error && (
          <p className="text-[10px] font-bold text-red-500 text-right px-1">{error}</p>
        )}

        {/* Nút lưu */}
        <button
          onClick={handleSave}
          disabled={isSaving || !!error}
          className="w-full py-2.5 mt-1 text-xs font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-all active:scale-95 disabled:bg-slate-300 disabled:cursor-not-allowed cursor-pointer"
        >
          {isSaving ? 'ĐANG LƯU...' : isDiscountEnabled ? 'ÁP DỤNG KHUYẾN MÃI' : 'XÓA KHUYẾN MÃI'}
        </button>
      </div>
    </div>
  );
}