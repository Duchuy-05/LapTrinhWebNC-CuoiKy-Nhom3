# Software Requirement Specification (SRS)
## Chức năng: Quản lý Người dùng (User Management)
**Mã chức năng:** ADMIN-02  
**Trạng thái:** Draft  
**Người soạn thảo:** Hậu  
**Vai trò:** Backend / Admin Developer

---

### 1. Mô tả tổng quan (Description)
Cung cấp công cụ cho Quản trị viên (Admin) quản lý toàn bộ tài khoản trên hệ thống, bao gồm tài khoản của người học (User) và giảng viên (Instructor). Admin có quyền thêm mới, chỉnh sửa thông tin, khóa/mở khóa tài khoản, phân quyền và khôi phục tài khoản khi cần thiết.

### 2. Luồng nghiệp vụ (User Workflow)
| Bước | Hành động người dùng | Phản hồi hệ thống |
| :--- | :--- | :--- |
| 1 | Truy cập Menu `Quản lý người dùng` | Hiển thị danh sách toàn bộ người dùng kèm thanh tìm kiếm và bộ lọc (Role, Trạng thái). |
| 2 | Nhấn "Thêm người dùng mới" | Hiển thị Form nhập liệu (Họ tên, Email, Mật khẩu, Phân quyền). |
| 3 | Nhấn "Sửa/Khóa/Khôi phục" trên 1 user | Hiển thị Modal xác nhận thao tác hoặc Form chỉnh sửa thông tin. |
| 4 | Lưu thay đổi | Validate dữ liệu, cập nhật Database và hiển thị thông báo thành công. |
| 5 | Gửi yêu cầu reset mật khẩu | Hệ thống tạo link khôi phục và gửi qua Email của người dùng đó. |

### 3. Yêu cầu dữ liệu (Data Requirements)
#### 3.1. Dữ liệu đầu vào (Input Fields)
* **Họ và tên:** `string`, tối đa 100 ký tự, bắt buộc.
* **Email:** `string`, định dạng email hợp lệ, duy nhất, bắt buộc.
* **Mật khẩu:** `string`, tối thiểu 8 ký tự (khi tạo mới), bắt buộc.
* **Phân quyền (Role):** `dropdown` (Admin, Instructor, User).
* **Trạng thái:** `dropdown` (Active, Locked).

#### 3.2. Dữ liệu lưu trữ (Database - Bảng `users`)
* `id`: primary key, auto increment.
* `full_name`: string.
* `email`: string, unique, index.
* `password_hash`: string (mã hóa).
* `role`: enum ('admin', 'instructor', 'user').
* `status`: enum ('active', 'locked').
* `created_at`, `updated_at`: timestamp.

### 4. Ràng buộc kỹ thuật & Bảo mật (Technical Constraints)
* **Phân quyền:** Chỉ người dùng có vai trò `ADMIN` mới truy cập được module này.
* **Bảo mật:** Mật khẩu người dùng phải được mã hóa một chiều (ví dụ: bcrypt) trước khi lưu vào cơ sở dữ liệu (Node.js backend đảm nhiệm). Không ai kể cả Admin có thể xem được mật khẩu gốc.
* **Hiệu suất:** Danh sách người dùng phải được phân trang (Pagination) để tránh quá tải khi số lượng học viên lớn.

### 5. Trường hợp ngoại lệ & Xử lý lỗi (Edge Cases)
* **Trường hợp:** Admin tạo tài khoản với Email đã tồn tại.  
  * **Xử lý:** Hiển thị lỗi: "Email này đã được sử dụng, vui lòng nhập email khác."
* **Trường hợp:** Admin cố gắng tự khóa (Lock) tài khoản của chính mình.  
  * **Xử lý:** Ẩn nút "Khóa" đối với dòng tài khoản đang đăng nhập hoặc hiển thị thông báo "Bạn không thể khóa tài khoản của chính mình."