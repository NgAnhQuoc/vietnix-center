<?php
try {
  $data = isset($data) ? $data : new stdClass();
  $settings = $data->settings;
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
          $element['icon'] = $item['icon'];
          $found = true;
          break;
        }
      }
      if ($found) {
        $final[] = $element;
      }
    }
  }
?>
  <div class="w-full flex flex-wrap <?= $settings['style_layout'] ?>" id="vnx_posts_filter_<?= $settings['widget_target_id'] ?>">
    <div class="w-fit flex gap-x-6 items-center">
      <?php foreach ($final as $key => $value) {
      ?>
        <div class="container_posts_filter" data-filter="<?= $value['name'] ?>">
          <div class="select-btn">
            <span class="btn-text-icon">
              <?php
              if ($value['icon']['library'] == 'svg') {
                echo  '<img src="' . $value["icon"]["svg"]["url"] . '" alt="' . $value["name"] . '">';
              } else {
                echo '<i class="' . $value["icon"]["icon"] . '"></i>';
              }
              ?>
            </span>
            <span class="btn-text"><?= $value['label'] ?></span>
            <div class="div-btn-text-count">
              <span class="btn-text-count"></span>
            </div>
            <span class="arrow-dwn">
              <i class="fas fa-angle-down"></i>
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
      <?php
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
?>
<style>
  .case_studies .select-btn {
    width: fit-content;
    border: none;
    height: 28px;
    display: flex;
    gap: 8px;
  }

  .case_studies span.btn-text-icon {
    height: 24px;
    width: 24px;
  }

  .case_studies span.btn-text-icon i {
    font-size: 24px;
  }

  .case_studies .btn-text {
    color: #525666;
    font-family: Roboto;
    font-size: 18px;
    font-style: normal;
    font-weight: 400;
    line-height: 28px;
  }
  .case_studies .div-btn-text-count {
    width: fit-content;
  }
  .case_studies .div-btn-text-count span.btn-text-count {
    padding: 0px;
    text-align: center;
    width: 20px;
    height: 20px;
    background: #FFFDE5;
    color: #FFB800;
    border-radius: 50px;
    margin: 0px;
    border: 1px solid #FFB800;
  }
  .case_studies .container_posts_filter{
    width: fit-content;
  }
  .case_studies .container_posts_filter .list-items {
    width: max-content !important;
  }
</style>