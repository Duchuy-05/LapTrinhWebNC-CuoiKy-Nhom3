# Software Requirement Specification (SRS)
## Chức năng: Quản lý Khóa học (Course Management)
**Mã chức năng:** ADMIN-01 / LECTURER-01  
**Trạng thái:** Final  
**Người soạn thảo:** Tiêu Trung Kiên  
**Vai trò:** Backend / Full-stack Developer

---

### 1. Mô tả tổng quan (Description)
Chức năng Quản lý Khóa học phục vụ hai nhóm người dùng:

* **Giảng viên (Lecturer):** Soạn thảo, cập nhật nội dung và quản lý vòng đời xuất bản (Draft → Published → Unpublished) của khóa học thông qua REST API (xác thực Sanctum Token).
* **Quản trị viên (Admin):** Xem, sửa và xóa bất kỳ khóa học nào trong hệ thống thông qua giao diện Admin (Blade template, xác thực Session).

Dữ liệu khóa học được lưu trữ trong MongoDB dưới dạng document linh hoạt, cho phép nhúng cấu trúc Unit/Lesson (`courseData`) và toàn bộ nội dung block bài học (`blocks`) vào trong cùng một document.

---

### 2. Luồng nghiệp vụ (User Workflow)

#### 2.1. Giảng viên soạn thảo và xuất bản khóa học (API)

| Bước | Hành động người dùng | Endpoint | Phản hồi hệ thống |
| :--- | :--- | :--- | :--- |
| 1 | Tạo khóa học mới | `POST /api/lecturer/courses` | Tạo document với `status = DRAFT`, trả về `courseGroupId`. |
| 2 | Xem bản nháp | `GET /api/lecturer/courses/{courseGroupId}/draft` | Trả về toàn bộ dữ liệu khóa học dạng JSON. |
| 3 | Cập nhật nội dung (tiêu đề, mô tả, cấu trúc bài học, block nội dung) | `PUT /api/lecturer/courses/{courseGroupId}/draft` | Lưu thay đổi, trả về document đã cập nhật. |
| 4 | Tải lên video bài học | `POST /api/lecturer/courses/upload-video` | Lưu file vào storage, trả về URL công khai. |
| 5 | Tải lên hình ảnh | `POST /api/lecturer/courses/upload-image` | Lưu file vào storage, trả về URL công khai. |
| 6 | Đặt giá khóa học | `PUT /api/lecturer/courses/{courseGroupId}/price` | Cập nhật `price` và `discountPrice`. |
| 7 | Xuất bản khóa học | `POST /api/lecturer/courses/{courseGroupId}/publish` | Tạo document mới với `status = PUBLISHED` (version tăng). |
| 8 | Thu hồi khóa học | `POST /api/lecturer/courses/{courseGroupId}/unpublish` | Cập nhật `status = UNPUBLISHED`, ẩn khóa học khỏi danh sách công khai. |

#### 2.2. Admin quản lý khóa học (Blade Admin Panel)

| Bước | Hành động | URL | Phản hồi hệ thống |
| :--- | :--- | :--- | :--- |
| 1 | Xem danh sách khóa học | `GET /admin/courses` | Hiển thị table view toàn bộ khóa học. |
| 2 | Tạo khóa học mới | `GET /admin/courses/create` → `POST /admin/courses` | Form nhập liệu và lưu vào MongoDB. |
| 3 | Chỉnh sửa khóa học | `GET /admin/courses/{id}/edit` → `PUT /admin/courses/{id}` | Cập nhật thông tin khóa học. |
| 4 | Xóa khóa học | `DELETE /admin/courses/{id}` | Xóa document, yêu cầu xác nhận trước khi thực hiện. |

---

### 3. Yêu cầu dữ liệu (Data Requirements)

#### 3.1. Dữ liệu đầu vào (Input Fields)
* **Tiêu đề (`title`):** `string`, tối đa 255 ký tự, bắt buộc.
* **Mô tả (`description`):** `text`, hỗ trợ định dạng (rich text), bắt buộc.
* **Danh mục (`category_id`):** `ObjectId`, chọn từ danh sách danh mục có sẵn, bắt buộc.
* **Thumbnail (`thumbnail`):** `file` (jpg, png, webp), tối đa 5MB.
* **Tags (`tags`):** `string`, các tag phân tách bằng dấu phẩy.
* **Cấu trúc bài học (`courseData`):** `array` (JSON), danh sách Unit và Lesson.
* **Nội dung block (`blocks`):** `array` (JSON), mỗi block có `type` là một trong: `text`, `video`, `image`, `quiz`.
* **Giá (`price`):** `integer` (VNĐ), bắt buộc khi xuất bản.
* **Giá khuyến mãi (`discountPrice`):** `integer` (VNĐ) hoặc `null` nếu không khuyến mãi.

#### 3.2. Dữ liệu lưu trữ (MongoDB - Collection `courses`)

| Field | Kiểu dữ liệu | Mô tả |
| :--- | :--- | :--- |
| `_id` | ObjectId | Khóa chính tự động |
| `courseGroupId` | string | ID nhóm liên kết các phiên bản Draft/Published |
| `status` | string | `DRAFT`, `PUBLISHED`, `UNPUBLISHED`, `ARCHIVED` |
| `version` | integer | Phiên bản, tăng mỗi lần xuất bản |
| `title` | string | Tiêu đề khóa học |
| `description` | text | Mô tả chi tiết |
| `thumbnail` | string | URL ảnh bìa |
| `tags` | string | Các từ khóa |
| `courseData` | array | Cấu trúc Unit/Lesson (cột điều hướng bên trái) |
| `blocks` | array | Nội dung từng block bài học |
| `authorId` | string | ID giảng viên (User._id) |
| `price` | integer | Giá gốc (VNĐ) |
| `discountPrice` | integer / null | Giá khuyến mãi; null = không KM |
| `student_count` | integer | Số học viên đã đăng ký |
| `rating_count` | integer | Số lượt đánh giá |
| `rating_score` | float | Điểm đánh giá trung bình (0.0 – 5.0) |
| `comments` | array | Danh sách bình luận/đánh giá nhúng |
| `created_at` | timestamp | Thời điểm tạo |
| `updated_at` | timestamp | Thời điểm cập nhật |

---

### 4. Ràng buộc kỹ thuật & Bảo mật (Technical Constraints)

* **Phân quyền API (Giảng viên):** Tất cả endpoint `/api/lecturer/*` yêu cầu Bearer Token hợp lệ (Laravel Sanctum). Giảng viên chỉ được phép thao tác trên các khóa học có `authorId` trùng với `id` của chính mình.
* **Phân quyền Admin:** Các route `/admin/courses/*` được bảo vệ bởi middleware `auth` kết hợp `CheckAdmin`. Truy cập không hợp lệ sẽ bị redirect về trang đăng nhập Admin.
* **Xử lý file upload:** Hệ thống kiểm tra MIME type và giới hạn dung lượng trước khi lưu. Video được lưu vào `storage/app/public/videos`, hình ảnh vào `storage/app/public/images`.
* **Vòng đời Document (Versioning):** Khi giảng viên `publish`, hệ thống tạo một document **mới** với `status = PUBLISHED` thay vì ghi đè document Draft. Điều này đảm bảo học viên đang học không bị ảnh hưởng bởi bản Draft đang chỉnh sửa.
* **Giao thức:** Toàn bộ giao tiếp qua HTTPS.

---

### 5. Trường hợp ngoại lệ & Xử lý lỗi (Edge Cases)

| Trường hợp | Xử lý |
| :--- | :--- |
| Tải file vượt quá dung lượng giới hạn | Trả về lỗi 422: `"Dung lượng tệp quá lớn (Tối đa 5MB cho ảnh)"`. |
| Giảng viên cố publish khóa học chưa có tiêu đề hoặc giá | Trả về lỗi 422 với thông báo validate từng trường còn thiếu. |
| Giảng viên truy cập khóa học của người khác | Trả về lỗi 403: `"Bạn không có quyền truy cập khóa học này"`. |
| Admin xóa khóa học đang có học viên đang học | Hệ thống cảnh báo xác nhận và ghi log trước khi xóa. |
| Token hết hạn khi giảng viên đang soạn thảo | API trả về 401, Frontend hiển thị thông báo yêu cầu đăng nhập lại. |
