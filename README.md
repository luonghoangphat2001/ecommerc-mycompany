# 🛍️ My Ecommerce Fullstack Project

Dự án Thương mại điện tử toàn diện bao gồm Backend quản trị (Laravel/FilamentPHP) và Frontend (ReactJS).

## 📂 Cấu trúc dự án

- **`admin_filamentphp/`**: Hệ thống quản trị nội bộ (Backend API & Admin Panel).
- **`website_reactjs/`**: Giao diện người dùng cuối (Frontend Dashboard/Web).

## 🚀 Hướng dẫn chạy nhanh (Quick Start)

| Component | Thư mục | Lệnh thực thi | URL mặc định |
| :--- | :--- | :--- | :--- |
| **Frontend** | `website_reactjs` | `npm start` | `http://localhost:3000` |
| **Backend** | `admin_filamentphp` | `php artisan serve` | `http://localhost:8000` |

---

## 🛠 Cấu hình API

Hệ thống sử dụng prefix `api/v1/admin/` cho toàn bộ các endpoint quản trị. 
- **Frontend** được cấu hình proxy qua `http://localhost:8000` để tránh lỗi CORS.
- Tài liệu API (Swagger) có thể truy cập tại: `http://localhost:3000/api-docs`.

## 📦 Yêu cầu hệ thống

- PHP >= 8.1 & Composer
- Node.js >= 18 & NPM
- MySQL/PostgreSQL

---
⚡ Được thiết kế và phát triển bởi **Antigravity AI Assistant**.
