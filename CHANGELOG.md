# Những gì đã làm – AdLinkFly

Tài liệu ghi nhận các tính năng và sửa lỗi đã triển khai theo kế hoạch yêu cầu kỹ thuật.

---

## A. Hệ thống và Bảo mật

### A1. Fix Token (CSRF/Form Token) ✅

- **Vấn đề:** Lỗi `implode()` khi render form shorten (PHP 8.x)
- **Giải pháp:** Thêm `'shorten'` vào `unlockedActions` trong LinksController
- **File:** `src/Controller/LinksController.php`

### A2. Anti-VPN/Proxy ✅

- **ProxyCheck trong go():** Gọi `ProxyCheckService::checkIp()` trước khi ghi earnings
- **IpInfoService:** Thêm fallback dùng IPinfo.io (option `proxy_check_provider`: proxycheck | ipinfo)
- **Files:** 
  - `src/Service/IpInfoService.php` (mới)
  - `src/Service/ProxyCheckService.php` – thêm `checkIp()`
  - `config/Migrations/20260226160000_add_proxy_check_provider_options.php`
  - `src/Template/Admin/Options/index.ctp` – form Provider, IPinfo Token

### A3. Anti-Bypass nâng cao ✅

- **Token-based redirect:** URL đích không trả trực tiếp; dùng token một lần, redirect qua `/links/r?t=TOKEN`
- **Delay redirect:** Độ trễ ngẫu nhiên 2–5 giây trước khi redirect (cấu hình được)
- **Referrer validation:** Kiểm tra `document.referrer` phải chứa domain site trước khi redirect
- **Files:**
  - `src/Controller/LinksController.php` – action `r()`, cập nhật `_getRedirectUrl()`
  - `src/Template/Links/view_interstitial.ctp` – referrer check + delay
  - Options: `anti_bypass_redirect_token`, `anti_bypass_redirect_delay_min`, `anti_bypass_redirect_delay_max`

### A4. Rate Limiting ✅

- Đã có sẵn: 1 IP/link/ngày = max N views có earnings
- Option: `rate_limit_views_per_link_day` (mặc định 2)

---

## B. Logic quảng cáo và nhiệm vụ

### B1. Keyword Task Manager ✅

- **Bảng `keyword_tasks`:** keyword, target_url, ad_code, campaign_id, status, sort_order
- **Admin CRUD:** `Admin/KeywordTasksController` + views
- **Tích hợp:** Interstitial dùng keyword từ Keyword Task theo campaign
- **Files:**
  - `config/Migrations/20260226120000_create_keyword_tasks.php`
  - `src/Model/Table/KeywordTasksTable.php`
  - `src/Controller/Admin/KeywordTasksController.php`
  - `src/Template/Admin/KeywordTasks/`

### B2. Adsterra Integration ✅

- **Options:** `adsterra_social_bar`, `adsterra_popunder`, `adsterra_direct_link`
- **Chèn script:** captcha.ctp, go_banner.ctp, go_interstitial.ctp
- **Files:**
  - `config/Migrations/20260226130000_add_adsterra_options.php`
  - `src/Template/Admin/Options/index.ctp`
  - Layouts: captcha, go_banner, go_interstitial

### B3. Vietnamese Localization ✅

- Bổ sung bản dịch trong `src/Locale/vi_VN/default.po`
- Các chuỗi mới: Provider, Token-based Redirect, Redirect Delay, Invalid referrer, v.v.

---

## C. Quản lý Admin và Payout

### C1. KYC Profile ✅

- **Cột mới:** `users.traffic_source`, `users.kyc_status`
- **Form profile:** Field "Nguồn traffic chính" (dropdown)
- **Admin view user:** Hiển thị Traffic Source, KYC Status
- **Files:**
  - `config/Migrations/20260226140000_add_kyc_columns_to_users.php`
  - `src/Template/Member/Users/profile.ctp`
  - `src/Template/Admin/Users/view.ctp`

### C2. Fraud Report theo IP ✅

- **Action:** `Admin/ReportsController::ips()`
- **Nội dung:** Bảng IP, views, earnings, tỉ lệ Referer (Google/Direct/Other), top referers
- **Files:**
  - `src/Controller/Admin/ReportsController.php`
  - `src/Template/Admin/Reports/ips.ctp`
  - Menu: Reports → Fraud Report by IP

### C3. Withdrawal – Min $3, Reject ✅

- **Migration:** Set `minimum_withdrawal_amount` = 3
- **Action reject:** Từ chối, hoàn tiền vào balance
- **Files:**
  - `config/Migrations/20260226150000_set_minimum_withdrawal_to_3.php`
  - `src/Controller/Admin/WithdrawsController.php` – action `reject()`
  - `src/Template/Admin/Withdraws/index.ctp` – nút Reject

---

## D. Sửa lỗi khác

### D1. emptyTmp() – PHP 8.x ✅

- **Lỗi:** `array_merge() does not accept unknown named parameters` khi lưu Settings
- **Nguyên nhân:** `Cake\Filesystem\Folder->findRecursive()` không tương thích PHP 8.x
- **Giải pháp:** Viết lại `emptyTmp()` dùng `RecursiveIteratorIterator`
- **File:** `config/functions.php`

### D2. Cache redirect token ✅

- **Lỗi:** `The "+1 minute" cache configuration does not exist`
- **Nguyên nhân:** `Cache::write(..., '+1 minute')` – tham số thứ 3 là tên config, không phải duration
- **Giải pháp:** Thêm config `redirect_token` trong `config/app.php`, dùng đúng config khi read/write/delete
- **File:** `config/app.php`, `src/Controller/LinksController.php`

### D3. Cache config khi đọc token ✅

- **Lỗi:** Redirect về trang chủ thay vì Final Ad / link đích
- **Nguyên nhân:** Ghi cache vào config `redirect_token` nhưng đọc từ config `default`
- **Giải pháp:** Dùng `Cache::read(..., 'redirect_token')` và `Cache::delete(..., 'redirect_token')`
- **File:** `src/Controller/LinksController.php`

---

## Migrations đã chạy


| Migration      | Mô tả                                                            |
| -------------- | ---------------------------------------------------------------- |
| 20260226100000 | ProxyCheck, Rate limit, Anti-bypass options                      |
| 20260226110000 | Final Ad options                                                 |
| 20260226120000 | Keyword tasks table                                              |
| 20260226130000 | Adsterra options                                                 |
| 20260226140000 | KYC columns (traffic_source, kyc_status)                         |
| 20260226150000 | Minimum withdrawal = 3                                           |
| 20260226160000 | Proxy check provider, IPinfo token, Anti-bypass redirect options |


---

## Tóm tắt

- **Đã triển khai:** Token fix, Anti-VPN/Proxy (ProxyCheck + IpInfo), Anti-Bypass, Keyword Task Manager, Adsterra, KYC, Fraud Report IP, Withdrawal reject + min $3, Vietnamese locale
- **Đã sửa:** emptyTmp PHP 8.x, Cache redirect token, Cache config read/delete

