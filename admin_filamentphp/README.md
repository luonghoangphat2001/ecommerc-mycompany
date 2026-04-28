# 🛠 Ecommerce Admin & API — `admin_filamentphp`

Backend trung tâm của hệ thống My Ecommerce: **REST API** cho storefront React + **Admin Panel** cho người quản trị, xây trên **Laravel 11** và **FilamentPHP 3**.

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-3-F59E0B)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)

---

## ✨ Chức năng chính

| Nhóm | Mô tả |
|------|------|
| **Catalog** | Sản phẩm, danh mục, thương hiệu, thuộc tính, hình ảnh (Curator) |
| **Đơn hàng** | Đơn hàng + items + meta + refunds + shipping + tax, theo dõi trạng thái |
| **Khách hàng** | Tài khoản User/Customer, phân quyền RBAC (Filament Shield) |
| **Địa chỉ** | Quốc gia / tỉnh / huyện / xã, địa chỉ mặc định shipping & billing |
| **Vận chuyển & thuế** | Shipping zones, shipping methods, tax classes & rates |
| **Thanh toán** | Phương thức thanh toán (cash, banking, ...), webhook log |
| **Nội dung** | Bài viết blog, danh mục bài viết, comments, menu, sitemap, page builder (Fabricator) |
| **Cài đặt** | Spatie Settings (logo, store name, currency, ...) |
| **Hệ thống** | Activity log, mail log, job monitor (Horizon), translation manager |

---

## 🧰 Tech stack

- **Framework**: Laravel 11, FilamentPHP 3
- **Auth**: Laravel Sanctum (token-based cho REST API)
- **Phân quyền**: `bezhansalleh/filament-shield` (RBAC)
- **Media**: `awcodes/filament-curator` + Spatie Media Library
- **API**: `rupadana/filament-api-service` (CRUD endpoint tự sinh)
- **Settings**: `spatie/laravel-settings` (+ Filament plugin)
- **Search/Filter**: `spatie/laravel-query-builder`
- **Activity**: `spatie/laravel-activitylog`
- **Báo cáo**: `leandrocfe/filament-apex-charts`, `flowframe/laravel-trend`
- **Queue**: Laravel Horizon
- **Khác**: Translation Manager, Sitemap, DOMPDF

---

## 📂 Cấu trúc thư mục

```text
app/
├── Filament/               # Admin Panel
│   ├── Resources/          # CRUD Resource (Product, Order, User, Brand, ...)
│   ├── Pages/              # Custom admin pages
│   └── Widgets/            # Dashboard widgets
├── Http/
│   ├── Controllers/Api/V1/ # API controllers (storefront)
│   └── Resources/          # API JSON resources
├── Models/                 # Eloquent models (Product, Order, Address, ...)
├── Settings/               # Spatie Settings classes
├── Jobs/                   # Background jobs (mail, webhook, ...)
└── Policies/               # Authorization policies

routes/
├── api.php                 # /api/v1/...  endpoints cho frontend
└── web.php                 # Admin panel routes (Filament)

database/
├── migrations/
└── seeders/
```

---

## 🚀 Cài đặt & chạy

### 1. Truy cập thư mục
```bash
cd admin_filamentphp
```

### 2. Cài dependencies
```bash
composer install
npm install
```

### 3. Cấu hình `.env`
```bash
cp .env.example .env
php artisan key:generate
```
Cập nhật các biến database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_ecommerce
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Migrate & seed
```bash
php artisan migrate --seed
```

### 5. Compile assets
```bash
npm run dev                  # dev (watch)
# hoặc
npm run build                # production
```

### 6. Chạy server
```bash
php artisan serve            # http://localhost:8000
```

### 7. Tạo tài khoản admin
```bash
php artisan make:filament-user
```
Sau đó truy cập **http://localhost:8000/admin**.

---

## 🔌 REST API

Tất cả endpoint phục vụ storefront đặt sau prefix `/api/v1/`:

| Nhóm        | Ví dụ endpoint |
|-------------|----------------|
| Auth        | `POST /api/v1/auth/login`, `register`, `logout`, `forgot-password`, `reset-password` |
| Products    | `GET /api/v1/products`, `GET /api/v1/products/{id}` |
| Orders      | `GET/POST /api/v1/orders`, `GET /api/v1/orders/{id}` |
| Addresses   | `GET/POST/PUT/DELETE /api/v1/addresses` |
| Settings    | `GET /api/v1/settings` |
| Categories  | `GET /api/v1/categories`, `GET /api/v1/brands` |

Authentication dùng **Laravel Sanctum** — gửi header `Authorization: Bearer <token>`.

---

## 🎛 Admin Panel (Filament)

Đăng nhập tại `/admin`. Một số Resource chính:

- **ProductResource** — Quản lý sản phẩm (gallery, biến thể, danh mục, brand)
- **OrderResource** — Đơn hàng, đổi trạng thái, refund, in PDF
- **UserResource** — User + role/permission (Filament Shield)
- **PostResource / PostCategoryResource** — Blog & nội dung
- **ShippingZoneResource / TaxClassResource** — Cấu hình vận chuyển & thuế
- **WebhookResource / WebhookLogResource** — Tích hợp webhook
- **MailLogResource** — Theo dõi email đã gửi

---

## 🧑‍💻 Lệnh hữu ích

| Mục đích | Lệnh |
|----------|------|
| Format code (Pint)        | `vendor/bin/pint` |
| Phân tích tĩnh (PHPStan)  | `vendor/bin/phpstan analyse` |
| Chạy test                 | `php artisan test` |
| Xoá toàn bộ cache         | `php artisan optimize:clear` |
| Khởi động Horizon (queue) | `php artisan horizon` |
| Cập nhật Filament Shield  | `php artisan shield:generate --all` |

---

## 📦 Yêu cầu hệ thống

- **PHP** ≥ 8.2 (cần ext: `pdo_mysql`, `gd`/`imagick`, `bcmath`, `mbstring`, `redis` nếu dùng Horizon)
- **Composer** ≥ 2.0
- **Node.js** ≥ 18 + NPM
- **MySQL** ≥ 8.0 (hoặc MariaDB tương đương)
- **Redis** (khuyến nghị, cho cache & queue)

---

## 📄 License

Mã Laravel framework theo [MIT License](./LICENSE.md).
