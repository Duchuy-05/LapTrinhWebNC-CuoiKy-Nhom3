# Software Requirement Specification (SRS)
## Chức năng: Quản lý Khóa học (Course Management)
**Mã chức năng:** ADMIN-01  
**Trạng thái:** Draft  
**Người soạn thảo:** Kien  
**Vai trò:** Backend / Admin Developer

---

### 1. Mô tả tổng quan (Description)
Cung cấp công cụ cho Quản trị viên (Admin) để khởi tạo, cập nhật thông tin, tải lên tài liệu và quản lý danh sách các khóa học trên hệ thống. Đảm bảo nội dung học tập luôn được cập nhật và chính xác.

### 2. Luồng nghiệp vụ (User Workflow)
| Bước | Hành động người dùng | Phản hồi hệ thống |
| :--- | :--- | :--- |
| 1 | Truy cập Menu `Quản lý khóa học` | Hiển thị danh sách các khóa học hiện có (Table view). |
| 2 | Nhấn nút "Thêm khóa học mới" | Hiển thị Form nhập liệu (Tên, Mô tả, Danh mục, Hình ảnh). |
| 3 | Nhập dữ liệu và nhấn "Lưu" | Validate dữ liệu đầu vào và tải tệp tin lên Server. |
| 4 | Lưu trữ thành công | Hiển thị thông báo thành công và cập nhật danh sách khóa học. |
| 5 | Chỉnh sửa/Xóa | Hệ thống yêu cầu xác nhận trước khi thực hiện thay đổi vĩnh viễn. |

### 3. Yêu cầu dữ liệu (Data Requirements)
#### 3.1. Dữ liệu đầu vào (Input Fields)
* **Tên khóa học:** `string`, tối đa 255 ký tự, bắt buộc.
* **Mô tả:** `text` (Rich editor), hỗ trợ định dạng văn bản, bắt buộc.
* **Danh mục:** `dropdown`, chọn từ danh sách chủ đề có sẵn (Hoặc tạo chủ đề mới).
* **Thumbnail:** `file` (jpg, png), tối đa 5MB.
* **Tài liệu đính kèm:** `file` (pdf, docx) hoặc link video.

#### 3.2. Dữ liệu lưu trữ (Database - Bảng `courses`)
* `title`: string, index.
* `description`: text.
* `category_id`: foreign key.
* `status`: boolean (Active/Inactive).
* `created_at`: timestamp.

### 4. Ràng buộc kỹ thuật & Bảo mật (Technical Constraints)
* **Phân quyền:** Chỉ người dùng có vai trò `ADMIN` mới có quyền truy cập và thực hiện CRUD.
* **Giao thức:** Truyền tải dữ liệu qua **HTTPS**.
* **Xử lý file:** Hệ thống phải kiểm tra định dạng tệp tin trước khi cho phép upload để tránh mã độc.

### 5. Trường hợp ngoại lệ & Xử lý lỗi (Edge Cases)
* **Trường hợp:** Tải tệp tin vượt quá dung lượng quy định.  
  * **Xử lý:** Hiển thị lỗi: "Dung lượng tệp quá lớn (Tối đa 5MB)".
* **Trường hợp:** Trùng tên khóa học đã có trong hệ thống.  
  * **Xử lý:** Thông báo: "Tên khóa học đã tồn tại, vui lòng chọn tên khác".
