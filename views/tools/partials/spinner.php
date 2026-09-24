<?php

/**
 * Spinner SVG dùng chung cho nút bấm/overlay đang xử lý (Vue).
 *
 * Gọi: View::render('tools/partials/spinner', ['show' => 'loading']);
 * $show: tên biến Vue để bind v-if (vd. 'loading', 'exporting', 'isLoading').
 *       Bỏ trống nếu spinner nằm trong khối cha đã tự v-if rồi (không cần bind lại).
 * $class: class thêm ngoài 'vnx-spinner' (mặc định rỗng).
 */

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

$show = isset($data->show) ? (string) $data->show : 'loading';
$class = isset($data->class) && $data->class !== '' ? 'vnx-spinner ' . $data->class : 'vnx-spinner';
?>
<svg <?= $show !== '' ? 'v-if="' . esc_attr($show) . '" ' : '' ?>class="<?= esc_attr($class) ?>"
  xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
</svg>
