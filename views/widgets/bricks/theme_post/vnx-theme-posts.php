<?php
$settings = $data->settings;
$post_type = isset($settings['post_type']) ? $settings['post_type'] : '';
$text_filter = isset($settings['text_filter']) ? $settings['text_filter'] : 'Bộ lọc';
$icon_filter = isset($settings['icon_filter']) ? $settings['icon_filter'] : '';
$num_post = isset($settings['num_post']) ? $settings['num_post'] : 6;
$pagination_show = isset($settings['pagination_show']) ? $settings['pagination_show'] : true;
$pagination_icon_prev = isset($settings['pagination_icon_prev']) ? $settings['pagination_icon_prev'] : '';
$pagination_icon_next = isset($settings['pagination_icon_next']) ? $settings['pagination_icon_next'] : '';
$pagination_column = isset($settings['pagination_column']) ? $settings['pagination_column'] : '2';

$filter_categories = $data->get_category_options();
$widget_id = isset($data->id) ? $data->id : 'vnx-theme-posts-' . uniqid();
?>
<div class="wrap_theme_posts">
  <div class="vnx-theme-posts-container theme_posts" id="<?php echo esc_attr($widget_id); ?>">
    <aside class="vnx-theme-posts-sidebar">
      <div class="vnx-theme-posts-filter-title" @click="openPopup">
        <?php if ($icon_filter): ?>
          <?php
          echo Bricks\Element::render_icon($icon_filter, ['vnx_icon']);
          ?>
        <?php endif; ?>
        <span><?php echo esc_html($text_filter); ?></span>
      </div>
      <div class="vnx-theme-posts-filter-separator"></div>
      <ul class="vnx-theme-posts-filter-list" data-filter-categories="<?php echo $post_type; ?>">
        <?php foreach ($filter_categories as $key => $category):
          ?>
          <li class="vnx-theme-posts-filter-item">
            <input type="checkbox" id="filter-<?php echo esc_attr($widget_id); ?>-<?php echo esc_attr(sanitize_title($key)); ?>" name="filter[]" value="<?php echo esc_attr($key); ?>">
            <label for="filter-<?php echo esc_attr($widget_id); ?>-<?php echo esc_attr(sanitize_title($key)); ?>"><?php echo esc_html($category); ?></label>
          </li>
        <?php endforeach; ?>
      </ul>
    </aside>
    <div class="vnx-theme-posts-popup-template" @click="closePopup" id="vnx-theme-posts-popup-template-<?php echo esc_attr($widget_id); ?>" style="display: none;">
      <div class="vnx-theme-posts-popup" @click.stop id="vnx-theme-posts-popup-<?php echo esc_attr($widget_id); ?>">
        <div class="vnx-theme-posts-popup-content">
          <div class="vnx-theme-posts-popup-header">
            <div class="vnx-theme-posts-filter-title">
              <?php if ($icon_filter): ?>
                <?php
                echo Bricks\Element::render_icon($icon_filter, ['vnx_icon']);
                ?>
              <?php endif; ?>
              <span><?php echo esc_html($text_filter); ?></span>
            </div>
            <button class="vnx-theme-posts-popup-close" aria-label="Close" @click="closePopup">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
          </div>
          <div class="vnx-theme-posts-filter-separator"></div>
          <div class="vnx-theme-posts-popup-body">
            <ul class="vnx-theme-posts-filter-list" data-filter-categories="<?php echo $post_type; ?>">
              <?php foreach ($filter_categories as $key => $category): ?>
                <li class="vnx-theme-posts-filter-item">
                  <input type="checkbox" id="popup-filter-<?php echo esc_attr($widget_id); ?>-<?php echo esc_attr(sanitize_title($key)); ?>" name="filter[]" value="<?php echo esc_attr($key); ?>">
                  <label for="popup-filter-<?php echo esc_attr($widget_id); ?>-<?php echo esc_attr(sanitize_title($key)); ?>"><?php echo esc_html($category); ?></label>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div class="vnx-theme-posts-popup-footer">
            <button class="vnx-theme-posts-popup-apply">Áp dụng</button>
          </div>
        </div>
      </div>
    </div>
    <div class="vnx-theme-posts-content relative">
      <button class="vnx-theme-posts-open-popup" style="display: none;">
        <?php if ($icon_filter): ?>
          <?php
          echo Bricks\Element::render_icon($icon_filter, ['vnx_icon']);
          ?>
        <?php endif; ?>
        <span><?php echo esc_html($text_filter); ?></span>
      </button>
      <input type="hidden" name="vnx_theme_posts_data" class="vnx-theme-posts-data" data-loop-not-template="<?php echo esc_attr(isset($settings['loop_item_not_template']) ? $settings['loop_item_not_template'] : ''); ?>" data-post-type="<?php echo esc_attr($post_type ?: 'post'); ?>" data-posts-per-page="<?php echo esc_attr($num_post); ?>"
        data-loop-template="<?php echo esc_attr(isset($settings['loop_item_template']) ? $settings['loop_item_template'] : ''); ?>" data-current-page="<?php echo esc_attr(get_query_var('paged') ?: 1); ?>"
        data-max-pages="<?php echo esc_attr($query->max_num_pages); ?>" data-root-div="bricks">
      <?php
      $args = [
        'post_type' => $post_type ?: 'post',
        'posts_per_page' => $num_post,
        'paged' => get_query_var('paged') ?: 1,
      ];
      $query = new WP_Query($args);
      ?>
      <div class="vnx-theme-posts-grid grid grid-cols-<?php echo esc_attr($pagination_column); ?>" id="vnx-theme-posts-grid">
        <!-- content  -->
      </div>

      <?php if ($pagination_show && $query->max_num_pages > 1): ?>
        <div class="vnx-theme-posts-pagination">
          <div class="vnx-theme-pagi-wrapper">
            <button class="vnx-theme-posts-pagination-button" data-page="prev">
              <?php if ($pagination_icon_prev): ?>
                <?php
                if ($pagination_icon_prev['library'] == 'svg') {
                  echo '<img src="' . $pagination_icon_prev["svg"]["url"] . '" alt="Previous">';
                } else {
                  echo '<i class="' . $pagination_icon_prev["icon"] . '"></i>';
                }
                ?>
              <?php endif; ?>
            </button>

            <?php
            $current_page = max(1, get_query_var('paged'));
            $total_pages = $query->max_num_pages;

            if ($total_pages <= 10):
              for ($i = 1; $i <= $total_pages; $i++):
                ?>
                <button class="vnx-theme-posts-pagination-number <?php echo $i === $current_page ? 'active' : ''; ?>" data-page="<?php echo $i; ?>">
                  <?php echo $i; ?>
                </button>
                <?php
              endfor;
            else:
              if ($current_page <= 2):
                for ($i = 1; $i <= 2; $i++):
                  ?>
                  <button class="vnx-theme-posts-pagination-number <?php echo $i === $current_page ? 'active' : ''; ?>" data-page="<?php echo $i; ?>">
                    <?php echo $i; ?>
                  </button>
                  <?php
                endfor;
                ?>
                <span class="vnx-theme-posts-pagination-ellipsis">...</span>
                <button class="vnx-theme-posts-pagination-number" data-page="<?php echo $total_pages - 1; ?>">
                  <?php echo $total_pages - 1; ?>
                </button>
                <button class="vnx-theme-posts-pagination-number" data-page="<?php echo $total_pages; ?>">
                  <?php echo $total_pages; ?>
                </button>
                <?php
              elseif ($current_page >= $total_pages - 1):
                ?>
                <button class="vnx-theme-posts-pagination-number" data-page="1">1</button>
                <button class="vnx-theme-posts-pagination-number" data-page="2">2</button>
                <span class="vnx-theme-posts-pagination-ellipsis">...</span>
                <?php
                for ($i = $total_pages - 1; $i <= $total_pages; $i++):
                  ?>
                  <button class="vnx-theme-posts-pagination-number <?php echo $i === $current_page ? 'active' : ''; ?>" data-page="<?php echo $i; ?>">
                    <?php echo $i; ?>
                  </button>
                  <?php
                endfor;
              else:
                ?>
                <button class="vnx-theme-posts-pagination-number" data-page="1">1</button>
                <button class="vnx-theme-posts-pagination-number" data-page="2">2</button>
                <span class="vnx-theme-posts-pagination-ellipsis">...</span>
                <button class="vnx-theme-posts-pagination-number" data-page="<?php echo $current_page - 1; ?>">
                  <?php echo $current_page - 1; ?>
                </button>
                <button class="vnx-theme-posts-pagination-number active" data-page="<?php echo $current_page; ?>">
                  <?php echo $current_page; ?>
                </button>
                <button class="vnx-theme-posts-pagination-number" data-page="<?php echo $current_page + 1; ?>">
                  <?php echo $current_page + 1; ?>
                </button>
                <span class="vnx-theme-posts-pagination-ellipsis">...</span>
                <button class="vnx-theme-posts-pagination-number" data-page="<?php echo $total_pages - 1; ?>">
                  <?php echo $total_pages - 1; ?>
                </button>
                <button class="vnx-theme-posts-pagination-number" data-page="<?php echo $total_pages; ?>">
                  <?php echo $total_pages; ?>
                </button>
                <?php
              endif;
            endif;
            ?>

            <button class="vnx-theme-posts-pagination-button" data-page="next">
              <?php if ($pagination_icon_next): ?>
                <?php
                if ($pagination_icon_next['library'] == 'svg') {
                  echo '<img src="' . $pagination_icon_next["svg"]["url"] . '" alt="Next">';
                } else {
                  echo '<i class="' . $pagination_icon_next["icon"] . '"></i>';
                }
                ?>
              <?php else: ?>
                &gt;
              <?php endif; ?>
            </button>
          </div>
        </div>
      <?php endif; ?>
      <div class="loading_posts hidden">
        <div role="status" class="vnx-loading-spinner">
        </div>
      </div>
    </div>
  </div>

</div>