<?php

use HelperCenter\View;
try {
    $settings = $data->settings;
    $display_mode = isset($settings['display_mode']) ? $settings['display_mode'] : '';

    View::render("widgets/bricks/vnx-search-ai/" . $display_mode, $data);
} catch (Exception $e) {
    error_log('Error rendering vnx-search-ai: ' . $e->getMessage());
}
