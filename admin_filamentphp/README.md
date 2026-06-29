# Ecommerce Admin & API

Backend trung tâm của hệ thống My Ecommerce:
- REST API cho storefront React
- Admin backend bằng Laravel thuần

## Công nghệ
- Laravel 11
- PHP 8.2+
- MySQL
- Spatie Settings / Permission

## Cấu trúc chính
- `app/Ecommerce`: nghiệp vụ theo module
- `app/Http/Controllers/Api`: API controllers
- `app/Http/Controllers/Admin`: admin controllers
- `resources/views/admin`: giao diện admin Blade
- `routes/api.php`: API routes
- `routes/web.php`: web/admin routes

## Chạy dự án
```bash
cd <backend-folder>
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Admin routes
- `GET /admin/login`
- `POST /admin/login`
- `GET /admin`
- `POST /admin/logout`
