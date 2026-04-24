# Quá trình Xây dựng Dashboard Ecommerce (Walkthrough)

Chào bạn, dưới đây là chi tiết những gì tôi đã thực hiện để xây dựng ứng dụng Dashboard theo chuẩn ReactJS dễ hiểu và có phong cách Antigravity Design (Kính mờ - Glassmorphism). Bạn có thể bám sát danh sách này để tuỳ chỉnh tiếp nhé!

## 1. Cài đặt các thư viện cần thiết

Tôi đã chạy lệnh sau trong Terminal (Command Line):

```bash
npm install -D tailwindcss postcss autoprefixer gsap axios react-router-dom lucide-react
```

**Mục đích:**
- `tailwindcss`, `postcss`, `autoprefixer`: Giúp viết CSS siêu nhanh ngay trên class của HTML.
- `gsap`: Thư viện tạo hiệu ứng ảnh động (Animation) cực kỳ mượt mà.
- `axios`: Sẵn sàng cho việc gọi API lấy dữ liệu thực từ MySQL/Backend của bạn.
- `react-router-dom`: Để chuyển trang (Dashboard, Products, Orders) mà không cần tải lại trang.
- `lucide-react`: Bộ icon siêu đẹp (ví dụ: Icon tìm kiếm, giỏ hàng, thông báo).

## 2. Cấu hình Tailwind CSS

Tôi đã tạo 2 file để hệ thống nhận diện Tailwind:
- `tailwind.config.js`: Chỉ định Tailwind quét các file `.jsx` trong thư mục `src` để tạo CSS.
- `postcss.config.js`: Cấu hình engine biên dịch CSS.
- Và cập nhật `src/index.css` thêm 3 dòng `@tailwind` để nhúng thư viện, đồng thời thêm một số class tuỳ chỉnh tiện dụng như `.glass` (để làm hiệu ứng kính mờ cho các thẻ) và `.hover-float` (hiệu ứng bay bay khi rê chuột vào).

## 3. Tạo cấu trúc Thư mục & Components

Tôi sắp xếp lại dự án của bạn sao cho chuyên nghiệp nhưng vẫn dễ hiểu với người mới bắt đầu:

- **`src/services/api.js`**: Nơi chứa các hàm gọi dữ liệu. Hiện tại tôi dựng Mock Data bằng hàm `setTimeout`. Về sau bạn đổi mã này thành `axios.get('http://api-website-ban-hang-cua-ban.com/products')` là xong!
- **`src/components/`**: 
  - `Header.jsx`: Thanh ngang trên cùng (Tìm kiếm, thông báo, User).
  - `Sidebar.jsx`: Menu điều hướng bên trái, áp dụng `NavLink` của React Router để tạo trạng thái "Đang chọn" (Active).
  - `Layout.jsx`: Khuôn mẫu chính lồng ghép Sidebar và Header, ở giữa chứa `<Outlet />`. Khi bạn bấm menu, ruột trang bên trong `<Outlet />` sẽ thay đổi.
- **`src/pages/`**:
  - `DashboardOverview.jsx`: Trang chủ hiển thị 3 Card thống kê với hiệu ứng nhảy số tự động (GSAP stagger). Dễ dàng sao chép để tạo card mới.
  - `ProductsPage.jsx`: Bảng hiển thị danh sách sản phẩm kính mờ siêu đẹp.
  - `OrdersPage.jsx`: Bảng danh sách đơn hàng tương tự.
- **`src/App.js`**: Sắp xếp lại hệ thống Route để nối các trang trên thành một luồng (Flow) hoàn chỉnh.
- **`src/index.js`**: Tạm tắt `<React.StrictMode>` (chỉ liên quan đến lúc làm dev thô) để các hiệu ứng GSAP chạy thử mượt nhất và không bị gọi ngầm 2 lần gây nháy màn hình.

## Hướng dẫn Tuỳ Chỉnh (Cho Người Mới)
- **Đổi màu sắc**: Tìm các class như `text-blue-600`, `bg-purple-100` trong file component và đổi sang màu mình thích.
- **Tuỳ chỉnh Animation**: Mở file `DashboardOverview.jsx`, tìm block code `gsap.from(...)`, thử thay đổi con số `y: 40`, `duration: 0.6` thành con số khác và xem kết quả lưu thay đổi.
- **Kết nối API thật**: Để kết nối DB, ở trang backend trả về JSON ví dụ `[ { id: 1, name: 'Sản phẩm 1' } ]`. Vào `src/services/api.js`, sửa hàm thành `return (await axios.get('...')).data;`.
