<?php

/**
 * vietnix_banner_single_metabox_Center function
 *
 * @return void
 */
function vietnix_banner_single_metabox_Center()
{
  add_meta_box(
    'vietnix_banner_single_metabox_Center',
    __('Banner Settings', VIETNIX_BANNER),
    'vietnix_banner_single_metabox_content_Center',
    'vietnix_banner',
    'normal',
    'high'
  );
  add_meta_box(
    'vietnix_banner_single_metabox_style_Center',
    __('Banner Style', VIETNIX_BANNER),
    'vietnix_banner_single_metabox_style_Center',
    'vietnix_banner',
    'normal',
    'high'
  );
}
add_action('add_meta_boxes', 'vietnix_banner_single_metabox_Center');

/**
 * vietnix_banner_single_metabox_content_Center function
 *
 * @param [type] $post
 * @return void
 */
function vietnix_banner_single_metabox_content_Center($post)
{
  // Add an nonce field so we can check for it later.
  wp_nonce_field('vietnix_banner_single_metabox_save_Center', 'vietnix_banner_single_metabox_nonce');

  $settings = get_post_meta($post->ID, '_vietnix_banner_single_settings', true);
  $position = isset($settings['position']) ? $settings['position'] : '';
  $categories = isset($settings['category']) ? $settings['category'] : array(get_option('default_category'));
  // $priority = isset($settings['priority']) ? $settings['priority'] : '1';
  $number_p = isset($settings['number_p']) ? $settings['number_p'] : '1';
  $pos_insert = isset($settings['pos_insert']) ? $settings['pos_insert'] : 'before';
  $tag_insert = isset($settings['tag_insert']) ? $settings['tag_insert'] : 'p';
  ?>
  <div style="display: flex;flex-direction:row;">
    <div class="vietnix-banner-form-column" style="width:50%;">
      <h3>
        <?php _e('Placement on the post:', VIETNIX_BANNER); ?>
      </h3>
      <div class="vietnix-banner-select-child form-group" data-parent-select-id="vietnix_banner_fields_placement_type"
        data-parent-select-value="auto">
        <div class="form-field">
          <input type="radio" name="vietnix_banner_fields[position]" id="vietnix_banner_fields_position_before_content"
            value="" <?php checked('', $position, true); ?> />
          <label for="vietnix_banner_fields_position_before_content">
            <?php _e('Handmade with shortcode', VIETNIX_BANNER); ?>
          </label>
        </div>
        <div class="form-field">
          <input type="radio" name="vietnix_banner_fields[position]" id="vietnix_banner_fields_position_before_content"
            value="before_content" <?php checked('before_content', $position, true); ?> />
          <label for="vietnix_banner_fields_position_before_content">
            <?php _e('Begin the content', VIETNIX_BANNER); ?>
          </label>
        </div>
        <div class="form-field">
          <input type="radio" name="vietnix_banner_fields[position]" id="vietnix_banner_fields_position_in_content"
            value="in_content" <?php checked('in_content', $position, true); ?> />
          <?php _e('In the content: ', VIETNIX_BANNER); ?>

          <label for="vietnix_banner_fields_position_in_content">
            <select name="vietnix_banner_fields[pos_insert]" id="vietnix_banner_fields_pos_insert">
              <option value="before" <?php selected($pos_insert, 'before', true); ?>>Before</option>
              <option value="after" <?php selected($pos_insert, 'after', true); ?>>After</option>
            </select>
            <select name="vietnix_banner_fields[tag_insert]" id="vietnix_banner_fields_tag_insert">
              <option value="p" <?php selected($tag_insert, 'p', true); ?>>Paragraphs</option>
              <option value="div" <?php selected($tag_insert, 'div', true); ?>>div</option>
              <option value="h1" <?php selected($tag_insert, 'h1', true); ?>>H1</option>
              <option value="h2" <?php selected($tag_insert, 'h2', true); ?>>H2</option>
              <option value="h3" <?php selected($tag_insert, 'h3', true); ?>>H3</option>
              <option value="h4" <?php selected($tag_insert, 'h4', true); ?>>H4</option>
              <option value="h5" <?php selected($tag_insert, 'h5', true); ?>>H5</option>
              <option value="h6" <?php selected($tag_insert, 'h6', true); ?>>H6</option>
            </select>

            <?php _e(' position ', VIETNIX_BANNER); ?>
            <input type="number" step="1" min="1" name="vietnix_banner_fields[number_p]"
              id="vietnix_banner_fields_number_p" size="10" value="<?php echo esc_attr($number_p); ?>" class="small-text"
              style="max-width: 100px;">

          </label>
        </div>
        <div class="form-field">
          <input type="radio" name="vietnix_banner_fields[position]" id="vietnix_banner_fields_position_after_content"
            value="after_content" <?php checked('after_content', $position, true); ?> />
          <label for="vietnix_banner_fields_position_after_content">
            <?php _e('End the content', VIETNIX_BANNER); ?>
          </label>
        </div>
      </div>

      <!-- <h3><?php _e('Priority:', VIETNIX_BANNER); ?></h3>
                                                                                                                                                                                                                                                  <div class="vietnix-banner-select-child form-group" data-parent-select-id="vietnix_banner_fields_priority" data-parent-select-value="auto">
                                                                                                                                                                                                                                                    <div class="form-field">
                                                                                                                                                                                                                                                      <label for="vietnix_banner_fields_priority">
                                                                                                                                                                                                                                                        <div><?php _e('Banner Priority:', VIETNIX_BANNER); ?></div>
                                                                                                                                                                                                                                                        <input type="number" step="1" min="1" name="vietnix_banner_fields[priority]" id="vietnix_banner_fields_priority" value="<?php echo $priority; ?>" class="small-text" />
                                                                                                                                                                                                                                                      </label>
                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                  </div> -->
    </div>

    <div class="vietnix-banner-form-column" style="width:50%;">
      <h3>
        <?php _e('Show on Categories:', VIETNIX_BANNER); ?>
      </h3>
      <?php
      $post_categories = get_categories(['hide_empty' => false]);
      foreach ($post_categories as $post_category) {
        ?>
        <div>
          <input type="checkbox" name="vietnix_banner_fields[category][]" id="vietnix_banner_fields_category"
            value="<?php echo esc_attr($post_category->term_id); ?>" <?php in_array($post_category->term_id, $categories) ? print esc_attr('checked') : '' ?>>
          <a href="'<?php echo esc_attr(get_category_link($post_category->term_id)); ?>'">
            <?php echo esc_attr($post_category->name); ?>
          </a>
        </div>
      <?php } ?>
    </div>
  </div>
<?php
}

/**
 * vietnix_banner_single_metabox_save_Center function
 *
 * @param [type] $post_id
 * @return void
 */
function vietnix_banner_single_metabox_save_Center($post_id)
{
  if (!isset($_POST['vietnix_banner_single_metabox_nonce'])) {
    return;
  }

  if (!wp_verify_nonce($_POST['vietnix_banner_single_metabox_nonce'], 'vietnix_banner_single_metabox_save_Center')) {
    return;
  }

  if ('page' == $_POST['post_type']) {
    if (!current_user_can('edit_page', $post_id))
      return;
  } else {
    if (!current_user_can('edit_post', $post_id))
      return;
  }

  if (!isset($_POST['vietnix_banner_fields'])) {
    return;
  }

  $vietnix_banner_fields = $_POST['vietnix_banner_fields'];

  // Update the meta field in the database.
  update_post_meta($post_id, '_vietnix_banner_single_settings', $vietnix_banner_fields);
  // print_r($vietnix_banner_fields);
  // die();
}
add_action('save_post', 'vietnix_banner_single_metabox_save_Center');

function vietnix_banner_single_metabox_style_Center($post)
{
  // Add an nonce field so we can check for it later.
  wp_nonce_field('vietnix_banner_single_metabox_save_Center', 'vietnix_banner_single_metabox_nonce');
  
  $settings = get_post_meta($post->ID, '_vietnix_banner_single_settings', true);
  $margin = isset($settings['margin']) ? $settings['margin'] : [];
  $margin['unit'] = isset($settings['margin']['unit']) ? $settings['margin']['unit'] : 'px';
  $padding = isset($settings['padding']) ? $settings['padding'] : [];
  $padding['unit'] = isset($settings['padding']['unit']) ? $settings['padding']['unit'] : 'px';
  $border = isset($settings['border']) ? $settings['border'] : [];
  $border['type'] = isset($settings['border']['type']) ? $settings['border']['type'] : 'none';
  $border_radius = isset($settings['border_radius']) ? $settings['border_radius'] : [];
  $border_radius['unit'] = isset($settings['border_radius']['unit']) ? $settings['border_radius']['unit'] : 'px';
  $box_shadow = isset($settings['box_shadow']) ? $settings['box_shadow'] : 0;
  $border_radius['position'] = isset($settings['border_radius']['position']) ? $settings['border_radius']['position'] : 'outset';
  ?>
  <div style="display: flex;flex-direction:row;">
    <div class="vietnix-banner-form-column" style="width:50%;">
      <h3>
        <?php _e('Margin', VIETNIX_BANNER); ?>
      </h3>
      <div class="vietnix-banner-select-child form-group" data-parent-select-id="vietnix_banner_fields_placement_type"
        data-parent-select-value="auto">
        <div class="form-field">
          <span style="display: inline-block;">
            <?php _e(' Top ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($margin['top']) ? $margin['top'] : 0 ?>"
            name="vietnix_banner_fields[margin][top]" id="vietnix_banner_fields_style" style="width: 15%">
          <span style="display: inline-block;">
            <?php _e(' Right ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($margin['right']) ? $margin['right'] : 0 ?>"
            name="vietnix_banner_fields[margin][right]" id="vietnix_banner_fields_style" style="width: 15%">
          <span style="display: inline-block;">
            <?php _e(' Bottom ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($margin['bottom']) ? $margin['bottom'] : 0 ?>"
            name="vietnix_banner_fields[margin][bottom]" id="vietnix_banner_fields_style" style="width: 15%">
          <span style="display: inline-block;">
            <?php _e(' Left ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($margin['left']) ? $margin['left'] : 0 ?>"
            name="vietnix_banner_fields[margin][left]" id="vietnix_banner_fields_style" style="width: 15%">
          <select name="vietnix_banner_fields[margin][unit]" id="vietnix_banner_fields_style" style="width: 8%;">
            <option value="px" <?php selected($margin['unit'], 'px', true); ?>>px</option>
            <option value="em" <?php selected($margin['unit'], 'em', true); ?>>em</option>
            <option value="%" <?php selected($margin['unit'], '%', true); ?>>%</option>
            <option value="rem" <?php selected($margin['unit'], 'rem', true); ?>>rem</option>
          </select>

        </div>
      </div>
      <h3>
        <?php _e('Padding', VIETNIX_BANNER); ?>
      </h3>
      <div class="vietnix-banner-select-child form-group" style="margin-top: 10px"
        data-parent-select-id="vietnix_banner_fields_placement_type" data-parent-select-value="auto">
        <div class="form-field">
          <span style="display: inline-block;">
            <?php _e(' Top ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($padding['top']) ? $padding['top'] : 0 ?>"
            name="vietnix_banner_fields[padding][top]" id="vietnix_banner_fields_style" style="width: 15%">
          <span style="display: inline-block;">
            <?php _e(' Right ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($padding['right']) ? $padding['right'] : 0 ?>"
            name="vietnix_banner_fields[padding][right]" id="vietnix_banner_fields_style" style="width: 15%">
          <span style="display: inline-block;">
            <?php _e(' Bottom ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($padding['bottom']) ? $padding['bottom'] : 0 ?>"
            name="vietnix_banner_fields[padding][bottom]" id="vietnix_banner_fields_style" style="width: 15%">
          <span style="display: inline-block;">
            <?php _e(' Left ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($padding['left']) ? $padding['left'] : 0 ?>"
            name="vietnix_banner_fields[padding][left]" id="vietnix_banner_fields_style" style="width: 15%">
          <select name="vietnix_banner_fields[padding][unit]" id="vietnix_banner_fields_style" style="width: 8%;">
            <option value="px" <?php selected($padding['unit'], 'px', true); ?>>px</option>
            <option value="em" <?php selected($padding['unit'], 'em', true); ?>>em</option>
            <option value="%" <?php selected($padding['unit'], '%', true); ?>>%</option>
            <option value="rem" <?php selected($padding['unit'], 'rem', true); ?>>rem</option>
          </select>

        </div>
      </div>
      <h3>
        <?php _e('Box Shadow:', VIETNIX_BANNER); ?>
      </h3>
      <div class="vietnix-banner-select-child form-group" data-parent-select-id="vietnix_banner_fields_placement_type"
        data-parent-select-value="auto">
        <div class="form-field">
          <span style="width: 7%;display: inline-block;">
            <?php _e('Position', VIETNIX_BANNER); ?>
          </span>
          <select name="vietnix_banner_fields[box_shadow][position]" id="vietnix_banner_fields_style" style="width: 8%;">
            <option value="inset" <?php selected($box_shadow['position'], 'inset', true); ?>>Inset</option>
            <option value="outset" <?php selected($box_shadow['position'], 'outset', true); ?>>Outset</option>
          </select>
          <span style="width: 8%;display: inline-block; margin-left: 10px">
            <?php _e('Color', VIETNIX_BANNER); ?>
          </span>
          <input type="color" id="vietnix_banner_fields_border" name="vietnix_banner_fields[box_shadow][color]"
            value="<?=($box_shadow['color']) ? $box_shadow['color'] : "#1196E8" ?>">
        </div>

      </div>

      <div class="vietnix-banner-select-child form-group" style="margin-top: 10px"
        data-parent-select-id="vietnix_banner_fields_placement_type" data-parent-select-value="auto">

        <div class="form-field">
          <span style="display: inline-block;">
            <?php _e(' Horizontal ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($box_shadow['horizontal']) ? $box_shadow['horizontal'] : 0 ?>"
            name="vietnix_banner_fields[box_shadow][horizontal]" id="vietnix_banner_fields_style2" style="width: 15%;">
          <span style="display: inline-block;">
            <?php _e(' Vertical ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($box_shadow['vertical']) ? $box_shadow['vertical'] : 0 ?>"
            name="vietnix_banner_fields[box_shadow][vertical]" id="vietnix_banner_fields_style" style="width: 15%">
          <span style="display: inline-block;">
            <?php _e(' Blur ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($box_shadow['blur']) ? $box_shadow['blur'] : 0 ?>"
            name="vietnix_banner_fields[box_shadow][blur]" id="vietnix_banner_fields_style" style="width: 15%">
          <span style="display: inline-block;">
            <?php _e(' Spread ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($box_shadow['spread']) ? $box_shadow['spread'] : 0 ?>"
            name="vietnix_banner_fields[box_shadow][spread]" id="vietnix_banner_fields_style" style="width: 15%">
          <span style="display: inline-block;">
          </span>
        </div>
        <div class="form-field">
          <span style="width: 6%;display: inline-block;">
          </span>
        </div>
      </div>
    </div>
    <div class="vietnix-banner-form-column" style="width:50%;">
      <h3>
        <?php _e('Border:', VIETNIX_BANNER); ?>
      </h3>
      <div class="vietnix-banner-select-child form-group" data-parent-select-id="vietnix_banner_fields_placement_type"
        data-parent-select-value="auto">
        <div class="form-field">
          <span style="width: 7%;display: inline-block;">
            <?php _e('Border Type', VIETNIX_BANNER); ?>
          </span>
          <select name="vietnix_banner_fields[border][type]" id="vietnix_banner_fields_style" style="width: 8%;">
            <option value="none" <?php selected($border['type'], 'none', true); ?>>none</option>
            <option value="solid" <?php selected($border['type'], 'solid', true); ?>>Solid</option>
            <option value="double" <?php selected($border['type'], 'double', true); ?>>Double</option>
            <option value="dotted" <?php selected($border['type'], 'dotted', true); ?>>Dotted</option>
            <option value="dashed" <?php selected($border['type'], 'dashed', true); ?>>Dashed</option>
            <option value="groove" <?php selected($border['type'], 'groove', true); ?>>Groove</option>
          </select>
          <span style="width: 8%;display: inline-block; margin-left: 10px">
            <?php _e('Border color', VIETNIX_BANNER); ?>
          </span>
          <input type="color" id="vietnix_banner_fields_border" name="vietnix_banner_fields[border][color]"
            value="<?=($border['color']) ? $border['color'] : "#1196E8" ?>">
        </div>

      </div>
      <div class="vietnix-banner-select-child form-group" style="margin-top: 10px"
        data-parent-select-id="vietnix_banner_fields_placement_type" data-parent-select-value="auto">

        <div class="form-field">
          <h4 style="width: 5%;display: inline-block;">
            <?php _e(' Width ', VIETNIX_BANNER); ?> :
          </h4>
          <span style="display: inline-block;">
            <?php _e(' Top ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($border['top']) ? $border['top'] : 0 ?>"
            name="vietnix_banner_fields[border][top]" id="vietnix_banner_fields_style2" style="width: 15%;">
          <span style="display: inline-block;">
            <?php _e(' Right ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($border['right']) ? $border['right'] : 0 ?>"
            name="vietnix_banner_fields[border][right]" id="vietnix_banner_fields_style" style="width: 15%">
          <span style="display: inline-block;">
            <?php _e(' Bottom ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($border['bottom']) ? $border['bottom'] : 0 ?>"
            name="vietnix_banner_fields[border][bottom]" id="vietnix_banner_fields_style" style="width: 15%">
          <span style="display: inline-block;">
            <?php _e(' Left ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($border['left']) ? $border['left'] : 0 ?>"
            name="vietnix_banner_fields[border][left]" id="vietnix_banner_fields_style" style="width: 15%">
          <span style="display: inline-block;">
            px
          </span>
        </div>
        <div class="form-field">
          <span style="width: 6%;display: inline-block;">
          </span>
        </div>
      </div>
      <h3>
        <?php _e('Border Radius', VIETNIX_BANNER); ?>
      </h3>
      <div class="vietnix-banner-select-child form-group" style="margin-top: 10px"
        data-parent-select-id="vietnix_banner_fields_placement_type" data-parent-select-value="auto">
        <div class="form-field">
          <span style="display: inline-block;">
            <?php _e(' Top ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($border_radius['top']) ? $border_radius['top'] : 0 ?>"
            name="vietnix_banner_fields[border_radius][top]" id="vietnix_banner_fields_style" style="width: 15%">
          <span style="display: inline-block;">
            <?php _e(' Right ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($border_radius['right']) ? $border_radius['right'] : 0 ?>"
            name="vietnix_banner_fields[border_radius][right]" id="vietnix_banner_fields_style" style="width: 15%">
          <span style="display: inline-block;">
            <?php _e(' Bottom ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($border_radius['bottom']) ? $border_radius['bottom'] : 0 ?>"
            name="vietnix_banner_fields[border_radius][bottom]" id="vietnix_banner_fields_style" style="width: 15%">
          <span style="display: inline-block;">
            <?php _e(' Left ', VIETNIX_BANNER); ?>
          </span>
          <input type="number" step="any" value="<?=($border_radius['left']) ? $border_radius['left'] : 0 ?>"
            name="vietnix_banner_fields[border_radius][left]" id="vietnix_banner_fields_style" style="width: 15%">
          <select name="vietnix_banner_fields[border_radius][unit]" id="vietnix_banner_fields_style" style="width: 8%;">
            <option value="px" <?php selected($border_radius['unit'], 'px', true); ?>>px</option>
            <option value="em" <?php selected($border_radius['unit'], 'em', true); ?>>em</option>
            <option value="%" <?php selected($border_radius['unit'], '%', true); ?>>%</option>
            <option value="rem" <?php selected($border_radius['unit'], 'rem', true); ?>>rem</option>
          </select>

        </div>
      </div>
    </div>
  </div>
<?php
}