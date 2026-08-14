# Ecommerce Storefront (`website`)

SPA storefront dành cho khách mua hàng của My Ecommerce. Website dùng React và gọi Laravel dashboard backend qua REST API `/api/v1/storefront`.

## Tính năng

- Trang chủ, catalog, tìm kiếm/lọc sản phẩm và trang chi tiết.
- Đăng ký, đăng nhập, quên/reset mật khẩu và tự refresh Sanctum token.
- Giỏ hàng local với Zustand persist.
- Checkout, địa chỉ giao hàng, shipping method và payment flow.
- Tài khoản, profile, address book, lịch sử/chi tiết/theo dõi đơn hàng.
- Blog/posts, static pages và menu động.
- Đa ngôn ngữ/currency theo settings backend.

## Tech stack

- React 19, React Router 7.
- Tailwind CSS, Lucide React, GSAP.
- Zustand 5 cho auth/cart/settings.
- Axios + TanStack Query.
- Create React App (`react-scripts`) để dev/build.

## Cấu trúc source

```text
src/
├── api/                    # axios client, response wrapper, interceptors
├── components/common/      # UI design system dùng chung
├── components/layout/      # header, navbar, footer, layout
├── features/
│   ├── auth/               # auth store, hooks, services
│   ├── product/            # catalog/product APIs và components
│   ├── cart/               # Zustand cart và cart APIs
│   ├── checkout/           # order, shipping, payment
│   ├── account/            # profile, address book
│   ├── order/              # history, detail, tracking
│   ├── address/            # country/province/district/ward
│   ├── home/, menu/, post/ # nội dung trang chủ, menu, blog
├── pages/                  # route-level pages
├── store/                  # settings/menu stores
└── utils/                  # format tiền/ngày và helpers
```

## Routes giao diện

| Path | Mục đích | Auth |
|---|---|---|
| `/` | Trang chủ | Không |
| `/shop` | Danh sách sản phẩm | Không |
| `/products/:slug` | Chi tiết sản phẩm | Không |
| `/cart` | Giỏ hàng | Không |
| `/checkout` | Checkout | Có |
| `/checkout/success` | Kết quả đặt hàng | Không |
| `/my-account` | Tài khoản, đơn hàng, địa chỉ | Có |
| `/login`, `/register` | Đăng nhập/đăng ký | Không |
| `/forgot-password`, `/reset-password` | Khôi phục mật khẩu | Không |
| `/posts`, `/posts/:slug` | Blog | Không |
| `/about`, `/contact`, `/shipping`, `/returns`, `/faq`, `/privacy`, `/terms`, `/p/:page` | Static pages | Không |

## Cài đặt local

Yêu cầu Node.js và npm.

```bash
cd apps/website
npm ci
cp .env.example .env
npm start
```

Dev server chạy tại `http://localhost:3000`. `package.json` proxy request tới `http://127.0.0.1:8000` nếu dùng backend local.

Build production:

```bash
npm run build
```

Build output nằm trong `build/`. Test React:

```bash
npm test -- --watchAll=false
```

## Cấu hình API

`.env.example`:

```env
REACT_APP_API_URL=http://127.0.0.1:8000/api/v1/storefront
REACT_APP_ADMIN_EMAIL=admin@admin.com
REACT_APP_ADMIN_PASSWORD=password
```

`REACT_APP_API_URL` là base URL thực tế được dùng bởi `src/api/axiosClient.js`. Nếu bỏ trống, client fallback về `/api/v1`; khi chạy local nên đặt đầy đủ `/api/v1/storefront` để khớp backend hiện tại.

`REACT_APP_ADMIN_EMAIL` và `REACT_APP_ADMIN_PASSWORD` chỉ được dùng để prefill thông tin login trong môi trường dev; không đưa credential production vào frontend vì biến `REACT_APP_*` được đóng gói vào JavaScript public.

## API backend được sử dụng

Base URL là `/api/v1/storefront`:

```text
auth/login, auth/register, auth/logout, auth/profile, auth/refresh-token
settings, menus, pages, posts, post-categories
products, product-categories, brands, combos
cart, cart/items, cart/sync, cart/shipping-methods, coupons/apply
addresses/countries, .../states, .../regions, .../sub-regions
user-addresses, orders
```

Request authenticated tự động gắn `Authorization: Bearer <token>` từ Zustand/localStorage. Khi nhận `401`, Axios thử refresh token; nếu refresh thất bại sẽ xóa auth state và chuyển về `/login`.

Backend tương ứng nằm tại [../dashboard](../dashboard). API route source là `dashboard/routes/api.php`; các endpoint checkout/payment phải được đối chiếu với backend hiện tại trước khi bật ở production.

## Nguyên tắc phát triển

- Giữ luồng `Component → custom hook/service → axiosClient → backend`.
- Component tập trung render; validation và API orchestration đặt ở hooks/services.
- Dùng `utils/` cho format tiền, ngày và trạng thái đơn hàng.
- Không hardcode secret hoặc credential vào source/frontend.
- Khi thêm API, cập nhật service trong feature tương ứng và kiểm tra lại prefix `/api/v1/storefront`.

## Deploy

Repository `apps` hiện có workflow deploy cho dashboard tại `../.github/workflows/deploy-dashboard.yml`; workflow đó không build hoặc deploy `website`. Vì vậy, sau `npm run build`, cần cấu hình riêng nơi host storefront để publish thư mục `build/` và trỏ API về domain dashboard. Không nên ghi website đã tự động deploy nếu chưa có workflow/hosting tương ứng.
