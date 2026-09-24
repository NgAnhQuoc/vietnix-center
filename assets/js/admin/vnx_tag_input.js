import Tagify from "@yaireo/tagify";

/**
 * Tagify cho các ô nhập danh sách chuỗi ngăn cách bằng dấu phẩy (IP, tên kênh...).
 *
 * Danh sách thực tế có thể rất dài (IP hệ thống, kênh UTM...). maxTags mặc định của
 * Tagify là 10, vượt quá thì nó lặng lẽ bỏ đi - nhập 12 IP chỉ lưu được 10 mà không
 * báo gì. Nâng lên mức chỉ còn ý nghĩa chặn dán nhầm cả file. Cũng cho phép dán một
 * lần cả chuỗi ngăn bằng dấu phẩy, chấm phẩy, khoảng trắng hoặc xuống dòng, vì danh
 * sách này thường được copy từ chỗ khác.
 */
export function createTagInput(input, whitelist = []) {
  // admin.js nạp trên mọi trang của plugin, còn ô nhập (allow_api, seo_channel...) chỉ có
  // ở trang tool tương ứng - không có thì bỏ qua, tránh Tagify log
  // "[Tagify]: input element not found null".
  if (!input) {
    return null;
  }

  return new Tagify(input, {
    whitelist,
    maxTags: 200,
    delimiters: ",|;| |\n",
    duplicates: false,
    trim: true,
    dropdown: {
      maxItems: 20,
      classname: "tags-look",
      enabled: 0,
      closeOnSelect: false,
    },
  });
}
