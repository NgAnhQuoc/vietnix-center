# Plan: Đổi tên hàm/class/file còn mang tên "Telegram" cho khớp chức năng thật (Discord) — plugin vietnix-center

Ngày lập: 2026-09-23
Dựa trên: `bao-cao/plan-telegram-to-discord.md` (đã triển khai xong phần chuyển gửi tin sang Discord — xem báo cáo kiểm tra trong cùng thư mục)

## 0. Mục tiêu & phạm vi

Sau khi đã chuyển hẳn sang gửi Discord, nhiều hàm/class/file/action vẫn còn tên "Telegram" vì lúc chuyển đổi **cố ý giữ tên** để không phải migrate dữ liệu/JS. Plan này đổi tiếp những chỗ đổi được **mà không mất cấu hình cũ, không phải build lại JS, không đổi option/route dùng chung với hệ thống khác**.

Không đổi (xem mục 4 — lý do):
- Option `vnx_sync_telegram_sheet_setting` (dùng chung với vietnix-plugin).
- CSS class `input_sync_elements_*_telegram` (gắn với JS đã build sẵn trong `assets/js/admin.js`).
- Route `POST /wp-json/vietnix/telegram` (frontend/Bricks đang gọi thẳng URL này).

## 1. Phát hiện quan trọng — vì sao đổi tên file/key an toàn được

- `extentions/{key}.php` và `tools/{key}.php`: **key trong `ExtentionsRegister()`/`ToolsRegister()` = tên file**, `vnx_active.php:87,104` `require_once` theo đúng tên key. Đổi tên file bắt buộc đổi key.
- `register.php` đã có sẵn cơ chế migrate key an toàn: `RegisterVariables_Center::RENAMED_KEYS` + `migrate_legacy_keys()` (đang dùng cho `vietnix-sync-telegram-sheet-form` → `vietnix-sync-telegram-sheet`). Site cũ có option `..._tools`/`..._extentions` lưu key cũ sẽ **tự chuyển sang key mới** lần load đầu tiên sau khi đổi code, không mất trạng thái bật/tắt.
- Với tool, tên **view** mặc định = key đổi `-` thành `_` (`register.php:341`, `normalize_addons($tools_list, true)`), nên đổi key tool kéo theo phải đổi tên file view tương ứng trong `views/tools/`.
- `functions/requires/*.php`: được nạp bằng `scandir()` trong `functions/init.php` — **không phụ thuộc tên file vào bất kỳ registry nào**, đổi tên tự do, không cần khai báo lại đâu cả.
- Hàm/class PHP thuần (không phải key extension/tool): chỉ cần sửa đồng bộ nơi gọi + wrapper compat trong `functions/compat-vietnix-plugin.php` (**giữ nguyên tên bên trái** — đó là tên cũ mà vietnix-plugin/snippet ngoài còn gọi vào).

## 2. Danh sách đổi tên

### 2.1 Comment / docblock (rủi ro 0, không đổi code chạy)

| File | Sửa |
|---|---|
| `vietnix-center.php:76` | "...dùng cho callback gửi Telegram/Sheet/Discord" → "...dùng cho callback gửi Discord/Sheet" |
| `vnx_active.php:33` | "...Telegram, sitemap..." → "...Discord, sitemap..." |
| `functions/requires/vnx_tool_notice.php:8` | "Telegram & Sheets" → "Discord & Sheets" |

### 2.2 Hàm / class PHP thuần (không đụng DB, không cần build JS)

| Hiện tại | Đổi thành | Sửa ở |
|---|---|---|
| `function bricksSendTelegramMessage_Center()` | `bricksSendDiscordMessage_Center()` | `extentions/api_send_message_bot.php:10,77`; wrapper `functions/compat-vietnix-plugin.php:68` (chỉ đổi tham số truyền vào `vnx_center_compat_call`, **giữ tên hàm bên trái `bricksSendTelegramMessage`**) |
| `function sendTelegramMessage_Center()` | `sendDiscordCallmeMessage_Center()` | `extentions/api.php:4,37`; wrapper `functions/compat-vietnix-plugin.php:248` (giữ tên trái `sendTelegramMessage`) |
| Class `DataSyncTelegramSheet_Center` (file `extentions/helper/DataSyncTelegramSheet_Center.php`) | Class `DataSyncDiscordSheet_Center` (file đổi tên `extentions/helper/DataSyncDiscordSheet_Center.php`) | Đổi tên file + khai báo class; sửa `require_once` và `new DataSyncTelegramSheet_Center()` trong `extentions/api_send_message_bot.php:3,19` |
| Method `getdataSyncTelegramSheet()` | `getDataSyncDiscordSheet()` | `extentions/helper/DataSyncDiscordSheet_Center.php:6`; gọi ở `extentions/api_send_message_bot.php:28` |
| `admin_post_vnx_sync_telegram_sheet_submit_Center`, hàm `vnx_sync_telegram_sheet_submit_Center()`, nonce action `vnx_sync_telegram_sheet_security`, nonce field `vnx-sync-telegram-sheet-nonce` | đổi `telegram_sheet` → `discord_sheet` trong tất cả 4 tên trên | `functions/requires/vietnix-sync-telegram-sheet.php` (toàn bộ, tự chứa trong 1 file); `views/tools/vietnix_sync_telegram_sheet.php` (input hidden `action`, `wp_nonce_field`, `data-action`); wrapper `functions/compat-vietnix-plugin.php:386-387` (giữ tên trái `vnx_sync_telegram_sheet_submit`, chỉ đổi tham số gọi) |

### 2.3 Tên file + key registry (dùng cơ chế `RENAMED_KEYS` ở mục 1)

| Hiện tại | Đổi thành |
|---|---|
| File `extentions/vnx_telegram_post_message.php` | `extentions/vnx_discord_post_notify.php` |
| Key extension `vnx_telegram_post_message` (`register.php:161`) | `vnx_discord_post_notify` |
| File `tools/vietnix-sync-telegram-sheet.php` | `tools/vietnix-sync-discord-sheet.php` |
| File `views/tools/vietnix_sync_telegram_sheet.php` | `views/tools/vietnix_sync_discord_sheet.php` |
| File `functions/requires/vietnix-sync-telegram-sheet.php` | `functions/requires/vietnix-sync-discord-sheet.php` (đổi tự do, xem mục 1) |
| Key tool `vietnix-sync-telegram-sheet` (`register.php:293`) | `vietnix-sync-discord-sheet` |

Thêm vào `RegisterVariables_Center::RENAMED_KEYS` (giữ đúng thứ tự — `migrate_legacy_keys()` chạy 1 lượt `foreach` nên xếp theo thứ tự cũ→mới để chain đúng, key rất cũ vẫn lên được key mới nhất trong cùng 1 lần load):

```php
const RENAMED_KEYS = [
    'vietnix-sync-telegram-sheet-form' => 'vietnix-sync-telegram-sheet', // đã có
    'vietnix-sync-telegram-sheet'      => 'vietnix-sync-discord-sheet',  // mới
    'vnx_telegram_post_message'        => 'vnx_discord_post_notify',    // mới
];
```

Đồng thời sửa 2 chỗ dùng key cũ để tham chiếu đúng file/label mới trong `register.php` (`'vnx_telegram_post_message' => [...]` → `'vnx_discord_post_notify' => [...]`, `'vietnix-sync-telegram-sheet' => [...]` → `'vietnix-sync-discord-sheet' => [...]`).

## 3. Không đổi — lý do

| Cái gì | Vì sao |
|---|---|
| Option `vnx_sync_telegram_sheet_setting` | Dùng chung với vietnix-plugin (đọc/ghi cùng option) — đổi tên = mất tương thích 2 chiều, phải sửa cả 2 plugin cùng lúc |
| CSS class `input_sync_elements_*_telegram` | Gắn cứng trong JS đã build (`assets/js/admin.js`, nguồn `assets/js/admin/vietnix_sync_telegram_sheet.js`) — đổi phải `yarn build`/`npm run build` lại, có thể lệch nếu môi trường build không có sẵn |
| Route `POST /wp-json/vietnix/telegram` | Frontend/Bricks gọi thẳng URL này — đổi phải sửa theme/Bricks đồng thời (việc riêng, đã ghi trong `plan-telegram-to-discord.md` mục 4.3) |

## 4. Thứ tự thực hiện

1. Mục 2.1 (comment) — làm trước, không rủi ro.
2. Mục 2.2 (hàm/class PHP) — sửa từng cặp (định nghĩa + nơi gọi + wrapper compat) trong 1 lần sửa để không bị lỗi "undefined function" giữa chừng.
3. Mục 2.3 (file + key) — theo thứ tự: đổi tên file → sửa nội dung file (nếu có tự tham chiếu tên cũ) → sửa key trong `register.php` → thêm `RENAMED_KEYS` → chạy thử trang Tools/Settings xem tool cũ có tự bật lại đúng không.
4. Test theo mục 5.

## 5. Kiểm thử sau khi đổi

| Case | Cách test | Kỳ vọng |
|---|---|---|
| Site đã bật "Discord & Sheets" trước khi đổi | Load lại trang Setting → Tools sau khi deploy code mới | Tool vẫn hiện đang **bật**, không phải bật lại tay (nhờ `RENAMED_KEYS`) |
| Site đã bật "Discord Post Notify" trước khi đổi | Tương tự | Extension vẫn bật, publish bài viết vẫn gửi Discord |
| Submit form luồng 1 (`vietnix-sync-discord-sheet`) | Gửi form có cấu hình webhook | Vẫn gửi Discord + Sheet bình thường, nonce/action mới hoạt động |
| Submit form luồng 2 (`bricksSendDiscordMessage_Center`) | Gửi form dùng thử kiểu cũ | Vẫn gửi embed "ĐĂNG KÝ DÙNG THỬ" |
| REST luồng 3 (`sendDiscordCallmeMessage_Center`) | `POST /wp-json/vietnix/telegram` | Vẫn 200 + tin Discord (route không đổi) |
| vietnix-plugin gọi tên cũ qua compat | Trên site chạy song song vietnix-plugin (nếu có), gọi `bricksSendTelegramMessage()`/`sendTelegramMessage()`/`vnx_sync_telegram_sheet_submit()` | Vẫn chạy đúng qua `vnx_center_compat_call` (không fatal "undefined function") |
| Trang Tools hiển thị đúng view | Mở tool "Discord & Sheets" | Panel form hiện đúng, không bị lỗi "không tìm thấy view" |

## 6. Rủi ro

| Rủi ro | Giảm thiểu |
|---|---|
| Quên sửa 1 trong 2 đầu (định nghĩa hàm mới / nơi gọi cũ) → fatal "Call to undefined function" | Grep lại tên cũ sau khi sửa, đảm bảo 0 kết quả ngoài comment/RENAMED_KEYS |
| Đổi key nhưng quên thêm `RENAMED_KEYS` → site cũ mất trạng thái bật tool/extension | Bắt buộc thêm `RENAMED_KEYS` cùng lúc đổi key trong bước 2.3 |
| `RENAMED_KEYS` sai thứ tự → chain migrate không chạy hết (key rất cũ dừng ở bước giữa) | Xếp đúng thứ tự cũ → mới như ví dụ mục 2.3, test bằng cách set thử option với key cũ nhất rồi gọi `get_enabled()` |
| Đổi tên view file nhưng quên đổi tên tool key tương ứng (hoặc ngược lại) → "không tìm thấy view" | Đổi 2 thứ cùng 1 commit, test mở trang Tools ngay sau khi sửa |
