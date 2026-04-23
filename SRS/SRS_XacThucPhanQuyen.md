# Software Requirement Specification (SRS)
## Chức năng: Xác thực & Phân quyền (Authentication & Authorization)
**Người soạn thảo:** Nguyễn Tiến Đức Huy  

---

### 1. Mô tả tổng quan (Description)
Hệ thống StudyHub sử dụng hai cơ chế xác thực song song:

* **Laravel Session Authentication:** Dành cho Admin đăng nhập vào giao diện quản trị Blade (`/admin/*`). Middleware `CheckAdmin` đảm bảo chỉ tài khoản có `role = admin` mới được phép truy cập.
* **Laravel Sanctum (Bearer Token):** Dành cho toàn bộ API (`/api/*`) phục vụ ứng dụng React.js Frontend. Token được cấp phát sau khi đăng nhập thành công và phải được đính kèm trong header `Authorization: Bearer {token}` cho các request cần xác thực.

Người dùng API có thể đăng nhập bằng email/mật khẩu hoặc qua Google OAuth 2.0. Tài khoản mới bắt buộc phải xác thực địa chỉ email qua mã OTP trước khi được phép đăng nhập.

---

### 2. Luồng nghiệp vụ (User Workflow)

#### 2.1. Đăng ký và xác thực OTP

```
[React Frontend]
    |
    |-- POST /api/register {name, email, password}
    |                              |
    |                   [Laravel Backend]
    |                       Tạo User (status: unverified)
    |                       Gửi OTP 6 số qua email (Laravel Mail)
    |<-- 200 OK "Vui lòng kiểm tra email"
    |
    |-- POST /api/register/verify-email {email, otp}
    |                              |
    |                   Xác thực OTP (EmailVerification table)
    |                   Cập nhật email_verified_at
    |                   Tạo Sanctum Token
    |<-- 200 OK {token, user}
```

#### 2.2. Đăng nhập thông thường

```
[React Frontend]
    |-- POST /api/login {email, password}
    |                              |
    |                   Kiểm tra email + bcrypt verify password
    |                   Kiểm tra email_verified_at != null
    |                   Tạo Sanctum Token mới
    |<-- 200 OK {token, user}
```

#### 2.3. Đăng nhập Google OAuth

```
[React Frontend]
    |-- Kích hoạt Google Sign-In SDK
    |<-- Google trả về id_token
    |
    |-- POST /api/login/google {id_token}
    |                              |
    |                   Xác thực id_token với Google API
    |                   Tìm hoặc tạo User theo email từ Google
    |                   Tạo Sanctum Token
    |<-- 200 OK {token, user}
```

#### 2.4. Admin đăng nhập (Session)

| Bước | Hành động | URL |
| :--- | :--- | :--- |
| 1 | Truy cập trang đăng nhập Admin | `GET /admin/login` |
| 2 | Gửi form email + mật khẩu | `POST /admin/login` |
| 3 | Laravel xác thực và tạo Session | Redirect về `GET /admin/dashboard` |
| 4 | Đăng xuất | `POST /admin/logout` → Xóa Session, redirect về `/admin/login` |

---

### 3. Yêu cầu dữ liệu (Data Requirements)

#### 3.1. Bảng `email_verifications` (MongoDB)

| Field | Kiểu dữ liệu | Mô tả |
| :--- | :--- | :--- |
| `email` | string | Email cần xác thực |
| `otp` | string | Mã OTP 6 chữ số |
| `expires_at` | timestamp | Thời điểm hết hạn OTP |
| `created_at` | timestamp | Thời điểm tạo |

#### 3.2. Bảng `personal_access_tokens` (MongoDB)

| Field | Kiểu dữ liệu | Mô tả |
| :--- | :--- | :--- |
| `tokenable_id` | string | ID người dùng sở hữu token |
| `tokenable_type` | string | `App\Models\User` |
| `name` | string | Tên token (ví dụ: `"auth_token"`) |
| `token` | string | Hash SHA-256 của plaintext token |
| `abilities` | array | Danh sách quyền (mặc định: `['*']`) |
| `expires_at` | timestamp / null | Thời điểm hết hạn; null = không hết hạn |
| `last_used_at` | timestamp | Thời điểm sử dụng gần nhất |

---

### 4. Ràng buộc kỹ thuật & Bảo mật (Technical Constraints)

* **Mã hóa mật khẩu:** `bcrypt` thông qua Laravel `hashed` cast — không thể giải mã ngược.
* **OTP:** 6 chữ số ngẫu nhiên, hết hạn sau thời gian giới hạn (cấu hình trong Backend). OTP cũ bị vô hiệu hóa khi người dùng request OTP mới.
* **Sanctum Token:** Lưu dạng hash SHA-256. Plaintext token chỉ hiển thị một lần duy nhất khi tạo.
* **CORS:** Laravel CORS config (`config/cors.php`) cho phép Frontend React gọi API từ domain khác. Preflight `OPTIONS /api/{any}` được xử lý riêng.
* **Middleware CheckAdmin:** Kiểm tra `auth()->check() && auth()->user()->role === 'admin'`. Fail → redirect `/admin/login`.
* **Google OAuth:** Backend không lưu `id_token` của Google. Chỉ sử dụng để xác thực một lần, sau đó cấp Sanctum Token nội bộ.

---

### 5. Trường hợp ngoại lệ & Xử lý lỗi (Edge Cases)

| Trường hợp | Xử lý |
| :--- | :--- |
| Đăng nhập khi email chưa xác thực OTP | Trả về 403: `"Tài khoản chưa được xác thực email."` |
| OTP nhập sai hoặc đã hết hạn | Trả về 422: `"Mã OTP không hợp lệ hoặc đã hết hạn."` |
| Đăng nhập sai mật khẩu | Trả về 401: `"Thông tin đăng nhập không chính xác."` |
| Google `id_token` không hợp lệ | Trả về 401: `"Xác thực Google thất bại."` |
| Bearer Token không hợp lệ hoặc bị thu hồi | Sanctum trả về 401. |
| Admin đã đăng nhập cố truy cập `/admin/login` | Redirect về `/admin/dashboard`. |
