# Dự Án WordPress Nhóm 6 - Quản Lý Khách Hàng

Dự án website WordPress tích hợp Plugin Quản lý Khách hàng chuyên nghiệp.

---

## 🛠️ Thành phần & Plugin

### 📌 Plugin Quản Lý Khách Hàng (`quan-ly-khach-hang`)
Plugin hỗ trợ đăng ký, quản lý thông tin khách hàng, thống kê & chăm sóc phía Dashboard.

- **Frontend Shortcode**:
  ```text
  [dang_ky_khach_hang]
  ```
- **Tính năng Dashboard**:
  - Thống kê 4 chỉ số (Tổng KH, Đăng ký hôm nay, KH VIP, Đã liên hệ).
  - Tìm kiếm, lọc theo trạng thái.
  - Sửa thông tin, cập nhật trạng thái, ghi chú, xóa khách hàng.
  - Xuất dữ liệu Excel/CSV (chuẩn tiếng Việt UTF-8).

---

## 🚀 Hướng Dẫn Cài Đặt Cho Thành Viên Nhóm

### 1. Clone dự án về máy:
```bash
git clone https://github.com/ALiang2909/Nh-m6.git
```

### 2. Cấu hình Database & WordPress:
1. Tạo CSDL MySQL trong phpMyAdmin tên: `nhom6`.
2. Tạo file `wp-config.php` (hoặc đổi tên từ `wp-config-sample.php`) với thông tin DB cá nhân.
3. Kích hoạt Plugin **Quản Lý Khách Hàng** trong trang Admin WordPress (`Plugins -> Installed Plugins`).

---

## 🌿 Quy Trình Làm Việc (Git Workflow For Team)

1. **Kéo code mới nhất trước khi làm**:
   ```bash
   git checkout main
   git pull origin main
   ```
2. **Tạo branch làm tính năng mới**:
   ```bash
   git checkout -b feature/ten-tinh-nang
   ```
3. **Commit & Push**:
   ```bash
   git add .
   git commit -m "Mô tả thay đổi"
   git push origin feature/ten-tinh-nang
   ```
4. **Tạo Pull Request (PR)** trên GitHub để Trưởng nhóm Review & Merge vào `main`.
