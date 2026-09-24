<?php

/**
 * vietnix_banner_shortcode_Center function
 *
 * @param [type] $atts
 * @return void
 */
function vietnix_banner_shortcode_Center($atts)
{
  $args = array(
    'p' => $atts['id'],
    'post_type' => 'vietnix_banner',
    'posts_per_page' => '-1',
    'publish_status' => 'published',
  );

  $query = new WP_Query($args);

  if ($query->have_posts()) :

    while ($query->have_posts()) :

      $query->the_post();
      $style = get_post_meta(get_the_ID(), '_vietnix_banner_single_settings', true);

      $result = '<div class="vietnix-banner-item vietnix-banner-item-style-' . get_the_ID() . '" data-id="' . esc_attr(get_the_ID()) . '">';
      $result .= '<div class="vietnix-banner-content">' . get_the_content() . '</div>';
      $result .= '</div>';
      if (isset($style)) {
        $result .= '<style>.vietnix-banner-item-style-' . get_the_ID();
        if (isset($style["margin"])) {
          $result .= '{margin:';
          $result .= $style["margin"]["top"] . $style["margin"]["unit"] . ' ' . $style["margin"]["right"] . $style["margin"]["unit"] . ' ' . $style["margin"]["bottom"] . $style["margin"]["unit"] . ' ' . $style["margin"]["left"] . $style["margin"]["unit"] . ';';
        }
        if (isset($style["padding"])) {
          $result .= 'padding:';
          $result .= $style["padding"]["top"] . $style["padding"]["unit"] . ' ' . $style["padding"]["right"] . $style["padding"]["unit"] . ' ' . $style["padding"]["bottom"] . $style["padding"]["unit"] . ' ' . $style["padding"]["left"] . $style["padding"]["unit"] . ';';
        }
        if (isset($style["border"])) {
          $result .= 'border-top:';
          $result .= $style["border"]["top"] . 'px ' . $style["border"]["type"] . ' ' . $style["border"]["color"] . ';';
          $result .= 'border-right:';
          $result .= $style["border"]["right"] . 'px ' . $style["border"]["type"] . ' ' . $style["border"]["color"] . ';';
          $result .= 'border-bottom:';
          $result .= $style["border"]["bottom"] . 'px ' . $style["border"]["type"] . ' ' . $style["border"]["color"] . ';';
          $result .= 'border-left:';
          $result .= $style["border"]["left"] . 'px ' . $style["border"]["type"] . ' ' . $style["border"]["color"] . ';';
        }
        if (isset($style["border_radius"])) {
          $result .= 'border-radius:';
          $result .= $style["border_radius"]["top"] . $style["border_radius"]["unit"] . ' ' . $style["border_radius"]["right"] . $style["border_radius"]["unit"] . ' ' . $style["border_radius"]["bottom"] . $style["border_radius"]["unit"] . ' ' . $style["border_radius"]["left"] . $style["border_radius"]["unit"] . ';';
        }
        if (isset($style["box_shadow"])) {
          $result .= 'box-shadow:';
          $result .= $style["box_shadow"]["horizontal"] . 'px ' . $style["box_shadow"]["vertical"] . 'px ' . $style["box_shadow"]["blur"] . 'px ' . $style["box_shadow"]["spread"] . 'px ' . $style["box_shadow"]["color"];
        }
        if (isset($style["box_shadow"]) && $style["box_shadow"]["position"] == "inset") {
          $result .= ' inset;';
        } else
          $result .= ';';
        $result .= '}</style>';
      }
    endwhile;

    wp_reset_postdata();

  endif;

  return $result;
}

// Tag shortcode nam trong noi dung bai viet -> phai trung voi vietnix-plugin de doi plugin
// khong vo noi dung. VIETNIX_BANNER_CENTER chi giu lam alias cho noi dung lo tao bang tag cu.
add_shortcode('VIETNIX_BANNER', 'vietnix_banner_shortcode_Center');
add_shortcode('VIETNIX_BANNER_CENTER', 'vietnix_banner_shortcode_Center');
add_filter('vietnix_banner', 'do_shortcode');
