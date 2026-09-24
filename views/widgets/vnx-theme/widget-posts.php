<?php

use HelperCenter\View;

$data = isset($data) ? $data : new stdClass();

$template = isset($data->template) ? $data->template : 'layout-1';
$card = (isset($data->card)) ? $data->card : 'post-item-1';
$class = isset($data->class) ? $data->class : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8';
$pagination_class = isset($data->pagination_class) ? $data->pagination_class : '';
$show_pagination = isset($data->show_pagination) ?  $data->show_pagination : 'none';
$posts_per_page = get_option('posts_per_page') ? (int) get_option('posts_per_page') : 6;
$post_status = isset($data->post_status) ? $data->post_status : 'publish';
$settings = isset($data->settings) ? $data->settings : "";
$num_post = isset($settings['num_post']) ? $settings['num_post'] : $posts_per_page;
$show_filter = isset($settings["show_filter"]) ?  $settings["show_filter"] : 'no';
$current_pages = get_queried_object();

$args = array(
  'post_type' => 'post',
  'posts_per_page' => $num_post,
  'post_status' => $post_status
);

if (!empty($current_pages->term_id)) {
  $args = array(
    'term__in' => array($current_pages->term_id),
    'post_type' => 'post',
    'posts_per_page' => $num_post,
    'post_status' => $post_status
  );
}

if (isset($_GET["category"]) && $_GET["category"] != "") {
  $id_category = get_term_by('slug', $_GET["category"], 'category');
  if ($id_category) {
    $args = array(
      'term__and' => array($current_pages->term_id, $id_category->term_id),
      'post_type' => 'post',
      'posts_per_page' => $num_post,
      'post_status' => $post_status,
    );
  }
}

if (isset($_GET["key"]) && $_GET["key"] != "") {
  $arrs = ['s' => $_GET["key"]];
  $args = array_merge($args, $arrs);
}


if (isset($data->categories)) {
  $args['category_name'] = $data->categories;
}

if (isset($data->tags)) {
  $args['tag_slug__in'] = $data->tags;
}

if (isset($data->tag)) {
  $args['tag'] = $data->tag;
}

if (isset($data->author)) {
  $args['author'] = $data->author;
}

if (isset($_GET['s'])) {
  $args['s'] = esc_sql($_GET['s']);
}

if (get_query_var('paged')) {
  $paged = get_query_var('paged');
} elseif (get_query_var('page')) {
  $paged = get_query_var('page');
} else {
  $paged = 1;
}

$args['paged'] = (int) $paged;

$args['paged'] = $paged;

$wp_query = new WP_Query($args);

$args = array(
  'hide_empty' => 0, // Hiển thị cả các category không có bài viết
);

if ($show_filter == "yes") {
  $categories = get_categories($args);
  $categories_list = array();
  foreach ($categories as $category) {
    if (!empty($current_pages->term_id)) {
      $arg = array(
        'category__and' => array($current_pages->term_id, $category->term_id),
        'category' => $category->term_id,
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
      );
    } else {
      $arg = array(
        'category' => $category->term_id,
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
      );
    }

    $posts = get_posts($arg);
    if ($posts && $category->term_id !==  get_queried_object_id()) {
      $cat = [
        "name" => $category->name,
        "slug" => $category->slug,
      ];
      array_push($categories_list, $cat);
    }
  }


  $category_link = get_category_link(get_queried_object_id());
?>
  <div class="w-full m-auto text-center mb-5">
    <ul class="flex justify-center items-center vnx_custom_hover  flex-wrap">
      <li class="mx-2 border border-gray-300 py-2.5 px-5 mb-2 rounded-3xl whitespace-nowrap vnx_custom_hover_li <?php echo (!isset($_GET["category"])) ? " active_category" : ""; ?>">
        <a href="<?php echo esc_url($category_link); ?>" class=" "><?php _e('Tất cả'); ?></a>
      </li>
      <?php
      if (count($categories_list) > 0) {
        foreach ($categories_list as $index => $category) {
          if (strpos($category_link, '?') !== false) {
            $param = "&category=" . $category["slug"];
          } else {
            $param = "?category=" . $category["slug"];
          }
      ?>
          <li class="mx-2 border border-gray-300 py-2.5 mb-2 px-5 rounded-3xl whitespace-nowrap vnx_custom_hover_li <?php echo (isset($_GET["category"]) && ($_GET["category"] == $category["slug"])) ? " active_category" : ""; ?>">
            <a href="<?php echo esc_url($category_link) . $param; ?>" class=" "><?php _e($category["name"]); ?></a>
          </li>
      <?php }
      } ?>
    </ul>
  </div>
<?php } ?>
<?php
// Id rieng cho moi lan render: mot trang co the co nhieu widget nay, script load more
// ben duoi chi duoc dong vao danh sach/nut cua chinh no.
$vnx_posts_uid = wp_unique_id('vnx-widget-posts-');
?>
<section id="<?= esc_attr($vnx_posts_uid) ?>" class="widget-posts <?= $class ?>">
  <?php
  if ($wp_query->have_posts()) :
    while ($wp_query->have_posts()) : $wp_query->the_post();
      View::render('components/post/' . $card);
    endwhile;
  else :
    View::render('components/post/not-found');
  endif;
  ?>
</section>


<?php
if (isset($settings['num_post']) && (get_option('posts_per_page') > $num_post)) :
  $num_pages = $wp_query->found_posts / $num_post;
  $page_count = $num_pages -  ($wp_query->found_posts / $posts_per_page);
  $wp_query->max_num_pages =  round($wp_query->max_num_pages -  $page_count);
endif;

if ($show_pagination === 'numbers' || $show_pagination === true) {
  echo '<div class="vnx-pagination-2 w-full flex items-center justify-center mt-8 ' . $pagination_class . '">';
  vnx_paging_nav_Center($wp_query);
  echo '</div>';
}
wp_reset_postdata();

if ($show_pagination === 'load_more_on_click') {
  if ($wp_query->max_num_pages > 1) {
    $load_more_text = $settings['loadmore_button_text'];
    $current_page = get_query_var('paged') ? get_query_var('paged') : 1;
    $next_page = intval($current_page) + 1;
    $total_pages = $wp_query->max_num_pages;
    $args_next = array(
      'post_type' => 'post',
      'posts_per_page' => $data->settings['num_post'],
      'post_status' => $post_status
    );
?>
    <div id="<?= esc_attr($vnx_posts_uid) ?>-loadmore" class="vnx_loadmore_button mt-8 text-<?= $settings['loadmore_button_align'] ?>">
      <button type="button" data-next-page="<?= $next_page ?>" data-total-pages="<?= $total_pages ?>" class="load-more-posts text-white bg-[#38A7FF] hover:bg-[#2D9CF4] font-medium rounded text-sm px-5 py-2.5 text-center mr-2 dark:bg-[#38A7FF] dark:hover:bg-[#2D9CF4] inline-flex items-center">
        <div class="spiner"></div>
        <?= $load_more_text ?>
      </button>
    </div>
<?php
  }
}
wp_reset_postdata();
?>

<style>
  .active_category {
    background-color: #38A7FF;
    color: #FFFFFF;
  }

  .vnx_button_register_post {
    width: 100%;
    border-radius: 4px;
    padding: 11px 0px;
  }

  .vnx_button_register_post_bg {
    background: #38A7FF;
  }

  .vnx_button_register_post_bg_white {
    background: #FFFFFF;
    border: 1px solid #38A7FF;
  }

  .vnx_custom_count_down {
    display: flex;
    flex-wrap: nowrap;
    justify-content: space-evenly;
    align-content: center;
    background: #FFFFFF;
    border: 1px solid #E2E2E2;
    box-shadow: 0px 0px 6px rgba(0, 0, 0, 0.18);
    border-radius: 6px;
    width: 80%;
  }

  .vnx_custom_count_down div {
    width: 25%;
    line-height: normal;
  }

  .vnx_label_countdown {
    font-family: 'Roboto';
    font-style: normal;
    font-weight: 400;
    font-size: 10px;
    line-height: 16px;
    color: #828282;
  }

  .vnx_days_time_second {
    font-family: 'Roboto';
    font-style: normal;
    font-weight: 600;
    font-size: 14px;
    line-height: 16px;
    color: #4F4F4F;
  }

  .vnx_custom_mr_top {
    margin-top: -30px;
  }

  .vnx_custom_sale {
    background: #D5332A;
    transform: rotate(35deg);
    position: absolute;
    top: 25px;
    right: -50px;
    width: 55%;
    font-family: 'Roboto';
    font-style: normal;
    font-weight: 400;
    font-size: 12px;
    line-height: 26px;
    text-align: center;
    color: #FFFFFF;
  }


  ul.vnx_custom_hover li.vnx_custom_hover_li:hover {
    background-color: #38A7FF;
    color: #FFFFFF;
    transition: 0.5s;
    border: 1px #38A7FF solid !important;
  }
</style>

<script>
  (function($) {
    $(document).ready(function() {
      var postList = $('#<?= esc_js($vnx_posts_uid) ?>');
      var loadMoreWrap = $('#<?= esc_js($vnx_posts_uid) ?>-loadmore');
      var loadMoreButton = loadMoreWrap.find('.load-more-posts');
      var postsPerPage = <?= $num_post ?>;
      var currentPage = 1;
      var totalPosts = <?= $wp_query->max_num_pages ?>;
      var totalPages = <?= $wp_query->max_num_pages ?>;
      var spiner =
        '<svg aria-hidden="true" role="status" class="inline w-4 h-4 mr-3 text-white animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="#E5E7EB"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentColor"/></svg>'
      loadMoreButton.on('click', function(e) {
        e.preventDefault();
        var nextPage = loadMoreButton.data('next-page');
        loadMoreButton.attr("disabled", "disabled");
        loadMoreButton.find('.spiner').append(spiner)
        var data = {
          action: 'vnx_load_more_posts_center',
          page: nextPage,
          posts_per_page: postsPerPage,
          post_status: '<?= $post_status ?>',
          card: '<?= $card ?>',
        };
        <?php
        if (isset($data->author)) {
        ?>
          $.extend(data, {
            author: '<?= $data->author ?>'
          });
        <?php
        } ?>
        $.ajax({
          url: '<?php echo admin_url("admin-ajax.php"); ?>',
          type: 'POST',
          dataType: 'html',
          data: data,
          success: function(data) {
            if (data) {
              currentPage = nextPage;
              loadMoreButton.data('next-page', nextPage + 1);
              if (currentPage >= totalPages) {
                loadMoreWrap.hide();
              }
              postList.append(data);
              loadMoreButton.removeAttr("disabled");

            } else {
              loadMoreWrap.hide();
            }
            loadMoreButton.find('.spiner').empty()
          },
        });
      })
    });
  })(jQuery);

  function count_down() {
    var cs_date_time = document.querySelectorAll(".vnx_custom_sale");
    var vnx_custom_count_down = document.querySelectorAll(".vnx_custom_count_down");
    var has_expiration_date = document.querySelectorAll(".has_expiration_date");
    var countdownInterval = setInterval(function() {
      for (let i = 0; i < cs_date_time.length; i++) {
        var targetDate = new Date(cs_date_time[i].getAttribute("data-time"));
        var countdownElement = "";

        var now = new Date().getTime();
        var distance = targetDate - now;
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);
        countdownElement = `<div class="border-r border-gray-200 pr-4 pl-4 ">
      <span class="vnx_days_time_second">` + days + `</span> </br> 
        <span class="vnx_label_countdown">Days</span>
      </div>
      <div class="border-r border-gray-200 pr-4 pl-4 ">
      <span class="vnx_days_time_second">` + hours + `</span></br> 
        <span class="vnx_label_countdown">Hours</span></div>  
      <div class="border-r border-gray-200 pr-4 pl-4">
      <span class="vnx_days_time_second">` + minutes + `</span></br> 
        <span class="vnx_label_countdown">Minutes</span></div>
      <div class="pr-4 pl-4 ">
        <span class="vnx_days_time_second">` + seconds + `</span></br> 
        <span class="vnx_label_countdown">Seconds</span></div>
      </div>`;
        vnx_custom_count_down[i].innerHTML = countdownElement;
        if (distance < 0) {
          vnx_custom_count_down[i].innerHTML = `<div class="border-r border-gray-200 pr-4 pl-4 ">
      <span class="vnx_days_time_second">00</span> </br> 
        <span class="vnx_label_countdown">Days</span>
      </div>
      <div class="border-r border-gray-200 pr-4 pl-4 ">
      <span class="vnx_days_time_second">00</span></br> 
        <span class="vnx_label_countdown">Hours</span></div>  
      <div class="border-r border-gray-200 pr-4 pl-4">
      <span class="vnx_days_time_second">00</span></br> 
        <span class="vnx_label_countdown">Minutes</span></div>
      <div class="pr-4 pl-4 ">
        <span class="vnx_days_time_second">00</span></br> 
        <span class="vnx_label_countdown">Seconds</span></div>
      </div>`;
          has_expiration_date[i].innerText = "Hết hạn";

        }
      }
    }, 1000);
  }

  count_down();
</script>