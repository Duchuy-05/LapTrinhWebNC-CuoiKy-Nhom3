<div align="center">
  <strong>TRƯỜNG ĐẠI HỌC ĐIỆN LỰC</strong><br>
  <strong>KHOA CÔNG NGHỆ THÔNG TIN</strong>
</div>

<br>

# ĐỀ CƯƠNG MÔN LẬP TRÌNH WEB NÂNG CAO
## TÊN ĐỀ TÀI: XÂY DỰNG WEB HỌC TRỰC TUYẾN (STUDYHUB)

**Giảng viên hướng dẫn:** Ths CẤN ĐỨC ĐIỆP  
**Ngành:** CÔNG NGHỆ THÔNG TIN  
**Chuyên ngành:** CÔNG NGHỆ PHẦN MỀM  
**Lớp:** D18-CNPM2  
**Khóa:** D18  

*Hà Nội, năm 2026*

---

### 1. Tên đề tài
Thiết kế và xây dựng website học trực tuyến **StudyHub** sử dụng Laravel (PHP) kết hợp React.js và cơ sở dữ liệu MongoDB.

### 2. Sinh viên thực hiện

| STT | Tên sinh viên | Mã sinh viên |
|:---:|:---|:---|
| 1 | Nguyễn Trọng Đại | 23810310120 |
| 2 | Nguyễn Tiến Đức Huy | 23810310127 |
| 3 | Tiêu Trung Kiên | 23810310129 |

### 3. Giảng viên hướng dẫn
* **Họ và tên:** Cấn Đức Điệp
* **Học vị:** Thạc sĩ
* **Số điện thoại:** 0987838870 | **Email:** diepcd@gmail.com
* **Đơn vị công tác:** Khoa Công Nghệ Thông Tin trường Đại học Điện Lực.

### 4. Mô tả tóm tắt đề tài
Đề tài xây dựng nền tảng học trực tuyến **StudyHub** — một hệ thống web đầy đủ với kiến trúc tách biệt Frontend (React.js) và Backend (Laravel PHP REST API), sử dụng MongoDB làm cơ sở dữ liệu. Hệ thống cho phép giảng viên soạn thảo và xuất bản khóa học dạng đa phương tiện (video, hình ảnh, quiz), học viên đăng ký học và thanh toán trực tuyến qua cổng **PayOS**, theo dõi tiến độ học tập và để lại đánh giá. Quản trị viên có bảng điều khiển riêng để quản lý người dùng, danh mục, khóa học, đơn hàng và duyệt yêu cầu rút tiền của giảng viên. Hệ thống hỗ trợ xác thực đa phương thức bao gồm đăng nhập thông thường (kèm xác thực email OTP) và đăng nhập qua Google OAuth.

---

### 5. Nội dung báo cáo

#### Đặt vấn đề
Thị trường học trực tuyến (E-learning) đang tăng trưởng mạnh mẽ, thúc đẩy nhu cầu xây dựng các nền tảng có khả năng phục vụ đồng thời nhiều nhóm đối tượng: quản trị viên, giảng viên và học viên. Nhóm lựa chọn xây dựng **StudyHub** nhằm thực hành toàn bộ chu trình phát triển web hiện đại, từ thiết kế REST API với Laravel, quản lý dữ liệu phi cấu trúc với MongoDB, đến xây dựng giao diện người dùng phản hồi nhanh với React.js và tích hợp thanh toán thực tế với PayOS.

#### Chương 1: Tổng quan về lĩnh vực của bài toán cần giải quyết

Hệ thống StudyHub phục vụ ba nhóm người dùng với phân quyền rõ ràng:

**Chức năng dành cho Quản trị viên (Admin)**  
Admin đăng nhập qua giao diện quản trị riêng biệt (`/admin/login`) và có toàn quyền trên hệ thống:
* Xem tổng quan dashboard: tổng số người dùng, danh mục, khóa học, đơn hàng hoàn thành.
* Quản lý người dùng: thêm, sửa, xóa tài khoản; phân quyền (Admin / Instructor / User).
* Quản lý danh mục khóa học: thêm, sửa, xóa danh mục.
* Quản lý khóa học: xem, sửa, xóa; duyệt trạng thái xuất bản.
* Quản lý đơn hàng: xem danh sách, cập nhật trạng thái đơn hàng.
* Quản lý yêu cầu rút tiền: xem danh sách và duyệt/từ chối yêu cầu payout của giảng viên.

**Chức năng dành cho Giảng viên (Instructor/Lecturer)**  
Giảng viên đăng nhập qua giao diện học viên (API token - Sanctum) và có khu vực riêng:
* Soạn thảo khóa học theo cấu trúc Unit/Lesson với trình soạn thảo block (văn bản, video, hình ảnh, quiz).
* Tải lên video và hình ảnh minh họa cho từng bài học.
* Đặt giá khóa học và giá khuyến mãi.
* Xuất bản (`publish`) hoặc thu hồi (`unpublish`) khóa học.
* Xem thống kê doanh thu, số học viên, đánh giá của khóa học.
* Quản lý thông tin ngân hàng và gửi yêu cầu rút tiền.
* Xem danh sách học viên đang học khóa học của mình.

**Chức năng dành cho Học viên (Student/User)**  
Học viên sử dụng giao diện React.js:
* Đăng ký tài khoản với xác thực OTP qua email; đăng nhập bằng tài khoản hoặc Google OAuth.
* Duyệt trang chủ, tìm kiếm và lọc khóa học theo danh mục.
* Xem chi tiết khóa học (giới thiệu, nội dung, đánh giá) khi chưa đăng nhập.
* Thanh toán mua khóa học qua cổng PayOS (webhook tự động xác nhận).
* Học bài: xem video, đọc nội dung, làm quiz theo từng block bài học.
* Cập nhật tiến độ học tập tự động.
* Xem danh sách khóa học đã mua (`My Courses`).
* Để lại đánh giá và bình luận cho khóa học.

#### Chương 2: Chi tiết giải pháp kỹ thuật để giải quyết bài toán đã đặt ra

**Kiến trúc hệ thống:**  
StudyHub sử dụng kiến trúc **Monorepo tách Frontend/Backend**:
* **Frontend:** React.js (Vite), giao tiếp với Backend qua REST API.
* **Backend:** Laravel 11 (PHP), cung cấp REST API và giao diện Admin (Blade template).
* **Database:** MongoDB — lưu trữ dữ liệu dạng document, phù hợp với cấu trúc khóa học phức tạp (courseData, blocks).
* **Storage:** Laravel Storage (public disk) cho video và hình ảnh.

**Công nghệ sử dụng:**

| Thành phần | Công nghệ |
|:---|:---|
| Frontend UI | React.js 18, Vite, Tailwind CSS |
| Backend API | Laravel 11 (PHP 8.2+) |
| Database | MongoDB (qua `mongodb/laravel-mongodb`) |
| Xác thực API | Laravel Sanctum (Bearer Token) |
| Xác thực Admin | Laravel Session + Middleware CheckAdmin |
| Đăng nhập mạng xã hội | Google OAuth 2.0 |
| Xác thực Email | OTP gửi qua Laravel Mail (SMTP) |
| Thanh toán | PayOS (webhook tự động xác nhận đơn hàng) |
| Admin UI | Laravel Blade + Bootstrap |
| Triển khai Backend | Vercel (vercel.json có sẵn) |

**Luồng dữ liệu chính:**  
React.js gọi API tới Laravel Backend (endpoint `/api/*`). Backend xử lý nghiệp vụ, giao tiếp với MongoDB và trả về JSON. Với các tác vụ cần xác thực, Frontend đính kèm Bearer Token (Sanctum) trong header `Authorization`.

#### Chương 3: Triển khai giải pháp và đánh giá kết quả

**Kết quả đạt được**
* Xây dựng hoàn chỉnh REST API với Laravel, bao gồm 30+ endpoint cho đầy đủ các nghiệp vụ.
* Giao diện Admin (Blade) quản lý toàn bộ hệ thống.
* Giao diện học viên (React.js) với các màn hình: Trang chủ, Danh sách khóa học, Chi tiết khóa học, Học bài, Thanh toán, Kết quả thanh toán, Khóa học của tôi, Đăng ký, Đăng nhập, Xác thực Email.
* Giao diện giảng viên với bộ soạn thảo khóa học trực quan và trang thống kê doanh thu.
* Tích hợp thành công cổng thanh toán PayOS với webhook.
* Hệ thống xác thực đa tầng: Session cho Admin, Sanctum Token cho API, OTP email cho đăng ký.

**Một số giao diện của website**
* Trang chủ hiển thị danh sách khóa học nổi bật và tìm kiếm.
* Trang chi tiết khóa học với thông tin giá, nội dung preview và đánh giá.
* Trang thanh toán và xác nhận kết quả qua PayOS.
* Trang học bài (CoursePlayer) với video player, nội dung và theo dõi tiến độ.
* Trang soạn thảo khóa học (CourseEditor) cho giảng viên.
* Trang thống kê doanh thu cho giảng viên.
* Bảng điều khiển Admin với quản lý đầy đủ.

#### Kết luận và hướng nghiên cứu trong tương lai

**Những kết quả đạt được**
* Xây dựng thành công hệ thống học trực tuyến đầy đủ theo kiến trúc hiện đại (SPA + REST API).
* Triển khai được toàn bộ luồng nghiệp vụ cốt lõi: đăng ký → mua khóa học → học bài → đánh giá.
* Tích hợp thanh toán thực tế với PayOS và xác thực đa phương thức.
* Hệ thống phân quyền rõ ràng với 3 vai trò: Admin, Giảng viên, Học viên.

**Những hạn chế**
* Hệ thống chưa có chức năng thi trắc nghiệm trực tuyến hoàn chỉnh với chấm điểm tự động.
* Chức năng cấp phát chứng chỉ sau khi hoàn thành khóa học chưa được triển khai.
* Hệ thống chưa được kiểm thử tải (load testing) với số lượng người dùng lớn.
* Tính năng phát trực tiếp (livestream) chưa được tích hợp.

**Hướng phát triển trong tương lai**
* Xây dựng hệ thống bài kiểm tra trực tuyến với chấm điểm và cấp phát chứng chỉ tự động.
* Tích hợp tính năng diễn đàn trao đổi giữa học viên và giảng viên.
* Tối ưu hiệu năng và mở rộng hạ tầng để phục vụ nhiều người dùng đồng thời.
* Phát triển ứng dụng di động (React Native) sử dụng cùng Backend API.
* Tích hợp AI để gợi ý khóa học phù hợp với từng học viên.