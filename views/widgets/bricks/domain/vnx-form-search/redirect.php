<?php
if (!isset($data->settings)) {
  if (current_user_can('update_core'))
    echo '<div class="vnx_error no_data"><b>Không có $data tryền vào: ' . esc_html(__FILE__) . '</div>';
  return;
}
$settings = $data->settings;
$placeholder = isset($settings['placeholder']) ? $settings['placeholder'] : '';
$button_text = isset($settings['button_text']) ? $settings['button_text'] : '';
$button_icon = isset($settings['button_icon']) ? $settings['button_icon'] : array();
$button_class = isset($settings['button_class']) ? ' '.$settings['button_class'] : '';
$redirect_url = isset($settings['redirect_url']) ? $settings['redirect_url'] : '';
?>
<form method="GET" action="<?php echo esc_attr($redirect_url); ?>" class="relative">
  <div class="vnx_wrapper rounded overflow-hidden relative">
    <input type="text" class="relative z-0 py-1.5 border-none outline-none" name="domain" placeholder="<?php echo esc_attr($placeholder); ?>" v-model="domain">
    <button type="submit" class="absolute z-[1] right-0 top-0 h-full flex items-center<?php echo esc_attr( $button_class ); ?>" @click="clickSearchButtonRedirect">
      <?php
      if (!empty($button_icon))
        echo Bricks\Element::render_icon($button_icon, ['vnx_icon']);
      echo '<span class="button_text">' . esc_html($button_text) . '</span>';
      ?>
    </button>
    <div class="loading_small flex flex-col items-center justify-center absolute w-full left-0 top-0 z-1 h-full" v-if="DomainLoading == true">
      <div class="loading_wrapper">
        <div class="loading_icon"></div>
      </div>
    </div>
  </div>
</form>