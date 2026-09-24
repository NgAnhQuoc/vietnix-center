<section class="py-6 lg:py-16 relative bg-white">
  <div class="absolute w-full top-0 left-0 overflow-hidden" style="height: 100%;">
    <div class="relative w-full top-0 left-0 h-full">
      <img src="<?php echo VNX_PLUGIN_URL_CENTER ?>assets/images/pages/tai-lieu/blog-banner.png" alt="" class="w-full h-full hidden md:block">
      <img src="<?php echo VNX_PLUGIN_URL_CENTER ?>assets/images/pages/tai-lieu/blog-banner-mobile.png" alt="" class="w-full h-full lg:hidden">
    </div>
  </div>

  <div class="container flex flex-col relative" style="z-index: 2;">
    <div class="center flex flex-col pt-10 pb-16 text-center">
      <h1 class="f-36 underline font-bold text-orange-500">
        BLOG
      </h1>

      <div class="f-38 lg:f-38 text-white font-bold">
        Tài liệu sử dụng và Tài liệu kỹ thuật của Vietnix
      </div>
    </div>

    <div class="center py-8">
      <form role="search" method="get" class="flex flex-col lg:flex-row" style="width: 580px;" action="<?php echo esc_url(home_url('/')); ?>">
        <div class="flex-1 relative lg:mr-4">
          <i class="fas fa-search absolute text-opacity-50 text-white" style="top: 16px; left: 12px;"></i>
          <input type="text" name="s" placeholder="Tìm kiếm tài liệu" class="flex-1 h-12 w-full outline-none rounded-md pl-10 pr-5 text-white" style="background: rgba(255, 255, 255, 0.27);">
        </div>

        <button id="searchsubmit" type="submit" class="mt-3 lg:mt-0 h-12 font-bold px-8 leading-none text-white" style="background: linear-gradient(96.72deg, #38A7FF 0%, #6877FB 106.03%); border-radius: 6px;">
          TÌM KIẾM
        </button>
      </form>
    </div>

  </div>
</section>