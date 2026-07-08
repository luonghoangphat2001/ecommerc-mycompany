# 🛒 Ecommerce Storefront — `website`

Giao diện **người mua** của hệ thống My Ecommerce. Một SPA hiện đại xây trên **React 19**, sử dụng **Tailwind CSS** với phong cách Glassmorphism, kết nối tới backend Laravel/Filament qua REST API `/api/v1/`.

![React](https://img.shields.io/badge/React-19-61DAFB?logo=react&logoColor=white) ![Tailwind](https://img.shields.io/badge/Tailwind-3.4-38B2AC?logo=tailwind-css&logoColor=white) ![Zustand](https://img.shields.io/badge/State-Zustand-orange) ![TanStack Query](https://img.shields.io/badge/Data-TanStack_Query-FF4154)

---

## ✨ Tính năng

| Module       | Chức năng                                                                                                    |
| ------------ | ------------------------------------------------------------------------------------------------------------ |
| **Auth**     | Đăng ký, đăng nhập, quên & đặt lại mật khẩu, lưu token Sanctum vào `localStorage`                            |
| **Catalog**  | Trang chủ với sản phẩm nổi bật, Shop với lọc/tìm kiếm, trang chi tiết sản phẩm                               |
| **Cart**     | Giỏ hàng với Zustand persist, cập nhật số lượng, xóa item                                                    |
| **Checkout** | Form giao hàng + thanh toán đa phương thức, chọn shipping method, hỗ trợ địa chỉ Việt Nam (tỉnh/quận/phường) |
| **Account**  | Quản lý hồ sơ, sổ địa chỉ (mặc định shipping/billing), đổi mật khẩu, lịch sử & theo dõi đơn hàng             |
| **i18n**     | Đa ngôn ngữ qua `useSettingsStore.translate()` (key-based)                                                   |
| **API Docs** | Swagger UI tại `/api-docs` (cần backend đang chạy)                                                           |

---

## 🧰 Tech stack

- **Core**: React 19, React Router v7
- **Styling**: Tailwind CSS 3.4, Lucide React (icons)
- **State**: Zustand 5 (auth, cart, settings — có persist)
- **Data fetching**: TanStack Query 5 + Axios (interceptors xử lý 401/403/500)
- **Animation**: GSAP 3
- **Docs**: Swagger UI React

---

## 📂 Cấu trúc thư mục

```text
src/
├── api/                    # Axios client + interceptors
├── components/
│   └── common/             # Design system: Button, Card, FormInput, FormSelect,
│                           # FormCheckbox, FormRadio, FormSection, Alert,
│                           # IconBadge, EmptyState, BackgroundOrbs, PageHeading,
│                           # Loading, Error, Logo, NavLink, Skeleton
├── features/               # Tổ chức theo domain
│   ├── account/            # Profile, AddressBook, ChangePassword
│   ├── address/            # Hooks lấy danh sách quốc gia / tỉnh / phường
│   ├── auth/               # Login/Register/Forgot/Reset hooks + store
│   ├── cart/               # CartItem, CartSummary, store
│   ├── checkout/           # CheckoutForm, OrderSummary, hooks
│   ├── home/               # Hero, Features
│   ├── order/              # OrderHistory, OrderDetail, OrderTracking
│   └── product/            # ProductCard, ProductInfo, ProductGallery
├── hooks/                  # Hooks dùng chung
├── pages/                  # HomePage, ShopPage, ProductDetailPage, CartPage,
│                           # CheckoutPage, OrderSuccessPage, MyAccountPage,
│                           # Login/{LoginPage,RegisterPage,ForgotPasswordPage,ResetPasswordPage}
├── store/                  # Zustand stores (settings, ...)
├── utils/                  # useFormatters, helpers
└── App.js / index.js
```

---

## 🎨 Design system — `components/common/`

Tất cả component trong thư mục này đều dùng **Glassmorphism** (`bg-white/60 backdrop-blur-xl rounded-[2.5rem]`) và export qua `index.js`.

```jsx
import { Button, Card, FormInput, FormSelect, FormSection, FormCheckbox, FormRadio, Alert, IconBadge, EmptyState, BackgroundOrbs, PageHeading } from "../components/common"
```

Một số component thường dùng:

| Component        | Props chính                                                                                           |
| ---------------- | ----------------------------------------------------------------------------------------------------- |
| `Button`         | `variant` (primary/blue/secondary/ghost/danger), `size` (sm/md/lg/xl/block), `as` (Link, button, ...) |
| `Card`           | `shadow` (none/sm/md/lg), `overflow`, sub-components: `Header`, `Body`, `Footer`, `Title`             |
| `FormInput`      | `icon` (focus-aware), `labelExtra`, `disabled`, `placeholder`                                         |
| `FormSelect`     | `options` dạng `[[key, label], ...]`, `placeholder`                                                   |
| `Alert`          | `variant` (error/success/info/warning), `shake`                                                       |
| `IconBadge`      | `icon`, `color` (9 màu), `size` (sm/md/lg/xl)                                                         |
| `EmptyState`     | `icon`, `title`, `description`, `action`                                                              |
| `BackgroundOrbs` | `preset` (auth/hero) hoặc `orbs` tự định nghĩa                                                        |

---

## 🚀 Cài đặt & chạy

### 1. Cài dependencies

```bash
npm install --legacy-peer-deps
```

### 2. Cấu hình env

```bash
cp .env.example .env
```

Nội dung `.env`:

```env
REACT_APP_API_URL=http://127.0.0.1:8000/api
```

> Mặc định đã có `"proxy": "http://127.0.0.1:8000"` trong `package.json` nên không bị CORS khi dev.

### 3. Chạy dev server

```bash
npm start                    # http://localhost:3000
```

### 4. Build production

```bash
npm run build
```

---

## 🔗 Tích hợp backend

| Endpoint kiểu | Đường dẫn                                                                  |
| ------------- | -------------------------------------------------------------------------- |
| Auth          | `POST /api/v1/auth/{register,login,logout,forgot-password,reset-password}` |
| Sản phẩm      | `GET /api/v1/products`, `GET /api/v1/products/{id}`                        |
| Đơn hàng      | `GET/POST /api/v1/orders`                                                  |
| Địa chỉ       | `GET/POST/PUT/DELETE /api/v1/addresses`                                    |
| Settings      | `GET /api/v1/settings`                                                     |

Token Sanctum được Axios tự inject vào header `Authorization: Bearer ...` (xem `src/api/`).

---

## 🧑‍💻 Code style

- **Indent**: 4 spaces
- **Quotes**: double `"..."`
- **Semicolons**: KHÔNG dùng
- **JSX**: ưu tiên một dòng dài cho element ngắn
- **Comments**: tránh viết comment giải thích — đặt tên rõ ràng & tách biến

---

## 📝 Ghi chú dev

- Swagger UI: `http://localhost:3000/api-docs` (cần backend đang chạy).
- Dữ liệu test: dùng tài khoản seed từ Filament backend (`php artisan db:seed`).

## 🏛 Kiến trúc & Nguyên tắc Thiết kế (Clean Architecture)

Dự án áp dụng mô hình phân tách rõ ràng giữa UI (Giao diện) và Business Logic:

- **Component (UI Layer):** Render UI, không chứa logic. File `.jsx` CHỈ dùng để render HTML/Giao diện.
- **Custom Hook (Logic Layer):** Xử lý state, validation, API orchestration.
- **Service Layer (Data Layer):** Đảm nhận việc gọi API qua `axiosClient`.

### 🔄 Các Pattern Chuyên sâu Đã áp dụng

- **Lazy Loading:** Tải các trang (`HomePage`, `ShopPage`, ...) theo nhu cầu bằng `React.lazy()`.
- **Error Boundary:** Bao bọc toàn bộ ứng dụng để xử lý crash UI mượt mà.
- **Strategy Pattern:** Tách biệt UI cho từng phương thức thanh toán (`PaymentStrategies.jsx`).
- **Refresh Token Pattern:** Tự động cấp lại token trong [axiosClient.js](file:///Users/luonghoangphat/Documents/My_Ecommerce/website/src/api/axiosClient.js).
- **Data Normalization:** Lưu trữ State giỏ hàng `O(1)` bằng `itemsById` trong [useCartStore.js](file:///Users/luonghoangphat/Documents/My_Ecommerce/website/src/features/cart/store/useCartStore.js).
- **Memoization:** Tiết kiệm re-render qua `useMemo` và `React.memo()`.

### 📊 Luồng Dữ liệu (Strict Data Flow)

`Component (UI) ↔ Custom Hook (Logic) ↔ Service Layer (API) ↔ Backend`

### ⚠️ Quy tắc Code (Coding Conventions)

- **Tách Utils:** Không viết inline format tiền/ngày tháng, dùng file `utils/`.
- **Đa ngôn ngữ (i18n):** Lấy text qua `translate()` của Zustand store từ API. Không hardcode.
- **Class Tailwind:** Tách thành biến riêng nếu chuỗi class quá dài.
