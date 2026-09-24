<?php

use HelperCenter\View;

$data = isset($data) ? $data : new stdClass();

$search_query = get_search_query();
?>

<div class="flex flex-col">
  <div class="center py-16">
    <?= 'Kết quả tìm kiếm cho: ' .  $search_query ?>
  </div>

  <div class="pt-5">
    <div class="container blog main-post py-12 flex flex-col">
      <?php View::render('widgets/widget-posts', [
        'pagination_class' => 'center',
        'show_pagination' => true,
        'hide_cta' => true
      ]); ?>
    </div>
  </div>
</div>