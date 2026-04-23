# Software Requirement Specification (SRS)
## Chức năng: Quản lý Người dùng (User Management)
 
**Người soạn thảo:** Nguyễn Trọng Đại  

---

### 1. Mô tả tổng quan (Description)
Chức năng Quản lý Người dùng bao gồm hai phạm vi:

* **Quản trị viên (Admin):** Toàn quyền quản lý tài khoản trong hệ thống qua giao diện Admin (Blade). Bao gồm: thêm, sửa, xóa tài khoản và phân quyền (Admin / Instructor / User).
* **Người dùng (Học viên & Giảng viên):** Tự đăng ký tài khoản qua API với luồng xác thực email OTP, hoặc đăng nhập nhanh bằng Google OAuth 2.0. Sau khi đăng nhập thành công, hệ thống cấp phát Sanctum Bearer Token dùng cho các request API tiếp theo.

Dữ liệu người dùng được lưu trữ trong MongoDB (collection `users`), mật khẩu được mã hóa bằng `bcrypt` trước khi lưu.

---

### 2. Luồng nghiệp vụ (User Workflow)

#### 2.1. Đăng ký tài khoản mới (API)

| Bước | Hành động người dùng | Endpoint | Phản hồi hệ thống |
| :--- | :--- | :--- | :--- |
| 1 | Nhập họ tên, email, mật khẩu và nhấn "Đăng ký" | `POST /api/register` | Tạo tài khoản với `status = unverified`, gửi mã OTP 6 chữ số đến email. |
| 2 | Nhập mã OTP nhận được | `POST /api/register/verify-email` | Xác thực OTP, kích hoạt tài khoản (`email_verified_at` được ghi), trả về Bearer Token. |
| 3 | (Tùy chọn) Yêu cầu gửi lại OTP | `POST /api/register/resend-otp` | Tạo OTP mới và gửi lại email, OTP cũ bị vô hiệu hóa. |

#### 2.2. Đăng nhập (API)

| Bước | Hành động người dùng | Endpoint | Phản hồi hệ thống |
| :--- | :--- | :--- | :--- |
| 1a | Đăng nhập bằng email + mật khẩu | `POST /api/login` | Kiểm tra thông tin đăng nhập, trả về Bearer Token và thông tin người dùng. |
| 1b | Đăng nhập bằng Google | `POST /api/login/google` | Nhận Google ID Token từ Frontend, xác thực với Google, tạo/cập nhật tài khoản và trả về Bearer Token. |
| 2 | Truy cập tài nguyên bảo vệ | Header: `Authorization: Bearer {token}` | Sanctum Middleware xác thực token và inject `auth()->user()` vào request. |

#### 2.3. Admin quản lý người dùng (Blade Admin Panel)

| Bước | Hành động | URL | Phản hồi hệ thống |
| :--- | :--- | :--- | :--- |
| 1 | Xem danh sách người dùng | `GET /admin/users` | Hiển thị table với phân trang, bộ lọc theo Role và trạng thái. |
| 2 | Thêm người dùng mới | `GET /admin/users/create` → `POST /admin/users` | Form nhập liệu, lưu tài khoản mới với mật khẩu đã mã hóa. |
| 3 | Chỉnh sửa thông tin | `GET /admin/users/{id}/edit` → `PUT /admin/users/{id}` | Cập nhật thông tin người dùng. |
| 4 | Xóa tài khoản | `DELETE /admin/users/{id}` | Yêu cầu xác nhận, xóa tài khoản và tất cả token liên quan. |

---

### 3. Yêu cầu dữ liệu (Data Requirements)

#### 3.1. Dữ liệu đầu vào (Input Fields)

**Đăng ký / Admin tạo tài khoản:**
* **Họ và tên (`name`):** `string`, tối đa 100 ký tự, bắt buộc.
* **Email:** `string`, định dạng email hợp lệ, duy nhất trong hệ thống, bắt buộc.
* **Mật khẩu (`password`):** `string`, tối thiểu 8 ký tự, bắt buộc khi tạo mới.
* **Phân quyền (`role`):** `enum` — `admin`, `instructor`, `user` (mặc định: `user`).

**Bảng OTP (Collection `email_verifications`):**
* **Email:** Liên kết với tài khoản đang xác thực.
* **OTP:** Mã 6 chữ số, có thời hạn hiệu lực.

#### 3.2. Dữ liệu lưu trữ (MongoDB - Collection `users`)

| Field | Kiểu dữ liệu | Mô tả |
| :--- | :--- | :--- |
| `_id` | ObjectId | Khóa chính tự động |
| `name` | string | Họ và tên |
| `email` | string | Địa chỉ email (unique, indexed) |
| `password` | string | Mật khẩu đã mã hóa (bcrypt) |
| `role` | string | `admin`, `instructor`, `user` |
| `email_verified_at` | timestamp / null | Thời điểm xác thực email; null = chưa xác thực |
| `bank_info` | object / null | Thông tin ngân hàng của giảng viên (số TK, tên NH...) |
| `remember_token` | string | Token ghi nhớ phiên |
| `created_at` | timestamp | Thời điểm tạo |
| `updated_at` | timestamp | Thời điểm cập nhật |

**MongoDB - Collection `personal_access_tokens` (Sanctum):**
* `tokenable_id`: ID người dùng.
* `token`: Hash SHA-256 của Bearer Token.
* `abilities`: Mảng quyền (`['*']`).
* `expires_at`: Thời điểm hết hạn token (nếu có).

---

### 4. Ràng buộc kỹ thuật & Bảo mật (Technical Constraints)

* **Mã hóa mật khẩu:** Mật khẩu được mã hóa một chiều bằng `bcrypt` (Laravel `hashed` cast) trước khi lưu vào MongoDB. Không ai — kể cả Admin — có thể xem mật khẩu gốc.
* **Xác thực Token (API):** Sử dụng Laravel Sanctum. Token được lưu dạng hash SHA-256. Mỗi lần đăng nhập mới tạo token mới, token cũ không tự động bị xóa (cần implement logout để revoke).
* **Xác thực Admin (Session):** Admin đăng nhập qua form riêng (`/admin/login`), sử dụng Laravel Session Authentication. Middleware `CheckAdmin` kiểm tra `role == 'admin'` trên mỗi request vào `/admin/*`.
* **Google OAuth:** Frontend nhận `id_token` từ Google SDK, gửi lên Backend (`POST /api/login/google`). Backend xác thực token với Google API, sau đó tạo hoặc cập nhật tài khoản và trả về Sanctum Token.
* **OTP Email:** Sử dụng Laravel Mail (SMTP), mỗi OTP chỉ có hiệu lực trong một khoảng thời gian giới hạn. OTP cũ bị vô hiệu hóa khi người dùng yêu cầu gửi lại.
* **Phân trang:** Danh sách người dùng trong Admin phải sử dụng Pagination để tránh quá tải khi số lượng tài khoản lớn.

---

### 5. Trường hợp ngoại lệ & Xử lý lỗi (Edge Cases)

| Trường hợp | Xử lý |
| :--- | :--- |
| Đăng ký với Email đã tồn tại | Trả về lỗi 422: `"Email này đã được sử dụng, vui lòng nhập email khác."` |
| Nhập sai OTP | Trả về lỗi 422: `"Mã OTP không hợp lệ hoặc đã hết hạn."` |
| Đăng nhập sai mật khẩu | Trả về lỗi 401: `"Email hoặc mật khẩu không chính xác."` |
| Đăng nhập bằng Google với email đã đăng ký thủ công | Hệ thống nhận diện qua email, liên kết tài khoản và trả về Token bình thường. |
| Token hết hạn hoặc không hợp lệ | Sanctum middleware trả về 401, Frontend redirect về trang đăng nhập. |
| Admin tự xóa tài khoản của chính mình | Hệ thống chặn thao tác và hiển thị thông báo: `"Bạn không thể xóa tài khoản đang đăng nhập."` |
| Người dùng chưa xác thực email cố đăng nhập | Trả về lỗi 403: `"Tài khoản chưa được xác thực. Vui lòng kiểm tra email."` |
