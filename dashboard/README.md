# Ecommerce Dashboard & API

Backend trung tâm của hệ thống My Ecommerce: admin dashboard Laravel và REST API cho storefront, agent và các service nội bộ.

## Công nghệ

- Laravel 11, PHP 8.2+.
- Blade/Livewire, Alpine.js, Tailwind CSS và Vite cho admin UI.
- MySQL/MariaDB, Laravel Sanctum, Horizon.
- Spatie Permission/Settings/Media Library/Activity Log.
- Swagger, DomPDF và các module nghiệp vụ Ecommerce trong `app/Ecommerce`.

## Chức năng chính

Dashboard admin bao gồm:

- Dashboard analytics: users, products, orders, revenue, payment và order status.
- Quản lý product, brand, category, inventory, shipping, tax, order, payment và refund.
- CMS: posts, pages, comments, menus, media và language lines.
- Marketing module có feature flag: coupon, loyalty, upsell, cross-sell và combo.
- Company workspace: departments, agents, audit logs, proposals, payroll, purchase orders, incidents và customer reviews.
- User, role, permission, settings, webhook và mail logs.

## URL chính

| URL | Mục đích | Quyền |
|---|---|---|
| `/` | Redirect tới admin login | Public |
| `/admin/login` | Đăng nhập admin | Public |
| `/admin` | Dashboard analytics | `auth` + `admin.access` + `view_dashboard` hoặc `super_admin` |
| `/docs/webhook` | Tài liệu webhook | Public |
| `/api/v1/storefront/*` | REST API cho storefront | Tùy endpoint; Sanctum cho route protected |
| `/api/v1/storefront/agents/*` | Health/metrics cho agent | Agent token |

API được version tại `/api/v1`. Các nhóm public gồm settings, menus, products, categories, brands, pages, posts và auth login/refresh. Cart, profile, address và orders yêu cầu Laravel Sanctum; endpoint agent yêu cầu `VerifyAgentToken`.

## Cài đặt local

Yêu cầu PHP 8.2+, Composer 2, Node.js/npm và MySQL/MariaDB.

```bash
cd apps/dashboard
composer install
cp .env.example .env
php artisan key:generate
```

Cấu hình database trong `.env`, sau đó chạy migration:

```bash
php artisan migrate
npm ci
npm run build
php artisan serve
```

Mở `http://localhost:8000/admin/login`. Tài khoản dev mặc định được khai báo bằng `ADMIN_LOGIN_EMAIL` và `ADMIN_LOGIN_PASSWORD`; hãy đổi ngay khi dùng môi trường không phải local.

Trong lúc phát triển frontend, có thể dùng hai terminal:

```bash
php artisan serve
npm run dev
```

## Biến môi trường quan trọng

Copy `.env.example` làm điểm bắt đầu. Các nhóm chính:

| Nhóm | Biến |
|---|---|
| Laravel | `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_URL`, `APP_DEBUG` |
| CORS | `ALLOWED_ORIGINS` |
| Admin local | `ADMIN_LOGIN_EMAIL`, `ADMIN_LOGIN_PASSWORD` |
| Database | `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` |
| Queue/cache/session | `QUEUE_CONNECTION`, `CACHE_DRIVER`, `SESSION_DRIVER`, Redis settings nếu dùng |
| Mail/storage | `MAIL_*`, `FILESYSTEM_DRIVER`, AWS/S3 settings nếu dùng |
| Agent/integration | Xem `config/api-service.php` và các biến trong `.env.example` |

Không commit `.env`, `APP_KEY`, database dump hoặc token agent. Production cần `APP_DEBUG=false`, `APP_URL` đúng domain và `ALLOWED_ORIGINS` chỉ chứa origin được phép.

## Kiểm tra source

```bash
composer validate --no-check-publish
composer run test:phpstan
php artisan test
npm run build
```

Format PHP:

```bash
composer run pint
```

## Production deploy

Workflow nằm ở `../.github/workflows/deploy-dashboard.yml` và chạy khi push vào branch `dashboarh` (giữ nguyên cách viết này vì đó là branch hiện được cấu hình).

Pipeline thực hiện:

1. cài PHP 8.4, Composer dependencies và validate/lint PHP;
2. cài npm dependencies và build Vite assets;
3. fetch source vào `/home/hpdev/deploy/ecommerce-dashboard-source`;
4. deploy app vào `/home/hpdev/dashboard.hpdev.name.vn`;
5. cài production Composer dependencies, migrate database, tạo storage link, cache config/view.

Workflow yêu cầu GitHub secrets `SSH_HOST`, `SSH_PORT`, `SSH_USER`, `SSH_PRIVATE_KEY`, tùy chọn `SSH_PASSPHRASE` và dùng `GITHUB_TOKEN` để fetch repository. File `.env` production nằm trên server và được giữ lại khi deploy.

## Cấu trúc source

```text
app/Ecommerce/                  # domain modules và services
app/Http/Controllers/Admin/     # admin controllers
app/Http/Controllers/Api/       # storefront/agent API controllers
app/Models/                     # Eloquent models
resources/views/admin/          # Blade admin UI
resources/js, resources/css/    # Vite frontend assets
routes/web.php                  # login, admin và web routes
routes/api.php                  # /api/v1 routes
database/migrations/            # schema changes
tests/                          # Unit và Feature tests
```
