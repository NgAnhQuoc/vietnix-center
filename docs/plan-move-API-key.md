# Chuyển API Key từ .env sang WordPress Database

## Mô tả

API key đã được chuyển từ file `.env` (đọc qua `get_env_api_key()`) sang lưu trong WordPress database (`wp_options`) với key `vnx_api_key`, được mã hóa bằng AES-256-CBC. Admin có thể cập nhật key qua UI trong Tools → Vietnix API.

**Cấu trúc dữ liệu lưu trong DB (sau khi encrypt):**
```json
{
  "key": "<base64(iv)>::<openssl_encrypted>",
  "count": 64
}
```
> `count` = `strlen(plaintext_key)` — dùng để render `*****` trên input mà không lộ key thật.
> `key` = `base64(random_iv) . '::' . openssl_encrypt(plaintext)` — IV ngẫu nhiên mỗi lần encrypt.

---

## ✅ Quyết Định Đã Chốt

| # | Câu hỏi | Quyết định |
|---|---------|------------|
| Q1 | Migrate key cũ từ `.env`? | **Không** — admin nhập key mới qua UI |
| Q2 | Encrypt key trong DB? | **Có** — AES-256-CBC với WP secret salt làm derive key |
| Q3 | Input type? | **`type="password"`** + button toggle show/hide |
| Q4 | Helper class đặt ở đâu? | `app/VnxApiKeyCrypt.php`, namespace `Helper` |
| Q5 | Tách class hay gộp vào `VietnixAPI`? | **Tách riêng** — `VnxApiKeyCrypt` phục vụ cả `register-api.php` và `functions/requires/vietnix_api.php` |

---

## ⚠️ Bảo Mật Cần Lưu Ý

### 🔴 Rủi ro Cao

| # | Vấn đề | Nguyên nhân | Trạng thái |
|---|--------|-------------|-----------|
| 1 | **Lộ key thật qua response** | Trả `key` từ DB ra HTML/JSON | ✅ Chỉ render `count` → `***` — không bao giờ decrypt ra view |
| 2 | **Privilege escalation — API Key** | User không phải admin POST form save key | ✅ `current_user_can('manage_options')` trong block xử lý `api_key` |
| 3 | **Privilege escalation — IP Whitelist** | User không phải admin có thể thay đổi IP whitelist | ✅ `current_user_can('manage_options')` check ở đầu block `save`, trước cả update IP và API Key |
| 4 | **CSRF** | Form submit không có nonce | ✅ `wp_nonce_field` + `wp_verify_nonce` |
| 5 | **Timing attack khi verify key** | So sánh string thường (`==`) | ✅ Dùng `hash_equals()` |
| 6 | **Plaintext trong DB** | Key lưu plaintext | ✅ Mã hóa AES-256-CBC + IV ngẫu nhiên |

### 🟡 Rủi ro Trung Bình

| # | Vấn đề | Nguyên nhân | Trạng thái |
|---|--------|-------------|-----------|
| 7 | **Key rỗng sau save** | User xóa input rồi submit → ghi đè key thành rỗng | ✅ Chỉ update khi `!empty($raw_api_key)` |
| 8 | **Key quá ngắn / weak** | Không validate độ dài | ✅ `MIN_KEY_LEN = 32`, `VnxApiKeyCrypt::save()` trả về `false` nếu ngắn hơn |
| 9 | **Log rò rỉ key** | Debug log in ra key | ✅ Không bao giờ log value key; chỉ log IP (debug tạm) |
| 10 | **XSS trong input** | Người dùng nhập ký tự HTML | ✅ `sanitize_text_field()` trước khi lưu |
| 11 | **IP spoofing qua X-Forwarded-For** | Chấp nhận header mù | ✅ Chỉ trust khi IP nằm trong `VNX_TRUSTED_PROXIES` constant |

---

## Encryption Strategy

Dùng `openssl_encrypt()` với:
- **Algorithm**: `aes-256-cbc`
- **Encryption Key**: SHA-256 hash (raw binary) của WP `AUTH_KEY . SECURE_AUTH_KEY`
- **IV**: Random 16 bytes, lưu kèm dưới dạng `base64(iv) . '::' . encrypted`
- **Fallback**: Nếu `AUTH_KEY` chưa được define → dùng `wp_salt('auth')`

```php
// Derive key
$salt = defined('AUTH_KEY') ? AUTH_KEY . SECURE_AUTH_KEY : wp_salt('auth');
$encryption_key = hash('sha256', $salt, true); // 32 bytes raw

// Encrypt
$iv        = openssl_random_pseudo_bytes(16);
$encrypted = openssl_encrypt($plaintext, 'aes-256-cbc', $encryption_key, 0, $iv);
$stored    = base64_encode($iv) . '::' . $encrypted;

// Decrypt
[$iv_b64, $enc] = explode('::', $stored, 2);
$decrypted = openssl_decrypt($enc, 'aes-256-cbc', $encryption_key, 0, base64_decode($iv_b64));
```

> **Lưu ý:** Key được gắn với môi trường WP — không thể migrate DB sang server khác mà không mất key.

---

## Cấu Trúc File Đã Triển Khai

```
wp-content/plugins/vietnix-plugin/
├── app/
│   └── VnxApiKeyCrypt.php              [NEW]  Helper mã hóa/giải mã (namespace Helper)
├── api/
│   └── register-api.php               [MOD]  Xác thực đọc key từ DB qua VnxApiKeyCrypt
├── functions/
│   └── requires/
│       └── vietnix_api.php            [MOD]  Form handler lưu key qua VnxApiKeyCrypt
└── views/
    └── tools/
        └── vietnix_api.php            [MOD]  View hiển thị mask **** từ stored_length()
```

---

## Chi Tiết Từng File

### 1. `app/VnxApiKeyCrypt.php` — Helper Class

**Namespace:** `Helper`  
**Class:** `VnxApiKeyCrypt` (final, constructor private — chỉ dùng static)

| Method | Mô tả |
|--------|-------|
| `save(string $key): bool` | Encrypt + lưu vào `wp_options`. Trả về `false` nếu key ngắn hơn 32 ký tự. Throw `RuntimeException` nếu `openssl_encrypt` thất bại. |
| `decrypt(): ?string` | Đọc từ DB, giải mã, trả về plaintext. `null` nếu chưa có hoặc lỗi. |
| `stored_length(): int` | Trả về `count` (số ký tự key gốc) để render mask — không decrypt. |
| `derive_key(): string` | Private. SHA-256 của WP salt → 32 bytes raw dùng cho AES-256. |

---

### 2. `api/register-api.php` — Xác Thực API

**Thay đổi so với version cũ:**

```diff
- use Helper\VnxApiKeyCrypt;                  // [ADDED] import namespace
+ use Helper\VnxApiKeyCrypt;

- private static function get_env_api_key()   // [REMOVED] đọc từ .env
- {
-     $env_file = plugin_dir_path(__FILE__) . '../.env';
-     ...
- }

+ private static function get_db_api_key(): ?string  // [ADDED] đọc từ DB
+ {
+     return VnxApiKeyCrypt::decrypt();
+ }

  // Trong authenticate():
- $env_api_key = self::get_env_api_key();
- if ($env_api_key !== null && hash_equals($env_api_key, $api_key_header)) {
+ $db_api_key = self::get_db_api_key();
+ if ($db_api_key !== null && hash_equals($db_api_key, $api_key_header)) {
```

> **Lưu ý:** Hiện còn `error_log` debug IP ở dòng 78 — chỉ dùng tạm để troubleshoot, cần xóa sau khi confirm hoạt động.

---

### 3. `functions/requires/vietnix_api.php` — Form Handler

**Class:** `VietnixAPI`

**Luồng xử lý `vnx_api_submit()`:**

1. Đọc `_wp_http_referer` để redirect sau khi xử lý
2. Verify nonce `vnx_api_security` — fail → redirect về, không làm gì
3. Nếu `vnx_api_submit === 'save'`:
   - Read `allow_api` → parse Tagify JSON → `update_option('vnx_api_allow_ip')`
   - Nếu `api_key` không rỗng:
     - Check `current_user_can('manage_options')` → fail → `wp_die(403)`
     - `sanitize_text_field()` → `VnxApiKeyCrypt::save()` (tự validate MIN_KEY_LEN=32)
4. Redirect về referrer

**Static proxy methods** (cho phép dùng mà không cần `use`):

| Method | Delegate tới |
|--------|-------------|
| `get_api_key_count(): int` | `VnxApiKeyCrypt::stored_length()` |
| `get_decrypted_api_key(): ?string` | `VnxApiKeyCrypt::decrypt()` |

---

### 4. `views/tools/vietnix_api.php` — View

**Logic hiển thị:**
```php
use Helper\VnxApiKeyCrypt;

$api_key_count = class_exists(VnxApiKeyCrypt::class)
    ? VnxApiKeyCrypt::stored_length()
    : 0;

$api_key_placeholder = $api_key_count > 0
    ? str_repeat('*', min($api_key_count, 40))
    : 'Nhập API Key mới...';
```

**UI:**
- Input `type="password"` — không bao giờ render key thật
- Placeholder hiển thị số `*` tương ứng độ dài key đang lưu
- Button toggle show/hide chỉ hiện khi user đang gõ (`oninput`)
- Warning text nếu `$api_key_count === 0` (chưa cấu hình)
- Form action → `admin-post.php`, method POST

---

## Verification Plan

### Manual Test

1. Vào **Tools → Vietnix API**
2. Nhập API key mới (>= 32 ký tự) → **Save**
3. Kiểm tra DB: `wp_options` có `vnx_api_key` với `key` (encrypted) và `count` đúng
4. Xác nhận `key` trong DB là chuỗi encrypted (không phải plaintext)
5. Reload trang → input placeholder hiển thị đúng số `*`
6. Gọi API endpoint với đúng key → trả về `200`
7. Gọi API endpoint với sai key → trả về `401`
8. Gọi API từ IP không trong whitelist → trả về `403`
9. Submit form với input **trống** → key trong DB **không bị xóa**
10. Kiểm tra HTML source → xác nhận key **không xuất hiện** ở bất kỳ đâu
11. Thử nhập key < 32 ký tự → `VnxApiKeyCrypt::save()` trả về `false`, không update DB

### Security Test

```bash
# Test 1: Đúng IP + đúng key → 200
curl -H "api-key: <YOUR_KEY>" https://site.com/wp-json/vnx/v1/...

# Test 2: Đúng IP + sai key → 401
curl -H "api-key: wrongkey" https://site.com/wp-json/vnx/v1/...

# Test 3: IP không whitelist → 403
# (gọi từ IP khác)

# Test 4: Contributor cố update IP (nonce hợp lệ)
# → current_user_can('manage_options') block tại block api_key
# ⚠️ Lưu ý: IP whitelist chưa có check quyền riêng — cần bổ sung
```

---

## TODO / Known Issues

| # | Vấn đề | Mức độ | Action |
|---|--------|--------|--------|
| 1 | `error_log` debug IP còn active trong `authenticate()` (dòng 78) | 🟡 Medium | Xóa sau khi confirm production |
| 2 | `$_POST['_wp_http_referer']` chưa được `sanitize_text_field()` trước khi truyền vào `site_url()` | 🟡 Medium | Thêm `sanitize_text_field()` |
