<?php wp_nonce_field('domain_suggest_checking', 'vnx_domain_suggest_security'); ?>
<div class=" gap-8 w-full bg-white ">
  <div id="suggestion_domain" class=" ">
    <div class="w-full h-full bg-white border border-gray-200 rounded-lg shadow  items-center justify-center" v-show="ResultEmpty == true "style="display: none;">
      <div class="flex flex-col items-center pb-10">
        <img class="w-20 h-20 m-4" src="https://vietnix.vn/wp-content/uploads/2023/06/none_domain.svg" alt="none domain" />
        <h5 class="mb-1 text-lg text-center font-medium text-[#000000]">Không tìm thấy kết quả phù hợp</h5>
        <span class="text-sm text-center font-normal text-[#828282]">Bạn vui lòng nhập tên miền để kiểm tra vào ô tìm
          kiếm</span>
      </div>
    </div>
    <div class="flex justify-center w-full h-full bg-white border border-gray-200 rounded-lg shadow" v-if="ResultError == true ">
      <div class=" py-5 flex flex-col items-center gap-2"><img class="mx-auto" src="https://vietnix.vn/wp-content/uploads/2023/08/timeout.svg">
        <p class="text-center">Đã xảy ra lỗi trong quá trình tìm kiếm. <br> Vui lòng thử lại.</p>
        <button class="rounded flex items-center vnx_reload_searchdomain" onclick="window.location.reload();" id="reloadButton_searchDomain">
          <i class="fa-solid fa-rotate-right"></i> Tải lại trang
        </button>
      </div>
    </div>
    <div class="vnc_loading_result w-full flex items-center justify-center gap-2" v-if="IsLoading &&  ResultEmpty == false && ResultError == false" style="display: none;">
      <p>Loading</p>
      <svg aria-hidden="true" role="status" class="inline w-4 h-4  animate-spin " viewBox="0 0 100 101" fill="red" xmlns="http://www.w3.org/2000/svg">
        <path
          d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
          fill="#E5E7EB" />
        <path
          d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
          fill="#3498db" />
      </svg>  
    </div>

    <div class="vnx_custom_result" v-show="IsLoading == false" style="display: none;">
      <div id="show-domain-suggest" class="mb-3">
        <div class="box" v-for="(domain, index) in ListDomain" :key="index">
          <div class="box_general flex flex-row justify-between">
            <div class="sub_box first flex items-center flex-wrap">
              <p class="domains inline-flex items-center break-all" style="max-width: 85%"><span>{{domain.sld}}<span class="dots" style="word-break: keep-all">.{{domain.tld}}</span> </span></p>
              <div class="w-full lg:hidden" v-if="domain.isPremium == false"><span class="w-fit rounded bg-[#EB5757] py-1 px-5 text-xs font-medium text-white" v-if="calculatePriceDomain(formatPrice(domain.pricing.register['1']), getPriceDomainData(domain.tld)) > 0">-{{calculatePriceDomain(formatPrice(domain.pricing.register['1']), getPriceDomainData(domain.tld))}}%</span></div>
            </div>
            <div class="sub_box second flex flex-row justify-between items-center">
              <div class="text_sale_price" v-if=" domain.isPremium == false">
                <span>
                  {{formatPrice(domain.pricing.register['1'])}} VND/<span class="year">năm</span>
                </span>
                <span class="discount hidden lg:inline-flex" v-if="calculatePriceDomain(formatPrice(domain.pricing.register['1']), getPriceDomainData(domain.tld)) > 0">
                  -{{calculatePriceDomain(formatPrice(domain.pricing.register['1']), getPriceDomainData(domain.tld))}}%
                </span><br>
                <small class="line-through text-gray-400" v-if="calculatePriceDomain(formatPrice(domain.pricing.register['1']), getPriceDomainData(domain.tld)) > 0">{{getPriceDomainData(domain.tld)}} đ</small>
              </div>
              <div class="vnx_box_status" v-if="domain.isPremium == true">
                <div class="flex sm:mt-0 lg:mt-3 lg:ml-2 w-fit">
                  <div class="inline-flex lg:px-3 lg:py-1 md:p-1 items-center text-sm text-[#CE6A00] rounded-full bg-[#FFF0BA]" role="alert">
                    <div class="mr-1 relative">
                      <span class="font-normal">Tên miền đặc biệt <i class="vnx_tooltip_icon fal fa-info-circle"></i></span>
                      <div class="vnx_tooltip_domaincontact">
                        <p class=" font-normal italic" style="white-space: initial;">Một số tên miền đặc biệt vui lòng liên hệ Vietnix để được hỗ trợ đăng ký</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="flex flex-col mb:min-w-10 mb:max-w-10 mb:max-h-10">
                <!-- đã có trong giỏ hàng  -->
                <div class="button bg-transparent vnx_haveto_cart" v-if="domain.isInCart && domain.isPremium == false">
                  <button class="button_detail add_domain_cart cursor-not-allowed text-white font-medium rounded-lg max-md:w-full text-sm px-3 py-2.5 focus:outline-none add_domain_cart lg:bg-[#81AFD3] bg-[#38A7FF] lg:opacity-100 opacity-50"
                    :data-domain="domain.domainName" disabled="">
                    <span class="hidden lg:block"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline-block">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                      </svg> Đã thêm</span>
                    <img class="m-auto lg:hidden block" src="https://vietnix.vn/wp-content/uploads/2023/08/added_cart_icon.svg" alt="added to cart icon">
                  </button>
                </div>
                <!-- chưa có trong giỏ hàng  -->
                <div class="button bg-transparent vnx_addto_cart" v-if="!domain.isInCart && domain.isPremium == false">
                  <button class="button_detail add_domain_cart text-white bg-[#38A7FF] hover:bg-[#38A7FF] font-medium rounded-lg max-md:w-full text-sm px-3 py-2.5 focus:outline-none" :data-sld="domain.sld" :data-tld="domain.tld"
                    @click="clickAddToCart(domain.sld,domain.tld)" :data-domain="domain.domainName">
                    <span class="hidden lg:block">Thêm vào giỏ hàng</span>
                    <img class="m-auto lg:hidden block" src="https://vietnix.vn/wp-content/uploads/2023/08/add_cart_mobile.svg" alt="add to cart icon">
                  </button>
                </div>
                <!-- Liên hệ  -->
                <div class="button bg-transparent vnx_contact_cart" v-if="domain.isPremium == true">
                  <button type="button" data-domain="" class=" btn_tawk  border border-[#38A7FF] text-[#38A7FF] text-sm rounded-lg " >
                    <span class="max-md:hidden lg:block">Liên hệ</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40" fill="none" >
                      <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M20.0551 7.00684C17.5377 6.923 14.9944 7.60981 12.7986 9.07553C10.3324 10.7235 8.60886 13.157 7.815 15.8775C7.59577 15.8507 7.33325 15.8625 7.02153 15.9507C5.85769 16.2809 4.98236 17.2346 4.58877 18.0745C4.07829 19.169 3.863 20.6166 4.0901 22.1111C4.31562 23.6008 4.94497 24.8288 5.73214 25.5912C6.52167 26.3539 7.41314 26.6019 8.29871 26.4157C9.61722 26.1343 10.271 25.9238 10.0864 24.6954L9.19254 18.7408C9.37281 15.5181 11.0412 12.4844 13.8317 10.6188C17.5669 8.12383 22.4639 8.28363 26.0266 11.0187C28.505 12.9189 29.944 15.7661 30.1101 18.755L29.485 22.9203C28.091 26.7353 24.6349 29.3779 20.6357 29.7529H17.9727C17.2855 29.7529 16.7321 30.3063 16.7321 30.9927V31.6469C16.7321 32.3337 17.2855 32.8871 17.9727 32.8871H21.3303C22.0171 32.8871 22.5682 32.3337 22.5682 31.6469V31.3049C25.5838 30.5689 28.2161 28.7155 29.9302 26.1308L31.0059 26.4161C31.8812 26.6432 32.7833 26.3539 33.5724 25.5916C34.3596 24.8288 34.9886 23.6012 35.2145 22.1115C35.4424 20.617 35.2208 19.1718 34.7162 18.0749C34.2096 16.9779 33.4551 16.2813 32.5841 16.0314C32.2193 15.9263 31.8233 15.8877 31.4837 15.8775C30.7658 13.4176 29.2879 11.1804 27.1578 9.54704C25.0635 7.94003 22.5725 7.08949 20.0551 7.00684Z"
                        fill="white" />
                      <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M23.9852 17.7198C24.8727 17.7198 25.5922 18.4392 25.5942 19.3291C25.5922 20.2167 24.8727 20.9381 23.9852 20.9381C23.0953 20.9381 22.3738 20.2167 22.3738 19.3291C22.3738 18.4396 23.0957 17.7198 23.9852 17.7198ZM19.6518 17.7198C20.5413 17.7198 21.2608 18.4392 21.2608 19.3291C21.2608 20.2167 20.5413 20.9381 19.6518 20.9381C18.7615 20.9381 18.042 20.2167 18.042 19.3291C18.042 18.4396 18.7615 17.7198 19.6518 17.7198ZM15.32 17.7198C16.2075 17.7198 16.929 18.4392 16.929 19.3291C16.929 20.2167 16.2075 20.9381 15.32 20.9381C14.4305 20.9381 13.7106 20.2167 13.7106 19.3291C13.7106 18.4396 14.4305 17.7198 15.32 17.7198ZM19.6518 10.8076C14.9327 10.8076 11.1299 14.4853 11.1299 19.3291C11.1299 21.6556 12.0095 23.7117 13.4426 25.2203L12.9341 27.5C12.7664 28.2502 13.2867 28.7547 13.9609 28.3793L16.1871 27.1375C17.245 27.5968 18.4152 27.8507 19.6518 27.8507C24.3725 27.8507 28.1729 24.1754 28.1729 19.3291C28.1729 14.4853 24.3725 10.8076 19.6518 10.8076Z"
                        fill="white" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

           <!-- Load more -->
      <div class="w-full flex items-center justify-center gap-2 text-[#007CFC] font-medium" v-if="IsLoadingMore"
        style="display: none;">
        <p>Đang tải thêm</p>
        <svg aria-hidden="true" role="status" class="inline w-4 h-4  animate-spin " viewBox="0 0 100 101" fill="red"
          xmlns="http://www.w3.org/2000/svg">
          <path
            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
            fill="#E5E7EB" />
          <path
            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
            fill="#007CFC" />
        </svg>
      </div>

      <!-- 
      1. IsLoadingMore = true khi loading more đang chạy
      2. isLoadFullTLD = true khi kết tổng TLD đã gọi = list tld 
      3. IsHiddenResultMore = true khi bị ẩn kết quả tìm kiếm thêm
      -->
      <div v-if="ResultEmpty == false" class="w-full flex items-center justify-center gap-2 text-[#007CFC]">
        <!-- Hiện nút xem thêm  -->
        <div class="" v-if="isLoadFullTLD == false && IsLoadingMore == false">
          <button class="vnx-btn-load-more font-medium flex gap-1 items-center" @click="loadMoreDomain">Xem thêm tuỳ
            chọn <i class="fa-thin fa-angle-down mt-[3px]"></i></button>
        </div>

        <!-- Hiện nút ẩn bớt kết quả   -->
        <div class=" flex justify-center flex-col items-center gap-2" v-if="isLoadFullTLD === true && IsHiddenResultMore === false">
          <p class="text-center px-10 text-gray-500 italic text-sm">
            Đây là tất cả gợi ý cho bạn, nếu bạn vẫn không tìm được tên miền phù hợp, vui lòng tìm kiếm lại với từ khóa
            khác!
          </p>
          <button class="vnx-btn-load-more font-medium flex gap-1 items-center" @click="hiddenResultMore">Xem ít tùy chọn hơn <i
              class="fa-thin fa-angle-up"></i></button>
        </div>

        <!-- Hiện nút xem thêm  -->
        <div class=" flex justify-center flex-col items-center gap-2" v-if="isLoadFullTLD === true && IsHiddenResultMore">
          <p class="text-center px-10 text-gray-500 italic text-sm">
          Các kết quả gợi ý đã được ẩn đi. Vui lòng chọn “Hiển thị kết quả gợi ý” để hiển thị lại!
          </p>
          <button class="vnx-btn-load-more font-medium flex gap-1 items-center" @click="hiddenResultMore">Hiển thị kết quả gợi ý <i
              class="fa-thin fa-angle-down mt-[3px]"></i></button>
        </div>
      </div>
    </div>
  </div>
</div>