<?php

use HelperCenter\View;

get_header();

$template = 'search';
$title = sprintf(esc_html__('Search Results: %s', 'leaf'), get_search_query());
$s = $_GET["s"];
$allsearch = new WP_Query("s=$s&showposts=0&post_type=post");
?>

<div class="flex flex-col">

  <main class="flex-1" style="background-color: #F5F7FA;">
    <!-- Start: Breadcrumbs -->
    <div class="h-10 bg-white hidden md:flex items-center border-b border-gray-200">
      <div class="container text-secondary flex items-center f-12">
        <i class="fas fa-home mr-2"></i>
        <?php echo esc_html(iwp_breadcrumbs_Center()); ?>
      </div>
    </div>
    <!-- End: Breadcrumbs -->

    <div class="bg-white py-6">
      <div class="container">
        <!--  -->
        <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
          <div class="rounded-md h-12 relative overflow-hidden">
            <i class="fas fa-search absolute text-gray-400 f-18" style="top: 15px; left: 10px;"></i>
            <input name="s" type="text" class="f-22 round w-full h-full outline-none pl-10 pr-4 placeholder-gray-400 border-[#fff] border-b focus:border-gray-300 focus:border-b" placeholder="Tìm kiếm" value="<?php echo get_search_query(); ?>">
          </div>
        </form>
        <!--  -->

        <div class="pt-5">
          <?php echo $allsearch->found_posts . ' kết quả';  ?>
        </div>
      </div>
    </div>

    <section>
      <div class="container py-16">
        <div class="border border-gray-300 rounded bg-white">
          <?php View::render('partials/loop-posts', [
            'class' => 'flex flex-col',
            'card' => 'card-post-3',
            'posts_per_page' => 10,
            'pagination_class' => 'center',
            'show_pagination' => true
          ]); ?>
        </div>
      </div>
    </section>
  </main>
</div>
<style>
  input:not([type=submit]){
    border-width: 0px;
  }
</style>
<?php get_footer(); ?>