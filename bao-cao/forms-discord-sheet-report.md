# Báo cáo: Danh sách form trong phạm vi chuyển notify Telegram → Discord

Ngày: 2026-09-24
Môi trường kiểm tra: staging `https://stag.vietnix.dev` (qua MCP vnx-stag + quét HTML trang)
Liên quan: `bao-cao/plan-telegram-to-discord.md`

## 1. Phạm vi quét

- Toàn bộ 89 page, 7 ebook, 8 case-studies, 5 jobs.
- 20 bài mẫu mỗi loại: post, lap-trinh, themes-wordpress.
- Chưa quét hết 4.505 post / 491 lap-trinh: popup chỉ hiện theo category/điều kiện riêng có thể bị sót.
- Popup/template không gắn vào trang nào thì không quét được (template không truy cập public).
- Danh sách đầy đủ nhất cần chạy trên server:
  ```
  wp db query "SELECT post_id FROM vnx_postmeta WHERE meta_key LIKE '_bricks_page_%' AND meta_value LIKE '%form_field_id%'"
  ```

## 2. Luồng notify hiện tại (luồng 1)

Form Bricks có field ẩn `form_field_id` → `bricksSendDiscordMessage_Center` (`extentions/api_send_message_bot.php`) → đọc cấu hình trong **Tools → Sync Discord & Sheets** theo `form_field_id`:

- Có **Discord Webhook** và **Content** ra ít nhất 1 dòng → gửi Discord.
- Có **ID Sheet** + **Page name** + **Data** → ghi 1 dòng Google Sheet.
- Thiếu webhook hoặc Content rỗng → **bỏ qua, không log**.

Cách lấy dữ liệu field (`DataSyncDiscordSheet_Center::getDataRecord`):

- Tìm `<tên>` trong dữ liệu form; không có thì thử `form_field_<tên>`.
- Checkbox / select nhiều giá trị (kể cả mảng lồng như `form_field_author[][]`) → nối bằng ` / `.
- `date` không phải field của form → tự điền ngày gửi (d/m/Y, giờ UTC của server).
- Giá trị rỗng → không hiện dòng đó trong tin Discord.

## 3. Danh sách form

### 3.1 Form nằm trên page

| `form_field_id` | Trang | URL staging |
|---|---|---|
| `dang-ky-dung-thu-hosting-mp` | Dùng thử Hosting (#273655) | /dung-thu-hosting/ |
| `dang-ky-dung-thu-hosting` | SEO Hosting – V2 (#219445) | /seo-hosting-2-2/ |
| `dang-ky-toi-uu-web` | Dịch Vụ Tối Ưu Tốc Độ Website (#428726) | /toi-uu-toc-do-website/ |
| `dang-ky-dai-ly` | Chương trình đại lý – Reseller (#435196) | /reseller/ |
| `lien-he` | Liên hệ – Bricks (#244131) | /lien-he/ |
| `dang-ky-nhan-thong-tin-tu-tac-gia` | Tác giả (#291219) | /author/ |
| `form-cafe-talk` | [Vietnix x Getfly] Cafe talk (#475487) | /cafe-talk-cung-getfly/ |

### 3.2 Form trong popup / template (hiện trên nhiều trang)

| `form_field_id` | Nguồn | Xuất hiện ở |
|---|---|---|
| `dang-ky-dung-thu-hosting-mp` | Popup #283679 "Form Trial Hosting SP Model 1" | Mọi bài post |
| `dang-ky-dung-thu-hosting-mp` | Popup #283976 "Form Trial Hosting SP Model 2" | Mọi bài post |
| `DANG-KY-NHAN-TAI-LIEU` | Template single post | Mọi bài post |
| `dang-nhan-ebook` | Template single ebook | Cả 7 trang ebook |

Không thấy form trên các bài mẫu lap-trinh, themes-wordpress, case-studies, jobs.

### 3.3 Field của từng form

| `form_field_id` | Field (tên thật trên form) |
|---|---|
| `dang-ky-dung-thu-hosting` | `form_field_name`, `form_field_phone`, `form_field_email`, `form_field_package` |
| `dang-ky-dung-thu-hosting-mp` (page) | `form_field_name`, `form_field_phone`, `form_field_email`, `form_field_service`, `form_field_package`, `form_field_read_pop[]` |
| `dang-ky-dung-thu-hosting-mp` (popup #283679) | `name`, `phone`, `email`, `service` |
| `dang-ky-dung-thu-hosting-mp` (popup #283976) | `name`, `phone`, `email`, `service` |
| `lien-he` | `name`, `phone`, `title`, `content` |
| `dang-ky-toi-uu-web` | `name`, `phone`, `email`, `website`, `message` |
| `dang-ky-dai-ly` | `name`, `name-cty`, `phone`, `email`, `website`, `quoc-gia`, `city`, `dist`, `address`, `read-pop[]`, `date` (ẩn) |
| `form-cafe-talk` | `name`, `email`, `phone`, `name-cty`, `chuc-vu`, `cau-hoi` |
| `dang-ky-nhan-thong-tin-tu-tac-gia` | `form_field_author[][]`, `name`, `email` |
| `DANG-KY-NHAN-TAI-LIEU` | `topic[]`, `name`, `email` |
| `dang-nhan-ebook` | `name`, `email`, `read-pop[]` |

## 4. Các điểm khác biệt cần lưu ý

### 4.1 Một `form_field_id` dùng cho 3 form khác field

`dang-ky-dung-thu-hosting-mp` dùng chung 1 cấu hình cho trang /dung-thu-hosting/ (field `form_field_*`, có Gói) và 2 popup trên post (field `name/phone/...`, **không có Gói**).

- Luồng mới: cấu hình `name, phone, ...` chạy được cả 3 (nhờ fallback `form_field_`). Gửi từ popup thì tin không có dòng Gói.
- **Luồng cũ vẫn chạy song song**: WPCode snippet #406680 + `functions/requires/vietnix-sync-discord.php` gửi vào webhook cứng `…/1385187864408494252/…` và Sheet `Data-Trial-Hosting`. Hai chỗ này chỉ đọc `form_field_*` → submit từ popup ra **dữ liệu trống**; submit từ page thì channel cũ nhận **2 tin trùng**.

### 4.2 Checkbox

| Form | Field | Ghi chú |
|---|---|---|
| `dang-ky-nhan-thong-tin-tu-tac-gia` | `form_field_author[][]` | Mảng lồng 2 cấp, đã sửa để nối được giá trị |
| `DANG-KY-NHAN-TAI-LIEU` | `topic[]` | Ghi `topic` hoặc `topic-checkbox` đều được |
| `dang-ky-dai-ly`, `dang-nhan-ebook`, `-mp` | `read-pop[]` / `form_field_read_pop[]` | Ô đồng ý điều khoản, không cần gửi |

### 4.3 Tên field đặc biệt

- Có gạch ngang: `name-cty`, `quoc-gia`, `chuc-vu`, `cau-hoi` (`dang-ky-dai-ly`, `form-cafe-talk`) → ghi đúng tên trong Content/Data.
- `dang-ky-dai-ly` có field ẩn `date` → dùng giá trị field đó, không tự điền ngày gửi.
- `lien-he` dùng `title`, `content` làm tên field.

### 4.4 Ngoài luồng mới

- Không còn form nào theo luồng 2 (form dùng thử kiểu cũ, không có `form_field_id`).
- Không có form "Gọi lại cho tôi" (luồng 3) trên site Bricks; frontend của nó chỉ nằm trong theme `vietnix-wp-theme` (không active).
- Không gửi notify: form chuyển tên miền (/chuyen-ten-mien-ve-vietnix/), form đánh giá bài viết (popup #365714), ô chọn tab (/shared-hosting/, /hosting-linux/).
- Case `dang-ky-black-friday` trong snippet #406680 không còn form nào dùng.

## 5. Cấu hình gợi ý trong Tools → Sync Discord & Sheets

| `form_field_id` | Content |
|---|---|
| `dang-ky-dung-thu-hosting` | `Họ tên: name, SĐT: phone, Email: email, Gói: package, Referrer: referrer` |
| `dang-ky-dung-thu-hosting-mp` | `Họ tên: name, SĐT: phone, Email: email, Dịch vụ: service, Gói: package, Referrer: referrer` |
| `lien-he` | `Họ tên: name, SĐT: phone, Tiêu đề: title, Nội dung: content, Referrer: referrer` |
| `dang-ky-toi-uu-web` | `Họ tên: name, SĐT: phone, Email: email, Website: website, Lời nhắn: message` |
| `dang-ky-dai-ly` | `Họ tên: name, Công ty: name-cty, SĐT: phone, Email: email, Website: website, Quốc gia: quoc-gia, Tỉnh: city, Quận: dist, Địa chỉ: address` |
| `form-cafe-talk` | `Họ tên: name, Email: email, SĐT: phone, Công ty: name-cty, Chức vụ: chuc-vu, Câu hỏi: cau-hoi` |
| `dang-ky-nhan-thong-tin-tu-tac-gia` | `Họ tên: name, Email: email, Tác giả: author` |
| `DANG-KY-NHAN-TAI-LIEU` | `Họ tên: name, Email: email, Chủ đề: topic` |
| `dang-nhan-ebook` | `Họ tên: name, Email: email, Referrer: referrer` |

Google Sheet:

- **ID Sheet**: chỉ nhập ID (đoạn giữa `/d/` và `/edit` trong link). Dán cả link cũng được, code tự tách ID.
- **Page name**: đúng tên tab trong Sheet (phân biệt hoa/thường).
- **Data**: cùng tên field như Content, thêm `date`, `formId`, `referrer` nếu cần.
- Sheet phải share quyền **Editor** cho `vietnix-gbot-tracking@vietnix-gbot.iam.gserviceaccount.com`.

## 6. Code đã sửa (local, cần deploy lên staging)

| File | Thay đổi |
|---|---|
| `extentions/helper/DataSyncDiscordSheet_Center.php` | Fallback `form_field_<tên>`; nối giá trị checkbox/select (kể cả mảng lồng); `date` tự điền cho Discord |
| `extentions/helper/gapi.php` | Tự tách ID từ link Sheet; encode tên sheet; ghi `error_log` khi Google trả lỗi |
| `extentions/helper/discord_notify.php` | Không escape URL (trước đây link Referrer bị hỏng thành `dung/-thu/-hosting`) |

Staging đã có bản sửa của `gapi.php`, `discord_notify.php` và fallback `form_field_`; còn thiếu bản sửa checkbox lồng và `date` cho Discord.

## 7. Việc còn lại

1. Deploy `extentions/helper/DataSyncDiscordSheet_Center.php` mới nhất lên staging.
2. Kiểm tra trong tool: đủ 9 `form_field_id` ở mục 3 đã được ghi nhận, đã có webhook + Content. Form chỉ xuất hiện trong tool sau lần submit đầu tiên.
3. Tắt WPCode snippet #406680 và bỏ `vnx_FormSendMessageDiscord_Center` (`functions/requires/vietnix-sync-discord.php`) sau khi luồng mới chạy ổn: tránh tin trùng, tin trống từ popup, và webhook đang nằm cứng trong code.
4. Snippet #406680 không `return $validation_errors` → xoá lỗi validate của form chuyển tên miền; lý do thêm để tắt.
5. Submit thử từng form sau khi cấu hình, đối chiếu Discord + Sheet.
