<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý sân thể thao</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background: #f4f4f4;
    }

    header {
      background-color: #2c3e50;
      color: white;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 30px;
      flex-wrap: wrap;
    }

    .search-bar {
      flex: 1;
      margin: 0 20px;
    }

    .search-bar input[type="text"] {
      width: 100%;
      padding: 8px 12px;
      border-radius: 5px;
      border: none;
    }

    .auth-links a {
      color: white;
      text-decoration: none;
      margin-left: 15px;
      font-weight: bold;
    }

    .auth-links a:hover {
      color: #1abc9c;
    }

    nav {
      background-color: #34495e;
    }

    .menu {
      display: flex;
      list-style-type: none;
      margin: 0;
      padding: 0 30px;
    }

    .menu > li {
      position: relative;
    }

    .menu > li > a {
      display: block;
      padding: 12px 20px;
      color: white;
      text-decoration: none;
      font-weight: bold;
    }

    .menu > li:hover {
      background-color: #2c3e50;
    }

    .menu > li:hover > a {
      color: #1abc9c;
    }

    .submenu {
      display: none;
      position: absolute;
      top: 100%;
      left: 0;
      background-color: #2c3e50;
      min-width: 200px;
      z-index: 999;
    }

    .submenu a {
      display: block;
      padding: 10px 20px;
      color: white;
      text-decoration: none;
    }

    .submenu a:hover {
      background-color: #1abc9c;
    }

    .menu li:hover .submenu {
      display: block;
    }

    .container {
      padding: 20px;
    }
  </style>
</head>
<body>

  <header>
    <div class="logo">
        <img src="asset/img/logo.png" alt="">
    </div>
    <div class="search-bar">
      <input type="text" placeholder="Tìm kiếm...">
    </div>
    <div class="auth-links">
      <a href="#">Đăng nhập</a>
      <a href="#">Đăng ký</a>
    </div>
  </header>

  <nav>
    <ul class="menu">
      <li>
        <a href="#">Số liệu thống kê</a>
      </li>
      <li>
      <li>
        <a href="#">Quản lý sân bóng</a>
        <div class="submenu">
          <a href="/Sport_manager/pages/add_field.php?sport=football">Thêm sân</a>
          <a href="/Sport_manager/pages/bookings.php?sport=football">Danh sách lịch đặt</a>
        </div>
      </li>
      <li>
        <a href="#">Quản lý nhân viên</a>
      </li>
      <li>
        <a href="#">Quản lý khách hàng</a>
        </div>
      </li>
      <li>
        <a href="#">Quản lý dịch vụ</a>
        </div>
      </li>
    </ul>
  </nav>
</body>
</html>
