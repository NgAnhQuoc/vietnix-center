<?php
try {
  $data = isset($data) ? $data : new stdClass();
  $settings = $data->settings;
  $per_page=isset($settings['per_page'])?$settings['per_page']:6;
  $curret_page = isset($_POST['current_page']) ? $_POST['current_page'] + 1 : 1;
  $post_types = isset($settings['post_type']) ? $settings['post_type'] : 'post';
  $text_btn= isset($settings['text_btn']) ? $settings['text_btn'] : 'Lọc';
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
  ?>
  <div class="vnx_box_wraper_fillter">
    <form method="post" class="vnx_box_fillter">
      <input type="hidden" name="vnx_perpage" value="<?php echo $per_page;?>">
      <input type="hidden" name="vnx_post_type" value="<?php echo $post_types; ?>">
      <input type="hidden" name="vnx_current_page" value="1">
      <div class="vnx_box_option">
        <?php foreach ($final as $value):
          $whitelists = [];
          foreach ($value['choices'] as $choice_value => $choice_label):
            $whitelists[] = ["value" => $choice_value, "label" => $choice_label];
          endforeach;
          ?>
          <div class="vnx_content_item" data-item="data_<?php echo $value['name']; ?>">
            <label class="vnx_item_label" for="<?php echo $value['name']; ?>"><?php echo $value['label']; ?></label>
            <input name="<?php echo $value['name']; ?>" class="selectMode" placeholder="Lựa chọn <?php echo $value['label']; ?>" data-list='<?php echo json_encode($whitelists, JSON_UNESCAPED_UNICODE); ?>' />
            <input type="hidden" name="<?php echo $value['name']; ?>" value="">
          </div>
        <?php endforeach; ?>

      </div>
      <div class="vnx_box_button">
        <button type="submit" @click="clicksubmit" class=" vnx_btn_fillter flex flex-row gap-2 items-center"><?php echo $text_btn;?> <i class="fa-regular fa-bars-filter"></i></button>
      </div>
    </form>
  </div>
  </div>
  <style>
    .tagify__dropdown {
      width: 248px ;
    }
    @media screen and (max-width: 768px) {
      .tagify__dropdown {
        width: 100% !important;
      }
      
    }
  </style>
  <?php
} catch (Exception $e) {
  echo $e->getMessage();
}
?>