// trang gọi API liên quan đến Course của FrontEnd (bao gồm draft và published)
import axios from 'axios';

// 1. Khởi tạo instance của axios
const apiClient = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

apiClient.interceptors.request.use(
  (config) => {
    // Lấy token từ localStorage (trùng với key bạn đã set ở file Login.jsx)
    const token = localStorage.getItem('token');
    
    if (token) {
      // Nhét token vào header Authorization theo chuẩn Bearer
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user_data');
      window.location.href = '/login'; 
    }
    return Promise.reject(error);
  }
);

// Class CourseAPI giữ nguyên như cũ
class CourseAPI {
  static async getLecturerCourses() { return apiClient.get('/lecturer/courses'); } // Lấy tất cả khóa học của giảng viên (bao gồm cả draft và published)
  static async createDraft(title) { return apiClient.post('/lecturer/courses', { title }); } // Tạo khóa học mới ở trạng thái draft với tiêu đề mặc định
  static async getDraft(courseGroupId) { return apiClient.get(`/lecturer/courses/${courseGroupId}/draft`); } // Lấy chi tiết khóa học ở trạng thái draft (dùng để edit)
  static async updateDraft(courseGroupId, data) { return apiClient.put(`/lecturer/courses/${courseGroupId}/draft`, data); } // Cập nhật thông tin khóa học ở trạng thái draft
  static async publishCourse(courseGroupId) { return apiClient.post(`/lecturer/courses/${courseGroupId}/publish`); } // Xuất bản khóa học từ draft sang published
  static async unpublishCourse(courseGroupId) { return apiClient.post(`/lecturer/courses/${courseGroupId}/unpublish`); } // Hủy xuất bản khóa học để đưa về draft
  static async updateCoursePrice(courseGroupId, data) { return apiClient.put(`/lecturer/courses/${courseGroupId}/price`, data); } // Cập nhật giá khóa học (áp dụng cho cả draft và published)
  static async getPublishedCourse(courseGroupId) { return apiClient.get(`/lecturer/courses/${courseGroupId}/published`); }
  
  static async uploadVideo(file) {
    const formData = new FormData(); // Tạo một chiếc "hộp" chuyên dụng để chứa file là FormData.
    formData.append('video', file); // Nhét cái file đó vào hộp (formData.append()).

    return apiClient.post('/lecturer/courses/upload-video', formData, {
      headers: {
        'Content-Type': 'multipart/form-data', //Gửi cái hộp đó đi và phải "nhắc" Axios đổi tem dán (Header) thành 'multipart/form-data'
      }
    });
  }
  static async uploadImage(file) {
    const formData = new FormData(); 
    formData.append('image', file); // Phải khớp với 'image' trong validate của Laravel

    return apiClient.post('/lecturer/courses/upload-image', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      }
    });
  }

  static async getStudentDashboard() { return apiClient.get('/student/home'); }
  static async getMyCourses() { return apiClient.get('/student/my-courses'); }
}

export default CourseAPI;