# My Ecommerce Admin (Backend)

This is a modern web application built using **Laravel** and **FilamentPHP**. It serves as the core foundation for the project.

## Requirements

Before you begin, ensure you have the following installed on your local machine:
- **PHP** >= 8.1
- **Composer** (PHP package manager)
- **Node.js** & **NPM** (for compiling frontend assets)
- **MySQL** or any other supported database system

## Local Setup & Installation

Follow these steps to set up the project on your local machine:

### 1. Truy cập thư mục
```bash
cd admin_filamentphp
```

### 2. Install PHP Dependencies
Install the required Laravel packages using Composer:
```bash
composer install
```

### 3. Install Node Dependencies
Install the frontend tools (Vite, Tailwind, etc.) using NPM:
```bash
npm install
```

### 4. Environment Configuration
Copy the sample environment file and update it with your local settings:
```bash
cp .env.example .env
```
Open the `.env` file and configure your database connection:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

### 5. Generate Application Key
Generate a unique key for your Laravel application:
```bash
php artisan key:generate
```

### 6. Run Database Migrations
Create the table structures in your database (make sure your database is running and created):
```bash
php artisan migrate
```
*(Optional: If the project has seeders, you can run `php artisan migrate --seed` to populate dummy data).*

### 7. Compile Frontend Assets
To compile the CSS and JS files for Filament and the main site:
```bash
# For development (auto-reloads on changes)
npm run dev

# OR for production build
npm run build
```

### 8. Run the Local Server
Start Laravel's built-in development server:
```bash
php artisan serve
```

You can now access the application at `http://localhost:8000`.

## Accessing the Filament Admin Panel
By default, Filament's admin panel is usually accessible at `/admin`. 
If you need to create an admin user to log in, you can run:
```bash
php artisan make:filament-user
```
Follow the prompts to set up your Name, Email, and Password.

---

## Useful Commands

- Code formatting (Pint): `vendor/bin/pint`
- Static analysis (PHPStan): `vendor/bin/phpstan analyse`
- Clear all caches: `php artisan optimize:clear`
