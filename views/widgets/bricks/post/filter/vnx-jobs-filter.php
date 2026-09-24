<?php
try {
  $data = isset($data) ? $data : new stdClass();
  $settings = $data->settings;
  $post_types = isset($settings['post_type']) ? $settings['post_type'] : 'jobs';
  $text_btn = isset($settings['text_btn']) ? $settings['text_btn'] : 'Lọc';
  $text_btn_clear = isset($settings['text_btn_clear']) ? $settings['text_btn_clear'] : 'Clear all';
  if ($settings['acf_key_group'] != null) {
    $id_group = acf_get_field_group($settings['acf_key_group'])['ID'];
    $field_group = acf_get_fields($id_group);
    $options = [];
    foreach ($field_group as $field) {
      $options[$field['name']] = $field['label'];
    }
    $final = [];
    foreach ($field_group as $element) {
      $found = false;
      foreach ($settings['filter_layout'] as $item) {
        if ($item['title'] === $element['label']) {
          $found = true;
          break;
        }
      }
      if ($found) {
        $final[] = $element;
      }
    }
  }
  // $field_group = acf_get_fields(acf_get_field_group($settings['acf_key_group'])['ID']);
  ?>
  <div class="w-full flex flex-wrap justify-center" id="vnx_posts_filter_<?= $settings['widget_target_id'] ?>">
    <form action="#" method="post" class="vnx_box_form_filter">
      <input type="hidden" name="vnx_perpage" value="1">
      <input type="hidden" name="vnx_post_type" value="<?php echo $post_types; ?>">
      <input type="hidden" name="vnx_current_page" value="1">
      <div class="vnx_body_filter flex justify-center">
        <?php
        foreach ($final as $value) {
          ?>
          <div class="container_posts_filter" data-filter="<?= $value['name'] ?>">
            <input type="hidden" name="<?= $value['name'] ?>" value="" class="vnx_filter_value">
            <div class="select-btn" @click="clickSelect">
              <div class="flex flex-row items-center">
                <span class="btn-text-count"></span>
                <span class="btn-text text-sm"><?= $value['label'] ?></span>
              </div>
              <span class="arrow-dwn">
                <img src="https://vietnix.vn/wp-content/uploads/2023/06/chevron-down.svg" alt="">
              </span>
            </div>
            <ul class="list-items">
              <?php foreach ($value['choices'] as $key => $value) { ?>
                <li class="item" data-filter_value="<?= $key ?>">
                  <span class="checkbox">
                    <i class="ion-ios-checkmark brxe-icon"></i>
                  </span>
                  <span class="item-text text-sm">
                    <?= $value ?>
                  </span>
                </li>
              <?php } ?>
            </ul>
          </div>
          <?php
        }
        ?>
      </div>
      <div class="vnx_btn_filter flex lg:justify-start justify-center pt-4 lg:pt-0">
        <div class="my-px mx-4">
          <button type="submit" @click="clickSubmit"  class="filter_submit text-white bg-[#38A7FF] font-medium rounded text-sm py-3 px-5 text-center inline-flex items-center h-full" data-target_id="<?= $settings['widget_target_id'] ?>">
            <img src="https://vietnix.vn/wp-content/uploads/2023/06/filter.svg" alt="">
            <span class="sr-only">Icon filter</span>
          </button>
        </div>
        <?php if( !empty($settings['text_btn_clear'])){?>
        <div class="my-px flex items-center">
          <a href="javascript:void(0);" @click="clickClearAll" class="filter_clear font-medium text-[#38A7FF] hover:underline text-sm" data-target_id="<?= $settings['widget_target_id'] ?>"><?= $text_btn_clear ?></a>
        </div>
          <?php } ?>
      </div>
    </form>
  </div>
  <?php
} catch (Exception $e) {
  error_log($e->getMessage());
}
