<?php
try {
  $data = isset($data) ? $data : new stdClass();
  $settings = $data->settings;

  $field_group = acf_get_fields(acf_get_field_group($settings['acf_key_group'])['ID']);
?>
  <div class="w-full flex flex-wrap justify-center" id="vnx_posts_filter_<?= $settings['widget_target_id'] ?>">
    <div class="lg:w-6/12 w-full flex lg:justify-end justify-center">
      <?php foreach ($field_group as $key => $value) {
        if ($value['name'] == 'department') {
      ?>
          <div class="container_posts_filter w-full" data-filter="<?= $value['name'] ?>">
            <div class="select-btn">
              <span class="btn-text-count"></span>
              <span class="btn-text text-sm">Phòng ban</span>
              <span class="arrow-dwn">
                <img src="https://vietnix.vn/wp-content/uploads/2023/06/chevron-down.svg" alt="">
              </span>
            </div>
            <ul class="list-items">
              <?php foreach ($value['choices'] as $key => $value) { ?>
                <li class="item" data-filter_value="<?= $key ?>">
                  <span class="checkbox">
                    <i class="fa-solid eicon-check check-icon"></i>
                  </span>
                  <span class="item-text text-sm">
                    <?= $value ?>
                  </span>
                </li>
              <?php } ?>
            </ul>
          </div>
        <?php } else if ($value['name'] == 'type') {
        ?>
          <div class="container_posts_filter w-full" data-filter="<?= $value['name'] ?>">
            <div class="select-btn">
              <span class="btn-text-count"></span>
              <span class="btn-text text-sm">Loại công việc</span>
              <span class="arrow-dwn">
                <img src="https://vietnix.vn/wp-content/uploads/2023/06/chevron-down.svg" alt="">
              </span>
            </div>
            <ul class="list-items">
              <?php foreach ($value['choices'] as $key => $value) { ?>
                <li class="item" data-filter_value="<?= $key ?>">
                  <span class="checkbox">
                    <i class="fa-solid eicon-check check-icon"></i>
                  </span>
                  <span class="item-text text-sm">
                    <?= $value ?>
                  </span>
                </li>
              <?php } ?>
            </ul>
          </div>
        <?php } else if ($value['name'] == 'position') {
        ?>

          <div class="container_posts_filter w-full" data-filter="<?= $value['name'] ?>">
            <div class="select-btn">
              <span class="btn-text-count"></span>
              <span class="btn-text text-sm">Vị trí</span>
              <span class="arrow-dwn">
                <img src="https://vietnix.vn/wp-content/uploads/2023/06/chevron-down.svg" alt="">
              </span>
            </div>
            <ul class="list-items">
              <?php foreach ($value['choices'] as $key => $value) { ?>
                <li class="item" data-filter_value="<?= $key ?>">
                  <span class="checkbox">
                    <i class="fa-solid eicon-check check-icon"></i>
                  </span>
                  <span class="item-text text-sm">
                    <?= $value ?>
                  </span>
                </li>
              <?php } ?>
            </ul>
          </div>
      <?php };
      }; ?>
    </div>
    <div class="lg:w-2/12 w-full flex lg:justify-start justify-center pt-4 lg:pt-0">
      <div class="my-px mx-4">
        <button type="button" class="filter_submit text-white bg-[#38A7FF] font-medium rounded text-sm py-3 px-5 text-center inline-flex items-center h-full" data-target_id="<?= $settings['widget_target_id'] ?>">
          <img src="https://vietnix.vn/wp-content/uploads/2023/06/filter.svg" alt="">
          <span class="sr-only">Icon filter</span>
        </button>
      </div>
      <div class="my-px flex items-center">
        <a href="javascript:void(0);" class="filter_clear font-medium text-[#38A7FF] hover:underline text-sm" data-target_id="<?= $settings['widget_target_id'] ?>">Clear All</a>
      </div>
    </div>
  </div>
<?php
} catch (Exception $e) {
  error_log($e->getMessage());
}
