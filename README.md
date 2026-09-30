# Vietnix Center

**Contributors:** Vietnix
**Plugin Name:** Vietnix Center
**Stable tag:** 0.1.0
**License:** Nội bộ (Proprietary) – chỉ dùng cho vietnix.vn, không phát hành trên WordPress.org

## Description

Plugin nội bộ cho website **vietnix.vn**, gom các công cụ vận hành, extension chạy ngầm, widget Bricks/Gutenberg và REST API vào một chỗ.

Mỗi tool/extension bật/tắt độc lập ở trang **Settings**; tool nào tắt sẽ không được load và không hiện trong menu.

### Tools (menu Vietnix Center → Tools)

| Nhóm | Tool | Mô tả |
|---|---|---|
| Content | Import Docs | Nhập nội dung từ Google Docs thành bài viết WordPress |
| Content | Filter Posts | Lọc bài viết theo từ khoá, chuyên mục, trạng thái và xuất CSV |
| Content | Report Posts | Gửi báo cáo bài viết định kỳ qua webhook Discord |
| Content | Sync Authors | Gán hàng loạt tác giả cho bài viết theo chuyên mục |
| Content | AI Search | Tạo và cập nhật embeddings phục vụ tìm kiếm bài viết bằng AI |
| Content | Internal Links | Quét bài viết có link đến LDP và xuất ra Google Sheet |
| SEO & Sitemap | Sitemap Settings | Chọn loại nội dung và thiết lập priority cho sitemap |
| SEO & Sitemap | Sitemap Export | Xuất danh sách URL trong sitemap ra file |
| Domain & Pricing | Domain Checker | Kiểm tra tình trạng và thông tin WHOIS của domain |
| Domain & Pricing | Price Sources | Danh sách URL CSV cấp dữ liệu cho API bảng giá và khuyến mãi |
| Integrations | Portal API | Kết nối API portal Vietnix (token, endpoint) |
| Integrations | Landing Page API | Endpoint `/get-ldp` trả nội dung và bảng giá của landing page |
| Integrations | UTM Tracker | Ghi nhận và lưu tham số UTM vào cookie |
| Integrations | Discord & Sheets | Đồng bộ dữ liệu form sang Discord và Google Sheet |
| Integrations | Logger Search | Ghi log từ khoá người dùng tìm kiếm trên site, đọc lại qua API `/vnx_api/v1/search-keywords` (không có trang cấu hình riêng) |
| System | Cache Scheduler | Hẹn giờ tự động xoá cache LiteSpeed theo URL cụ thể |
| System | Media Tool | Đổi slug file upload thành chuỗi ngẫu nhiên 32 ký tự |
| System | Banner | Quản lý banner bằng custom post type, không có trang cấu hình riêng |

### Extensions (chạy ngầm, bật/tắt ở Settings)

| Extension | Mô tả |
|---|---|
| REST API | Bật các endpoint REST API của plugin |
| Discord Form Notify | Gửi nội dung form submit về Discord webhook |
| Discord Post Notify | Thông báo về Discord khi có bài viết mới |
| Post SEO & Writer | Cần ACF field `seo_author`, `writer` (user) cho post |
| Bricks Author Condition | Thêm điều kiện tác giả cho Bricks ở trang archive |
| Dev SEO & Writer | Cần ACF field `seo_author_dev`, `writer_dev` (user) cho mục Lập Trình |
| Custom Page Sitemap | Cần ACF field `vnx_check_product_page` để tuỳ biến sitemap của page |
| Portal Order API | Kết nối portal Vietnix để đặt sản phẩm tuỳ chỉnh |
| New Sitemap | Sitemap tuỳ chỉnh dành riêng cho Vietnix |
| Hidden Login | Ẩn form đăng nhập mặc định của WordPress |

### Widget & Block

- **Bricks Builder** (`widgets/bricks`): star rating, view counter, breadcrumbs, form/kết quả tìm kiếm domain, bảng giá dịch vụ/domain, coupon, so sánh dịch vụ, before/after image, tìm kiếm bài viết bằng AI... Danh sách đầy đủ tại `register.php → BricksRegisterWidget()`.
- **Gutenberg** (`widgets/gutenberg`): Button, Note, Icon Note, Featured Snippet, Shortcode, Blockquote, Compare Table, View More List, Coupon Table.

## Installation

Plugin **không dùng Composer** — không cần `composer install`, không có thư mục `vendor` nào được nạp. Mọi gọi API ngoài (Google, OpenAI, Discord...) đi qua WordPress HTTP API.

### 1. Cài plugin

1. Chép thư mục `vietnix-center` vào `/wp-content/plugins/` (hoặc upload file zip tạo bằng `yarn zip`).
2. Kiểm tra đã có thư mục `build/` (CSS/JS đã build). `build/` nằm trong `.gitignore` — nếu lấy code từ git thì phải build trước (xem bước 3).
3. Kích hoạt plugin qua menu **Plugins** trong WP Admin.
4. Vào **Vietnix Center → Settings** bật tool/extension/widget cần dùng, rồi vào **Vietnix Center → Tools** cấu hình từng tool.

### 2. Cấu hình riêng theo tool

| Tool | Cần chuẩn bị |
|---|---|
| Import Docs | File service account Google tại `secrets/credentials.json` (có `client_email`, `private_key`), share Docs/folder Drive cho email service account. File này không commit, chép tay lên server. |
| AI Search | API key OpenAI, nhập trong trang tool. |
| Portal API / Landing Page API / Price Sources | API key + danh sách IP được phép (Allow IP), nhập trong trang tool. |
| Report Posts, Cache Scheduler | Webhook URL Discord (nếu muốn nhận thông báo). |
| Cache Scheduler | Plugin **LiteSpeed Cache** đã cài và đang bật. |
| Post SEO & Writer, Dev SEO & Writer, Custom Page Sitemap | Plugin **ACF** + các custom field tương ứng (xem FAQ). |
| Widget Bricks | Theme dùng **Bricks Builder**. |

### 3. Build assets (chỉ khi sửa SCSS/JS hoặc lấy code từ git)

Cần Node.js (đã test với v20) + Yarn:

```bash
yarn install
yarn build   # build production ra thư mục build/
yarn dev     # watch mode khi đang phát triển (Laravel Mix)
yarn zip     # build + đóng gói plugin thành file zip trong dist/
```

### 4. Crontab hệ thống

Chỉ đặt cron cho tool đang dùng, và tool đó phải được bật ở **Settings** — tool tắt thì job chỉ ghi lỗi vào `error_log` rồi thoát.

| File | Tần suất | Khi nào cần | Lý do |
|---|---|---|---|
| `crontab/reportPost.php` | Mỗi phút | Dùng **Report Posts** | So giờ hiện tại (`H:i`) với giờ cài trong tool, khớp đúng phút mới gửi báo cáo ngày/tuần |
| `crontab/updateSearchAI.php` | Mỗi phút | Dùng **AI Search** | So với `syncTime` trong tool, khớp đúng phút mới cập nhật embeddings |
| `crontab/exportSitemap.php` | 30 phút | Dùng **Sitemap Export** | Mỗi lần chạy là xuất sitemap lên Google Sheet luôn, không so giờ |
| `crontab/cacheScheduler.php` | Mỗi phút | Nên có nếu dùng **Cache Scheduler** | Không có thì WP-Cron dự phòng vẫn chạy mỗi 5 phút, nhưng chỉ khi có người vào site nên có thể trễ |

> `reportPost` và `updateSearchAI` phải chạy **mỗi phút** — đặt thưa hơn thì dễ trượt đúng phút đã cài và job không bao giờ chạy.

Lệnh mẫu (cPanel/SSH, thay `/path/to` bằng đường dẫn thật):

```bash
* * * * *    php /path/to/wp-content/plugins/vietnix-center/crontab/reportPost.php >/dev/null 2>&1
* * * * *    php /path/to/wp-content/plugins/vietnix-center/crontab/updateSearchAI.php >/dev/null 2>&1
*/30 * * * * php /path/to/wp-content/plugins/vietnix-center/crontab/exportSitemap.php >/dev/null 2>&1
* * * * *    php /path/to/wp-content/plugins/vietnix-center/crontab/cacheScheduler.php >/dev/null 2>&1
```

Không cần cron hệ thống cho cache sitemap — tự chạy 30 phút/lần qua WP-Cron (`vnx_sitemap_rebuild_cache`).

## Frequently Asked Questions

**Plugin có chạy được nếu thiếu ACF không?**
Không — các extension "Post SEO & Writer", "Dev SEO & Writer", "Custom Page Sitemap" cần plugin ACF vì phụ thuộc custom field (`seo_author`, `writer`, `seo_author_dev`, `writer_dev`, `vnx_check_product_page`).

**Widget Bricks có hoạt động trên theme khác không?**
Bộ widget trong `widgets/bricks` được viết cho Bricks Builder, cần theme/builder này để render.

**API key của Portal API lưu ở đâu, có an toàn không?**
Key được mã hoá AES-256-CBC rồi lưu vào `wp_options`, không bao giờ hiển thị lại dạng plaintext trên UI. Chi tiết cơ chế và các rủi ro đã rà soát: [`docs/plan-move-API-key.md`](docs/plan-move-API-key.md).

**Import Docs kết nối Google như thế nào?**
Qua `app/GoogleAuth.php`: tự ký JWT service account bằng `openssl` và gọi Google Docs/Drive API qua WordPress HTTP API (không cần Composer/Guzzle), token lưu ở option `gg_auth_token` và tự làm mới khi hết hạn (401).

**Sao trang Tools/Settings không hiện notice của plugin khác?**
Trên trang của center, các admin notice không do code trong thư mục plugin này đăng ký sẽ bị gỡ (`vnx_hide_foreign_notices()` trong `vietnix-center.php`), tránh notice lạ (vd Action Scheduler) bị kéo vào khung UI của Vietnix. Trang admin khác không bị ảnh hưởng.

## REST API

- Endpoint đăng ký qua `register_rest_route()`, rải trong thư mục `api/` và một số file `tools/`, dùng namespace `vnx_api/v1` và `vnx_api/mkt` (hằng số khai báo tại `vietnix-center.php`).
- Xác thực bằng API key (header `api-key`) kết hợp IP whitelist, xử lý tại `api/register-api.php`.
- `GET /wp-json/vnx_api/v1/search-keywords` — đọc log từ khoá tìm kiếm (phân trang, lọc theo từ khoá/khoảng thời gian, sắp xếp). Tài liệu: [`docs/search-keywords-api.md`](docs/search-keywords-api.md).

## Cron Jobs

Đăng ký trong `crontab/`:

- `exportSitemap.php` — xuất sitemap định kỳ.
- `reportPost.php` — gửi báo cáo bài viết qua Discord webhook.
- `updateSearchAI.php` — cập nhật embeddings cho AI Search.
- `cacheScheduler.php` — chạy các lịch hẹn xoá cache LiteSpeed đến hạn (tool **Cache Scheduler**, xem hướng dẫn bên dưới).

Mọi job đều qua `vnx-cron-guard.php`: crontab hệ thống gọi thẳng file PHP nên không đi qua `active_plugins`, guard sẽ bỏ qua lượt chạy (ghi `error_log`) nếu WordPress chưa load hoặc plugin đang không active. Nghĩa là tắt plugin trong wp-admin là dừng được cron, không cần gỡ crontab.

Cần đặt cron nào, tần suất bao nhiêu: xem [Installation → 4. Crontab hệ thống](#4-crontab-hệ-thống).

## Cache Scheduler — hướng dẫn dùng

Tool hẹn giờ tự động xoá cache **LiteSpeed Cache** theo URL cụ thể. Yêu cầu plugin **LiteSpeed Cache** đã cài và đang bật — nếu không, trang tool hiện banner cảnh báo và mọi lịch hẹn tạm dừng chạy (dữ liệu đã lưu không mất, tự chạy lại khi bật LiteSpeed lên).

**Bật tool:** Vietnix Center → Settings → bật **Cache Scheduler** trong nhóm System, rồi vào Vietnix Center → Tools → tab **Cache Scheduler**.

**Thêm lịch hẹn:**
1. Bấm **Thêm lịch mới**.
2. Nhập danh sách URL cần xoá cache, mỗi dòng 1 link; chấp nhận đường dẫn tương đối như `/blog/bai-viet`.
3. Chọn loại lịch:
   - **Chạy 1 lần** — chọn ngày giờ cụ thể, sau khi chạy lịch tự tắt.
   - **Hằng ngày** — chọn giờ chạy mỗi ngày.
   - **Hằng tuần** — chọn giờ + các ngày trong tuần.
4. Tick **Bật lịch này ngay sau khi lưu** (chỉ chọn được khi LiteSpeed đang active), bấm **Lưu lịch**.

**Quản lý:** bảng danh sách cho phép bật/tắt từng lịch bằng toggle, sửa, xoá, hoặc **Chạy ngay** để test purge thủ công không cần đợi tới giờ. Công tắc **Tự động chạy** ở đầu trang là "cầu dao tổng" - tắt sẽ tạm dừng toàn bộ cron mà không đổi trạng thái bật/tắt riêng của từng lịch, bật lại là chạy tiếp bình thường (mặc định tắt, phải chủ động bật).

**Thông báo Discord:** nhập Webhook URL (Server Settings → Integrations → Webhooks trong Discord), bấm **Gửi thử** để kiểm tra, tick **Bật thông báo** rồi **Lưu cài đặt**. Mỗi lần 1 lịch xoá cache chạy xong (tự động hoặc Chạy ngay) sẽ gửi 1 tin nhắn vào kênh đó, gồm tên lịch, phạm vi xoá và thời gian chạy.

**Độ chính xác thời gian:** tool có WP-Cron dự phòng tự chạy mỗi 5 phút nên hoạt động ngay không cần cấu hình gì thêm, nhưng WP-Cron chỉ được kích hoạt khi có người truy cập site nên có thể trễ vài phút trên site ít traffic. Để chạy đúng giờ, thêm cron job thật trên hosting (cPanel/SSH), khuyến nghị chạy mỗi phút:

```bash
* * * * * php /duong-dan-toi-wp-content/plugins/vietnix-center/crontab/cacheScheduler.php >/dev/null 2>&1
```

## Requirements

- WordPress; theme dùng Bricks Builder nếu cần các widget trong `widgets/bricks`.
- PHP có extension `openssl` (mã hoá API key, xác thực Google) và `curl` (Cache Scheduler warm cache).
- Plugin ACF cho các extension cần custom field (xem FAQ); LiteSpeed Cache cho tool Cache Scheduler.
- Node.js + Yarn khi cần build lại assets front-end. **Không cần Composer.**

## Security

- Không commit `.env`, `secrets/credentials.json` hay bất kỳ token/API key thật nào.
- Các form xử lý dữ liệu nhạy cảm (API key, IP whitelist) dùng nonce (`wp_verify_nonce`) và kiểm tra quyền (`current_user_can('manage_options')`).

## Changelog

### Unreleased
- **Cron guard** — các job trong `crontab/` tự bỏ qua lượt chạy khi plugin không active (`crontab/vnx-cron-guard.php`).
- **Google API không cần Guzzle/google-auth** — `app/GoogleAuth.php` tự ký JWT service account và gọi qua WordPress HTTP API, tự lấy token mới khi gặp 401.
- **Thông báo sau khi lưu form theo từng tool** — mỗi tool (Portal API, UTM, Discord & Sheets) dùng transient riêng, không còn bị tool khác "lấy mất" thông báo (`functions/requires/vnx_tool_notice.php`).
- **Ẩn admin notice của plugin khác** trên trang Tools/Settings.
- **Tài liệu API log từ khoá tìm kiếm** (`docs/search-keywords-api.md`).
- **Thêm tool Cache Scheduler** — hẹn giờ tự động xoá cache LiteSpeed (1 lần / hằng ngày / hằng tuần) theo URL cụ thể, bật/tắt từng lịch độc lập, cộng công tắc "Tự động chạy" tổng (mặc định tắt). Tự phát hiện LiteSpeed Cache chưa cài/đang tắt và dừng chạy lịch trong lúc đó. Có thể bật thông báo qua Discord webhook mỗi khi 1 lịch chạy xong (`tools/vietnix-cache-scheduler.php`, `crontab/cacheScheduler.php`).

### 0.1.0 (2026-08-27)
Phiên bản đầu tiên.

- Trang **Tools** gom các công cụ theo nhóm (sidebar, tab, help drawer, toast/alert), trang trống khi chưa bật tool nào (`tool_page.php`, `tools_layout.php`, `tools_empty.php`).
- Trang **Settings** bật/tắt độc lập từng tool/extension/widget; tool tắt sẽ không load và không hiện trong menu.
- Tool Portal API có nút hiện/ẩn API Key, key lưu mã hoá AES-256-CBC.
- Bộ widget Bricks Builder và block Gutenberg, REST API `vnx_api/v1`, `vnx_api/mkt`, các cron job trong `crontab/`.

## Cấu trúc thư mục

```
vietnix-center/
├── api/                  Đăng ký & xử lý REST API
├── app/                  Helper dùng chung: AutoLoader, View, GoogleAuth, DiscordBot, mã hoá API key
├── assets/               Nguồn CSS/JS (Tailwind + Laravel Mix)
├── build/                File CSS/JS đã build
├── config/               Cấu hình admin, Bricks, theme Vietnix
├── crontab/              Job cron: export sitemap, report post, cập nhật AI search, hẹn giờ xoá cache (+ cron guard)
├── docs/                 Tài liệu kỹ thuật (mã hoá API key, API log từ khoá tìm kiếm)
├── extentions/           Extension chạy ngầm, bật/tắt ở trang Settings
├── functions/            Hàm dùng chung, form handler, init
├── tools/                Các công cụ có trang cấu hình riêng trong menu Tools
├── views/                Template hiển thị cho tool/extension/widget
├── widgets/              Widget cho Bricks Builder và block cho Gutenberg
├── vietnix-center.php    File khởi tạo plugin (đăng ký menu, style, hook)
├── register.php          Khai báo danh sách tool/extension/widget theo nhóm
├── tool_page.php         Render trang Tools
├── general.php           Trang Settings
├── hooks_function.php    Hook dùng chung của plugin
└── vnx_active.php        Load các tool/extension đang được bật
```
