# Software Requirement Specification (SRS)
## Chức năng: Bình luận, Đánh giá & Thống kê (Comments, Reviews & Statistics)
**Người soạn thảo:** Tiêu Trung Kiên

---

### 1. Mô tả tổng quan (Description)
Sau khi hoàn thành hoặc trong quá trình học, học viên có thể gửi đánh giá (rating) và bình luận (review) cho khóa học. Dữ liệu đánh giá được nhúng trực tiếp vào document của khóa học trong MongoDB (`comments` array) và cập nhật các trường thống kê (`rating_count`, `rating_score`).

Giảng viên có quyền truy cập trang **Thống kê** để xem tổng quan doanh thu, số học viên và điểm đánh giá các khóa học của mình. Ngoài ra, giảng viên có thể yêu cầu rút tiền (payout) và quản lý thông tin ngân hàng.

---

### 2. Luồng nghiệp vụ (User Workflow)

#### 2.1. Học viên đánh giá khóa học

| Bước | Hành động | Endpoint |
| :--- | :--- | :--- |
| 1 | Học viên gửi đánh giá (rating + nội dung) | `POST /api/student/courses/{courseGroupId}/comment` |
| 2 | Backend thêm comment vào `comments` array trong document khóa học | Cập nhật `rating_count` và tính lại `rating_score` trung bình. |
| 3 | Frontend hiển thị đánh giá mới ngay lập tức | |

#### 2.2. Giảng viên xem thống kê

| Hành động | Endpoint | Dữ liệu trả về |
| :--- | :--- | :--- |
| Lấy tổng quan thống kê | `GET /api/lecturer/statistics` | Tổng doanh thu, tổng học viên, danh sách khóa học kèm doanh thu từng khóa. |
| Xem danh sách học viên | `GET /api/lecturer/my-students` | Danh sách học viên đang học khóa học của giảng viên. |

#### 2.3. Giảng viên quản lý Payout (Rút tiền)

| Bước | Hành động | Endpoint |
| :--- | :--- | :--- |
| 1 | Cập nhật thông tin ngân hàng | `POST /api/update-bank-info` |
| 2 | Xem thông tin ngân hàng hiện tại | `GET /api/get-bank-info` |
| 3 | Gửi yêu cầu rút tiền | `POST /api/request-payout` |
| 4 | Xem lịch sử yêu cầu rút tiền | `GET /api/my-payouts` |
| 5 | Hủy yêu cầu rút tiền đang chờ | `POST /api/cancel-payout` |

#### 2.4. Admin duyệt Payout (Blade)

| Bước | Hành động | URL |
| :--- | :--- | :--- |
| 1 | Xem danh sách yêu cầu rút tiền | `GET /admin/payouts` |
| 2 | Duyệt yêu cầu | `POST /admin/payouts/{id}/approve` |

---

### 3. Yêu cầu dữ liệu (Data Requirements)

#### 3.1. Cấu trúc Comment (nhúng trong `courses.comments`)
```json
{
  "user_id": "...",
  "user_name": "Nguyễn Văn A",
  "rating": 5,
  "content": "Khóa học rất hay và chi tiết!",
  "created_at": "2026-04-23T10:00:00Z"
}
```

#### 3.2. Collection `payout_requests` (MongoDB)

| Field | Kiểu dữ liệu | Mô tả |
| :--- | :--- | :--- |
| `_id` | ObjectId | Khóa chính |
| `instructor_id` | string | ID giảng viên |
| `amount` | integer | Số tiền yêu cầu rút (VNĐ) |
| `bank_info` | object | Thông tin ngân hàng snapshot tại thời điểm yêu cầu |
| `status` | string | `pending`, `approved`, `rejected`, `cancelled` |
| `note` | string / null | Ghi chú từ Admin khi duyệt/từ chối |
| `created_at` | timestamp | Thời điểm tạo yêu cầu |
| `updated_at` | timestamp | Thời điểm cập nhật |

---

### 4. Ràng buộc kỹ thuật & Bảo mật (Technical Constraints)

* **Quyền đánh giá:** Chỉ học viên đã đăng nhập (Bearer Token) và đã sở hữu khóa học mới được gửi đánh giá. `CommentController` kiểm tra Order `status = completed` trước khi cho phép.
* **Thống kê giảng viên:** `GET /api/lecturer/statistics` yêu cầu Bearer Token; chỉ trả về dữ liệu của các khóa học có `authorId` trùng với `auth()->id()`.
* **Payout:** Tất cả các endpoint payout yêu cầu Bearer Token. Backend kiểm tra `role = instructor` trước khi cho phép thao tác.
* **Admin Payout:** Route `/admin/payouts/*` được bảo vệ bởi middleware Session Auth + CheckAdmin.

---

### 5. Trường hợp ngoại lệ & Xử lý lỗi (Edge Cases)

| Trường hợp | Xử lý |
| :--- | :--- |
| Học viên chưa mua cố gửi đánh giá | Trả về 403: `"Bạn cần sở hữu khóa học để đánh giá."` |
| Giảng viên gửi yêu cầu rút tiền khi chưa có thông tin ngân hàng | Trả về 422: `"Vui lòng cập nhật thông tin ngân hàng trước khi rút tiền."` |
| Giảng viên hủy yêu cầu đã được duyệt | Trả về 409: `"Không thể hủy yêu cầu đã được xử lý."` |
| Rating gửi lên ngoài phạm vi 1–5 | Trả về 422 với thông báo validate. |
