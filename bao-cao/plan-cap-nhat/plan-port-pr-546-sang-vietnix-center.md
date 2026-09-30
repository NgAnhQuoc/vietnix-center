# Plan: Cập nhật PR #546 (layout "Price Server") sang plugin `vietnix-center`

- **Nguồn:** `vietnix-plugin` PR #546 (branch local `pr-546`, base `ff1473a8`)
- **Đích:** `wp-content/plugins/vietnix-center` (branch `master`, commit `2730d38`)
- **Ngày lập:** 2026-09-24
- Báo cáo PR gốc: [pr-546-vnx-price-server.md](pr-546-vnx-price-server.md)

---

## 1. Kết quả so sánh hiện trạng

So sánh 5 file mà PR chạm tới giữa `vietnix-plugin` (tại commit base của PR) và `vietnix-center`:

| File | Trạng thái bên center | Ghi chú |
|---|---|---|
| `widgets/bricks/vnx-service-price-v2.php` | Có, **khác nhẹ** | Center đã đổi riêng cho bản của mình (xem bảng dưới) |
| `widgets/inc/bricks/js/vnx_service_price_v2.js` | Có, **giống hệt** base | Áp dụng patch trực tiếp được |
| `assets/scss/bricks/_service_price.scss` | Có, **giống hệt** base | Áp dụng patch trực tiếp được |
| `views/widgets/bricks/vnx-service/vnx-price-server.php` | **Chưa có** | Tạo mới |
| `views/widgets/bricks/vnx-service/server/vnx-price-server.php` | **Chưa có** (thư mục `server/` cũng chưa có) | Tạo mới |

Những chỗ center đã đổi riêng so với `vietnix-plugin`:

| Điểm khác | vietnix-plugin | vietnix-center |
|---|---|---|
| Namespace View | `use Helper\View;` | `use HelperCenter\View;` |
| Tên class widget | `VNX_Service_Price_V2` | `VNX_Service_Price_V2_Center` |
| Hàm render icon | `showIcon()` | **`showIcon_Center()`** |
| Hằng URL | `VNX_PLUGIN_URL` | `VNX_PLUGIN_URL_CENTER` |
| Handle script | `vuejs-library`, `vnx-service-price-v2` | `vuejs-library-center`, `vnx-service-price-v2-center` |

**Đã chạy thử `git apply --check`:** patch của PR áp dụng **sạch, không conflict** vào center. Tuy vậy vẫn phải sửa tay thêm 2 chỗ (bước 3), vì code gốc gọi `Helper\View` và `showIcon()`, mà center không có hai tên này.

Không có file nào khác trong center (`register.php`, `tools/vietnix-api-price-table.php`, `tools/vietnix-api-ldp.php`, `class-vietnix-get-csv-widget.php`) phải sửa theo. Widget đã được đăng ký sẵn (`register.php:107`), và không file nào khác liệt kê danh sách version.

## 2. Việc cần quyết định trước

1. **Sửa key `banner_tabel` → `banner_table`?** Nếu định sửa thì nên sửa ở `vietnix-plugin` (PR #546) **trước**, rồi port sang, để 2 plugin dùng cùng một key. Nếu port nguyên trạng thì sau này đổi key sẽ làm mất banner đã cấu hình ở cả 2 nơi.
2. Chờ PR #546 **merge xong** rồi mới port (nên làm), hay port ngay từ branch `pr-546`. Nếu port sớm mà PR còn commit mới thì phải port thêm lần nữa.

## 3. Các bước thực hiện

### Bước 1 – Tạo branch bên center
```bash
cd wp-content/plugins/vietnix-center
git checkout -b feature/vnx-price-server
```

### Bước 2 – Áp dụng patch của PR
```bash
# tạo patch từ vietnix-plugin (sau khi PR đã merge: dùng commit merge thay cho master...pr-546)
cd ../vietnix-plugin
git diff master...pr-546 > /tmp/pr546.patch

cd ../vietnix-center
git apply /tmp/pr546.patch
```
Patch sẽ:
- thêm option `vnx-price-server`, control group `price_server` và 3 control `banner_tabel`, `banner_mobile`, `price_unit` vào `widgets/bricks/vnx-service-price-v2.php`, đồng thời mở rộng `required` của Popular / Yes Icon / No Icon;
- thêm guard `typeof Vue` và block Vue cho `.vnx-price-server` vào JS;
- thêm style `.vnx-price-server` vào `_service_price.scss`;
- tạo 2 file view mới.

> Nếu không dùng patch thì copy tay đúng 5 thay đổi trên. Phần JS và SCSS có thể copy nguyên file từ `pr-546`, vì 2 file này bên center đang giống hệt bản base.

### Bước 3 – Chỉnh cho khớp với center (bắt buộc)

**3.1** `views/widgets/bricks/vnx-service/vnx-price-server.php`
```diff
- use Helper\View;
+ use HelperCenter\View;
```
Nếu không đổi, `View::render` sẽ gọi class của `vietnix-plugin`. Khi site chỉ bật center thì lỗi fatal; khi bật cả 2 thì nó render nhầm view từ thư mục của plugin kia.

**3.2** `views/widgets/bricks/vnx-service/server/vnx-price-server.php` – đổi 3 lời gọi:
```diff
- $widget->showIcon('no_icon', 'vnx-no-icon');
+ $widget->showIcon_Center('no_icon', 'vnx-no-icon');
- $widget->showIcon('yes_icon', 'vnx-yes-icon');
+ $widget->showIcon_Center('yes_icon', 'vnx-yes-icon');
- $widget->showIcon('tooltip_icon', 'tooltip-icon');
+ $widget->showIcon_Center('tooltip_icon', 'tooltip-icon');
```
Nếu không đổi sẽ bị lỗi `Call to undefined method VNX_Service_Price_V2_Center::showIcon()`.

Lệnh nhanh:
```bash
sed -i '' 's/use Helper\\View;/use HelperCenter\\View;/' views/widgets/bricks/vnx-service/vnx-price-server.php
sed -i '' 's/\$widget->showIcon(/$widget->showIcon_Center(/g' views/widgets/bricks/vnx-service/server/vnx-price-server.php
```

**3.3** Kiểm tra lại, không được còn sót:
```bash
grep -rn "Helper\\\\View\|->showIcon(" views/widgets/bricks/vnx-service/ widgets/bricks/vnx-service-price-v2.php
# kết quả mong đợi: không có dòng nào
grep -n "VNX_PLUGIN_URL\b" widgets/bricks/vnx-service-price-v2.php
# kết quả mong đợi: không có dòng nào (center dùng VNX_PLUGIN_URL_CENTER)
```

### Bước 4 – Build CSS
Thư mục `build/` của center **không được commit**, nên phải build lại thì style mới có hiệu lực:
```bash
yarn build        # mix -> build/css/vnx-bricks.css
# khi đóng gói phát hành:
yarn zip
```

### Bước 5 – Kiểm thử trên site dùng center
- [ ] Không có lỗi PHP trong log (`debug.log` / công cụ `get_errors`).
- [ ] Trong Bricks editor, chọn **VNX Service Price V2 → Version: Price Server** thì thấy group **Price Server** (Banner Table, Banner Mobile, Price Unit), và các group Popular / Yes Icon / No Icon hiển thị.
- [ ] Import CSV mẫu (dùng chung file với bên vietnix-plugin) thì tab chu kỳ, card, giá, thông số và tooltip hiển thị đúng.
- [ ] Bấm tab chu kỳ thì giá, nút "Đăng ký ngay" và link thay đổi theo.
- [ ] Banner: kiểm tra 3 trường hợp chỉ desktop / chỉ mobile / cả hai.
- [ ] Không upload CSV thì hiện "Không có data truyền vào".
- [ ] **Regression:** các version cũ (`vnx-price_hosting_v2`, `vnx-price-email`, `vnx-price-have-range`, `vnx-price_scroll`, `vnx-price_ssl`) vẫn chạy, vì file JS dùng chung đã được sửa.
- [ ] Responsive tại 1200 / 991 / 767 / 375px.
- [ ] Nếu site bật **cả 2 plugin**: kiểm tra element không bị trùng hoặc lẫn view, vì cả 2 cùng dùng `$name = 'vnx-service-price-v2'`. Hiện trạng này đã có từ trước, không phải do PR.

### Bước 6 – Commit
```bash
git add widgets/bricks/vnx-service-price-v2.php \
        widgets/inc/bricks/js/vnx_service_price_v2.js \
        assets/scss/bricks/_service_price.scss \
        views/widgets/bricks/vnx-service/vnx-price-server.php \
        views/widgets/bricks/vnx-service/server/vnx-price-server.php
git commit -m "Port vnx-price-server layout from vietnix-plugin PR #546"
```

## 4. Rủi ro & ước lượng

| Rủi ro | Mức | Cách giảm |
|---|---|---|
| Quên đổi `showIcon` hoặc `Helper\View` → fatal error | Cao nếu xảy ra | Làm đủ Bước 3.3 (grep) |
| Quên build CSS → layout vỡ | Trung bình | Làm Bước 4, kiểm tra `build/css/vnx-bricks.css` có `.vnx-price-server` |
| Sửa JS dùng chung ảnh hưởng version cũ | Thấp | Chỉ thêm guard và một block mới; vẫn chạy regression test ở Bước 5 |
| PR #546 thay đổi thêm sau khi đã port | Thấp–TB | Port sau khi PR merge |

**Khối lượng ước tính:** khoảng 30 phút cho code và build, cộng thời gian test (khoảng 30–60 phút).
