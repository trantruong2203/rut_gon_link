# AdLinkFly - Monetized URL Shortener

URL shortener có quảng cáo, hỗ trợ Interstitial, Banner, Popup. Dự án dựa trên **CakePHP 4** và **PHP 8.1+**.

## Yêu cầu hệ thống

- **PHP** >= 8.1 (khuyến nghị 8.2)
- **MySQL** 5.7+ hoặc MariaDB 10.3+
- **Composer** 2.x
- PHP extensions: `intl`, `mbstring`, `pdo_mysql`, `json`, `openssl`

## Cài đặt từ đầu

### 1. Clone và cài dependencies

```bash
git clone <repo-url> adlinkfly
cd adlinkfly

composer install
```

>

> Nếu gặp lỗi platform, chạy: `composer install --ignore-platform-reqs`

### 2. Cấu hình quyền thư mục

Đảm bảo `tmp` và `logs` có quyền ghi:

```bash
# Linux/Mac
chmod -R 775 tmp logs
chown -R www-data:www-data tmp logs

# Windows: Kiểm tra thư mục tmp, logs có quyền ghi
```

### 3. Cài đặt qua Web Installer

1. Cấu hình web server trỏ document root vào thư mục `webroot`
2. Truy cập: `http://your-domain/` 
3. Làm theo từng bước:
  - **Database**: Nhập host, username, password, tên database (tạo database trước)
  - **Data**: Chạy migrations (tạo bảng)
  - **Admin**: Tạo tài khoản admin
  - **Finish**: Hoàn tất cài đặt

### 4. Cài đặt thủ công (CLI)

Nếu không dùng Web Installer:

```bash
# Bước 1: Tạo database
mysql -u root -p -e "CREATE DATABASE shorten_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Bước 2: Copy và sửa config
cp config/configure.install.php config/configure.php

# Sửa config/configure.php: thay {default_host}, {default_username}, {default_password}, {default_database}
# và thay __SALT__ bằng chuỗi ngẫu nhiên (hoặc để composer post-install tạo)

# Bước 3: Chạy migrations
bin/cake migrations migrate

# Bước 4: Tạo admin qua Web Installer (action adminuser) hoặc tạo user trực tiếp trong DB
# Sau đó set installed=1 trong bảng options
```

## Cài đặt lại từ đầu (Reset)

Để cài đặt lại hoàn toàn:

```bash
# 1. Xóa config đã tạo
# Linux/Mac:
rm -f config/configure.php config/app_vars.php

# Windows PowerShell:
Remove-Item config/configure.php, config/app_vars.php -ErrorAction SilentlyContinue

# 2. Drop và tạo lại database
mysql -u root -p -e "DROP DATABASE IF EXISTS shorten_db; CREATE DATABASE shorten_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 3. Truy cập http://your-domain/install và làm lại từ đầu
# Hoặc cài thủ công: copy config/configure.install.php → config/configure.php, sửa host/username/password/database, rồi bin/cake migrations migrate
```

## Chạy development server

```bash
# Symlink assets của theme (cần chạy 1 lần sau khi cài đặt hoặc đổi theme)
php bin/cake.php plugin assets symlink CloudTheme
php bin/cake.php plugin assets symlink ModernTheme

# Chạy server
php -S localhost:8765 -t webroot
```

Truy cập: [http://localhost:8765](http://localhost:8765)

## Luồng chính

```
User click link → Captcha/Landing → Interstitial/Banner → Popad (popup) → Go (redirect + ghi nhận thu nhập)
```

## Tính năng chính

- **Shorten URL** – Rút gọn link với nhiều loại quảng cáo
- **Interstitial** – Màn hình chèn với keyword task, nhập mã
- **Banner** – Quảng cáo banner với countdown
- **Popup** – Popup quảng cáo
- **Final Ads** – Smartlink/Direct Link mở tab mới trước khi redirect
- **Keyword Task Manager** – Quản lý từ khóa theo campaign
- **Adsterra Integration** – Social Bar, Popunder, Direct Link
- **Anti-VPN/Proxy** – ProxyCheck.io / IPinfo.io
- **Anti-Bypass** – Token redirect, delay, referrer validation
- **Rate Limiting** – Giới hạn lượt earn theo IP/link/ngày
- **KYC Profile** – Khai báo nguồn traffic
- **Fraud Report** – Báo cáo theo IP, tỉ lệ Referer
- **Withdrawal** – Rút tiền, phê duyệt/từ chối
- **Social Login** – Đăng nhập Facebook, Google (ADmad/SocialAuth)

## Cấu trúc thư mục


| Thành phần          | Đường dẫn                                                |
| ------------------- | -------------------------------------------------------- |
| Controllers         | `src/Controller/`                                        |
| Models              | `src/Model/`                                             |
| Templates           | `templates/`                                             |
| Locales             | `resources/locales/`                                     |
| Config              | `config/`                                                |
| Migrations          | `config/Migrations/`                                     |
| Links Controller    | `src/Controller/LinksController.php`                     |
| ProxyCheck / IpInfo | `src/Service/ProxyCheckService.php`, `IpInfoService.php` |
| Keyword Tasks       | `src/Controller/Admin/KeywordTasksController.php`        |
| Anti-bypass         | `templates/Element/anti_bypass.php`                      |
| Options Admin       | `templates/Admin/Options/index.php`                      |
| Withdrawals         | `src/Controller/Admin/WithdrawsController.php`           |
| Reports             | `src/Controller/Admin/ReportsController.php`             |


## Cấu hình Admin

- **Settings** – General, Links, Integration, Captcha, Security, Payment, Blog, Social Media
- **Security** – ProxyCheck/IPinfo, Rate Limiting, Anti-Bypass, Token Redirect
- **Final Ads** – Enable, URL Smartlink/Direct Link, Delay
- **Adsterra** – Social Bar, Popunder, Direct Link scripts
- **Social Login** – Facebook, Google (cần App ID/Secret từ developer console)

## Final Ad URL

Điền URL đầy đủ (http/https) – Smartlink hoặc Direct Link. Khi user bấm tiếp tục:

1. Mở Final Ad URL trong tab mới
2. Chờ X giây (cấu hình)
3. Redirect đến link đích

## Keyword Task & Web vệ tinh

- **Mã code** – Quản lý tập trung trên server, sinh khi user click short link
- **Landing page** – `/landing/{alias}` hiển thị mã, load web vệ tinh trong iframe
- **Web vệ tinh** – Chỉ cần nút "LẤY MÃ" link đến `https://yourdomain.com/landing/{alias}`

## Lệnh CLI hữu ích

```bash
# Migrations
bin/cake migrations migrate          # Chạy migrations
bin/cake migrations status          # Xem trạng thái
bin/cake migrations rollback        # Rollback migration cuối

# Version
bin/cake version                    # Xem phiên bản CakePHP
```

## Xem thêm

- [CHANGELOG.md](CHANGELOG.md) – Chi tiết các thay đổi đã thực hiện

