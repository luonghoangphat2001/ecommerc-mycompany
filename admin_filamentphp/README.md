# 🛠 Ecommerce Admin & API — `admin_filamentphp`

Backend trung tâm của hệ thống My Ecommerce: **REST API** cho storefront React + **Admin Panel** cho người quản trị, xây trên **Laravel 11** và **FilamentPHP 3**.

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white) ![Filament](https://img.shields.io/badge/Filament-3-F59E0B) ![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white) ![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)

---

## ✨ Chức năng chính

| Nhóm                  | Mô tả                                                                                |
| --------------------- | ------------------------------------------------------------------------------------ |
| **Catalog**           | Sản phẩm, danh mục, thương hiệu, thuộc tính, hình ảnh (Curator)                      |
| **Đơn hàng**          | Đơn hàng + items + meta + refunds + shipping + tax, theo dõi trạng thái              |
| **Khách hàng**        | Tài khoản User/Customer, phân quyền RBAC (Filament Shield)                           |
| **Địa chỉ**           | Quốc gia / tỉnh / huyện / xã, địa chỉ mặc định shipping & billing                    |
| **Vận chuyển & thuế** | Shipping zones, shipping methods, tax classes & rates                                |
| **Thanh toán**        | Phương thức thanh toán (cash, banking, ...), webhook log                             |
| **Nội dung**          | Bài viết blog, danh mục bài viết, comments, menu, sitemap, page builder (Fabricator) |
| **Cài đặt**           | Spatie Settings (logo, store name, currency, ...)                                    |
| **Hệ thống**          | Activity log, mail log, job monitor (Horizon), translation manager                   |

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

| Nhóm       | Ví dụ endpoint                                                                       |
| ---------- | ------------------------------------------------------------------------------------ |
| Auth       | `POST /api/v1/auth/login`, `register`, `logout`, `forgot-password`, `reset-password` |
| Products   | `GET /api/v1/products`, `GET /api/v1/products/{id}`                                  |
| Orders     | `GET/POST /api/v1/orders`, `GET /api/v1/orders/{id}`                                 |
| Addresses  | `GET/POST/PUT/DELETE /api/v1/addresses`                                              |
| Settings   | `GET /api/v1/settings`                                                               |
| Categories | `GET /api/v1/categories`, `GET /api/v1/brands`                                       |

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

| Mục đích                  | Lệnh                                |
| ------------------------- | ----------------------------------- |
| Format code (Pint)        | `vendor/bin/pint`                   |
| Phân tích tĩnh (PHPStan)  | `vendor/bin/phpstan analyse`        |
| Chạy test                 | `php artisan test`                  |
| Xoá toàn bộ cache         | `php artisan optimize:clear`        |
| Khởi động Horizon (queue) | `php artisan horizon`               |
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

---

## 🏛 Kiến trúc & Nguyên tắc Thiết kế (SOLID & OOP)

Hệ thống tuân thủ nghiêm ngặt mô hình kiến trúc phân lớp (Layered Architecture) kết hợp với các **Design Patterns** (Mẫu thiết kế) chuẩn mực, lấy cảm hứng từ cấu trúc lõi của WooCommerce để đảm bảo tính mở rộng và khả năng bảo trì.

### 1. Các Design Patterns cốt lõi được áp dụng

- **Repository Pattern:** Lớp duy nhất được phép tương tác trực tiếp với Database (Eloquent). Tất cả truy xuất dữ liệu đều thông qua các `Interface` (Ví dụ: `ProductRepositoryInterface`). Controller hay Service **không bao giờ** được gọi `Model::where(...)` hay `Model::all()`. Điều này đảm bảo tính "Single Responsibility" (SRP) và giúp việc thay đổi ORM/Database trong tương lai không ảnh hưởng đến Business Logic.
- **Service Pattern:** Đóng vai trò là "Orchestrator" (người điều phối). Nó chứa toàn bộ Business Logic (Logic nghiệp vụ), kết nối các Action và Repository lại với nhau nhưng tuyệt đối không chứa các truy vấn SQL trực tiếp.
- **Action Pattern / Command Pattern:** Các đoạn code thực thi một hành động nguyên tử (Atomic logic) duy nhất (ví dụ: `CalculateTaxAction`, `ApplyShippingAction`). Pattern này giúp tái sử dụng mã ở nhiều nơi khác nhau như: Admin Panel (Filament), REST API, Background Jobs, hay CLI Commands.
- **Dependency Injection (DI) & Inversion of Control (IoC):** Tuân thủ chặt chẽ chữ `D` trong SOLID. Các dependencies (như Repository hay Service) luôn được tiêm (inject) qua Constructor thông qua các Interface (Contracts) thay vì khởi tạo cứng bằng `new Class()`.
- **Strategy Pattern (Pluggable Architecture):** Được áp dụng mạnh mẽ trong các hệ thống tính toán (Tax, Shipping, Payment). Cho phép dễ dàng thêm các nhà cung cấp vận chuyển (Shipping Providers) hoặc cổng thanh toán mới mà không cần sửa đổi mã lõi của đơn hàng.

### 2. Thiết kế Hệ thống (System Design - Hệ sinh thái E-commerce)

Kiến trúc hệ thống được thiết kế theo module hóa (Separation of Concerns), phân tách rạch ròi các domain chính tương tự WooCommerce:

- **Order System (Hệ thống đơn hàng):** Đơn hàng (`Order`) được coi là một **Snapshot** (bản sao tĩnh) tại thời điểm mua hàng. Nó sao chép giá cả, tên sản phẩm và các thuộc tính lưu vào `order_items`. Tuyệt đối không phụ thuộc (liên kết cứng) vào bảng `Products` sau khi đơn hàng đã tạo, tránh trường hợp giá sản phẩm thay đổi làm hỏng lịch sử đơn hàng.
- **Pricing & Calculator System (Hệ thống tính giá):** Tách biệt hoàn toàn logic tính giá khỏi UI. Sử dụng một Calculator Service trung tâm để tính toán tuần tự: `Subtotal -> Discount -> Shipping -> Tax -> Total`.
- **Shipping & Tax System:**
    - Hỗ trợ các quy tắc động và phức tạp (linh hoạt theo quốc gia, tỉnh/thành phố, hoặc mã bưu chính).
    - Logic matching (khớp địa chỉ với rule) được viết thành các Action tái sử dụng.
    - Thuế (Tax) được tính toán riêng biệt cho từng mặt hàng (itemized) và cả phí vận chuyển.
- **Payment System (Thanh toán):** Độc lập với Order. Luồng thanh toán là pluggable, giao tiếp với hệ thống cốt lõi qua các Webhook hoặc Callback chuẩn hóa.

### 3. Luồng Dữ liệu (Strict Data Flow)

Để đảm bảo tính nhất quán, mọi luồng xử lý từ request đến lúc lưu xuống DB đều phải đi qua các lớp sau: `Controller / Filament Resource ➔ Service ➔ Action ➔ Repository ➔ Model ➔ Database`

### 4. Quy tắc Code (Coding Conventions)

- **Không Hardcode Text:** Mọi chữ hiển thị trên UI hoặc API messages phải dùng helper `trans()` hoặc `__()` kết hợp với Spatie Translatable để hỗ trợ đa ngôn ngữ từ trong Database ra ngoài UI.
- **Transaction DB:** Bắt buộc dùng `DB::transaction` cho các luồng xử lý liên quan đến nhiều bảng (Ví dụ: Tạo đơn hàng bao gồm order, items, addresses, payment status) để ngăn ngừa dữ liệu rác nếu quá trình gặp lỗi ở giữa chừng.
- **Thin Controllers & Resources:** Resource của Filament chỉ dùng để khai báo Schema (Cấu hình Form, Table UI). Controller chỉ dùng để nhận Request và trả về Response. Mọi nghiệp vụ tính toán đều phải đẩy về Service Layer.

---

## 🛠 Kiến trúc Nâng cao Đã Triển khai (Advanced Architecture)

Hệ thống đã hiện thực hóa thành công các quy tắc nâng cao dưới sự hỗ trợ của `@antigravity-workflows` và `@laravel-ecommerce-architecture`:

### 1. Data Transfer Objects (DTOs) - Pure PHP 8.1+
- **Áp dụng:** Khai báo `CreateOrderDTO` tại [CreateOrderDTO.php](file:///Users/luonghoangphat/Documents/My_Ecommerce/admin_filamentphp/app/Ecommerce/Order/DTOs/Checkout/CreateOrderDTO.php).
- **Cách dùng:** Ép kiểu dữ liệu payload đầu vào thay vì `array` tự do trong [OrderService.php](file:///Users/luonghoangphat/Documents/My_Ecommerce/admin_filamentphp/app/Ecommerce/Order/Services/OrderService.php).

### 2. Event-Driven Architecture & Queue Horizon
- **Áp dụng:** Event `OrderCreated` + Listener `SendOrderCreatedWebhook`.
- **Cơ chế:** Khi chốt đơn hàng, Event được bắn ra để xử lý ngầm gửi webhook/email thông qua hàng đợi Laravel Horizon.

### 3. Redis Cache Tags
- **Áp dụng:** `Cache::tags(['products', 'categories'])` tại [EloquentProductCategoryRepository.php](file:///Users/luonghoangphat/Documents/My_Ecommerce/admin_filamentphp/app/Ecommerce/Product/Repositories/EloquentProductCategoryRepository.php).
- **Ưu điểm:** Gom nhóm Cache giúp dễ dàng Flush hàng loạt khi thay đổi dữ liệu.

### 4. Redis Rate Limiting (Bảo mật API)
- **Áp dụng:** Tạo RateLimiter `checkout` tại [RouteServiceProvider.php](file:///Users/luonghoangphat/Documents/My_Ecommerce/admin_filamentphp/app/Providers/RouteServiceProvider.php).
- **Cơ chế:** Chặn Brute-force/Spam API thanh toán thông qua middleware `throttle:checkout` trong [api.php](file:///Users/luonghoangphat/Documents/My_Ecommerce/admin_filamentphp/routes/api.php).
