<?php
define_if_not_defined_Center('Vietnix_Sync_Telegram_Sheet', 'Vietnix_Sync_Telegram_Sheet');
define_if_not_defined_Center('Vietnix_Sync_Telegram_Sheet_Center__FILE__', __FILE__);
define_if_not_defined_Center('Vietnix_Sync_Telegram_Sheet_Center__URL', plugins_url('/', __FILE__));
define_if_not_defined_Center('Vietnix_Sync_Telegram_Sheet_Center__PATH', plugin_dir_path(__FILE__));
define_if_not_defined_Center('Vietnix_Sync_Telegram_Sheet_Center__VERSION', '1.0.0');
define_if_not_defined_Center('Vietnix_Sync_Telegram_Sheet_Center__PREFIX', 'vnx_Sync_Telegram_Sheet_');



if (!function_exists('vnx_sync_form_info_Center')) {
  /**
   * Tim cac element "form" cua Bricks co field an name = form_field_id.
   * Tra ve danh sach ['form_id' => gia tri form_field_id, 'name' => "Form name" (submissionFormName, co the rong),
   * 'element_id' => ID element form - chinh la cot "Form ID" o Bricks > Form Submissions].
   */
  function vnx_sync_forms_in_elements_Center($elements)
  {
    $forms = array();
    if (!is_array($elements)) {
      return $forms;
    }
    foreach ($elements as $element) {
      if (!is_array($element) || ($element['name'] ?? '') !== 'form' || empty($element['settings']['fields']) || !is_array($element['settings']['fields'])) {
        continue;
      }
      foreach ($element['settings']['fields'] as $field) {
        $form_id = is_array($field) ? trim((string) ($field['value'] ?? '')) : '';
        if (($field['name'] ?? '') === 'form_field_id' && $form_id !== '') {
          $forms[] = array(
            'form_id' => $form_id,
            'name' => trim((string) ($element['settings']['submissionFormName'] ?? '')),
            'element_id' => trim((string) ($element['id'] ?? '')),
          );
          break;
        }
      }
    }
    return $forms;
  }

  /**
   * Map form_field_id => ['name' => ten de hieu, 'element_ids' => [ID element form Bricks]] cho trang Tools.
   * Ten: "Form name" dat trong element form cua Bricks, khong co thi lay tieu de template/trang chua form.
   * element_ids: moi ban sao cua form (template, popup, page) co ID rieng; ID cua ban cho ra ten dung dau.
   *
   * - Chi quet bricks_template va page: tren production _bricks_page_content_2 ~47k dong / 1.5 GB (phan lon la
   *   post + revision), quet het se rat cham. Form tren bai post deu den tu template nen khong bi sot.
   * - Uu tien: publish > draft/private, bricks_template > page, sua gan nhat truoc.
   * - Global element cua Bricks (option bricks_global_elements) luu settings ngoai postmeta nen quet rieng.
   * - Cache transient 12h, xoa khi luu template/page hoac global element.
   */
  function vnx_sync_form_info_Center()
  {
    $cached = get_transient('vnx_sync_form_info');
    if (is_array($cached)) {
      return $cached;
    }

    global $wpdb;
    $info = array();
    // Ten lay tu tieu de chi la tam: gap ban sao co "Form name" thi ghi de.
    $from_title = array();
    $add_form = function ($form, $title) use (&$info, &$from_title) {
      $clean = function ($text) {
        return trim(html_entity_decode(wp_strip_all_tags((string) $text), ENT_QUOTES, 'UTF-8'));
      };
      $form_id = $form['form_id'];
      $form_name = $clean($form['name']);
      $title = $clean($title);
      if (!isset($info[$form_id])) {
        $info[$form_id] = array('name' => '', 'element_ids' => array());
      }

      $takes_name = false;
      if ($form_name !== '' && ($info[$form_id]['name'] === '' || isset($from_title[$form_id]))) {
        $info[$form_id]['name'] = $form_name;
        unset($from_title[$form_id]);
        $takes_name = true;
      } elseif ($title !== '' && $info[$form_id]['name'] === '') {
        $info[$form_id]['name'] = $title;
        $from_title[$form_id] = true;
        $takes_name = true;
      }

      $element_id = $form['element_id'];
      if ($element_id !== '' && !in_array($element_id, $info[$form_id]['element_ids'], true)) {
        if ($takes_name) {
          array_unshift($info[$form_id]['element_ids'], $element_id);
        } else {
          $info[$form_id]['element_ids'][] = $element_id;
        }
      }
    };

    // Buoc 1: chi lay ID (khong keo meta_value ve PHP); STRAIGHT_JOIN de MySQL loc posts theo post_type truoc.
    $rows = $wpdb->get_results(
      "SELECT STRAIGHT_JOIN DISTINCT p.ID, p.post_title, p.post_status, p.post_type, p.post_modified
       FROM {$wpdb->posts} p
       INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
       WHERE p.post_type IN ('bricks_template', 'page')
         AND p.post_status IN ('publish', 'private', 'draft')
         AND pm.meta_key IN ('_bricks_page_content_2', '_bricks_page_header_2', '_bricks_page_footer_2')
         AND pm.meta_value LIKE '%form\\_field\\_id%'
       ORDER BY (p.post_status = 'publish') DESC, (p.post_type = 'bricks_template') DESC, p.post_modified DESC"
    );

    // Buoc 2: doc tung post mot de khong giu nhieu meta lon trong bo nho cung luc.
    foreach ((array) $rows as $row) {
      foreach (array('_bricks_page_content_2', '_bricks_page_header_2', '_bricks_page_footer_2') as $meta_key) {
        foreach (vnx_sync_forms_in_elements_Center(get_post_meta((int) $row->ID, $meta_key, true)) as $form) {
          $add_form($form, $row->post_title);
        }
      }
    }

    $global_elements = get_option('bricks_global_elements', array());
    if (is_array($global_elements)) {
      foreach ($global_elements as $global_element) {
        foreach (vnx_sync_forms_in_elements_Center(array($global_element)) as $form) {
          $add_form($form, !empty($global_element['label']) ? $global_element['label'] : 'Global element');
        }
      }
    }

    set_transient('vnx_sync_form_info', $info, 12 * HOUR_IN_SECONDS);
    return $info;
  }

  $vnx_sync_form_info_flush = function () {
    delete_transient('vnx_sync_form_info');
  };
  add_action('save_post_bricks_template', $vnx_sync_form_info_flush);
  add_action('save_post_page', $vnx_sync_form_info_flush);
  // Option chua co thi WP goi add_option_*, khong phai update_option_*.
  add_action('add_option_bricks_global_elements', $vnx_sync_form_info_flush);
  add_action('update_option_bricks_global_elements', $vnx_sync_form_info_flush);
  add_action('delete_option_bricks_global_elements', $vnx_sync_form_info_flush);
}
