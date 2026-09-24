<?php
/*
 * Template Name: Tin Tức
 * Template Post Type: page
 */

use HelperCenter\View;

$title = get_the_category() ? get_the_category()[0]->name : 'BÀI VIẾT MỚI';

get_header();
?>
<div class="flex flex-col">
  <main class="flex-1">

    <!--  -->
    <div class="border-b border-gray-200 flex items-center lg:h-56 py-4" style="background: #F9FBFC;">
      <div class="container flex flex-col lg:flex-row items-center">
        <div class="w-full lg:w-1/2 flex flex-col justify-center text-center lg:text-left">
          <h1 class="text-primary vnx-page-title">
            Tin tức Vietnix
          </h1>

          <div class="f-16 lg:f-18 mt-3 text-gray-700">
            Cập nhật các thông báo, chương trình khuyến mãi từ Vietnix
          </div>
        </div>

        <div class="w-full lg:w-1/2 flex justify-center lg:justify-end items-center">
          <img src="<?php echo VNX_PLUGIN_URL_CENTER . 'assets/images/banner/tin-tuc.svg'; ?>" alt="Colocation" height="100%" />
        </div>
      </div>
    </div>
    <!--  -->
    <!--  -->
    <div class="flex flex-col">
      <div class="pt-5">
        <div class="container blog main-post py-12 flex flex-col">
          <?php View::render('widgets/vnx-theme/widget-posts', [
            'pagination_class' => 'center',
            'categories' => 'khuyen-mai,thong-bao,su-kien',
            'show_pagination' => true
          ]); ?>
        </div>
      </div>
    </div>
    <!--  -->
  </main>
</div>

<?php get_footer(); ?>