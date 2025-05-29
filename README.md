# Sport Manager - Website Đặt Sân Bóng Đá Trực Tuyến

**Sport Manager** là một website đơn giản cho phép người dùng xem thông tin các sân thể thao và thực hiện đặt sân bóng theo từng khung giờ. Giao diện trực quan, thao tác dễ dàng và hỗ trợ hiển thị thông tin người đặt để quản lý lịch đặt hiệu quả.

---

## 🚀 Tính Năng Chính

- ✅ Xem danh sách các sân (tên sân, loại sân)
- 🕒 Chọn khung giờ đặt sân (6:00 - 22:00, mỗi khung 1 tiếng 30 phút)
- ❌ Tự động vô hiệu hóa (disable) các khung giờ đã có người đặt
- 👤 Hiển thị thông tin người đã đặt nếu khung giờ bị chiếm
- 📝 Mở modal để nhập thông tin người đặt khi chọn khung giờ

---

## 🛠 Cấu Trúc Dữ Liệu

### Bảng `fields`
| Tên cột   | Kiểu dữ liệu | Ghi chú |
|-----------|---------------|--------|
| id        | int           | Khóa chính |
| name      | varchar       | Tên sân |
| type      | varchar       | Loại sân (5 người, 7 người,...) |

### Bảng `bookings`
| Tên cột         | Kiểu dữ liệu | Ghi chú |
|------------------|--------------|--------|
| id               | int          | Khóa chính |
| field_id         | int          | FK đến bảng `fields` |
| customer_name    | varchar(100) | Tên người đặt |
| customer_phone   | varchar(20)  | Số điện thoại người đặt |
| date             | date         | Ngày đặt |
| start_time       | time         | Giờ bắt đầu |
| end_time         | time         | Giờ kết thúc |
| created_at       | timestamp    | Tự động cập nhật |

---

## ⚙️ Cách Sử Dụng

1. Truy cập `index.php` để xem danh sách sân.
2. Nhấn vào một sân để mở chi tiết (`field_detail.php`).
3. Tại trang chi tiết:
   - Các khung giờ đã được đặt sẽ bị **mờ** và **không thể chọn**.
   - Khi chọn một khung giờ còn trống, một modal sẽ xuất hiện để bạn **nhập thông tin người đặt (tên + SĐT)**.
4. Sau khi chọn khung giờ và điền thông tin, nhấn **"Đặt sân"** để gửi yêu cầu.

---

## 📸 Giao Diện Demo

| Trang chính | Trang chi tiết sân |
|-------------|---------------------|
| ![index.png](![image](https://github.com/user-attachments/assets/8c5ec3e6-8847-469a-9d18-1b23f2cf6a29)
) | ![field_detail.png](https://www.awesomescreenshot.com/video/40399402) |

---

## 🧑‍💻 Công Nghệ Sử Dụng

- PHP core
- MySQL (XAMPP)
- HTML/CSS
- JavaScript (cho phần modal)

---

## 📝 Ghi chú

- Bài tập lớn môn lập trình web bằng php
- Có thể mở rộng thêm chức năng thanh toán, gửi email, hoặc quản lý tài khoản đăng nhập cho người dùng/quản trị viên.

---
