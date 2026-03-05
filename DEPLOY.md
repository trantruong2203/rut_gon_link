# Hướng dẫn Deploy AdLinkFly lên VPS

## 1. Yêu cầu VPS

- **OS**: Ubuntu 20.04/22.04 hoặc CentOS 7/8
- **PHP** >= 8.1 (khuyến nghị 8.2)
- **MySQL** 5.7+ hoặc MariaDB 10.3+
- **Web server**: Apache (mod_rewrite) hoặc Nginx
- **Composer** 2.x

## 2. Cài đặt môi trường (Ubuntu)

```bash
# Cập nhật hệ thống
sudo apt update && sudo apt upgrade -y

# PHP 8.2 + extensions
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-intl php8.2-xml php8.2-curl php8.2-zip

# MySQL
sudo apt install -y mysql-server

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## 3. Upload code lên VPS

**Cách 1: Git (khuyến nghị)**

```bash
cd /var/www
sudo git clone <repo-url> adlinkfly
cd adlinkfly
```

**Cách 2: SCP/RSync từ máy local**

```bash
# Trên máy local (Windows PowerShell hoặc WSL)
scp -r e:\project\* user@your-vps-ip:/var/www/adlinkfly/
# Hoặc rsync
rsync -avz --exclude 'vendor' --exclude 'node_modules' --exclude 'tmp/*' --exclude 'logs/*' e:/project/ user@your-vps-ip:/var/www/adlinkfly/
```

## 4. Cài đặt dependencies

```bash
cd /var/www/adlinkfly
composer install --no-dev --optimize-autoloader
```

> Nếu lỗi platform: `composer install --no-dev --optimize-autoloader --ignore-platform-reqs`

## 5. Cấu hình Database

```bash
# Đăng nhập MySQL
sudo mysql -u root -p

# Tạo database và user
CREATE DATABASE shorten_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'shorten_user'@'localhost' IDENTIFIED BY 'mat_khau_manh';
GRANT ALL ON shorten_db.* TO 'shorten_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 6. Cấu hình ứng dụng

```bash
cd /var/www/adlinkfly

# Copy config mẫu
cp config/configure.install.php config/configure.php

# Sửa config/configure.php (dùng nano hoặc vim)
nano config/configure.php
```

Trong `config/configure.php`, sửa:
- `{default_host}` → `localhost` hoặc `127.0.0.1`
- `{default_username}` → `shorten_user`
- `{default_password}` → mật khẩu đã tạo
- `{default_database}` → `shorten_db`
- `__SALT__` → chuỗi ngẫu nhiên (chạy `openssl rand -hex 32` để tạo)

```bash
# Chạy migrations
php bin/cake.php migrations migrate

# Symlink assets theme (chọn theme đang dùng, ví dụ CloudTheme)
php bin/cake.php plugin assets symlink CloudTheme
php bin/cake.php plugin assets symlink ModernTheme
```

## 7. Bật Production mode

Sửa `config/app.php`:

```php
'debug' => false,
```

## 8. Quyền thư mục

```bash
sudo chown -R www-data:www-data /var/www/adlinkfly
sudo chmod -R 755 /var/www/adlinkfly
sudo chmod -R 775 /var/www/adlinkfly/tmp /var/www/adlinkfly/logs
```

## 9. Cấu hình Web Server

### Apache

```bash
sudo nano /etc/apache2/sites-available/adlinkfly.conf
```

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/adlinkfly/webroot

    <Directory /var/www/adlinkfly/webroot>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/adlinkfly-error.log
    CustomLog ${APACHE_LOG_DIR}/adlinkfly-access.log combined
</VirtualHost>
```

```bash
sudo a2enmod rewrite
sudo a2ensite adlinkfly
sudo systemctl reload apache2
```

### Nginx

```bash
sudo nano /etc/nginx/sites-available/adlinkfly
```

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/adlinkfly/webroot;

    index index.php;
    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/adlinkfly /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

## 10. Tạo tài khoản Admin

Truy cập: `http://yourdomain.com/install` → làm theo wizard, hoặc dùng action `adminuser` để tạo admin.

Sau khi cài xong, set `installed=1` trong bảng `options` (Web Installer tự làm).

## 11. SSL (HTTPS) – Let's Encrypt

```bash
sudo apt install certbot python3-certbot-apache   # Apache
# hoặc
sudo apt install certbot python3-certbot-nginx   # Nginx

sudo certbot --apache -d yourdomain.com   # Apache
# hoặc
sudo certbot --nginx -d yourdomain.com    # Nginx
```

## 12. Sau khi deploy – việc cần làm để web chạy

### A. Domain & DNS

- Trỏ **A record** domain về IP VPS (ví dụ: `yourdomain.com` → `123.45.67.89`)
- Đợi DNS propagate (5 phút – 48 giờ)

### B. Firewall

```bash
sudo ufw allow 80
sudo ufw allow 443
sudo ufw allow 22   # SSH
sudo ufw enable
```

### C. Thư mục upload (ảnh campaign)

```bash
mkdir -p /var/www/adlinkfly/webroot/uploads/campaigns
sudo chown www-data:www-data /var/www/adlinkfly/webroot/uploads -R
sudo chmod 775 /var/www/adlinkfly/webroot/uploads -R
```

### D. Cài đặt qua Web & tạo Admin

1. Truy cập: `http://yourdomain.com/install`
2. Làm theo wizard: Database → Data → Admin → Finish
3. Hoặc nếu đã chạy migrations: `http://yourdomain.com/install/adminuser` để tạo admin

### E. Cấu hình trong Admin

Đăng nhập Admin → **Settings** (Options):

| Option | Mô tả |
|--------|-------|
| **Main Domain** | Domain chính (vd: `yourdomain.com`) – dùng để redirect, validate |
| **Site Name** | Tên website |
| **Short Domain** | Domain rút gọn (nếu dùng subdomain riêng) |
| **Theme** | CloudTheme hoặc ModernTheme |

### F. Cron job (tùy chọn – verify campaign)

```bash
crontab -e
```

Thêm dòng (chạy mỗi ngày lúc 3h sáng):

```
0 3 * * * cd /var/www/adlinkfly && php bin/cake.php campaign_verification_recheck >> /var/log/adlinkfly-cron.log 2>&1
```

### G. SSL (HTTPS)

```bash
sudo certbot --apache -d yourdomain.com   # Apache
# hoặc
sudo certbot --nginx -d yourdomain.com    # Nginx
```

### H. PHP (nếu cần upload ảnh lớn)

Sửa `/etc/php/8.2/apache2/php.ini` hoặc `php.ini` của PHP-FPM:

```ini
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 256M
```

Sau đó restart: `sudo systemctl restart apache2` hoặc `sudo systemctl restart php8.2-fpm`

---

## 13. Checklist nhanh

- [ ] `composer install --no-dev`
- [ ] `config/configure.php` đã sửa host, user, pass, database, salt
- [ ] `config/app.php` → `debug => false`
- [ ] `bin/cake migrations migrate`
- [ ] `bin/cake plugin assets symlink CloudTheme` (hoặc ModernTheme)
- [ ] Quyền `tmp`, `logs` = 775
- [ ] Thư mục `webroot/uploads/campaigns` tồn tại, chmod 775
- [ ] Document root = `/var/www/adlinkfly/webroot`
- [ ] mod_rewrite (Apache) hoặc try_files (Nginx) đã bật
- [ ] DNS trỏ domain về VPS
- [ ] Firewall mở port 80, 443
- [ ] Truy cập `/install` để tạo admin
- [ ] Cấu hình Main Domain trong Admin
