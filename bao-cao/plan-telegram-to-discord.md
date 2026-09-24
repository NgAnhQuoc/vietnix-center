# Plan: Bỏ Telegram, chuyển toàn bộ notify sang Discord webhook — plugin vietnix-center

Ngày lập: 2026-09-22 (cập nhật: 2026-09-22 — bỏ giai đoạn chạy song song, cắt hẳn Telegram)
Dựa trên: `bao-cao/telegram-forms-report.md`

## 0. Mục tiêu & phạm vi

- Cả 4 luồng đang gửi Telegram (form cấu hình động, form dùng thử kiểu cũ, REST "Gọi lại cho tôi", bài viết mới) **chỉ gửi Discord webhook**, không gửi Telegram nữa.
- **Không chạy song song**: deploy xong là Telegram ngừng. Vì vậy phải điền đủ webhook **trước** khi deploy (mục 2), nếu không tin sẽ mất.
- Không đổi phần Google Sheet đang chạy kèm luồng 1.
- Gửi qua helper có sẵn `HelperCenter\DiscordBot::sendMessageByWebhook()` (`app/DiscordBot.php`). Không viết client mới.
- Ngoài phạm vi (chỉ ghi nhận): WPCode snippet #406680, #295046, các luồng đang gửi Discord sẵn, và plugin vietnix-plugin.

## 1. Quyết định đã chốt

| # | Câu hỏi | Chốt |
|---|---------|------|
| Q1 | Lưu webhook ở đâu? | Luồng 1: field **Discord Webhook** cho từng form trong tool (thay "ID Room"). Luồng 2, 3, 4: key trong option `vnx_plugin_setting_options` (trang Setting → Options), fallback constant `VNX_DISCORD_WEBHOOK_*` trong `wp-config.php`. Không hardcode webhook trong code. |
| Q2 | Chạy song song hay cắt hẳn? | **Cắt hẳn Telegram**, chỉ dùng Discord. |
| Q3 | Mỗi nhóm Telegram cũ ↔ channel Discord nào? | Team sale/marketing chốt bảng mapping ở mục 2 trước khi deploy. |
| Q4 | Chặn gửi trên staging/local? | Có — ngoài `https://vietnix.vn`, luồng 1–3 gửi vào webhook test (`discord_webhook_test`), không có thì bỏ qua. Luồng 4 chỉ đăng ký hook trên production. |
| Q5 | Random người phụ trách ở luồng 2 (`$user_list`)? | Bỏ. Nếu cần tag người thì làm ở phase sau bằng Discord user ID `<@id>`. |
| Q6 | Production có chạy song song vietnix-plugin không? | Vẫn phải kiểm tra. Nếu có: center ở companion mode không đăng ký hook, tin do vietnix-plugin gửi **Telegram** → phải sửa vietnix-plugin tương tự hoặc tắt nó. |

## 2. Việc cần làm trước khi deploy (không sửa code)

1. Trên **production**:
   - Export option `vnx_sync_telegram_sheet_setting` (`wp option get vnx_sync_telegram_sheet_setting --format=json`) để biết form nào đang có `roomID` (đang gửi Telegram).
   - Xem option `vnx_plugin_setting_extentions` và danh sách plugin active (vietnix-plugin?).
2. Lập bảng mapping và tạo webhook:

| Luồng | Nguồn | Group Telegram cũ | Channel Discord mới | Điền webhook ở |
|-------|-------|-------------------|---------------------|----------------|
| 1 | Form `dang-ky-toi-uu-web` | roomID trong option | ? | Tools → Discord & Sheets → form đó → Discord Webhook |
| 1 | (các form khác có roomID) | … | ? | như trên |
| 2 | Form dùng thử kiểu cũ | `-515577488` | ? | Setting → Options → Form dùng thử (kiểu cũ) |
| 3 | REST "Gọi lại cho tôi" | `-548848102` | ? | Setting → Options → Gọi lại cho tôi |
| 4 | Bài viết mới publish | `-1002201598215` | ? | Setting → Options → Bài viết mới |
| – | Staging/local | – | #test-notify | Setting → Options → Channel test (trên staging) |

3. Ô nhập webhook chỉ xuất hiện sau khi deploy. Thứ tự an toàn: đặt sẵn constant `VNX_DISCORD_WEBHOOK_TRIAL` / `_CALLME` / `_POST` trong `wp-config.php` (luồng 2–4) trước khi deploy → deploy giờ ít form → điền webhook luồng 1 cho từng form ngay → submit thử từng luồng.

## 3. Thay đổi code — ĐÃ LÀM

### 3.1 Helper dùng chung — `extentions/helper/discord_notify.php` (mới)

- `vnx_discord_get_webhook_Center($key)`: option `vnx_plugin_setting_options[$key]` → constant `VNX_<KEY>` → rỗng.
- `vnx_discord_parse_webhooks_Center($webhooks)`: nhận chuỗi cách dấu phẩy hoặc mảng, chỉ giữ URL `https://discord(app).com/api/webhooks/{id}/{token}`.
- `vnx_discord_escape_Center($text)` / `vnx_discord_format_fields_Center($fields)`: escape markdown Discord cho dữ liệu khách nhập, chèn zero-width space sau `@`.
- `vnx_discord_notify_Center($webhooks, $title, $content, $color, $envGuard = true)`: gửi tới từng webhook, timeout 5s, trả `true` nếu ít nhất 1 webhook gửi được; không có webhook → `error_log`.
- `vnx_notify_is_production_Center()`: `home_url === https://vietnix.vn` (có filter cùng tên).
- `app/DiscordBot.php`: thêm tham số `$timeout` (mặc định 15, giữ nguyên caller cũ) và `allowed_mentions: {parse: []}` để không ping ai.

### 3.2 Luồng 1 — form cấu hình động

| File | Việc |
|------|------|
| `views/tools/vietnix_sync_telegram_sheet.php` | Bỏ ô "ID Room", thêm "Discord Webhook" (Tagify, tối đa 5). Nhóm đổi tên "Discord". |
| `functions/requires/vietnix-sync-telegram-sheet.php` | Lưu `discordWebhook` (sanitize `esc_url_raw`). `roomID` giữ nguyên giá trị cũ khi lưu (vietnix-plugin dùng chung option). |
| `extentions/helper/DataSyncTelegramSheet_Center.php` | Bỏ `initDataSendTelegram()`, `escapeTelegramHtml()`, key `dataSendTelegram`/`group_id`. Thêm `initDataSendDiscord()`, key `discordWebhook`. Form mới vẫn ghi `roomID: ""` cho tương thích. |
| `extentions/api_send_message_bot.php` | Chỉ gửi Discord (nếu form có webhook) + Google Sheet. Bỏ token Telegram hardcode. |

Option vẫn tên `vnx_sync_telegram_sheet_setting`, class/tên file/tên class CSS (`input_sync_elements_*_telegram`) giữ nguyên để không phải migrate và build lại JS. Label tool đổi thành **"Discord & Sheets"**.

### 3.3 Luồng 2 — form dùng thử kiểu cũ

`extentions/api_send_message_bot.php`: embed "🆕 ĐĂNG KÝ DÙNG THỬ MIỄN PHÍ" (Tên/Email/SĐT/Gói) tới `discord_webhook_trial`. Đã xoá `$user_list`/`$mention` và token `368982987:…`.

### 3.4 Luồng 3 — REST "Gọi lại cho tôi"

`extentions/api.php`: gửi tới `discord_webhook_callme`. Response `true` khi Discord gửi được, ngược lại `400`. Giữ nonce. **Route vẫn là `/wp-json/vietnix/telegram`** vì frontend đang gọi URL này (đổi route cần sửa theme/Bricks cùng lúc).

### 3.5 Luồng 4 — bài viết mới

`extentions/vnx_telegram_post_message.php`: gộp nhánh `post`/`lap-trinh` thành `vnx_new_post_info_Center()`, chỉ gửi Discord tới `discord_webhook_post`. Giữ guard `home_url === https://vietnix.vn`. Tên file giữ nguyên (key extension = tên file, đổi tên sẽ làm extension tự tắt), chỉ đổi label.

### 3.6 Trang Setting

`general.php` tab Options: thêm 4 ô `discord_webhook_trial`, `discord_webhook_callme`, `discord_webhook_post`, `discord_webhook_test`. `functions/requires/vnx_plugin_general.php`: sanitize `esc_url_raw` khi lưu.

### 3.7 Dọn Telegram

- Xoá `extentions/helper/telegram_api.php` (`TelegramApi_Center`).
- Xoá `vnx_api_send_Center()` (`gapi.php`, bản GET cũ). Giữ compat `vnx_api_send()`: nếu snippet còn gọi thì chỉ ghi `error_log`, không fatal, không gửi gì. Theme không gọi.
- Xoá constant `VNX_Telegram_Bot_Token` (`vietnix-center.php`).
- `register.php`, `README.md`: label/desc đổi sang Discord.
- `crontab/reportPost.php`: sửa message "ID nhóm Telegram" → "Webhook Discord".

## 4. Việc còn lại sau deploy

1. **Revoke 2 bot token Telegram** (`5574621672:…`, `368982987:…`) qua BotFather — sau khi chắc vietnix-plugin/snippet không còn dùng.
2. Xoá `telegram_bot_token` khỏi option `vnx_plugin_setting_options` nếu có (không còn đọc).
3. Nếu muốn đổi route `/vietnix/telegram` → `/vietnix/callme`: thêm route mới, sửa frontend, rồi mới bỏ route cũ.
4. Cập nhật `bao-cao/telegram-forms-report.md` → đánh dấu đã chuyển.

## 5. Việc liên quan ngoài plugin

- Tìm trong WPCode snippet: `vnx_api_send(`, `TelegramApi_Center`, `VNX_Telegram_Bot_Token` — nếu có thì sửa trước deploy.
- WPCode #406680: thêm `return $validation_errors;` và bỏ nhánh gửi trùng Discord với `vnx_FormSendMessageDiscord_Center` cho form `dang-ky-dung-thu-hosting-mp`.
- WPCode #295046 "Notify admin post publish" đã gửi Discord khi publish → nếu cùng channel với `discord_webhook_post` thì tắt snippet.
- Webhook hardcode ở `functions/requires/vietnix-sync-discord.php:23` và snippet #406680 → chuyển vào option, rotate webhook.
- Nếu production chạy vietnix-plugin (Q6): áp dụng tương tự cho vietnix-plugin hoặc tắt nó.

## 6. Kiểm thử (staging, có `discord_webhook_test`)

| Case | Cách test | Kỳ vọng |
|------|-----------|---------|
| Luồng 1, form có webhook | Submit `dang-ky-toi-uu-web` | 1 embed ở channel test; Sheet vẫn có dòng mới; **không** có tin Telegram |
| Luồng 1, nhiều webhook | Điền 2 webhook | Trên production nhận ở cả 2 channel (staging: vào channel test) |
| Luồng 1, form chưa cấu hình | Submit form mới | Không gửi gì, form xuất hiện trong tool với field trống |
| Luồng 1, form lỗi validate | Bỏ trống field bắt buộc | Không gửi |
| Lưu form trong tool | Sửa webhook, bấm Cập nhật | `roomID` cũ trong option không bị mất |
| Dữ liệu đặc biệt | Nhập `@everyone`, `**x**`, `<b>`, `&`, emoji, 5000 ký tự | Không ping, không vỡ format, bị cắt đúng giới hạn |
| Luồng 2 | Form có `form_field_name`, không có `form_field_id` | Embed dùng thử |
| Luồng 3 | `POST /wp-json/vietnix/telegram` có/không nonce | Có nonce → 200 + tin Discord; sai nonce → 400; chưa có webhook → 400 |
| Luồng 4 | Publish post / lap-trinh trên production | 1 tin, không trùng snippet #295046 |
| Webhook sai / Discord down | Điền webhook hỏng | Form vẫn submit bình thường, có `error_log` |
| Staging không có webhook test | Xoá `discord_webhook_test` | Không gửi gì |

## 7. Thứ tự triển khai

1. Mục 2: export dữ liệu, chốt mapping, tạo webhook, kiểm tra snippet (mục 5).
2. Test staging theo mục 6.
3. Deploy production → điền webhook ngay (hoặc đặt constant trong `wp-config.php` trước) → submit thử từng luồng.
4. Mục 4: revoke token, dọn option, cập nhật báo cáo.

## 8. Rủi ro

| Rủi ro | Giảm thiểu |
|--------|-----------|
| Mất tin trong khoảng deploy → điền webhook | Đặt constant `VNX_DISCORD_WEBHOOK_*` trước; deploy giờ thấp điểm; `error_log` khi thiếu webhook |
| Form luồng 1 chưa điền webhook = im lặng | Đối chiếu danh sách form có `roomID` (mục 2) với form đã điền webhook |
| Snippet còn gọi `TelegramApi_Center` / `VNX_Telegram_Bot_Token` (đã xoá) → fatal | Kiểm tra mục 5 trước deploy |
| vietnix-plugin vẫn gửi Telegram (companion mode) | Kiểm tra Q6 |
| Gửi trùng (snippet WPCode + plugin) | Kiểm tra mục 5 |
| Submit form chậm | Timeout 5s mỗi webhook |
| Discord rate limit (~30 req/phút/webhook) | Lượng form thấp; HTTP 429 được log |
| Webhook lộ = ai cũng spam được channel | Lưu trong option/wp-config, không commit vào code |
