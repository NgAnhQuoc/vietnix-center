window.jQuery = window.$ = jQuery;
var admin_ajax_url = $(location).attr("origin") + '/wp-admin/admin-ajax.php';
$(document).ready(function () {

  var dataGetTLDPricing = {action: "GetTLDPricing",data: ""};
  var tld_data = get_sessionStorage('TLD_Data');
  var vnx_domain_search_result = $('#vnx_domain_search_result');
  var vnx_hidden_form_search = $('#vnx_hidden_form_search');
  var vnx_type_form_search = $('#type_form_search');
  var count_check_domain = $('.count_check_total');
  var count_check = $(".count_check");
  var progress = $("progress.progress");

  var domain_annual_price ='Giá đăng ký gốc';
  var domain_tooltip_title = 'Tooltip title';
  var domain_tooltip_des = 'Tooltip description';
  var count_result_search = $(".count-result-search");

  var vnx_link_see_whois = $(".vnx_link_see_whois");
  var vnx_button_export_csv_download = $(".vnx_button_export_csv_download");

  $('.animate_dot').hide();
  $(".button_search_other_domain").click(function () {
    if (vnx_type_form_search.length && vnx_type_form_search.val() == "form_search_many_domain"){
    vnx_domain_search_result.css("display", "none");
    vnx_hidden_form_search.css("display", "block");
    }
  })

  var vnx_search_many_domain = $('textarea#vnx_search_many_domain');
  if (vnx_search_many_domain.length === 0) return;
  var TLD = $("input#vnx_suggest_tld");
  var list_domain_check_false = Array();
  var array_domain_total = Array();

  //show domain có đuôi là TLD
 
  var count = 0;
  var animate_loading = '<div class="w-full" id="suggestion_loading"><div class="flex justify-center w-full"><div class="vnx_loader-square vnx_square vnx_reg vnx_positioning"><div class="vnx_loading_block"><div class="vnx_loading_box"></div></div><div class="vnx_text_animate_loading">Loading...</div></div></div></div>';
  var animate_loading_timeout = `<div class="w-full" id="suggestion_loading"><div class="flex justify-center w-full"><div class="vnx_time_out"><img src="https://vietnix.vn/wp-content/uploads/2023/08/timeout.svg"><p class="text-center">Đã xảy ra lỗi trong quá trình tìm kiếm. </br> Vui lòng thử lại</p></div></div></div></div>`;

  function get_sessionStorage(name) {
    return JSON.parse(sessionStorage.getItem(name));
  }
  //--------------------------TYPE SEARCH--------------------------------------

  function auto_run_search() {
    $(".vnx_button_export").hide();
    setTimeout(() => {
      if(vnx_search_many_domain.val() == '') return;
    if (vnx_search_many_domain.length) {
      var array_domain = $.grep(vnx_search_many_domain.val().toLowerCase().split("\n"), function(value) {
        return $.trim(value.trim()) !== '';
      });
        // progress.attr('max', array_domain.length);
        searchManyDomain();
    }
    if($(".count_key").val() > 0 || vnx_search_many_domain.val().toLowerCase() !== ''){
      var array_domains = vnx_search_many_domain.val().toLowerCase().split("\n");
      var check = false;
      var array_domain = $.grep(array_domains, function(value) {
        return $.trim(value) !== '';
      });

      for (let i = 0; i < array_domain.length; i++) {  
        if($('#type_form_search').val() != 'whois_domain'){
          if (array_domain[i].includes('.') == false && !$(".vnx-button-ex").length) {
            check = true;
          }
        }
      }
      if(check == false){
        $(".submmit_form_search").prop("disabled", false);
        $(".hidden_tooltip_button_search").hide();
        $(".hidden_tooltip_input_search").hide();
      }else{
        $(".submmit_form_search").prop("disabled", false);
        $(".hidden_tooltip_button_search").hide();
        $(".hidden_tooltip_input_search").show();
      }

      }else{
      $(".submmit_form_search").prop("disabled", false);
      $(".hidden_tooltip_button_search").hide();
      $(".hidden_tooltip_input_search").hide();
      }

    }, 1000);


    
  }
  auto_run_search();

  //-----------------------------END TYPE SEARCH------------------------------------------------


  //--------------------------------SEARCH DOMAIN-----------------------------------------------
  if (vnx_search_many_domain.val().toLowerCase() !== '' && vnx_type_form_search.length && vnx_type_form_search.val() == "form_search_many_domain") {
    var timeOutID = setTimeout(showTimeOutSearchDomain, 20000);
    $("#suggestion_domain").addClass('search_many_domain')
  }

  if (vnx_search_many_domain.val().toLowerCase() !== '' && vnx_type_form_search.length && vnx_type_form_search.val() == "whois_domain") {
    var intervalId = setInterval(showTimeOutWhois, 20000);
  }

  function searchManyDomain() {
    var count = 0;
    var check_true = 0; 

    if (vnx_search_many_domain.val().toLowerCase() !== '') {
      $(".submmit_form_search").prop("disabled", false);
      $('#vnx_show_hide_domain_search_result').removeClass('hidden');
      $('.animate_dot').show();
      var array_domains = vnx_search_many_domain.val().toLowerCase().split("\n");
      var array_domain = $.grep(array_domains, function(value) {
        return $.trim(value) !== '';
      });

      showSelectDomain(array_domain);
      $('#suggestion_domain').html(animate_loading);
      for (let i = 0; i < array_domain.length; i++) {
        count++
        array_domain[i] = array_domain[i].trim()
        if (array_domain[i].includes('.') == false) {
          if (isValidKey(array_domain[i]) == true) {
            checkDomainHasTLD(array_domain[i].replace(/\s/g, ""));
            check_true++
          } else {
            list_domain_check_false.push(array_domain[i]);
          }
        } else {
          if (isValidDomain(array_domain[i]) == true) {
            checkDomainHasTLD(array_domain[i].replace(/\s/g, ""));
            check_true++
          } else {
            list_domain_check_false.push(array_domain[i]);
          }
        }
        if (vnx_type_form_search.length && vnx_type_form_search.val() == "whois_domain") {
            count_check.text(check_true);
          }
      }
      //đưa hết vào mảng và  post lên api 
      showResultDomain(array_domain_total);
      if (vnx_type_form_search.length && vnx_type_form_search.val() == "whois_domain") {
            count_check_domain.text(array_domain.length); 
          }

    }else{
      $(".submmit_form_search").prop("disabled", true);
    }

    if(count == list_domain_check_false.length || vnx_search_many_domain.val().toLowerCase() == ""){
      suggest_domain('domain', 'domain/vietnix-domain-result');
    }

    if (list_domain_check_false.length !== 0) {
      showDomainCheckFalse(list_domain_check_false);
    }

  }

  function suggest_domain(searchQuery, template) {
    var data = [{ 'domain_search': searchQuery, 'template': template }];
    $.ajax({
      type: 'POST',
      url: admin_ajax_url,
      data: {
        action: 'suggest_domain_center',
        data: data,
      },
      success: function (data) {
        $("#suggestion_loading").remove();
        $('.loading_domain').hide();
        $('#suggestion_domain').html(data);
      },
      error: function (xhr, status, error) {
      
      }
    });
    return
  }


  function checkDomainHasTLD(domain) {
    var white_space = /\s/g;
    if (!white_space.test(domain)) {
      if (domain.includes('.') == false) {
        if (TLD.val() !== "") {
          list_TLD = JSON.parse(TLD.val());
          list_TLD.map(function (e) {
            array_domain_total.push(domain + "." + e)
          });
        } else {
          var get_object_tld = JSON.parse(sessionStorage.getItem("TLD_Domain"));
          const ex_tld = Object.keys(get_object_tld['pricing']);
          if(vnx_type_form_search.val() != 'whois_domain'){
            ex_tld.map(function (e) {
              array_domain_total.push(domain + "." + e)
              }); 
          }
          else{
            array_domain_total.push(domain + "." + $('#default_tld_whois').val())
          }
                 
        }
      } else {
        if (isValidDomain(domain) == true) {
          array_domain_total.push(domain);
        }
        else {
          list_domain_check_false.push(domain);
        }
      }
    }
  }

  function chunkArray(array, size) {
    var chunks = [];
    for (var i = 0; i < array.length; i += size) {
      chunks.push(array.slice(i, i + size));
    }
    return chunks;
  }
  function removeDuplicates(arr) {
    var uniqueArray = [];
    for (var i = 0; i < arr.length; i++) {
        if (uniqueArray.indexOf(arr[i]) === -1) {
            uniqueArray.push(arr[i]);
        }
    }
    return uniqueArray;
  }

function showResultDomain(domain) {
    if (domain.length > 0 ) {
      domain = removeDuplicates(domain);
      var chunkedArray = chunkArray(domain, 5);
      let check_count = 0;
      chunkedArray.map((e) => {
        setTimeout(function () {
          var data = {
            "action": "DomainWhois",
            "data": {
              security: $('#vnx_domain_suggest_security').val(),
              "domain": e,
            }
          }

          if (vnx_type_form_search.length && vnx_type_form_search.val() == "form_search_many_domain"){
            $.when(post_ajax_search_multipe('POST', 'get_api_whmcs_checkAllDomain_center', data).then(function (data_response) {
              if(data_response.length) {
                data_response.map((e)=>{
                  var data_parse = JSON.parse(e);
                  var data_domain = data_parse.domain;
                  var data_reponse = data_parse.reponse;
                  if (data_reponse !== null && data_reponse.status == '1' && data_reponse.result == 'success') {
                    showDomainUnavailable(data_domain, "Chưa thể đăng ký tên miền .vn có 1,2 kí tự",1);
                  } else if (data_reponse !== null && data_reponse.status == 'unavailable' && data_reponse.result == 'success') {
                    showDomainUnavailable(data_domain, "Tên miền đã được đăng ký",2);
                  }else if (data_reponse !== null && data_reponse.status == 'available' && data_reponse.result == 'success') {
                    if(data_reponse.premium != null && data_reponse.premium.status == 'available' && data_reponse.premium.costHash != undefined){
                      showDomainUnavailable(data_domain, "Tên miền đặc biệt",'premium');
                    } else {
                      getPriceAndShowDomain(data_domain);
                    }
                  }else if(data_reponse !== null && data_reponse.result == "error"){
                    showDomainUnavailable(data_domain,  "Tên miền không hợp lệ", "error");
                  }else{
                    showResultDomain([data_domain]);
                  }

                });
              }
              clearTimeout(timeOutID);
            }));
            } 
            
            if (vnx_type_form_search.length && vnx_type_form_search.val() == "whois_domain") {
              progress.attr('max', domain.length);
              $('#vnx_show_hide_domain_search_result').removeClass('hidden');
              $.when( post_ajax_search_multipe('POST', 'get_api_whmcs_checkAllDomain_center', data).then(function (data_response) {
                if(data_response.length) {
                  progress.val(progress.val() + data_response.length);
                  check_count++;
                    data_response.map((e)=>{
                      var data_parse = JSON.parse(e);
                      var data_domain = data_parse.domain;
                      var data_reponse = data_parse.reponse;
                        if (data_reponse !== null && data_reponse.status == '1' && data_reponse.result == 'success') {
                          showWhoisDomainUnavailable(data_domain, "Chưa thể đăng ký tên miền .vn có 1,2 kí tự",'error_vn');
                        } else if (data_reponse !== null && data_reponse.status == 'unavailable' && data_reponse.result == 'success') {
                          showWhoisDomainUnavailable(data_domain , "Tên miền đã được đăng ký");
                        }else if (data_reponse !== null && data_reponse.status == 'available' && data_reponse.result == 'success') {
                          if(data_reponse.premium != null && data_reponse.premium.status == 'available' && data_reponse.premium.costHash != undefined){
                            showWhoisDomainUnavailable(data_domain, "Tên miền đặc biệt",'premium');
                          } else {
                            getPriceAndShowWhoisDomain(data_domain);
                          }
                        }else if (data_reponse !== null && data_reponse.result == "error"){
                          showWhoisDomainUnavailable(data_domain,  "Tên miền không hợp lệ", "error");
                        }else{
                          showResultDomain([data_domain]); 
                        }
                      })
                      if(check_count == chunkedArray.length){
                        $('.animate_dot').remove();
                        $(".vnx_button_export").show();
                        $(".text_result").text("Đã tra cứu");
                      }
                }
              }));
            }

        }, 1500);

      });
    }
  }

  function getPriceAndShowDomain(domain) {
    var data = JSON.parse(sessionStorage.getItem("TLD_Domain"));
      if (data !== null) {
        var array_domain = splitDomain(domain);
        if (data['pricing'][array_domain[1]]) {
          showResultHasTLD(domain, data);
        }
      }
  }

  function splitDomain(domain) {
    var parts = domain.split(".");
    var firstPart = parts[0];
    var secondPart = parts.slice(1).join(".");
    return [firstPart, secondPart];
  }

  //show domain không khả dụng hoặc đã có người sử dụng
  function showDomainUnavailable(domain, message, status = null) {
    count++;
    var parse_domain = domain.split('.');
    var firstDomain = parse_domain.shift();
    var secondDomain = parse_domain.join(".");
    var kq = '';
    var tool_tip_html = '';
    var data_tooltip_des ;
    var sort = false;
    var special = false;
    if(status == 1){
      data_tooltip_des = `Theo quy định của Trung tâm Internet Việt Nam (VNNIC) tên miền cấp 2 có 1,2 kí tự hiện chưa thể đăng ký. Quý khách có thể tham khảo tại đây: <a href=""><strong>Xem chi tiết</strong></a>`;
      tool_tip_html = `<div class="search_tooltip ml-1 cursor-pointer">
      <img class="search_tooltip_icon" src = "https://vietnix.vn/wp-content/uploads/2023/06/circle-info-red.svg" alt="doamin info red"/>
      <div class="tool_tip_search absolute">
        <div class="tooltiptext_search w-full relative">`
      tool_tip_html+='<div class="text-xs text-left font-normal mt-1 w-full">'+data_tooltip_des+'</div>'
      tool_tip_html+='</div></div></div>'
      kq = `<div class="vnx_result_item p-3.5 md:py-6 md:px-7 flex flex-row items-center justify-between border-b flex-wrap">
              <div class="vnx_first md:min-w-[15rem] mr-1.5 md:mr-3">
                <p class="domains break-all">`+ firstDomain + `<span class="dots">.` + secondDomain + `</span></p>
              </div>
              <div class="vnx_second gap-x-3 lg:gap-x-5 flex flex-row justify-between items-center grow">
                <div class="info vnx_info flex relative">`+message+tool_tip_html+`</div>
              </div>
            </div>`; // box
    } else if(status == 'premium'){
      sort = true;
      special = true;
      data_tooltip_des = 'Một số tên miền đặc biệt vui lòng liên hệ Vietnix để được hỗ trợ đăng ký';
      tool_tip_html = `<div class="search_tooltip ml-1 cursor-pointer">
      <img class="search_tooltip_icon" src = "https://vietnix.vn/wp-content/uploads/2023/06/circle-info-red.svg" alt="doamin info red"/>
      <div class="tool_tip_search absolute">
        <div class="tooltiptext_search w-full relative">`
      tool_tip_html+='<div class="text-xs text-left font-normal mt-1 w-full">'+data_tooltip_des+'</div>'
      tool_tip_html+='</div></div></div>'
      kq = `<div class="vnx_result_item p-3.5 md:py-6 md:px-7 flex flex-row items-center justify-between border-b flex-wrap">
              <div class="vnx_first md:min-w-[15rem] mr-1.5 md:mr-3">
                <p class="domains break-all">`+ firstDomain + `<span class="dots">.` + secondDomain + `</span></p>
              </div>
              <div class="vnx_second gap-x-3 lg:gap-x-5 flex flex-row justify-between items-center grow">
                <div class="info vnx_info flex bg-[#FFF0BA] text-[#CE6A00] relative">`+message+tool_tip_html+`</div>
                <button class="vnx_btn btn_tawk w-10 h-10 md:w-36">
                  <span class="hidden md:block p-3">Liên hệ</span>
                  <img class="m-auto md:hidden block" src="https://vietnix.vn/wp-content/uploads/2023/08/support_chat.svg" alt="add to cart icon">
                </button>
              </div>
            </div>`; // box
                
    } else if(status == 2){
      kq = `<div class="vnx_result_item p-3.5 md:py-6 md:px-7 flex flex-row items-center justify-between border-b flex-wrap">
          <div class="vnx_first md:min-w-[15rem] mr-1.5 md:mr-3">
            <p class="domains break-all">`+ firstDomain + `<span class="dots">.` + secondDomain + `</span></p>
          </div>
          <div class="vnx_second gap-x-3 lg:gap-x-5 flex flex-row justify-between items-center grow">
            <div class="info vnx_info flex relative">`+message+`</div>
          </div>
        </div>`; // box
    }else if(status == 'error'){
      data_tooltip_des = `Hiện tại tên miền không khả dụng. Quý khách vui lòng tìm kiếm lại với một tên miền khác.`;
      tool_tip_html = `<div class="search_tooltip ml-1 cursor-pointer">
      <img class="search_tooltip_icon" src = "https://vietnix.vn/wp-content/uploads/2023/06/circle-info-red.svg" alt="doamin info red"/>
      <div class="tool_tip_search absolute">
        <div class="tooltiptext_search w-full relative">`
      tool_tip_html+='<div class="text-xs text-left font-normal mt-1 w-full">'+data_tooltip_des+'</div>'
      tool_tip_html+='</div></div></div>'
      kq = `<div class="vnx_result_item p-3.5 md:py-6 md:px-7 flex flex-row items-center justify-between border-b flex-wrap">
              <div class="vnx_first md:min-w-[15rem] mr-1.5 md:mr-3">
                <p class="domains break-all">`+ firstDomain + `<span class="dots">.` + secondDomain + `</span></p>
              </div>
              <div class="vnx_second gap-x-3 lg:gap-x-5 flex flex-row justify-between items-center grow">
                <div class="info vnx_info flex relative">`+message+tool_tip_html+`</div>
              </div>
            </div>`; // box
    } else {
      tool_tip_html = `<div class="search_tooltip ml-1 cursor-pointer">
      <img class="search_tooltip_icon" src = "https://vietnix.vn/wp-content/uploads/2023/06/circle-info-red.svg" alt="doamin info red"/>`;
      tool_tip_html+='</div>'

      kq = `<div class="vnx_result_item p-3.5 md:py-6 md:px-7 flex flex-row items-center justify-between border-b flex-wrap">
          <div class="vnx_first md:min-w-[15rem] mr-1.5 md:mr-3">
            <p class="domains break-all">`+ firstDomain + `<span class="dots">.` + secondDomain + `</span></p>
          </div>
          <div class="vnx_second gap-x-3 lg:gap-x-5 flex flex-row justify-between items-center grow">
            <div class="info vnx_info flex relative">`+message+tool_tip_html+`</div>
          </div>
        </div>`; // box
    }
    count_result_search.text(count);
    data_array_result.push(kq);
    addDataToHTML(sort,special);
  }

  var data_array_result = Array(); // Lưu trữ dữ liệu để in ra
  function showResultHasTLD(domain, json) {
    if (json !== null) {
  
      var data_parse = json;
      var parse_domain = domain.split('.');
      var firstDomain = parse_domain.shift();
      var secondDomain = parse_domain.join(".");

      var kq = ``;
      if (data_parse.result != 'success') {
        
        kq += `<div class="box">
              <div class="box_general flex flex-row">
              <div class="sub_box first">
                <p class="domains inline-flex items-center">`+ firstDomain + `<span class="dots">.` + secondDomain + `</span></p>
              </div>
                <div class="sub_box second flex flex-row justify-end">
                <span class="info float-right">Tên miền đã được đăng ký</span>
                </div>
              </div></div>`;
      }

      if (data_parse.result == 'success') {
          count++;
          var data_price = data_parse["pricing"];
          var array_price = data_price["" + secondDomain + ""];
          var price1 = (array_price["register"][1] && array_price["register"][1] !== "") ? formatPriceVn(array_price["register"][1]) + "đ" : "";
          var money = array_price["register"][1].split('.')[0];
          var tool_tip_html = '';
          var tld_data_price = '';
          var tld_data_price_nodot = '';
          var data_price_index = (tld_data.length > 0) ? tld_data[0].indexOf(domain_annual_price) : -1;
          var data_tooltip_title = (tld_data.length > 0) ? tld_data[0].indexOf(domain_tooltip_title) : -1;
          var data_tooltip_des = (tld_data.length > 0) ? tld_data[0].indexOf(domain_tooltip_des) : -1;
          
          if(findObjectByTLD(secondDomain,tld_data) !== -1 ){
            tld_data_price = tld_data[findObjectByTLD(secondDomain,tld_data)][data_price_index].replace(/\,/g, '');
            tld_data_price_nodot = tld_data_price.replace(/\./g, '')
            if(tld_data[findObjectByTLD(secondDomain,tld_data)][data_tooltip_title] !== ""){
              tool_tip_html+='<div class="tooltip relative"><i class="vnx_icon_info"></i><div class="tooltiptext bg-white text-black text-center py-1.5 px-2 rounded-md absolute w-full">'
              tool_tip_html+='<div class="text-xs text-left font-bold w-full">'+tld_data[findObjectByTLD(secondDomain,tld_data)][data_tooltip_title]+'</div>'
              tool_tip_html+='<div class="text-xs text-left font-normal mt-1 w-full">'+tld_data[findObjectByTLD(secondDomain,tld_data)][data_tooltip_des]+'</div>'
              tool_tip_html+='</div></div>'
            }
          }
          var persent_html = ''
          var tld_price_html = ''
          if(tld_data_price !== ''){
            if(!(money == tld_data_price_nodot)){
              var percent = persen_calculate(money, tld_data_price_nodot)
              persent_html = '<span class="discount"> -'+percent+'%</span>'
              var annual_price_dot = tld_data_price.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1.")
              tld_price_html = '<small class="line-through text-gray-400">'+annual_price_dot+' đ</small>'
            }
          }
          kq = `<div class="vnx_result_item domain_available p-3.5 md:py-6 md:px-7 flex flex-row items-center justify-between border-b flex-wrap">
                  <div class="vnx_first md:min-w-[15rem] mr-1.5 md:mr-3">
                    <p class="domains break-all">`+ firstDomain + `<span class="dots">.` + secondDomain + `</span></p>`
          if(persent_html != '') kq += '<div class="lg:hidden">'+persent_html+'</div>';
            kq += `</div>
              <div class="vnx_second gap-x-3 lg:gap-x-5 flex flex-row justify-between items-center grow">
                <div class="vnx_info block relative">
                  <span>`+ price1 + `<span class="year">/năm</span>` + persent_html + `</span><br>
                  `+tld_price_html+`
                </div>
                <button class="vnx_btn add_domain_cart w-10 h-10 md:w-36" data-domain="`+ domain + `">
                  <span class="hidden md:block p-3">Thêm vào giỏ hàng</span>
                  <img class="m-auto md:hidden block" src="https://vietnix.vn/wp-content/uploads/2023/08/add_cart_mobile.svg" alt="add to cart icon">
                </button>
              </div>
            </div>`; // box
      }
      count_result_search.text(count);
      data_array_result.push(kq);
      addDataToHTML(true);
    }
  }




  var select_domain = $("#select_domain");
  function showSelectDomain(data) {
    var temp = 0;
    var option = '';
    let newArr = data.filter(element => element.trim() !== "");
    newArr.map(function (e) {
      temp++;
      if (temp == 1) {
        option += `<option>` + newArr.length + ` tên miền </option>`;
        option += `<option>` + e + `</option>`;
      } else {
        option += `<option>` + e + `</option>`;
      }
    });
    $("#select_domain").html(option);
    $("#vnx-textarea-search").val(vnx_search_many_domain.val().toLowerCase().trim());
    $(".vnx-custom-result-domain").text('Đã nhận ' + data.length + ' tên miền')
  }



  select_domain.on("click", function () {
    if ($("#vnx-textarea-search").length) {
      if (vnx_type_form_search.length && vnx_type_form_search.val() == "form_search_many_domain"){
      vnx_domain_search_result.css("display", "none");
      vnx_hidden_form_search.css("display", "block");
      $("#vnx-textarea-search").val(vnx_search_many_domain.val().toLowerCase().trim());
      }
    }
  })

  //show kêt quả false 
  function showDomainCheckFalse(data) {
    if ($(".result_keyword_false").length && data.length > 0) {
      data.map(function (e) {
        if(e!=""){
          $(".result_keyword_false").removeClass("hidden");
        $(".key_word_check_false").append(`<small class="info px-1.5 mr-1"><span class="custom_text_middle">` + e + `</span></small>`);
        }
      })
    }
  }


  function formatPriceVn(price) {
    return Math.round(price).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }



  function isValidDomain(input) {
    var pattern = /^(?:[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*\.)+[a-zA-Z]{2,}$/g;
    return pattern.test(input);
  }


  function isValidKey(input) {
    var pattern = /^[a-zA-Z0-9]+$/g;
    return pattern.test(input);
  }

  //-------------------------------END SEARCH DOMAIN----------------------------------------------



  //-------------------------WHOIS DOMMAIN---------------------------------------

  function showWhoisDomainUnavailable(domain, message, status = null) {
    count++;
    var parse_domain = domain.split('.');
    var firstDomain = parse_domain.shift();
    var secondDomain = parse_domain.join(".");
    var nofollow =''
    var target="";
    if(vnx_link_see_whois.attr("data-rel") == "on"){
      nofollow="nofollow";
    }
    if(vnx_link_see_whois.attr("data-target") == "on"){
      target="_blank";
    }
    var tooltip="";
    var kq = `<div class="frame-item">
      <div class="vnx-frame-row-item" data-domain="`+ domain + `" data-status="`+message+`">
        <div class="vietnix-domain-no-available">
          <span><span class="vietnix-domain-no-available-name">`+firstDomain+`</span><span class="vietnix-domain-no-available-tld">.`+secondDomain+`</span></span>
        </div>
        <div class="vnx-frame-item-end-name">
          <div ><img src="https://vietnix.vn/wp-content/uploads/2023/08/Error.svg"></div>
          <div class="vnx-whois-message">`+message+`</div>
        </div>
      </div>`;
      if (status == "error_vn")
      {
        var data_tooltip_des = 'Theo quy định của Trung tâm Internet Việt Nam (VNNIC) tên miền cấp 2 có 1,2 kí tự hiện chưa thể đăng ký. Quý khách có thể tham khảo tại đây: <a href=""><strong>Xem chi tiết</strong></a>';
        tooltip=`
        <div class="tool_tip cursor-pointer relative inline-flex w-3.5 h-3.5 ml-1 border rounded-full items-center justify-center">
                          <img src="https://vietnix.vn/wp-content/themes/vietnix-wp-theme/assets/images/icons/exclamation-icon.svg" class="vnx_tooltip_icon h-2.5 opacity-70">
                          <div class="absolute hidden tool_tip_text cursor-default">
                          <div class="tooltip_content relative z-[1]">
                            <p class="tooltip_content_text" >`+data_tooltip_des+`</p> 
                          </div>
                        </div>
        `;
         kq = `<div class="frame-item">
          <div class="vnx-frame-row-item" data-domain="`+ domain + `" data-status="`+message+`">
            <div class="vietnix-domain-no-available">
              <span><span class="vietnix-domain-no-available-name">`+firstDomain+`</span><span class="vietnix-domain-no-available-tld">.`+secondDomain+`</span></span>
            </div>
            <div class="vnx-frame-item-end-name status ml-1">
              <div ><img src="https://vietnix.vn/wp-content/uploads/2023/08/Error.svg"></div>
              <div class="vnx-whois-message">`+message+`</div>`+tooltip+`
              </div>
            </div>
          </div>
        </div>`;    
    }else if (status == null)
    {
        kq+=`<a href="`+vnx_link_see_whois.val()+``+domain+`" rel="`+nofollow+`" target="`+target+`" class="frame-see-whois">
            <div class="xem-whois">Xem whois</div>
            </button></a>`;
    }
    else if(status == 'premium'){
      var data_tooltip_des = 'Một số tên miền đặc biệt vui lòng liên hệ Vietnix để được hỗ trợ đăng ký';
      tooltip=`
        <div class="tool_tip cursor-pointer relative inline-flex w-3.5 h-3.5 ml-1 border rounded-full items-center justify-center">
                          <img src="https://vietnix.vn/wp-content/themes/vietnix-wp-theme/assets/images/icons/exclamation-icon.svg" class="vnx_tooltip_icon h-2.5 opacity-70">
                          <div class="absolute hidden tool_tip_text cursor-default">
                          <div class="tooltip_content relative z-[1]">
                            <p class="tooltip_content_text" >`+data_tooltip_des+`</p> 
                          </div>
                        </div>
        `;
      kq = `
      <div class="frame-item">
          <div class="vnx-frame-row-item" data-domain="`+ domain + `" data-status="`+message+`">
            <div class="vietnix-domain-no-available">
              <span><span class="vietnix-domain-no-available-name">`+firstDomain+`</span><span class="vietnix-domain-no-available-tld">.`+secondDomain+`</span></span>
            </div>
            <div class="vnx-frame-item-end-name status ml-1">
              <div ><img src="https://vietnix.vn/wp-content/uploads/2023/08/Error.svg"></div>
              <div class="vnx-whois-message">`+message+`</div>`+tooltip+`
              </div>
            </div>
          </div>
          <div >
          <button class="vnx-button-register btn_tawk frame-see-whois-contact" >
            <div class="vnx-button-register-text">Liên hệ</div>
          </button>
        </div>
        </div>`;    
    }
    data_array_result.push(kq);
    addDataToHTML();
  }

  function getPriceAndShowWhoisDomain(domain) {
    var data = JSON.parse(sessionStorage.getItem("TLD_Domain"));
      if (data !== null) {
        var array_domain = splitDomain(domain);
        if (data['pricing'][array_domain[1]]) {
          showResultWhoisDomain(domain, data);
        }
    }
  }




  function showResultWhoisDomain(domain, json) {
    if (json !== null) {
      var data_parse = json;
      var parse_domain = domain.split('.');
      var firstDomain = parse_domain.shift();
      var secondDomain = parse_domain.join(".");

      var kq = ``;
      
    
      if (data_parse.result == 'success') {
          // count++;
          var data_price = data_parse["pricing"];
          var array_price = data_price["" + secondDomain + ""];
          var price1 = (array_price["register"][1] && array_price["register"][1] !== "") ? formatPriceVn(array_price["register"][1]) + "đ" : "";
          
          var money = array_price["register"][1].split('.')[0];
          var tool_tip_html = '';
          var tld_data_price = '';
          var tld_data_price_nodot = '';
  
          var data_price_index = (tld_data.length > 0) ? tld_data[0].indexOf(domain_annual_price) : -1;
          var data_tooltip_title = (tld_data.length > 0) ? tld_data[0].indexOf(domain_tooltip_title) : -1;
          var data_tooltip_des = (tld_data.length > 0) ? tld_data[0].indexOf(domain_tooltip_des) : -1;
          if(findObjectByTLD(secondDomain,tld_data) !== -1 ){
            tld_data_price = tld_data[findObjectByTLD(secondDomain,tld_data)][data_price_index].replace(/\,/g, '');
            tld_data_price_nodot = tld_data_price.replace(/\./g, '');

            if(tld_data[findObjectByTLD(secondDomain,tld_data)][data_tooltip_title] !== ""){
              tool_tip_html+='<div class="tooltip relative"><img src="https://vietnix.vn/wp-content/uploads/2023/08/Frame-1000002199-1.svg"><div class="tooltiptext bg-white text-black text-center  px-2 rounded-md absolute w-full">'
              tool_tip_html+='<div class="text-xs text-left font-bold w-full">'+tld_data[findObjectByTLD(secondDomain,tld_data)][data_tooltip_title]+'</div>'
              tool_tip_html+='<div class="text-xs text-left font-normal mt-1 w-full">'+tld_data[findObjectByTLD(secondDomain,tld_data)][data_tooltip_des]+'</div>'
              tool_tip_html+='</div></div>'
            }
          }
          var persent_html = '';
          var tld_price_html = '';
        
          if(tld_data_price !== ''){
            if(!(money == tld_data_price_nodot)){
              var percent = persen_calculate(money, tld_data_price_nodot)
              persent_html = '<span class="discount"> -'+percent+'%</span>';
              var annual_price_dot = tld_data_price.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1.")
              tld_price_html = annual_price_dot+' đ';
            }
          }
            kq+=`<div class="frame-item">
              <div class="vnx-frame-row-item" data-domain="`+ domain + `" data-status="Tên miền có thể đăng ký">
                <div class="vietnix-domain">
                  <span class="vietnix-com-span">`+firstDomain+`</span><span class="vietnix-com-span2">.`+secondDomain+`</span>
                </div>
                <div class="vnx-frame-item-end-name">
                  <div class="check"><img src="https://vietnix.vn/wp-content/uploads/2023/08/Check-1.svg"></div>
                  <div class="vnx-message-available">Tên miền có thể đăng ký</div>
                </div>
              </div>
              <div >
                <div class="vnx-frame-item-end">
                  <div class="vnx_price_whois">
                    <span><span class="price-cost">`+tld_price_html+`</span>
                    <span class="price-discount">`+price1+` </span></span>
                  </div>
                  <div class="vnx-frame-icon">
                    <div class="vnx-frame-icon-cricle">
                      <div>`+tool_tip_html+`</div>
                    </div>
                  </div>
                </div>
                <button class="vnx-button-register add_domain_cart_whois"  data-domain="`+ domain + `">
                  <div class="vnx-button-register-text">Đăng ký ngay</div>
                </button>
              </div>
            </div>`;
      }
      // count_result_search.text(count);
      data_array_result.push(kq);
      addDataToHTML(true);
    }
  }

  //----------------------------END WHOIS DOMAIN---------------------------------

  //----------------------------EXPORT XLSX --------------------------------------
    $(vnx_button_export_csv_download).on("click", () => {
      var data_domain_export = Array();
      $("#suggestion_domain .frame-item .vnx-frame-row-item").map(function() {
        var domain = $(this).attr("data-domain");
        var status_domain = $(this).attr("data-status")
        data_domain_export.push( { "Domain" : domain, "Trạng thái": status_domain })
      }).get();
      downloadXLSX(data_domain_export);
    })
  //----------------------------END EXPORT CSV ----------------------------------

  function addDataToHTML(sort = false, special = false) {
    if (data_array_result.length > 0) {
        $("#suggestion_loading").remove();
        $('.loading_domain').hide();
        if (sort == true) {
          last_available = $("#suggestion_domain").find('.domain_available').last();
          if(special ==true && $(last_available).length != 0){
            $(last_available).after(data_array_result[0]);
          } else {
            $("#suggestion_domain").prepend(data_array_result[0]);
          }
        }else {
          $("#suggestion_domain").append(data_array_result[0]);
        }
        $("#suggestion_domain").addClass('loaded');
      data_array_result = data_array_result.slice(1);
      setTimeout(addDataToHTML, 0);
    }
  }

  function persen_calculate(discount_price, price) {

    var percent = 100 - ((parseInt(discount_price) / parseInt(price)) * 100);
    return parseFloat(percent).toFixed(0)
  }

  function findObjectByTLD(tld, myArray) {
    foundIndex = myArray.findIndex(obj => obj[0] === tld);
    if (foundIndex !== -1) {
      return foundIndex;
    }
    else{
      return -1;
    }
  }

  function showTimeOutWhois() {
    if(!$("#suggestion_domain .frame-item").length) {
      $("#suggestion_loading").remove();
      $('.loading_domain').hide();
      $('#suggestion_domain').html(animate_loading_timeout);
      clearInterval(intervalId);
      abortActiveRequests();
    }
  }

  function showTimeOutSearchDomain() {
    if(!$("#suggestion_domain .box").length){
      $("#suggestion_loading").remove();
      $('.loading_domain').hide();
      $('#suggestion_domain').html(animate_loading_timeout);
      clearTimeout(timeOutID);
      abortActiveRequests();
    }
  }
});


  //----------------------------Hủy tất cả yêu cầu ajax đang chạy-----------------------------------------

  function abortActiveRequests() {
    if ($.active > 0) {
      $(document).ajaxStop(function() {
        $(document).off("ajaxStop");
      });
  
      $(document).ajaxSend(function(event, jqXHR, ajaxOptions) {
        jqXHR.abort();
      });
    }
  }

  //----------------------------End hủy tất cả yêu cầu ajax đang chạy-----------------------------------------

