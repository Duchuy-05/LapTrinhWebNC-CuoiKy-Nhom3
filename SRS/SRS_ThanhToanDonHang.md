# Software Requirement Specification (SRS)
## Chức năng: Thanh toán & Quản lý Đơn hàng (Payment & Order Management)
**Mã chức năng:** STUDENT-01 / ADMIN-03  
**Trạng thái:** Final  
**Người soạn thảo:** Nguyễn Tiến Đức Huy  
**Vai trò:** Backend / Payment Integration Developer

---

### 1. Mô tả tổng quan (Description)
Hệ thống StudyHub tích hợp cổng thanh toán **PayOS** để xử lý việc học viên mua khóa học. Toàn bộ luồng thanh toán được tự động hóa: học viên tạo đơn hàng → chuyển hướng đến trang thanh toán PayOS → PayOS gọi webhook báo kết quả → Hệ thống tự động kích hoạt quyền truy cập khóa học cho học viên.

Đối với khóa học miễn phí (`price = 0`), học viên có thể ghi danh trực tiếp (`enroll`) mà không cần thanh toán.

---

### 2. Luồng nghiệp vụ (User Workflow)

#### 2.1. Luồng mua khóa học có phí

```
[Học viên - React Frontend]
    |
    |-- POST /api/student/courses/{courseGroupId}/checkout
    |   (Bearer Token bắt buộc)
    |                              |
    |                   [Laravel Backend - OderController]
    |                       Tạo Order (status: pending)
    |                       Gọi PayOS API tạo payment link
    |<-- 200 OK {checkoutUrl, orderCode}
    |
    |-- Redirect học viên đến checkoutUrl (PayOS)
    |
    |   [Học viên thanh toán trên trang PayOS]
    |
    |   [PayOS gọi Webhook]
    |-- POST /api/webhook/payos {orderCode, status, ...}
    |                              |
    |                   Verify chữ ký HMAC từ PayOS
    |                   Cập nhật Order status = 'completed'
    |                   Kích hoạt quyền truy cập cho học viên
    |<-- 200 OK (PayOS nhận xác nhận)
    |
    |-- GET /api/student/courses/{courseGroupId}/order-status
    |<-- 200 OK {status: "completed"}  (Frontend polling/redirect)
```

#### 2.2. Ghi danh khóa học miễn phí

| Bước | Hành động | Endpoint |
| :--- | :--- | :--- |
| 1 | Học viên nhấn "Học ngay" | `POST /api/student/enroll/{courseGroupId}` |
| 2 | Hệ thống tạo Order với `status = completed`, `amount = 0` | Học viên ngay lập tức có quyền truy cập. |

#### 2.3. Admin quản lý đơn hàng (Blade)

| Bước | Hành động | URL |
| :--- | :--- | :--- |
| 1 | Xem danh sách đơn hàng | `GET /admin/orders` |
| 2 | Cập nhật trạng thái thủ công | `POST /admin/orders/{id}/status` |

---

### 3. Yêu cầu dữ liệu (Data Requirements)

#### 3.1. Dữ liệu đầu vào
* **courseGroupId:** ID nhóm khóa học, bắt buộc.
* **Thông tin thanh toán:** Do học viên nhập trực tiếp trên trang PayOS (không đi qua Backend StudyHub).

#### 3.2. Dữ liệu lưu trữ (MongoDB - Collection `orders`)

| Field | Kiểu dữ liệu | Mô tả |
| :--- | :--- | :--- |
| `_id` | ObjectId | Khóa chính |
| `user_id` | string | ID học viên |
| `course_group_id` | string | ID khóa học |
| `order_code` | string | Mã đơn hàng unique (gửi lên PayOS) |
| `amount` | integer | Số tiền thanh toán (VNĐ) |
| `status` | string | `pending`, `completed`, `cancelled`, `failed` |
| `payment_gateway` | string | `payos` |
| `payos_payment_link_id` | string | ID link thanh toán từ PayOS |
| `created_at` | timestamp | Thời điểm tạo đơn |
| `updated_at` | timestamp | Thời điểm cập nhật |

---

### 4. Ràng buộc kỹ thuật & Bảo mật (Technical Constraints)

* **Xác thực Webhook:** Mỗi webhook từ PayOS đều chứa chữ ký HMAC. Backend phải verify chữ ký này trước khi xử lý để tránh giả mạo. Key xác thực được cấu hình trong `.env` (`PAYOS_CHECKSUM_KEY`).
* **Idempotency:** Webhook có thể được gọi nhiều lần. Hệ thống kiểm tra trạng thái đơn hàng trước khi cập nhật để tránh xử lý trùng lặp (nếu `status` đã là `completed`, bỏ qua).
* **Bảo mật endpoint checkout:** `POST /api/student/courses/{courseGroupId}/checkout` yêu cầu Bearer Token (middleware `auth:sanctum`). Khách vãng lai không thể tạo đơn hàng.
* **Webhook endpoint:** `POST /api/webhook/payos` là public (PayOS không đính kèm token), nhưng được bảo vệ bằng xác thực chữ ký HMAC.
* **Biến môi trường PayOS:** `PAYOS_CLIENT_ID`, `PAYOS_API_KEY`, `PAYOS_CHECKSUM_KEY` được lưu trong `.env` và không được commit lên Git.

---

### 5. Trường hợp ngoại lệ & Xử lý lỗi (Edge Cases)

| Trường hợp | Xử lý |
| :--- | :--- |
| Học viên cố mua khóa học đã sở hữu | Hệ thống kiểm tra Order tồn tại, trả về 409: `"Bạn đã sở hữu khóa học này."` |
| Webhook đến với chữ ký không hợp lệ | Từ chối xử lý, trả về 400, ghi log cảnh báo. |
| PayOS timeout, không nhận được webhook | Frontend polling `order-status` sau redirect; Admin có thể cập nhật thủ công. |
| Học viên đóng tab trước khi hoàn tất thanh toán | Order giữ `status = pending`; không kích hoạt quyền truy cập. |
| Học viên chưa đăng nhập cố checkout | API trả về 401, Frontend redirect về trang đăng nhập. |
