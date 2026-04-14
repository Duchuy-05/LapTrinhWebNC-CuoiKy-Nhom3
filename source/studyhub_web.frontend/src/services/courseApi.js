import axios from 'axios';

// 1. Khởi tạo instance của axios
const apiClient = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// 2. Cấu hình Interceptor: Tự động đính kèm Token vào mọi request
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

// 3. (Tuỳ chọn) Cấu hình Interceptor xử lý lỗi: Nếu token hết hạn (lỗi 401), tự động đẩy về trang Login
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user_data');
      window.location.href = '/login'; // Ép người dùng đăng nhập lại
    }
    return Promise.reject(error);
  }
);

// Class CourseAPI giữ nguyên như cũ
class CourseAPI {
  static async getLecturerCourses() { return apiClient.get('/lecturer/courses'); }
  static async createDraft(title) { return apiClient.post('/lecturer/courses', { title }); }
  static async getDraft(courseGroupId) { return apiClient.get(`/lecturer/courses/${courseGroupId}/draft`); }
  static async updateDraft(courseGroupId, data) { return apiClient.put(`/lecturer/courses/${courseGroupId}/draft`, data); }
  static async publishCourse(courseGroupId) { return apiClient.post(`/lecturer/courses/${courseGroupId}/publish`); }
  static async unpublishCourse(courseGroupId) { return apiClient.post(`/lecturer/courses/${courseGroupId}/unpublish`); }
}

export default CourseAPI;