<?php wp_nonce_field( 'domain_suggest_checking', 'vnx_domain_suggest_security' ); ?>
<div class="grid grid-cols-3 gap-8 w-full bg-white rounded-lg">
  <div id="suggestion_domain" class="lg:col-span-3 col-span-3 relative">
    <div class="w-full h-full bg-white border border-gray-200 rounded-lg shadow flex items-center justify-center">
      <div class="flex flex-col items-center pb-10">
        <img class="w-20 h-20 m-4" src="https://vietnix.vn/wp-content/uploads/2023/06/none_domain.svg"
          alt="none domain" />
        <h5 class="mb-1 text-lg font-medium text-[#000000]">Không tìm thấy kết quả phù hợp</h5>
        <span class="text-sm font-normal text-[#828282]">Bạn vui lòng nhập tên miền để kiểm tra vào ô tìm
          kiếm</span>
      </div>
    </div>
  </div>
</div>

<style>
  #suggestion_domain {
    min-height: 400px;
  }

  @media screen and (max-width: 1024px) {
    #suggestion_domain {
      min-height: 500px;
    }
  }
  button#reloadButton_searchDomain {
    background: linear-gradient(97.32deg, #FFBD2A 5%, #FD7659 170.21%);
    color: white;
    padding: 0 40px;
    font-weight: 500;
    border-radius: 0.25rem;
    padding: 8px;
    gap: 10px;
}
</style>