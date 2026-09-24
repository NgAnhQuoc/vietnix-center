
function downloadXLSX(xlsxData, headers) {

  var wb = XLSX.utils.book_new();
  var ws = XLSX.utils.json_to_sheet(xlsxData);
  XLSX.utils.book_append_sheet(wb, ws, "Dữ liệu");
  var wbout = XLSX.write(wb, { type: "binary", bookType: "xlsx" });

  // Hàm chuyển đổi chuỗi ký tự thành ArrayBuffer
  function s2ab(s) {
    var buf = new ArrayBuffer(s.length);
    var view = new Uint8Array(buf);
    for (var i = 0; i < s.length; i++) {
      view[i] = s.charCodeAt(i) & 0xff;
    }
    return buf;
  }

  var blob = new Blob([s2ab(wbout)], { type: "application/octet-stream" });

  var url = URL.createObjectURL(blob);

  var a = document.createElement("a");
  a.href = url;
  a.download = "data.xlsx";
  a.style.display = "none";
  document.body.appendChild(a);
  a.click();

  document.body.removeChild(a);

  URL.revokeObjectURL(url);
}