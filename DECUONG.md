<div align="center">
  <strong>TRƯỜNG ĐẠI HỌC ĐIỆN LỰC</strong><br>
  <strong>KHOA CÔNG NGHỆ THÔNG TIN</strong>
</div>

<br>

# ĐỀ CƯƠNG MÔN LẬP TRÌNH WEB NÂNG CAO
## TÊN ĐỀ TÀI: XÂY DỰNG WEB HỌC TRỰC TUYẾN

**Giảng viên hướng dẫn:** Ths CẤN ĐỨC ĐIỆP 
**Ngành:** CÔNG NGHỆ THÔNG TIN
**Chuyên ngành:** CÔNG NGHỆ PHẦN MỀM
**Lớp:** D18-CNPM2
**Khóa:** D18 

*Hà Nội, năm 2026*

---

### 1. Tên đề tài
Thiết kế website học trực tuyến Laravel Php.

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
Đề tài nghiên cứu và phát triển website học trực tuyến nhằm xây dựng một giải pháp phần mềm toàn diện phục vụ quá trình giảng dạy và học tập từ xa. Ứng dụng các công nghệ lập trình web hiện đại, hệ thống cho phép quản lý cơ sở dữ liệu học viên bảo mật, hỗ trợ truyền tải đa phương tiện (video bài giảng, tài liệu PDF, bài trắc nghiệm) và xử lý luồng dữ liệu theo thời gian thực. Website tích hợp các chức năng phân quyền chi tiết (Quản trị viên, Giảng viên, Học viên), giúp tự động hóa quy trình đăng ký, thanh toán và cấp phát chứng chỉ. Kết quả của đề tài hướng đến một nền tảng ổn định, có khả năng mở rộng cao và đáp ứng tốt xu hướng chuyển đổi số trong giáo dục.

---

### 5. Nội dung báo cáo

#### Đặt vấn đề
Hiện nay, nhu cầu học tập trực tuyến ngày càng tăng cao do sự phát triển mạnh mẽ của công nghệ thông tin và Internet. Thay vì phải tham gia các lớp học truyền thống tại trường hoặc trung tâm, người học có thể truy cập các website học trực tuyến để tiếp cận kiến thức mọi lúc, mọi nơi. Các nền tảng học trực tuyến cho phép người dùng xem bài giảng, tài liệu học tập, làm bài kiểm tra và theo dõi tiến độ học tập một cách thuận tiện. Việc xây dựng một website học trực tuyến giúp người học dễ dàng tiếp cận các khóa học, đồng thời hỗ trợ giảng viên và quản trị viên quản lý nội dung học tập hiệu quả hơn. Website có thể cung cấp nhiều tính năng như đăng ký tài khoản, tham gia khóa học, xem bài giảng, làm bài kiểm tra và quản lý nội dung học tập. Trong đề tài này, nhóm sử dụng các công nghệ HTML, CSS, JavaScript và Angular để xây dựng giao diện cho website học trực tuyến nhằm mang lại trải nghiệm thân thiện và dễ sử dụng cho người dùng.

#### Chương 1: Tổng quan về lĩnh vực của bài toán cần giải quyết
Trong thời đại số hiện nay, học trực tuyến đang trở thành một xu hướng phổ biến trên toàn thế giới. Các nền tảng học tập trực tuyến giúp người học tiếp cận với nguồn kiến thức đa dạng, linh hoạt về thời gian và địa điểm. Người dùng có thể học tập theo tiến độ cá nhân mà không bị giới hạn bởi khoảng cách địa lý. Website học trực tuyến được xây dựng nhằm đáp ứng nhu cầu học tập của người dùng thông qua việc cung cấp các khóa học, bài giảng và tài liệu học tập theo từng chủ đề hoặc danh mục khác nhau.

**Chức năng dành cho người quản lý (Admin)** 
Người quản trị muốn sử dụng hệ thống cần phải có tài khoản và đăng nhập vào hệ thống. Tài khoản có quyền cao nhất là Admin, có thể quản lý toàn bộ nội dung của website.
* Thêm, sửa, xóa các danh mục khóa học.
* Thêm, sửa, xóa thông tin khóa học và bài giảng.
* Quản lý tài khoản người dùng và giảng viên.
* Phân loại khóa học theo từng danh mục.
* Quản lý nội dung bài giảng và tài liệu học tập.
* Thêm hoặc thay đổi các quy định, hướng dẫn sử dụng trên website.
* Quản lý quảng cáo hoặc thông báo trên hệ thống.
* Khôi phục tài khoản cho người dùng khi cần thiết.

**Chức năng dành cho người học (User)**
Đối với người học, để sử dụng đầy đủ các chức năng như tham gia khóa học, lưu tiến độ học tập hoặc xem nội dung bài học chi tiết, người dùng cần phải đăng ký tài khoản và đăng nhập vào hệ thống.
* Xem danh sách các khóa học theo danh mục.
* Xem chi tiết nội dung khóa học và bài giảng.
* Đăng ký tài khoản và đăng nhập vào hệ thống.
* Tham gia các khóa học trực tuyến.
* Tìm kiếm khóa học theo tên, chủ đề hoặc danh mục.
* Theo dõi tiến độ học tập của bản thân.

#### Chương 2: Chi tiết giải pháp kỹ thuật để giải quyết bài toán đã đặt ra
**Công nghệ sử dụng:**
* **HTML:** Dùng để xây dựng cấu trúc của các trang web.
* **CSS:** Dùng để thiết kế giao diện và bố cục cho website.
* **JavaScript:** Dùng để xử lý các chức năng tương tác trên website.
* **Laravel Php:** Dùng nodemodule để xây dựng cấu trúc cho trong web.

#### Chương 3: Triển khai giải pháp và đánh giá kết quả

**Kết quả đạt được**
* Dự án đã được xây dựng và chạy thành công.
* Website hiển thị giao diện đầy đủ và không gặp lỗi trong quá trình chạy.
* Các chức năng cơ bản của hệ thống như đăng ký, đăng nhập, xem khóa học và tìm kiếm khóa học hoạt động ổn định.

**Một số giao diện của website**
* Trang chủ hiển thị danh sách các khóa học nổi bật.
* Trang danh mục khóa học.
* Trang chi tiết khóa học và bài giảng.
* Trang đăng ký và đăng nhập tài khoản.
* Trang quản trị dành cho admin.

#### Kết luận và hướng nghiên cứu trong tương lai 

**Những kết quả đạt được**
* Xây dựng được giao diện website học trực tuyến hoàn chỉnh.
* Hệ thống đã triển khai được một số chức năng cơ bản như quản lý khóa học, đăng ký tài khoản và xem nội dung học tập.
* Website có giao diện thân thiện và dễ sử dụng đối với người dùng.

**Những hạn chế**
* Dữ liệu khóa học hiện tại còn hạn chế và chưa phong phú.
* Một số dữ liệu vẫn đang được gán cứng trong hệ thống.
* Hệ thống chưa được kiểm thử với số lượng người dùng lớn.
* Một số chức năng nâng cao như bài kiểm tra trực tuyến hoặc đánh giá khóa học chưa được triển khai.

**Hướng phát triển trong tương lai**
* Mở rộng thêm nhiều khóa học và nội dung học tập.
* Xây dựng hệ thống quản lý bài kiểm tra và đánh giá kết quả học tập.
* Tối ưu hiệu năng hệ thống để phục vụ nhiều người dùng cùng lúc.
* Phát triển thêm các tính năng như diễn đàn trao đổi, đánh giá khóa học và hệ thống chứng chỉ hoàn thành khóa học.
