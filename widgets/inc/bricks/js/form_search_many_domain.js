window.jQuery = window.$ = jQuery;
$(document).ready(function () {
  const dataGetTLDPricing = { action: "GetTLDPricing", data: { security: $('#vnx_domain_security').val(), }, };
  const tld_ex_default = ['com', 'com.vn', 'net', 'vn'];
  const openPopupButton = $('#open-popup');
  const popup = $('.popup');
  const closePopup = $('#closePopup');
  const openPopupExtension = $('#open-popup-extension');
  const extension = $('.extension');
  const closePopupExtension = $('#closePopupExtension');
  const search_TLD = $("#search_TLD");
  const form_search = $('form#form_search_many_domain');
  const vnx_search_many_domain = $('textarea#vnx_search_many_domain');
  let timeoutId;

  $("button.submmit_form_search").on('click', (e) => {
    e.preventDefault();
    if ($('#type_form_search').val() == 'form_search_many_domain') {
      let check = check_input_domain();

      if (check == false) {
        $(".loadding_button").removeClass("hidden");
        $('#form_search_many_domain').submit();
      }
      else {
        tooltip_show();
      }
    }
    else {
      $(".loadding_button").removeClass("hidden");
      $('#form_search_many_domain').submit();
    }

    // $('#form_search_many_domain').submit();
  })

  if ($(".count_key").val() <= 0) {
    $(".submmit_form_search").prop("disabled", false);
    $(".hidden_tooltip_button_search").hide();
    $(".hidden_tooltip_input_search").hide();
  }

  function check_input_domain() {
    let inputValues = $('#vnx-textarea-search').val().split('\n');
    let inputValue = $.grep(inputValues, function (value) {
      return $.trim(value) !== '';
    });
    let count = 0;
    let check = false;
    for (let i = 0; i < inputValue.length; i++) {
      if (inputValue[i] !== "") {
        count++;
        if (inputValue[i].includes('.') == false && !$(".vnx-button-ex").length) {
          check = true;
        }
      }
    }

    if (check == false) {
      return false;
    } else {
      return true;
    }
  }

  function tooltip_show() {
    $(".hidden_tooltip_button_search").show();
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
      $(".hidden_tooltip_button_search").hide()
    }, 2000);
  }

  async function showTLD() {
    try {
      const data = await post_ajax_gettld('POST', 'get_api_whmcs_center', dataGetTLDPricing);
      if (data == null) {
        showTLD();
        return;
      }
      sessionStorage.setItem("TLD_Domain", JSON.stringify(data));
      sessionStorage.setItem("domain_price_Data", JSON.stringify(data['pricing']));
      sessionStorage.setItem("domain_Data", JSON.stringify(data));
      if (data != null && data.result === "success") {
        const ex_tld = Object.keys(data.pricing);
        const array_tld_old = showExTld();

        ex_tld.map(function (e) {
          let prop = "";
          if (array_tld_old !== false && array_tld_old.includes(e)) {
            prop = "checked";
          }
          if (!tld_ex_default.includes(e)) {
            $("#resultTldDomain").append(`<div class="flex items-center justify-center vn-cs-checkbox p-4 "><div>
              <input type="checkbox" name="nameExtention['${e}']" id="${e.replace('.', '_')}" value="${e}" class="custom-checkbox" ${prop}> 
              <label for="${e.replace('.', '_')}"> .${e}</label></div></div>`);
          } else {
            if (array_tld_old !== false && array_tld_old.includes(e)) {
              $("#ex" + e.replace('.', '_')).prop('checked', true);
            }
            $("#resultTldDomain").append(`<div class="flex items-center justify-center vn-cs-checkbox p-4 " style="display: none !important;"><div>
              <input type="checkbox" name="nameExtention['${e}']" id="${e.replace('.', '_')}" value="${e}" class="custom-checkbox" ${prop}> 
              <label for="${e.replace('.', '_')}"> .${e}</label></div></div>`);
          }
        });
      }
    } catch (error) {
      console.warn("Lấy danh sách TLD thất bại:", error);
    }
  }
  showTLD();

  //ẩn hiện popup
  openPopupButton.click(function () {
    popup.removeClass('hidden');
  });

  closePopup.click(function () {
    popup.addClass('hidden');
  });

  openPopupExtension.click(function () {
    extension.removeClass('hidden');
  });

  closePopupExtension.click(function () {
    extension.addClass('hidden');
  });
  // end ẩn hiện popup

  // xử lý input file  csv

  const dropZone = $('#drop-zone');
  const csvFileInput = $('#csv-file');

  // Khi kéo thả file vào drop zone
  dropZone.on('dragover', function (e) {
    e.preventDefault();
    dropZone.addClass('dragging');
  });

  // Khi kết thúc kéo thả file
  dropZone.on('dragleave', function () {
    dropZone.removeClass('dragging');
  });

  dropZone.on('drop', function (e) 
  {
      e.preventDefault();
      var files = e.originalEvent.dataTransfer.files;
      $('#csv-file').prop('files', files);
      // Trigger the "change" event on the input field
      $('#csv-file').trigger('change');
  });

  // Khi thả file vào drop zone hoặc chọn file từ dialog
  csvFileInput.on('change', function (e) {
    const file = e.target.files[0];
    $("#name-file").html('<strong>File:</strong> ' + file['name']);
    // Xử lý file CSV ở đây
  });

  // dữ liệu sau khi input vào
  const csv_file = $("#csv-file");
  const vnx_textarea_search = $("#vnx-textarea-search");

  $("#vnx-upload-file").click(function () {
    var file = $("#csv-file")[0].files[0];
    if (file) {
      if (file.type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
        var reader = new FileReader();

        reader.onload = function (event) {
          var data = new Uint8Array(event.target.result);
          var workbook = XLSX.read(data, { type: "array" });

          var sheet = workbook.Sheets[workbook.SheetNames[0]];
          var csvData = XLSX.utils.sheet_to_csv(sheet, { blankrows: false });
          contents = csvData.split("\n");
          // for (let i = 1; i < contents.length; i++) {
          //   if (i <= 300) {
          //     if(!(i == contents.length)){
          //       contents[i] = contents[i]+'\r';
          //     }
          //   } else {
          //     break;
          //   }
          // }
          pushDataFromFileCSV(contents);
        };
        reader.readAsArrayBuffer(file);
      }
      else {
        var reader = new FileReader();
        // Đọc file
        reader.onload = function (e) {

          var contents = e.target.result;
          // Xử lý nội dung đọc được từ file ở đây
          contents = contents.split("\n");

          pushDataFromFileCSV(contents);
        };

        reader.readAsText(file);
      }

    } else {
      $("#name-file").html('<small class="text-danger">(*) Vui lòng nhập file</small>');
    }
  });

  function cleanDomain(inputDomain) {
    inputDomain = inputDomain.toString();
    const domainRegex = /([a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+)/;

    // Tìm tên miền trong chuỗi
    const matches = inputDomain.match(domainRegex);

    if (matches) {
      // Lấy tên miền từ kết quả khớp
      const inputDomain = matches[0];

      // Trả về tên miền hợp lệ
      return inputDomain;
    } else {
      // Nếu không tìm thấy tên miền hợp lệ, trả về null hoặc chuỗi rỗng
      return inputDomain; // hoặc return "";
    }
  }

  const vnx_custom_result_domain = $(".vnx-custom-result-domain");
  function pushDataFromFileCSV(file) {
    if (Array.isArray(file)) {
      var output = "";
      var count = 0;
      for (let i = 1; i < file.length; i++) {
        count++;
        if (i <= 300) {
          file[i] = cleanDomain(file[i]);
          output += file[i] + '\r';
        } else {
          break;
        }
      }
      vnx_custom_result_domain.text('Đã nhận ' + count + ' tên miền')
      vnx_textarea_search.val(output);
      popup.addClass('hidden');
      if (count > 0) {
        $(".submmit_form_search").prop("disabled", false);
      } else {
        $(".submmit_form_search").prop("disabled", true);
      }
    }
  }

  vnx_textarea_search.on('keyup', function () {
    var inputValues = $(this).val().split('\n');
    var inputValue = $.grep(inputValues, function (value) {
      return $.trim(value) !== '';
    });
    var count = 0;
    var check = false;
    for (let i = 0; i < inputValue.length; i++) {
      if (inputValue[i] !== "") {
        count++;
        if (inputValue[i].includes('.') == false && !$(".vnx-button-ex").length) {
          check = true;
        }
      }
    }

    if (check == false) {
      $(".submmit_form_search").prop("disabled", false);
      $(".hidden_tooltip_input_search").hide();
    } else {
      $(".submmit_form_search").prop("disabled", false);
      if ($('#type_form_search').val() == 'whois_domain') {
        $(".hidden_tooltip_input_search").hide();
      }
      else {
        $(".hidden_tooltip_input_search").show();
      }

    }

    vnx_custom_result_domain.text('Đã nhận ' + count + ' tên miền');
  });


  $('#deleteDataTextarea').click(function (e) {
    e.preventDefault();
    vnx_textarea_search.val("");
  });

  // end xử lý input file

  /**
   * Sự kiện click huỷ
   */

  const emty_input_file = $(".emty-input-file");
  emty_input_file.click(function () {
    csv_file.val("");
    $("#name-file").html('');
    popup.addClass('hidden');
  })

  //xử lý check all
  $(document).on("click", "#checkAll", function () {
    if (!$(this).is(':checked')) {
      $('.custom-checkbox').prop('checked', false);
    } else {
      $(".custom-checkbox").prop('checked', true);
    }
  });

  //xử lý un check all
  //check giống nhau
  $("input[name^='nameExtention'][name$=']']").click(function () {
    let text = $(this).val().replace('.', '_');
    if (!$(this).is(':checked')) {
      $(this).prop('checked', false);
      $("#ex" + text).prop('checked', false);
      $("#" + text).prop('checked', false);
      $(".border-ex" + text).removeClass("border-checkbox");
    } else {
      // Nếu ô đang được chọn
      $("#" + text).prop('checked', true);
      $("#ex" + text).prop('checked', true);
      $(".border-ex" + text).addClass("border-checkbox");
    }
  });

  //close popup phần mở rộng
  const uncheckAndClosePopup = $(".uncheckAndClosePopup");
  uncheckAndClosePopup.click(function () {
    $("input[name^='nameExtention'][name$=']']").map(function () {
      $(this).prop('checked', false);
      extension.addClass('hidden');
    });
  });

  let vnx_submit_extension = $("#vnx-submit-extension");
  var array_return_submit = Array();
  // xóa TLD
  $(document).on("click", "#deleteExInArray", function () {
    let dataEx = $(this).attr('data-ex');
    if (array_return_submit.includes(dataEx)) {
      $(".border-ex" + dataEx.replace('.', '_')).removeClass("border-checkbox");
      let index = array_return_submit.indexOf(dataEx);
      array_return_submit.splice(index, 1);
    }
    showItemExtensiton(array_return_submit);
    if (tld_ex_default.includes(dataEx)) {
      $("#ex" + dataEx.replace('.', '_')).prop('checked', false);
    }
    $("#" + dataEx.replace('.', '_')).prop('checked', false);
  });

  //submit extension
  vnx_submit_extension.click(function (e) {
    e.preventDefault();
    array_return_submit = array_return_submit.splice(0, array_return_submit.length);
    $(".custom-checkbox").map(function () {
      let text = $(this).val();
      text = text.replace('.', '_');
      if ($("#" + text + "").is(':checked')) {
        text = text.replace('_', '.');
        if (!array_return_submit.includes(text)) {
          array_return_submit.push(text);
        }
      } else {
        text = text.replace('_', '.');
        let index = array_return_submit.indexOf(text);
        if (index !== -1) {
          array_return_submit.splice(index, 1);
        }
      }
    });
    showItemExtensiton(array_return_submit);

    $("#closePopupExtension").click();
  });



  function showExTld() {
    const TLD = $("input#vnx_suggest_tld");
    if (TLD.length && TLD.val() != "") {
      const list_TLD = JSON.parse(TLD.val());
      showItemExtensiton(list_TLD);
      array_return_submit = list_TLD;
      return list_TLD;
    } else {
      return false;
    }

  }

  //------------- show output từ check box của phần mở rộng --------------------------------
  const result_extension = $("#result_extension");
  function showItemExtensiton(array) {
    var result = "";
    var array_domains = $('#vnx-textarea-search').val().toLowerCase().split("\n");
    var check = false;
    var array_domain = $.grep(array_domains, function (value) {
      return $.trim(value) !== '';
    });
    if (array.length > 0) {
      array.map(function (e) {
        result += '<span class="vnx-button-ex">.' + e + ' <span id="deleteExInArray" data-ex="' + e + '"><svg width="15" height="13" viewBox="0 0 8 9" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.5625 7.28906C7.77344 7.52344 7.77344 7.875 7.5625 8.08594C7.32812 8.32031 6.97656 8.32031 6.76562 8.08594L4 5.29688L1.21094 8.08594C0.976562 8.32031 0.625 8.32031 0.414062 8.08594C0.179688 7.875 0.179688 7.52344 0.414062 7.28906L3.20312 4.5L0.414062 1.71094C0.179688 1.47656 0.179688 1.125 0.414062 0.914062C0.625 0.679688 0.976562 0.679688 1.1875 0.914062L4 3.72656L6.78906 0.9375C7 0.703125 7.35156 0.703125 7.5625 0.9375C7.79688 1.14844 7.79688 1.5 7.5625 1.73438L4.77344 4.5L7.5625 7.28906Z" fill="#BDBDBD"/></svg></span></span>';
        result += '<input type="hidden" name="extension_TLD[]" value="' + e + '">';
      });
    } else {
      for (let i = 0; i < array_domain.length; i++) {
        if (array_domain[i].includes('.') == false) {
          check = true;
        }
      }
    }



    if (check == false) {
      $(".submmit_form_search").prop("disabled", false);
      $(".hidden_tooltip_input_search").hide();
    } else {
      $(".submmit_form_search").prop("disabled", false);
      $(".hidden_tooltip_input_search").show();
    }
    $("#result_extension").html(result);
  }

  // checked tld của người dùng đã chọn trước đó 
  function get_extension_domain() {
    var data_key = post_ajax_gettld('POST', 'get_api_whmcs_center', dataGetTLDPricing);
    return data_key;
  };

  var array_TLD = get_extension_domain();
  search_TLD.on('keyup', function () {
    var val_ex = $(this).val().replace(/\s+/g, '');;

    var array_key_similar = Array();
    var obj = Object.keys(array_TLD.responseJSON["pricing"]);
    var val_ex_hash = hash(val_ex);

    var kq = "";
    obj.map(function (e) {
      var result = compareStrings(val_ex_hash, e);
      if (result == true) {
        array_key_similar.push(e);
        kq += '<div class="flex items-center justify-center vn-cs-checkbox p-4 "><div><input type="checkbox" name="nameExtention[' + e + ']" id="' + e.replace('.', '_') + '" value="' + e + '" class=" custom-checkbox "> <label for="' + e + '"> .' + e + '</label></div></div>';
      }
    });
    if (val_ex) {
      $(".result-input-search").html(kq);
      if (array_key_similar.length !== 0) {
        $(".result-tld-search").html("<span class='text-success'>" + array_key_similar.length + " Kết quả cho từ khoá: </span>" + $(this).val() + "");
      } else {
        $(".result-tld-search").html("<span class='text-danger'>Phần mở rộng không được hỗ trợ.</span>");
      }
    } else {
      $(".result-tld-search").text("");
      $(".result-input-search").html("");
    }

  });

  function hash(string) {
    let hashValue = 0;
    for (let i = 0; i < string.length; i++) {
      hashValue += string.charCodeAt(i);
    }
    return hashValue % 100; // Modulo một số nguyên lớn để đảm bảo giá trị băm không quá lớn
  }

  function compareStrings(string1, string2) {
    const hashValue1 = string1;
    const hashValue2 = hash(string2);
    if (hashValue1 === hashValue2) {
      return true;
    } else {
      return false;
    }
  }

  function isValidKey(input) {
    var pattern = /^[a-zA-Z0-9]+$/g;
    return pattern.test(input);
  }


});