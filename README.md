# 🛍️ My Ecommerce — Fullstack Monorepo

Một dự án Thương mại điện tử **fullstack** gồm 2 ứng dụng độc lập:

| Ứng dụng | Mô tả | Công nghệ |
|----------|------|-----------|
| **`dashboard/`** | Backend API + Admin Panel cho người quản trị | Laravel 11, FilamentPHP 3, MySQL |
| **`website/`** | Storefront cho khách hàng cuối (mua hàng, tài khoản, đơn hàng) | React 19, Tailwind, Zustand, TanStack Query |

Hai ứng dụng giao tiếp qua REST API tại prefix `/api/v1/`.

---

## 🏗 Kiến trúc tổng quan

```text
My_Ecommerce/
├── dashboard/      # Laravel + Filament — backend & admin panel
│   ├── app/Filament/       # Resource (Product, Order, User, ...)
│   ├── app/Models/         # Eloquent models
│   ├── routes/api.php      # REST API endpoints
│   └── routes/web.php      # Admin panel routes
│
├── website/        # React storefront
│   ├── src/features/       # account, auth, cart, checkout, order, product
│   ├── src/pages/          # Home, Shop, ProductDetail, Cart, Checkout, MyAccount
│   ├── src/components/common/   # Button, Card, FormInput, Alert, IconBadge, ...
│   └── src/store/          # Zustand stores (auth, cart, settings)
│
├── package.json            # Workspace metadata
└── README.md               # File này
```

---

## 🚀 Quick start

### 1. Backend (Laravel + Filament)
```bash
cd dashboard
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve            # http://localhost:8000
```
- Admin panel: `http://localhost:8000/admin`
- API base URL: `http://localhost:8000/api/v1/`

### 2. Frontend (React storefront)
```bash
cd website
npm install --legacy-peer-deps
cp .env.example .env
npm start                    # http://localhost:3000
```

> Frontend đã cấu hình proxy sang `http://127.0.0.1:8000` nên không cần xử lý CORS khi dev.

---

## 📦 Yêu cầu hệ thống

| Thành phần | Phiên bản |
|-----------|-----------|
| PHP       | ≥ 8.2     |
| Composer  | ≥ 2.0     |
| Node.js   | ≥ 18      |
| NPM       | ≥ 9       |
| MySQL     | ≥ 8.0     |

---

## 🔧 Quy ước phát triển

- **API versioning**: tất cả endpoint đặt sau `/api/v1/`.
- **Auth**: Laravel Sanctum (token lưu trong `localStorage` ở frontend).
- **Phân quyền**: `bezhansalleh/filament-shield` (RBAC ở admin).
- **Code style frontend**: 4-space indent, double quotes, không semicolon (Prettier).
- **Code style backend**: Laravel Pint (chuẩn PSR-12).

---

## 📚 Tài liệu chi tiết

- [Backend README](./dashboard/README.md) — hướng dẫn cài đặt Laravel/Filament, danh sách Resource, API.
- [Frontend README](./website/README.md) — kiến trúc thư mục, design system, shared components.

---

## 📄 License

Code phía Laravel kế thừa [MIT License](./dashboard/LICENSE.md). Phần còn lại theo chính sách nội bộ của dự án.
