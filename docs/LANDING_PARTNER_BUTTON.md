# Hướng dẫn đặt nút LẤY MÃ trên trang đối tác

Khi trang landing được nhúng trong iframe, URL của trang đối tác sẽ có thêm tham số `landing_url`. Đối tác cần đọc tham số này và hiển thị nút LẤY MÃ với link trỏ về trang landing.

## Cách hoạt động

1. User vào trang landing → iframe load trang đối tác với URL: `https://trang-doi-tac.com?landing_url=https://yoursite.com/landing/abc123`
2. Trang đối tác đọc `landing_url` từ URL và hiển thị nút
3. User nhấn nút LẤY MÃ → link mở với `target="_parent"` → trang cha (landing) chuyển đến URL kèm `?action=get_code`
4. Trang landing tự động bắt đầu đếm ngược 60 giây và hiển thị mã

---

## WordPress

### Cách 1: Shortcode (khuyến nghị)

Thêm vào `functions.php` của theme hoặc plugin:

```php
function landing_get_code_button() {
    $landing_url = isset($_GET['landing_url']) ? esc_url_raw($_GET['landing_url']) : '';
    if (empty($landing_url)) {
        return '';
    }
    $get_code_url = add_query_arg('action', 'get_code', $landing_url);
    return '<div style="text-align: center; margin: 20px 0;">
        <a href="' . esc_url($get_code_url) . '" target="_parent" class="button" style="padding: 15px 40px; font-size: 18px; background: #d9534f; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">LẤY MÃ</a>
    </div>';
}
add_shortcode('landing_get_code', 'landing_get_code_button');
```

Sau đó trong bài viết hoặc trang, thêm: `[landing_get_code]`

### Cách 2: Template / theme file

Trong file template (ví dụ `single.php`, `page.php` hoặc `footer.php`):

```php
<?php if (!empty($_GET['landing_url'])) : 
    $landing_url = esc_url_raw($_GET['landing_url']);
    $get_code_url = add_query_arg('action', 'get_code', $landing_url);
?>
<div style="text-align: center; margin: 20px 0;">
    <a href="<?php echo esc_url($get_code_url); ?>" target="_parent" 
       style="padding: 15px 40px; font-size: 18px; background: #d9534f; color: white; text-decoration: none; border-radius: 4px;">
        LẤY MÃ
    </a>
</div>
<?php endif; ?>
```

### Cách 3: Block / Gutenberg

Dùng plugin "Shortcode" hoặc tạo Custom HTML block với shortcode `[landing_get_code]`.

---

## HTML thuần (không dùng WordPress)

Thêm script vào cuối `<body>`:

```html
<div id="landing-get-code-btn" style="display: none; text-align: center; margin: 20px 0;"></div>
<script>
(function() {
    var params = new URLSearchParams(window.location.search);
    var landingUrl = params.get('landing_url');
    if (landingUrl) {
        var getCodeUrl = landingUrl + (landingUrl.indexOf('?') >= 0 ? '&' : '?') + 'action=get_code';
        var btn = document.getElementById('landing-get-code-btn');
        btn.innerHTML = '<a href="' + getCodeUrl + '" target="_parent" style="padding: 15px 40px; font-size: 18px; background: #d9534f; color: white; text-decoration: none; border-radius: 4px;">LẤY MÃ</a>';
        btn.style.display = 'block';
    }
})();
</script>
```

---

## Lưu ý quan trọng

- **`target="_parent"`** bắt buộc: khi nhấn trong iframe, link phải mở trên frame cha (trang landing), không phải trong iframe.
- Nút chỉ hiển thị khi có `landing_url` trong URL (tức là trang được load trong iframe của landing).
- Có thể tùy chỉnh CSS để nút khớp với giao diện trang đối tác.
