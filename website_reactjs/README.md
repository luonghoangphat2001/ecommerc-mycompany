# 🚀 Ecommerce Admin Dashboard Pro

Một giao diện quản trị thương mại điện tử hiện đại, hiệu suất cao được xây dựng trên nền tảng **React 19**, kết hợp với sức mạnh của **GSAP** cho các hiệu ứng chuyển động và **Tailwind CSS** cho phong cách thiết kế Glassmorphism sang trọng.

![Antigravity Design](https://img.shields.io/badge/Design-Antigravity-blueviolet?style=for-the-badge)
![React](https://img.shields.io/badge/React-19-61DAFB?style=for-the-badge&logo=react)
![TailwindCSS](https://img.shields.io/badge/Tailwind-3.4-38B2AC?style=for-the-badge&logo=tailwind-css)
![GSAP](https://img.shields.io/badge/Animations-GSAP-green?style=for-the-badge)

## ✨ Tính năng nổi bật

- **🔒 Bảo mật & Phân quyền**: Tích hợp trang Login với Protected Routes. Lưu trạng thái đăng nhập vào LocalStorage.
- **📊 Tổng quan thông minh**: Dashboard hiển thị các chỉ số quan trọng (Sản phẩm, Đơn hàng, Doanh thu) với hiệu ứng Staggered Animation mượt mà.
- **📦 Quản lý Sản phẩm & Đơn hàng**: Danh sách hiển thị theo phong cách Glassmorphism, hỗ trợ lọc và tìm kiếm cơ bản.
- **🛠 Core API chuẩn chỉnh**: Hệ thống Axios Client chuyên nghiệp với Interceptors tự động xử lý Token và lỗi Global (401, 403, 500).
- **📖 API Documentation**: Tích hợp **Swagger UI** (truy cập qua `/api-docs`) dành riêng cho nhà phát triển để xem trực tiếp cấu trúc dữ liệu từ Backend.
- **🎨 Thẩm mỹ Antigravity**: Hiệu ứng lơ lửng, chiều sâu không gian (Z-axis layering) và Blur nền tinh tế.

## 🛠 Bộ công nghệ (Tech Stack)

- **Frontend**: React 19, React Router v7.
- **Styling**: Tailwind CSS (v3), Lucide React (Icons).
- **Animation**: GSAP (GreenSock Animation Platform).
- **Data Handling**: Axios (Core Interceptors), Mock Data fallback.
- **Docs**: Swagger UI React.

## 📂 Cấu trúc thư mục

```text
src/
├── components/     # Các thành phần tái sử dụng (Sidebar, Header, Layout)
├── pages/          # Các trang chính được phân module (Dashboard, Products, Orders, Login, Docs)
├── services/       # Core xử lý API (Axios Client, Product/Order Services)
└── styles/         # Cấu hình CSS toàn cục & Tailwind Directives
```

## 🚀 Hướng dẫn cài đặt

### 1. Truy cập thư mục
```bash
cd website_reactjs
```

### 2. Cài đặt thư viện
```bash
npm install --legacy-peer-deps
```

### 3. Cấu hình biến môi trường
Tạo file `.env` tại thư mục gốc và nhập thông tin (Tham khảo `.env.example`):
```env
REACT_APP_API_URL=http://127.0.0.1:8000/api
REACT_APP_ADMIN_USER=admin
REACT_APP_ADMIN_PASS=123456
```

### 4. Chạy ứng dụng
```bash
npm start
```

## 📝 Ghi chú cho Developer
- Để truy cập tài liệu API Swagger, hãy gõ trực tiếp URL: `http://localhost:3000/api-docs` (Yêu cầu Backend Laravel/Swagger đang chạy).
- Mật khẩu mặc định: `admin` / `123456`.

---
⚡ Được thiết kế và phát triển bởi **Antigravity AI Assistant**.
