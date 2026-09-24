# Báo cáo PR #546 – Thêm layout "Price Server" cho widget `vnx-service-price-v2`

- **Link:** https://github.com/vietnixvn/vietnix-plugin/pull/546
- **Tác giả commit:** Truong Duy Linh
- **Ngày review:** 2026-09-24
- **Phạm vi:** 5 file, +720 / −5 dòng

| Commit | Ngày | Nội dung |
|---|---|---|
| `8d016fc3` | 2026-09-22 | add new version vnx-price-server for vnc-service-price-v2 |
| `fab87504` | 2026-09-22 | update js vnx-price-server |
| `f1ab30cf` | 2026-09-24 | refactor: update responsive styles for price component |

---

## 1. Tóm tắt

PR thêm **một version (style) mới tên `vnx-price-server`** cho Bricks element **VNX Service Price V2**, dùng để hiển thị bảng giá dịch vụ Server:

- Thanh tab chọn **chu kỳ thanh toán** (tháng / năm …, có nhãn khuyến mãi).
- Card gói dịch vụ gồm **header có banner** (ảnh desktop/mobile riêng), **cột giá + nút "Đăng ký ngay"** theo từng chu kỳ, và **cột thông số kỹ thuật** có icon yes/no/custom và tooltip chú thích.
- Dữ liệu vẫn lấy từ file CSV import như các version khác.

## 2. Chi tiết thay đổi theo file

### 2.1 `widgets/bricks/vnx-service-price-v2.php` (+34 / −3)

- Thêm option `'vnx-price-server' => 'Price Server'` vào control **Version**.
- Mở rộng điều kiện `required` của các control group **Popular**, **Yes Icon**, **No Icon** để hiện cả khi chọn `vnx-price-server`.
- Thêm control group mới **Price Server** (`price_server`) với 3 control:

| Key | Loại | Ý nghĩa |
|---|---|---|
| `banner_tabel` | image | Banner header card trên desktop/tablet |
| `banner_mobile` | image | Banner header card trên mobile |
| `price_unit` | text | Đơn vị giá cố định (vd "tháng"); để trống thì dùng tên chu kỳ đang chọn |

### 2.2 `views/widgets/bricks/vnx-service/vnx-price-server.php` (mới, 28 dòng)

File wrapper, giống pattern của `vnx-price-email.php`:
- Kiểm tra đã chọn version, set class root = `[vnx-price-server, w-full]`.
- Render template con `views/widgets/bricks/vnx-service/server/vnx-price-server.php`.
- Khác `vnx-price-email.php`: **không** kiểm tra `import-csv` ở wrapper (việc này chuyển xuống template con).

### 2.3 `views/widgets/bricks/vnx-service/server/vnx-price-server.php` (mới, 203 dòng)

Template chính. Luồng xử lý:

1. Đọc CSV qua `geFileDataUpload()` → `transform_columns_to_packages()`. Không có data → in thông báo lỗi và dừng.
2. Quy ước cấu trúc CSV (theo cột):
   - `packages[0]` = cột nhãn → lấy **danh sách chu kỳ** từ `group_1`, định dạng `"Tên chu kỳ | Nhãn giảm giá"`.
   - `packages[1]` = cột chú thích → `group_2` dùng làm **tooltip** cho từng dòng thông số.
   - `packages[2..]` = các gói dịch vụ thực tế.
   - Mỗi gói: hàng 0 = tên, hàng 1 (`image`) = **link đăng ký mặc định**, hàng 2 (`range`) = **URL ảnh header**.
   - Ô giá (`group_1`): `"giá gốc | giá sau giảm | link đăng ký"`; thiếu giá giảm thì hiển thị giá gốc, thiếu link thì dùng link mặc định.
   - Ô thông số (`group_2`): `"yes|no|<class icon> | Nội dung"`; chỉ có text thì mặc định icon `yes`.
3. `cycle_popular` quyết định chu kỳ active ban đầu, có **clamp** về 0 nếu vượt phạm vi.
4. Banner truyền vào CSS qua 2 biến inline `--vnx-banner` / `--vnx-banner-mb`; thiếu 1 trong 2 thì dùng cái còn lại.
5. Nút đăng ký có các `data-price`, `data-period`, `data-product-name`, `data-product-category` (phục vụ tracking conversion, class `vnx-btn-conversion`).
6. Output được escape đầy đủ (`esc_html`, `esc_attr`, `esc_url`, `wp_kses` cho tên chỉ cho phép `<br>`).

### 2.4 `widgets/inc/bricks/js/vnx_service_price_v2.js` (+62 / −3)

- **Sửa lỗi tiềm ẩn:** bọc `Vue.prototype.$eventBus = new Vue();` trong `if (typeof Vue !== "undefined")` để tránh `ReferenceError` chặn toàn bộ script khi Vue chưa load. Thay đổi này ảnh hưởng tích cực đến **tất cả** version dùng chung file JS.
- Thêm block cho `.vnx-price-server`: mount một instance Vue lên markup có sẵn, khi click tab chu kỳ thì:
  - toggle class `vnx-active` trên tab,
  - chỉ hiện giá (`.vnx-warp-discount-price`) và nút đăng ký tương ứng với chu kỳ, ẩn phần còn lại bằng class `hidden`.
- Giữ chu kỳ active do PHP render làm trạng thái ban đầu. Không dùng Splide.

### 2.5 `assets/scss/bricks/_service_price.scss` (+392)

- Thêm toàn bộ style scope `.vnx-price-server { … }`: tab chu kỳ (có divider giữa các item, trạng thái hover/active), header card với banner nền, cột giá, nút đăng ký, cột thông số, tooltip.
- Responsive tại 2 breakpoint `991px` và `767px`; banner đổi sang `--vnx-banner-mb` trên mobile.
- File đã được import sẵn trong `assets/scss/vnx-bricks.scss`.

## 3. Đánh giá & điểm cần lưu ý

### Điểm tốt
- Theo đúng pattern có sẵn của widget (wrapper + template con, dùng chung parser CSV).
- Escape output cẩn thận; có fallback hợp lý cho giá, link, banner, chu kỳ active.
- Guard `typeof Vue` là một bản sửa lỗi hữu ích cho cả file JS.
- Style được scope trong `.vnx-price-server` nên không ảnh hưởng các version khác.

### Vấn đề / góp ý (mức độ nhỏ)

| # | Vị trí | Nhận xét |
|---|---|---|
| 1 | `vnx-service-price-v2.php` – key `banner_tabel` | Sai chính tả (`table`). Nên sửa **trước khi merge**, vì sau khi đã có dữ liệu lưu trong Bricks thì đổi key sẽ mất cấu hình. |
| 2 | Template – `$productLink = $package['image']`, `$productImage = $package['range']` | Tên key của parser không khớp ý nghĩa (cột "image" lại là link, cột "range" lại là ảnh). Chạy đúng nhưng dễ gây nhầm khi chuẩn bị CSV — nên ghi rõ quy ước CSV trong tài liệu/comment. |
| 3 | JS – dùng `new Vue({ el })` | Vue 2 sẽ compile markup PHP như template: nếu nội dung CSV có chuỗi `{{ … }}` sẽ bị Vue xử lý. Logic ở đây chỉ là toggle class nên có thể viết bằng vanilla JS/jQuery, không cần Vue (comment trong code cũng nói "không dùng template"). |
| 4 | `vnx-price-server.php` (wrapper) | `isset($version_style)` được kiểm tra sau khi đã truy cập `$data->settings['version']` → có thể sinh PHP notice nếu chưa chọn version (lỗi kế thừa từ các wrapper khác, không phải lỗi mới). |
| 5 | Template – `$productCategory` | Regex `[^a-zA-Z\s]` loại bỏ ký tự tiếng Việt có dấu và số → category tracking có thể bị méo (vd "Máy chủ 1" → "My ch"). Nhất quán với các template cũ nhưng nên lưu ý nếu dùng cho analytics. |
| 6 | Build CSS | PR chỉ sửa SCSS; cần đảm bảo pipeline build lại CSS (`vnx-bricks.scss`) khi deploy, nếu không style mới sẽ không có hiệu lực. |
| 7 | Cuối các file mới | Thiếu newline cuối file (`\ No newline at end of file`) — chỉ là vấn đề format. |

## 4. Checklist kiểm thử đề xuất

- [ ] Tạo element VNX Service Price V2, chọn version **Price Server**, import CSV mẫu → hiển thị đúng tab chu kỳ, card, giá, thông số, tooltip.
- [ ] Đổi `Popular → cycle_popular` (kể cả giá trị vượt số chu kỳ) → chu kỳ active đúng / fallback về 0.
- [ ] Click từng tab → giá + nút đăng ký + link thay đổi đúng chu kỳ.
- [ ] Chỉ set banner desktop / chỉ set banner mobile / set cả hai → banner hiển thị đúng ở desktop và mobile.
- [ ] Để trống / nhập `price_unit` → đơn vị hiển thị sau giá đúng.
- [ ] Ô giá không có giá giảm → không hiện giá gạch, hiện giá gốc.
- [ ] Không upload CSV → hiện thông báo "Không có data truyền vào", không lỗi PHP.
- [ ] Kiểm tra lại các version cũ (`vnx-price_hosting_v2`, `vnx-price-email`, `vnx-price-have-range`…) vẫn hoạt động sau thay đổi JS.
- [ ] Responsive tại 1200px / 991px / 767px / 375px.

## 5. Kết luận

PR bổ sung một layout mới, độc lập, ít rủi ro ảnh hưởng tới các version hiện có (thay đổi dùng chung duy nhất là guard `typeof Vue` – theo hướng an toàn hơn). **Có thể merge**, khuyến nghị sửa key `banner_tabel` trước khi merge và cân nhắc các góp ý nhỏ ở mục 3.
