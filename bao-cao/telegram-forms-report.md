# Báo cáo: Form / Tool gửi về Telegram — plugin vietnix-center

Ngày quét: 2026-09-22
Phạm vi: toàn bộ `vietnix-center` (bỏ qua `build/`, `node_modules/`)

## 1. Tóm tắt

Có **4 luồng** gửi tin về Telegram. Cả 4 đều nằm trong `extentions/`, tức chỉ chạy khi extension đó được bật ở trang Setting,
và **không chạy khi vietnix-plugin đang active song song** (companion mode, `vnx_active.php:35`).

| # | Luồng | Kích hoạt bởi | File | Nhóm Telegram (chat_id) | Bot |
|---|-------|---------------|------|-------------------------|-----|
| 1 | Form Bricks có `form_field_id` (cấu hình động) | `bricks/form/validate` | `extentions/api_send_message_bot.php:39-57` | Theo cấu hình từng form (field `roomID`) | Bot `5574621672:…` (hardcode) |
| 2 | Form Bricks kiểu cũ "Đăng ký dùng thử miễn phí" | `bricks/form/validate` | `extentions/api_send_message_bot.php:67-103` | `-515577488` | Option `telegram_bot_token`, fallback bot `368982987:…` |
| 3 | REST "Gọi lại cho tôi" | `POST /wp-json/vietnix/telegram` | `extentions/api.php:4-45` | `-548848102` | Option `telegram_bot_token`, fallback bot `368982987:…` |
| 4 | Thông báo bài viết mới publish (không phải form) | `transition_post_status` — chỉ khi `home_url = https://vietnix.vn` | `extentions/vnx_telegram_post_message.php` | `-1002201598215` | Bot `5574621672:…` (hardcode) |

Extension tương ứng (`register.php:153-164`):
- `api` — "REST API" → luồng 3
- `api_send_message_bot` — "Telegram Form Bot" → luồng 1, 2
- `vnx_telegram_post_message` — "Telegram Post Notify" → luồng 4

## 2. Chi tiết từng luồng

### 2.1 Form Bricks cấu hình động (Tool "Telegram & Sheets")

- Tool quản lý: `vietnix-sync-telegram-sheet` (Tools → **Telegram & Sheets**), view `views/tools/vietnix_sync_telegram_sheet.php`, handler lưu `functions/requires/vietnix-sync-telegram-sheet.php`.
- Dữ liệu cấu hình: option `vnx_sync_telegram_sheet_setting` (JSON, key = giá trị `form_field_id`), mỗi form có:
  `referrer`, `roomID` (chat_id Telegram, nhiều ID cách nhau dấu phẩy), `title`, `content` (dạng `Nhãn: field_name, ...`), `sheetID`, `pageName`, `dataSheet`.
- Cơ chế (`extentions/helper/DataSyncTelegramSheet_Center.php`):
  - Form Bricks có hidden field `form_id` → khi submit lần đầu, form tự được **ghi nhận** vào option với cấu hình rỗng (chưa gửi gì).
  - Form chỉ thực sự gửi Telegram khi admin điền **`roomID`** và **`content`/`title`** cho form đó trong tool.
  - Cùng lúc có thể đẩy Google Sheet nếu điền `sheetID` + `pageName`.
- Chỉ gửi khi form qua được validate của Bricks (`vnx_bricks_form_passes_validation_Center`).

- Tên option: bản center cũ lưu ở `vnx_sync_telegram_sheet_setting_center`, được `functions/requires/vnx_shared_data_migration.php:28` copy sang tên chung `vnx_sync_telegram_sheet_setting` (chỉ khi option chung chưa có) → dùng chung cấu hình với vietnix-plugin.

> ⚠️ **Danh sách form cụ thể đang gửi Telegram nằm trong DB** (option `vnx_sync_telegram_sheet_setting`), không có trong code.
> Lúc quét: DB local (docker) không chạy, MCP staging không đọc được option. Để lấy danh sách chính xác:
> vào **Tools → Telegram & Sheets**, form nào có ô **Room ID** khác rỗng là form đang gửi Telegram; hoặc chạy
> `wp option get vnx_sync_telegram_sheet_setting --format=json`.

Các template Bricks có form trên staging (ứng viên dùng luồng này — cần đối chiếu với option ở trên):

| ID | Template |
|----|----------|
| 244142 | Liên hệ - Form liên hệ |
| 240519 | Reseller - Form đăng ký |
| 219844 | SEO Hosting - Form Đăng ký |
| 226578 | Hosting Giá Rẻ - Form Dùng Thử |
| 273755 | HDT-V4 - Form đăng ký dùng thử |
| 340346 | MaxSpeed Hosting – Form Đăng ký |
| 428688 | LDP - PageSpeed - Form tư vấn lộ trình |
| 264654 | Tối ưu Pagespeed – FORM - TƯ VẤN CHO BẠN |
| 439348 | LDP - Black Friday Customer - Form |
| 475507 | LDP - Cafe Talk - Form Đăng Ký |
| 282253 / 282409 / 283338 | Form V4 – Form Trial Hosting Model 1/2/3 |
| 283679 / 283976 | Popup - V4 - Form Trial Hosting SP Model 1/2 |
| 282247 / 283182 / 283491 / 283522 | Single Post - V4 - Form Trial Hosting Model 1–4 |
| 455031 | SinglePost - 2026 - Form dùng thử hosting |
| 284031 / 284250 | Form Nhận Tài Liệu (V4 / SinglePost) |
| 293568 / 293262 | Ebook V4 - Form / Form Nhận Ebook |
| 291232 / 292915 | AUTHOR - V4 - Form (+ bản copy) |
| 286979 | Blog-V4-Form |
| 292036 | Share document - V4 - Form Trial Hosting |

(Nguồn: `list_builder_templates` trên stag.vietnix.dev, search "form". Form đặt trực tiếp trong page, không qua template, không có trong danh sách này.)

Form đã xác minh cụ thể:

| Trang | Form | `form_field_id` | Field | Kết luận |
|-------|------|-----------------|-------|----------|
| https://vietnix.vn/toi-uu-toc-do-website/ | TƯ VẤN LỘ TRÌNH TỐI ƯU WEBSITE MIỄN PHÍ (`brxe-svorld`) | `dang-ky-toi-uu-web` | `name`, `phone`, `email`, `website`, `message` | Đi luồng 2.1. Gửi Telegram **nếu** form này có `roomID` trong option; không có handler nào khác (Discord/snippet) xử lý form này |

### 2.2 Form Bricks kiểu cũ — "🆕 ĐĂNG KÝ DÙNG THỬ MIỄN PHÍ"

- Điều kiện: form **không có** `form_field_id` nhưng có ít nhất 1 trong `form_field_name` / `form_field_email` / `form_field_phone`.
- Nội dung: Tên, Email, SĐT, Gói (`form_field_package`).
- Gửi 1 nhóm cố định `-515577488`. Có đoạn random chọn người phụ trách (`$user_list`, 10 người) và tạo `$mention` nhưng **biến `$mention` không được đưa vào tin nhắn** (code thừa).

### 2.3 REST "☎ GỌI LẠI CHO TÔI"

- Endpoint `POST /wp-json/vietnix/telegram`, cần `phone` + nonce `form-call-me-now`.
- Gửi nhóm `-548848102`, parse_mode markdown (đã escape).
- Trong vietnix-center **không có** frontend nào gọi endpoint này (chỉ có bản gốc ở vietnix-plugin) — nơi gọi nằm ở theme/code khác.

### 2.4 Thông báo bài viết mới (không phải form)

- Post type `post` và `lap-trinh` chuyển sang `publish` → gửi link, tác giả, SEO, Writer, Category vào nhóm `-1002201598215`.
- Chỉ đăng ký hook khi `get_home_url() === "https://vietnix.vn"` (production).

### 2.5 Đối chiếu runtime trên staging (MCP vnx-stag)

- vietnix-center **active**, vietnix-plugin **inactive** → không ở companion mode, extension của center đang chạy.
- Callback đăng ký trên `bricks/form/validate`:

| Priority | Callback | Nguồn | Gửi đi đâu |
|----------|----------|-------|-----------|
| -10 | `domain_transfer_form_validate_Center` | vietnix-center `extentions/api.php` | — (validate) |
| 9 | Closure lưu lỗi validate | vietnix-center `vietnix-center.php:68` | — |
| 10 | `vnx_form_send_bot_discord` | **WPCode snippet #406680** "Notify To Website (All)" | Discord |
| PHP_INT_MAX | `vnx_FormSendMessageDiscord_Center` | vietnix-center | Discord + Sheet |
| PHP_INT_MAX | `bricksSendTelegramMessage_Center` | vietnix-center | **Telegram** + Sheet |

- `bricks/form/custom_action`: chỉ `domain_transfer_form_action_Center` (set cookie giỏ hàng, không gửi tin).
- debug.log staging: không có dòng `Telegram send failed` nào.
- Không có WPCode snippet nào gửi Telegram (đã liệt kê 33 snippet PHP active).

## 3. Không gửi Telegram (dễ nhầm)

| Thành phần | Thực tế gửi tới |
|-----------|-----------------|
| `crontab/reportPost.php` (báo cáo bài viết ngày/tuần) | **Discord** webhook — message lỗi ghi "ID nhóm Telegram" ở dòng 130 là sai chữ |
| `tools/vietnix-report-posts.php` | Discord |
| `tools/vietnix-cache-scheduler.php` | Discord |
| `functions/requires/vietnix-sync-discord.php` (form `dang-ky-dung-thu-hosting-mp`) | Discord + Google Sheet `Data-Trial-Hosting` |
| WPCode snippet #406680 "Notify To Website (All)" (ngoài plugin) — form `dang-ky-dung-thu-hosting-mp`, `dang-ky-black-friday` | Discord |
| WPCode snippet #295046 "Notify admin post publish" (ngoài plugin) | Discord |

## 4. Vấn đề phát hiện khi quét

1. **Token bot hardcode trong code**: `5574621672:…` ở `extentions/api_send_message_bot.php:26`, `extentions/vnx_telegram_post_message.php:6`, `vietnix-center.php:230` (`VNX_Telegram_Bot_Token` — define nhưng không file nào dùng); `368982987:…` ở `extentions/api.php:25` và `extentions/api_send_message_bot.php:98`. Ngoài ra webhook Discord hardcode ở `vietnix-sync-discord.php:23`. Nên chuyển vào option/`wp-config.php`.
2. **`vnx_telegram_post_message.php` vẫn dùng `file_get_contents` GET** (token + nội dung nằm trong URL, lỗi sẽ in URL ra log), không dùng `TelegramApi_Center` (POST) như các luồng khác. Tên tác giả/category chưa escape HTML trong khi `parse_mode=html` → bài có ký tự `&`/`<` sẽ bị Telegram trả 400, mất thông báo.
3. **`gapi.php:105` có hàm `vnx_api_send_Center()` bản cũ** (GET) trùng tên method với class `TelegramApi_Center` — không lỗi (một là function, một là method) nhưng gây nhầm; compat `vnx_api_send()` đang trỏ vào bản cũ này.
4. Luồng 2.2: `$user_list` / `$mention` tính ra nhưng không dùng.
5. Luồng 2.1 gửi tuần tự từng `roomID`, mỗi request timeout 10s, chạy đồng bộ trong lúc submit form → nhiều room + Telegram chậm sẽ làm submit form chậm.
6. **Luồng 2.1, 2.2, 2.3 không chặn theo môi trường** (chỉ luồng 2.4 có check `https://vietnix.vn`). Submit form test trên staging/local vẫn bắn tin vào nhóm Telegram thật nếu option có `roomID` (luồng 2.2, 2.3 thì luôn bắn vì group ID hardcode).
7. **Snippet WPCode #406680 (ngoài plugin) làm mất lỗi validate**: hook vào filter `bricks/form/validate` nhưng không `return $validation_errors` → trả `null`, xoá lỗi của `domain_transfer_form_validate_Center` (priority -10) → form chuyển tên miền sai mã EPP vẫn qua. Ngoài ra với form không nằm trong `switch`, snippet vẫn gọi Discord với `$title`/`$message` chưa khai báo.
8. **Gửi Discord trùng** cho form `dang-ky-dung-thu-hosting-mp`: cả snippet #406680 và `vnx_FormSendMessageDiscord_Center` cùng gửi vào cùng webhook.
9. `README.md:59` ghi "mọi gọi API ngoài đi qua WordPress HTTP API" — chưa đúng: `vnx_telegram_post_message.php` và `gapi.php:105` dùng `file_get_contents`, `gapi.php` dùng `curl` trực tiếp.

## 5. Chưa kiểm được (cần làm tiếp)

- Giá trị thực của option `vnx_sync_telegram_sheet_setting` trên production/staging (form nào có `roomID`, gửi vào group nào). MCP chỉ có quyền đọc code/log/builder, không đọc được option.
- Extension nào đang bật trên **production** (option `vnx_plugin_setting_extentions`) và production có chạy song song vietnix-plugin không — nếu có thì Telegram do vietnix-plugin gửi chứ không phải center. MCP chỉ kết nối staging.
- Form đặt trực tiếp trong page (không qua template) và hidden field `form_field_id` của từng template: MCP không trả field của form Bricks, chỉ xác minh được bằng HTML trang production như form ở mục 2.1.
- Nơi gọi `POST /wp-json/vietnix/telegram` ("Gọi lại cho tôi") — không có trong vietnix-center, cần tìm ở theme/Bricks/snippet.
