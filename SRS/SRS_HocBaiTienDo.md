# Software Requirement Specification (SRS)
## Chức năng: Học bài & Theo dõi Tiến độ (Course Learning & Progress Tracking)

**Người soạn thảo:** Tiêu Trung Kiên 

---

### 1. Mô tả tổng quan (Description)
Sau khi học viên sở hữu một khóa học (đã thanh toán hoặc ghi danh miễn phí), học viên có thể truy cập toàn bộ nội dung bài học thông qua trang `CoursePlayer` trên React Frontend. Hệ thống phân phối nội dung học theo cấu trúc Unit/Lesson, theo dõi tiến độ từng bài học và lưu vào MongoDB. Khách vãng lai và học viên chưa mua có thể xem nội dung preview, nhưng video và nội dung đầy đủ chỉ hiển thị sau khi sở hữu.

---

### 2. Luồng nghiệp vụ (User Workflow)

#### 2.1. Truy cập nội dung khóa học

| Bước | Hành động | Endpoint | Phản hồi hệ thống |
| :--- | :--- | :--- | :--- |
| 1 | Học viên mở trang học bài | `GET /api/student/courses/{courseGroupId}/learn` | Trả về toàn bộ cấu trúc `courseData` và `blocks`; Backend kiểm tra quyền sở hữu và mở khóa video nếu hợp lệ. |
| 2 | Học viên chọn một Lesson | Frontend render block tương ứng (video, text, image, quiz) | |
| 3 | Học viên hoàn thành một bài | `POST /api/student/courses/{courseGroupId}/progress` `{lessonId}` | Cập nhật tiến độ bài học vào MongoDB; trả về phần trăm hoàn thành. |

#### 2.2. Xem nội dung công khai (không cần đăng nhập)

| Hành động | Endpoint | Phản hồi hệ thống |
| :--- | :--- | :--- |
| Xem chi tiết khóa học (giới thiệu, cấu trúc) | `GET /api/courses/{courseGroupId}/detail` | Trả về metadata khóa học; video bị khóa nếu không có token hợp lệ hoặc chưa mua. |
| Xem danh sách khóa học đã xuất bản | `GET /api/courses` | Trả về danh sách khóa học `status = PUBLISHED`. |
| Tìm kiếm khóa học | `GET /api/courses/search?q=...` | Tìm kiếm theo tiêu đề, tag, danh mục. |

---

### 3. Yêu cầu dữ liệu (Data Requirements)

#### 3.1. Cấu trúc `courseData` (nhúng trong document `courses`)
```json
[
  {
    "unitId": "unit_1",
    "title": "Chương 1: Giới thiệu",
    "lessons": [
      { "lessonId": "lesson_1_1", "title": "Bài 1: Tổng quan" },
      { "lessonId": "lesson_1_2", "title": "Bài 2: Cài đặt môi trường" }
    ]
  }
]
```

#### 3.2. Cấu trúc `blocks` (nhúng trong document `courses`)
Mỗi block ứng với một `lessonId`, có kiểu (`type`) là một trong:
* `text`: Nội dung văn bản định dạng.
* `video`: URL video (lưu trên Storage).
* `image`: URL hình ảnh minh họa.
* `quiz`: Câu hỏi trắc nghiệm với các lựa chọn và đáp án.

#### 3.3. Lưu trữ tiến độ (nhúng trong document `orders` hoặc collection riêng)

| Field | Kiểu dữ liệu | Mô tả |
| :--- | :--- | :--- |
| `user_id` | string | ID học viên |
| `course_group_id` | string | ID khóa học |
| `completed_lessons` | array | Danh sách `lessonId` đã hoàn thành |
| `progress_percent` | float | Phần trăm hoàn thành (0–100) |
| `last_accessed_at` | timestamp | Lần truy cập gần nhất |

---

### 4. Ràng buộc kỹ thuật & Bảo mật (Technical Constraints)

* **Kiểm soát quyền truy cập:** `LearnController::showCourseContent` kiểm tra xem `auth()->user()` có Order với `status = completed` cho khóa học này không. Nếu không, video URL bị thay thế bằng `null` hoặc URL placeholder.
* **Endpoint công khai:** `GET /api/student/courses/{courseGroupId}/learn` sử dụng Sanctum guard nhưng **không bắt lỗi** nếu không có token — Backend tự populate `auth()->user()` nếu token hợp lệ, trả về nội dung hạn chế nếu không có.
* **Cập nhật tiến độ:** `POST /api/student/courses/{courseGroupId}/progress` yêu cầu Bearer Token (học viên phải đăng nhập).
* **Tìm kiếm:** `GET /api/courses/search` là endpoint công khai, không cần xác thực.

---

### 5. Trường hợp ngoại lệ & Xử lý lỗi (Edge Cases)

| Trường hợp | Xử lý |
| :--- | :--- |
| Học viên truy cập khóa học chưa mua | Video URL bị ẩn; chỉ hiển thị phần preview. |
| Khóa học bị `UNPUBLISHED` sau khi học viên đã mua | Học viên vẫn tiếp tục truy cập được (quyền lợi đã mua được bảo toàn). |
| `lessonId` gửi lên không tồn tại trong `courseData` | Trả về 422: `"Bài học không hợp lệ."` |
| Học viên gửi tiến độ khi chưa đăng nhập | Sanctum trả về 401. |
