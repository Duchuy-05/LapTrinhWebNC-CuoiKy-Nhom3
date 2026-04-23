@extends('layouts.admin')

@section('title', 'Thanh toán cho Giảng viên')

@section('content')
<div class="card card-primary card-outline card-outline-tabs">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active font-weight-bold text-primary" id="tabs-pending-tab" data-toggle="pill" href="#tabs-pending" role="tab">
                    <i class="fas fa-clock mr-1"></i> Đang chờ duyệt
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold text-success" id="tabs-history-tab" data-toggle="pill" href="#tabs-history" role="tab">
                    <i class="fas fa-history mr-1"></i> Lịch sử thanh toán
                </a>
            </li>
        </ul>
    </div>
    
    <div class="card-body">
        <div class="tab-content" id="custom-tabs-four-tabContent">
            
            {{-- ================= TAB 1: DANH SÁCH CHỜ DUYỆT ================= --}}
            <div class="tab-pane fade show active" id="tabs-pending" role="tabpanel">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Mã YC</th>
                            <th>Giảng viên</th>
                            <th>Thông tin Ngân hàng (CK)</th>
                            <th>Số tiền (VNĐ)</th>
                            <th>Ngày yêu cầu</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingPayouts as $payout)
                        <tr>
                            <td class="align-middle font-weight-bold text-secondary">
                                #PAY-{{ str_pad($payout->id, 4, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="align-middle">
                                <strong>{{ optional($payout->user)->name ?? 'Không xác định' }}</strong><br>
                                <small class="text-muted">{{ optional($payout->user)->email ?? '' }}</small>
                            </td>
                            <td class="align-middle bg-warning" style="opacity: 0.9;">
                                <span class="font-weight-bold text-dark">Ngân hàng:</span> {{ $payout->bank_name }} <br>
                                <span class="font-weight-bold text-dark">STK:</span> <span class="text-danger font-weight-bold text-lg">{{ $payout->account_number }}</span> <br>
                                <span class="font-weight-bold text-dark">Chủ TK:</span> {{ $payout->account_name }}
                            </td>
                            <td class="align-middle text-primary font-weight-bold text-lg">
                                {{ number_format($payout->amount) }} đ
                            </td>
                            <td class="align-middle">{{ $payout->created_at->format('d/m/Y H:i') }}</td>
                            <td class="align-middle text-center">
                                <button type="button" class="btn btn-success btn-sm font-weight-bold shadow-sm" data-toggle="modal" data-target="#approveModal-{{ $payout->id }}">
                                    <i class="fas fa-check-circle"></i> Duyệt & Up ảnh
                                </button>
                            </td>
                        </tr>

                        {{-- MODAL XÁC NHẬN DUYỆT & UP ẢNH CHO YÊU CẦU NÀY --}}
                        <div class="modal fade" id="approveModal-{{ $payout->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title font-weight-bold">Xác nhận Đã Chuyển Khoản</h5>
                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    {{-- Gắn Route duyệt tương ứng với ID của giao dịch --}}
                                    <form action="{{ route('admin.payouts.approve', $payout->id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="modal-body text-left">
                                            <div class="alert alert-light border border-success">
                                                Bạn đang xác nhận chuyển <strong>{{ number_format($payout->amount) }} đ</strong> 
                                                cho giảng viên <strong>{{ optional($payout->user)->name ?? 'Không xác định' }}</strong>.
                                            </div>
                                            <div class="form-group mt-3">
                                                <label>Tải lên ảnh Hóa đơn / Ủy nhiệm chi <span class="text-danger">*</span></label>
                                                <input type="file" name="receipt_image" class="form-control-file" accept="image/*" required>
                                                <small class="form-text text-muted">Bắt buộc phải có ảnh màn hình chuyển khoản thành công làm bằng chứng.</small>
                                            </div>
                                            <div class="form-group">
                                                <label>Ghi chú thêm (Tùy chọn)</label>
                                                <textarea name="admin_note" class="form-control" rows="2" placeholder="VD: Đã chuyển khoản qua app VCB..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                                            <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Xác nhận Hoàn tất</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        {{-- END MODAL --}}
                        
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                                <h5>Không có yêu cầu rút tiền nào đang chờ duyệt.</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ================= TAB 2: LỊCH SỬ THANH TOÁN ================= --}}
            <div class="tab-pane fade" id="tabs-history" role="tabpanel">
                <table class="table table-bordered table-striped">
                    <thead class="bg-light">
                        <tr>
                            <th>Mã YC</th>
                            <th>Giảng viên</th>
                            <th>Số tiền đã CK</th>
                            <th>Trạng thái</th>
                            <th>Ngày duyệt</th>
                            <th class="text-center">Biên lai (Bằng chứng)</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Vòng lặp lấy lịch sử đã thanh toán --}}
                        @forelse($completedPayouts as $payout)
                        <tr>
                            <td class="align-middle font-weight-bold text-secondary">
                                #PAY-{{ str_pad($payout->id, 4, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="align-middle">
                                <strong>{{ optional($payout->user)->name ?? 'Không xác định' }}</strong><br>
                                <small class="text-muted">{{ optional($payout->user)->email ?? '' }}</small>
                            </td>
                            <td class="align-middle font-weight-bold text-success">
                                {{ number_format($payout->amount) }} đ
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-success"><i class="fas fa-check"></i> Đã thanh toán</span>
                            </td>
                            <td class="align-middle">{{ $payout->updated_at->format('d/m/Y H:i') }}</td>
                            <td class="align-middle text-center">
                                @if($payout->receipt_image)
                                    <a href="{{ asset('storage/' . $payout->receipt_image) }}" target="_blank" class="btn btn-sm btn-info shadow-sm">
                                        <i class="fas fa-image"></i> Xem Biên Lai
                                    </a>
                                @else
                                    <span class="text-muted text-sm">Không có ảnh</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-history fa-3x mb-3 opacity-50"></i>
                                <h5>Chưa có lịch sử thanh toán nào.</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

{{-- Hiển thị thông báo khi Admin duyệt thành công --}}
@if(session('success'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: 'success',
            title: 'Thành công!',
            text: "{{ session('success') }}",
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    });
</script>
@endif
@endsection