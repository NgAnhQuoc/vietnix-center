# Danh sách cấu hình Discord trên vietnix.vn

Ngày: 2026-09-24
Môi trường: production `https://vietnix.vn`
Liên quan: `bao-cao/forms-discord-sheet-report.md`, `bao-cao/plan-telegram-to-discord.md`

## 0. Lưu ý trước khi cấu hình

- Tại thời điểm kiểm tra, vietnix.vn **vẫn chạy bản vietnix-center cũ (gửi Telegram)**: tab Options chỉ có Cookie Domain, chưa có mục Discord Webhook. Phải upload bản mới trước.
- Không cần xóa cache sau khi upload: OPcache có `validate_timestamps` bật; submit form đi qua AJAX nên LiteSpeed không cache; cấu hình lưu qua `update_option` nên Redis tự cập nhật.
- Sau khi upload, form **không gửi tin đi đâu cả** cho tới khi điền webhook Discord → chuẩn bị sẵn webhook, điền ngay sau khi cài.
- Form thiếu Discord Webhook hoặc Content rỗng → bị bỏ qua, **không ghi log**.

## 1. Form cấu hình trong Tools → Sync Discord & Sheets

Mỗi dòng ứng với 1 `form_field_id`. Điền **Discord Webhook** và **Content** (dạng `Nhãn: field, Nhãn: field`, ví dụ `Tên: name, SĐT: phone, Email: email`).

Extension cần bật: **Discord Form Notify** (`api_send_message_bot`).

| # | `form_field_id` | Nằm ở | Link Discord |
|---|---|---|---|
| 1 | `lien-he` | /lien-he/ |  |
| 2 | `dang-ky-dung-thu-hosting-mp` | /dung-thu-hosting/ + popup #283679, #283976 trên mọi bài post |  |
| 3 | `dang-ky-dung-thu-hosting` | /seo-hosting-2-2/ |  |
| 4 | `dang-ky-toi-uu-web` | /toi-uu-toc-do-website/ |  |
| 5 | `dang-ky-dai-ly` | /reseller/ |  |
| 6 | `dang-ky-affiliate` 🆕 | /affiliate/ |  |
| 7 | `form-cafe-talk` | /cafe-talk-cung-getfly/ |  |
| 8 | `dang-ky-nhan-thong-tin-tu-tac-gia` | /author/ |  |
| 9 | `DANG-KY-NHAN-TAI-LIEU` | Template single post (mọi bài post) |  |
| 10 | `dang-nhan-ebook` | Các trang /ebook/... |  |

Ghi chú:

- **#2** dùng chung 1 ID cho 3 form có tên field khác nhau (`form_field_name` ở trang /dung-thu-hosting/, `name` ở popup). Viết Content theo tên ngắn (`name`, `phone`...) — code tự thử thêm tiền tố `form_field_`. Tin gửi từ popup sẽ không có dòng Gói.
- **#6** là form mới (template sửa ngày 2026-09-24). Plugin chỉ ghi nhận form vào Tools ở lần submit đầu tiên → nếu chưa thấy dòng `dang-ky-affiliate` thì submit thử 1 lần rồi cấu hình.
- `date` không phải field người dùng nhập: tự điền ngày gửi (d/m/Y).
- Checkbox / select nhiều giá trị được nối bằng ` / `. Giá trị rỗng không hiện trong tin Discord.

## 2. Webhook cấu hình trong Settings → Options → Discord Webhook

3 luồng này trước đây gửi Telegram với chat_id viết cứng trong code, không cấu hình theo form.

| Ô | Key option | Extension cần bật | Kích hoạt khi | Telegram cũ | Cần điền? | Link Discord |
|---|---|---|---|---|---|---|
| Form đăng ký dùng thử | `discord_webhook_trial` | Discord Form Notify | Form Bricks **không có** `form_field_id` nhưng có `form_field_name` / `form_field_email` / `form_field_phone` | `-515577488` | Không bắt buộc — không thấy form nào còn dùng |  |
| Form liên hệ lại cho tôi | `discord_webhook_callme` | REST API (`api`) | `POST /wp-json/vietnix/telegram` | `-548848102` | Không bắt buộc — không thấy trang nào gọi |  |
| Thông báo bài viết mới | `discord_webhook_post` | Discord Post Notify | Bài viết chuyển sang Publish (chỉ chạy khi `home_url` = `https://vietnix.vn`) | `-1002201598215` | **Có** — đang dùng hằng ngày |  |

Nếu ô trống, code đọc tiếp constant trong `wp-config.php` (`VNX_DISCORD_WEBHOOK_TRIAL`, `VNX_DISCORD_WEBHOOK_CALLME`, `VNX_DISCORD_WEBHOOK_POST`); không có thì không gửi.

Nếu muốn chắc chắn luồng cũ không còn ai dùng: điền cùng 1 webhook cho cả 3 ô, thấy tin "ĐĂNG KÝ DÙNG THỬ MIỄN PHÍ" hoặc "Gọi lại" về kênh là vẫn còn chỗ dùng.

## 3. Form có trên site nhưng không gửi notify (không cần cấu hình)

| Trang | Form |
|---|---|
| /hosting-linux/, /shared-hosting/ | Chọn tab (`select-tab`) |
| /chuyen-ten-mien-ve-vietnix/ | Chuyển tên miền (`domain`, `authenzied`, `file`) |
| /tuyen-dung/ và các trang job | Form ứng tuyển |

## 4. Checklist sau khi upload bản mới

1. Settings → Options: điền **Thông báo bài viết mới** (và 2 ô còn lại nếu muốn) → Cập nhật.
2. Settings → Extensions: kiểm tra **Discord Form Notify**, **Discord Post Notify**, **REST API** đang bật.
3. Tools → Sync Discord & Sheets: điền Discord Webhook + Content cho 10 form ở mục 1.
4. Submit thử `/lien-he/` và 1 form khác → kiểm tra tin về Discord và dòng mới trong Google Sheet (nếu có cấu hình Sheet).

## 5. Phạm vi quét

- Quét HTML thật trên vietnix.vn: 91 page, toàn bộ ebook, case-studies, jobs, 15 bài mới nhất của post / lap-trinh / themes-wordpress (156 URL).
- Chưa quét hết 4.500+ bài post: popup chỉ hiện theo category riêng có thể bị sót.
- MCP không đọc được option `vnx_sync_telegram_sheet_setting`, nên chưa đối chiếu được form nào đang có Room ID Telegram. Danh sách chính xác nhất:
  ```
  wp option get vnx_sync_telegram_sheet_setting --format=json
  ```
