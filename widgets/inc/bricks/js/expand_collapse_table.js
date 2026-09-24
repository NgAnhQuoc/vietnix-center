$(".el-custom-btn-expand").click(function() {
  $(this)
    .parent()
    .closest("table")
    .find(".el-custom-default-hidden")
    .css('display', 'table-row');

  $(this)
    .parent()
    .closest("table")
    .find(".el-custom-btn-expand")
    .hide();
});

$(".el-custom-btn-collapse").click(function() {
  $(this)
    .parent()
    .closest("table")
    .find(".el-custom-default-hidden")
    .hide();

  $(this)
    .parent()
    .closest("table")
    .find(".el-custom-btn-expand")
    .show();
});