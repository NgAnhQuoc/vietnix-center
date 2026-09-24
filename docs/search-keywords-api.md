# API Log từ khoá tìm kiếm

```
GET /wp-json/vnx_api/v1/search-keywords
```

**Header bắt buộc:** `api-key: YOUR_KEY`
IP gọi API phải nằm trong danh sách `Allow IP` (Vietnix → tab API).

## Tham số

| Param | Mặc định | Giá trị |
|---|---|---|
| `page` | `1` | Số trang |
| `itemsPerPage` | `20` | 1–100 |
| `search` | — | Lọc theo từ khoá, khớp một phần |
| `period` | — | `today`, `yesterday`, `this_week`, `this_month`, `last_month`, `this_year` |
| `dateFrom` | — | `2026-09-01` hoặc `2026-09-01 08:30:00` |
| `dateTo` | — | Như trên |
| `orderBy` | `created_at` | `created_at`, `keyword`, `id` |
| `sortDir` | `desc` | `asc`, `desc` |

## Response

```json
{
  "success": true,
  "message": "Search keywords fetched successfully",
  "items": [
    {
      "keyword": "hosting",
      "createdAt": "2026-09-04T08:58:10.000Z",
      "urlSearch": "https://vietnix.vn/tim-kiem/"
    }
  ],
  "meta": {
    "totalItems": 137,
    "totalPages": 7,
    "page": 1,
    "itemsPerPage": 20
  }
}
```

## Ví dụ

**Lấy danh sách mới nhất**

```bash
curl -H "api-key: YOUR_KEY" \
  "https://vietnix.vn/wp-json/vnx_api/v1/search-keywords"
```

**Phân trang**

```
?page=2&itemsPerPage=50
```

## Filter

### Theo từ khoá

Khớp một phần, không phân biệt hoa thường.

```
?search=hosting
```

### Theo khoảng thời gian có sẵn

```
?period=this_month
```

| `period` | Khoảng |
|---|---|
| `today` | Hôm nay |
| `yesterday` | Hôm qua |
| `this_week` | Thứ Hai → hôm nay |
| `this_month` | Mùng 1 → hôm nay |
| `last_month` | Trọn tháng trước |
| `this_year` | 01/01 → hôm nay |

Tính theo timezone của site.

### Theo ngày tự chọn

```
?dateFrom=2026-09-01&dateTo=2026-09-30
```

Chỉ truyền ngày thì `dateTo` tự lấy đến hết ngày (`23:59:59`).
Dùng được riêng lẻ:

```
?dateFrom=2026-09-01
```

Nếu truyền cùng `period`, `dateFrom`/`dateTo` được ưu tiên.

### Sắp xếp

```
?orderBy=keyword&sortDir=asc
```

### Kết hợp

Từ khoá chứa "vps", trong tháng trước, 50 dòng mỗi trang:

```bash
curl -H "api-key: YOUR_KEY" \
  "https://vietnix.vn/wp-json/vnx_api/v1/search-keywords?search=vps&period=last_month&itemsPerPage=50"
```

## Lưu ý

- `createdAt` theo ISO 8601 UTC (`Y-m-d\TH:i:s.v\Z`, vd `2026-09-04T08:58:10.000Z`) — cùng định dạng với vietnix-plugin; trong DB vẫn lưu giờ local của site
- `urlSearch` là đường dẫn trang người dùng tìm kiếm, không kèm query string
- Bảng chưa có dữ liệu → trả `items: []`, `totalItems: 0` (không phải lỗi)
- Sai/thiếu `api-key` hoặc IP không được phép → `401` / `403`
