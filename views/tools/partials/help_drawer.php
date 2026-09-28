<?php

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

$context = isset($data->context) && $data->context === 'settings' ? 'settings' : 'tools';

$sections_by_context = array(
  'tools' => array(
    'title' => 'Hướng dẫn',
    'sections' => array(
      array(
        'id' => 'tong-quan',
        'icon' => 'fa-compass',
        'label' => 'Tổng quan',
        'blocks' => array(
          array(
            'type' => 'lead',
            'content' => 'Trang <strong>Tools</strong> là nơi thao tác với những tiện ích Vietnix đã bật cho website - mỗi tool có một khu vực cấu hình riêng biệt, không ảnh hưởng lẫn nhau.',
          ),
          array(
            'type' => 'list',
            'items' => array(
              'Chỉ tool đang ở trạng thái <strong>bật</strong> mới xuất hiện ở đây. Việc bật/tắt được quản lý tập trung ở trang <strong>Settings</strong>, tab <strong>Tool</strong>.',
              'Mỗi tool có panel riêng và tự lưu cấu hình theo hành động của tool đó (ví dụ nút "Tạo bài viết" ở Import Docs, "Export ngay" ở Sitemap Export...) - không dùng chung một form với tool khác.',
              'Có thể truy cập trực tiếp một tool bằng đường link riêng của nó (một số tool như Import Docs, UTM Tracker có menu riêng trong sidebar WordPress); màn hình vẫn hiện đúng panel đó kèm nút <strong>Tất cả Tool</strong> để quay lại danh sách đầy đủ.',
            ),
          ),
        ),
      ),
      array(
        'id' => 'bat-dau',
        'icon' => 'fa-rocket',
        'label' => 'Bắt đầu nhanh',
        'blocks' => array(
          array(
            'type' => 'lead',
            'content' => 'Chưa thấy tool cần dùng? Làm theo 3 bước sau để đưa nó ra trang Tools.',
          ),
          array(
            'type' => 'steps',
            'items' => array(
              'Vào <strong>Vietnix → Settings</strong>, chọn tab <strong>Tool</strong>.',
              'Tìm đúng tool trong danh mục tương ứng (Content, SEO & Sitemap, Domain & Pricing...), gạt công tắc sang bật.',
              'Bấm nút <strong>Lưu</strong> ở cuối danh mục đó, rồi quay lại trang <strong>Tools</strong> - tool sẽ xuất hiện ngay trong sidebar bên trái.',
            ),
          ),
        ),
      ),
      array(
        'id' => 'dung-tool',
        'icon' => 'fa-toolbox',
        'label' => 'Điều hướng & tìm tool',
        'blocks' => array(
          array(
            'type' => 'list',
            'items' => array(
              'Ô <strong>Tìm tool...</strong> ở đầu sidebar lọc theo tên và mô tả - gõ vài ký tự là danh sách thu gọn ngay, không cần cuộn tìm thủ công.',
              'Tool được nhóm theo danh mục (ví dụ <strong>Content</strong>, <strong>SEO & Sitemap</strong>, <strong>Domain & Pricing</strong>, <strong>Integrations</strong>, <strong>System</strong>) để dễ định vị khi danh sách dài.',
              'Bấm vào tên một tool trong sidebar để mở panel cấu hình của nó ở khung bên phải; tool đang mở được đánh dấu bằng vạch xanh bên trái và nền sáng hơn.',
              'Số "<strong>x tool đang bật</strong>" ở góc trên bên trái cho biết tổng số tool khả dụng hiện tại trên trang.',
            ),
          ),
        ),
      ),
      array(
        'id' => 'cache-scheduler',
        'icon' => 'fa-clock',
        'label' => 'Cache Scheduler',
        'blocks' => array(
          array(
            'type' => 'lead',
            'content' => 'Hẹn giờ tự động xoá cache <strong>LiteSpeed Cache</strong> theo URL cụ thể. Cần plugin <strong>LiteSpeed Cache</strong> đã cài và đang bật thì các lịch hẹn mới chạy được.',
          ),
          array(
            'type' => 'steps',
            'items' => array(
              'Bấm <strong>Thêm lịch mới</strong>.',
              'Nhập danh sách URL cần xoá cache, mỗi dòng 1 link; có thể dùng đường dẫn tương đối như <code>/blog/bai-viet</code>.',
              'Chọn loại lịch: <strong>Chạy 1 lần</strong> (chọn ngày giờ, tự tắt sau khi chạy), <strong>Hằng ngày</strong> hoặc <strong>Hằng tuần</strong> (chọn thêm các ngày trong tuần).',
              'Tick <strong>Bật lịch này ngay sau khi lưu</strong> rồi bấm <strong>Lưu lịch</strong>.',
            ),
          ),
          array(
            'type' => 'list',
            'items' => array(
              'Bảng danh sách cho bật/tắt từng lịch bằng công tắc, <strong>Sửa</strong>, hoặc <strong>Chạy ngay</strong>/<strong>Xoá</strong> (2 nút này hỏi xác nhận trước khi chạy, con trỏ chuột đổi thành "?" để báo trước) để test purge thủ công không cần đợi tới giờ.',
              'Cột <strong>Lần gần nhất</strong> cho biết lần chạy gần nhất là khi nào và có thành công hay không - sửa lại 1 lịch (đổi tên, đổi giờ...) không làm mất lịch sử này.',
              'Công tắc <strong>Tự động chạy</strong> ở đầu trang là cầu dao tổng - tắt sẽ tạm dừng toàn bộ cron mà không đổi trạng thái bật/tắt riêng của từng lịch. Mặc định đang tắt, phải chủ động bật.',
            ),
          ),
          array(
            'type' => 'tip',
            'tip_tone' => 'warning',
            'content' => 'Chưa cài hoặc đang tắt <strong>LiteSpeed Cache</strong> thì trang hiện banner cảnh báo, nút <strong>Chạy ngay</strong> và mọi thao tác <strong>bật</strong> (lịch mới, công tắc từng dòng, Tự động chạy) đều bị khoá - nhưng vẫn <strong>tắt</strong> được lịch đang bật bình thường. Lịch hẹn đã lưu không mất, chỉ tạm dừng chạy cho tới khi LiteSpeed được cài/bật lại.',
          ),
          array(
            'type' => 'tip',
            'tip_tone' => 'info',
            'content' => 'Bấm icon <strong>chuông</strong> (cạnh công tắc Tự động chạy) để mở hộp thoại <strong>Thông báo qua Discord</strong>: nhập Webhook URL, tick <strong>Bật thông báo</strong>, bấm <strong>Gửi thử</strong> để kiểm tra rồi <strong>Lưu cài đặt</strong>. Icon chuông tự chuyển xanh khi đang bật để nhận biết nhanh không cần mở ra xem. Mỗi lần 1 lịch chạy xong (tự động hoặc Chạy ngay) sẽ gửi 1 tin nhắn vào kênh đó, kèm tên người/hệ thống đã chạy.',
          ),
          array(
            'type' => 'tip',
            'tip_tone' => 'info',
            'content' => 'Mặc định tool dùng WP-Cron (cơ chế lịch có sẵn của WordPress, tự kiểm tra mỗi 5 phút) nên không cần cấu hình gì thêm - nhưng WP-Cron chỉ được kích hoạt khi có người/bot ghé site, không phải tiến trình chạy ngầm thật của server, nên site ít traffic có thể trễ giờ. Muốn chạy đúng giờ tuyệt đối, nhờ kỹ thuật thêm cron job thật trên hosting (cPanel hoặc SSH), khuyến nghị mỗi phút: <code>* * * * * php /duong-dan-toi-wp-content/plugins/' . VNX_PLUGIN_SLUG_CENTER . '/crontab/cacheScheduler.php</code>',
          ),
        ),
      ),
    ),
  ),
  'settings' => array(
    'title' => 'Hướng dẫn',
    'sections' => array(
      array(
        'id' => 'tong-quan',
        'icon' => 'fa-compass',
        'label' => 'Tổng quan',
        'blocks' => array(
          array(
            'type' => 'lead',
            'content' => 'Trang <strong>Settings</strong> là trung tâm điều khiển của plugin Vietnix: mọi Widget, Extension, Tool đều phải được <strong>bật ở đây</strong> trước khi dùng được ở bất kỳ đâu khác trên website.',
          ),
          array(
            'type' => 'list',
            'items' => array(
              '4 tab tương ứng 4 nhóm cấu hình: <strong>Widget</strong> (element/block cho trình dựng trang), <strong>Extensions</strong> (tiện ích mở rộng), <strong>Tool</strong> (tiện ích quản trị ở trang Tools) và <strong>Options</strong> (cấu hình chung toàn plugin).',
              'Số hiển thị cạnh tên tab (ví dụ <strong>3/8</strong>) là "đang bật / tổng số có sẵn" - nhìn thoáng qua là biết còn gì chưa dùng tới.',
              'Với <strong>Extensions</strong> và <strong>Tool</strong>, tắt bật thoải mái: cấu hình đã nhập trước đó không bị xoá, chỉ ẩn/hiện tính năng. Riêng <strong>Widget</strong> ảnh hưởng tới trang đang hiển thị ngoài website - xem lưu ý ở tab Widget trước khi tắt.',
            ),
          ),
        ),
      ),
      array(
        'id' => 'bat-tat',
        'icon' => 'fa-toggle-on',
        'label' => 'Bật / tắt tính năng',
        'blocks' => array(
          array(
            'type' => 'steps',
            'items' => array(
              'Chọn tab tương ứng: <strong>Widget</strong>, <strong>Extensions</strong> hoặc <strong>Tool</strong>.',
              'Nếu là tab <strong>Widget</strong>, chọn thêm nhóm <strong>Bricks</strong> hoặc <strong>Gutenberg</strong> ở cột bên trái. Nếu là tab <strong>Tool</strong>, chọn danh mục tương ứng (Content, SEO & Sitemap...).',
              'Gạt công tắc bên phải mỗi mục để bật (xanh) hoặc tắt (xám).',
              'Bấm nút <strong>Lưu</strong> ở cuối đúng khối vừa chỉnh - mỗi khối/danh mục có nút Lưu riêng, chỉ áp dụng thay đổi trong khối đó.',
            ),
          ),
          array(
            'type' => 'tip',
            'tip_tone' => 'warning',
            'content' => 'Đổi công tắc mà quên bấm <strong>Lưu</strong> thì thay đổi sẽ mất khi rời trang hoặc chuyển tab khác - mỗi khối phải lưu độc lập, lưu ở khối này không tự lưu khối kia.',
          ),
        ),
      ),
      array(
        'id' => 'widget',
        'icon' => 'fa-puzzle-piece',
        'label' => 'Widget',
        'blocks' => array(
          array(
            'type' => 'list',
            'items' => array(
              'Tab <strong>Widget</strong> chia theo builder: <strong>Bricks</strong> và <strong>Gutenberg</strong> - chỉ cần bật đúng nhóm builder đang dùng để xây trang, nhóm còn lại có thể để tắt hết.',
              'Sau khi bật, widget sẽ xuất hiện trong danh sách element (Bricks) hoặc block (Gutenberg) khi kéo-thả dựng trang - không cần thao tác gì thêm ở nơi khác.',
            ),
          ),
          array(
            'type' => 'tip',
            'tip_tone' => 'warning',
            'content' => 'Chỉ tắt widget khi chắc chắn <strong>không có trang nào đang dùng</strong> nó. Widget bị tắt sẽ không còn được nhận diện, nên các block/element đã chèn trên trang cũ có thể hiển thị sai hoặc không lên nội dung ngoài website - bật lại đúng widget đó là khôi phục về bình thường.',
          ),
        ),
      ),
      array(
        'id' => 'options',
        'icon' => 'fa-sliders',
        'label' => 'Options',
        'blocks' => array(
          array(
            'type' => 'list',
            'items' => array(
              'Tab <strong>Options</strong> chứa cấu hình dùng chung cho toàn bộ plugin, áp dụng cho mọi tool/widget cần đến (không thuộc riêng tab nào).',
              '<strong>Cookie Domain</strong>: domain dùng khi tool cần đọc/ghi cookie liên trang con (ví dụ <code>.vietnix.vn</code> để cookie dùng chung được cho mọi subdomain). Điền sai domain có thể khiến tool liên quan không đọc được cookie đã lưu.',
            ),
          ),
          array(
            'type' => 'tip',
            'tip_tone' => 'info',
            'content' => 'Giá trị này thường chỉ cần đặt một lần lúc cài plugin và khớp với domain chính của website - ít khi phải đổi lại sau đó.',
          ),
        ),
      ),
      array(
        'id' => 'faq',
        'icon' => 'fa-circle-question',
        'label' => 'Câu hỏi thường gặp',
        'blocks' => array(
          array(
            'type' => 'faq',
            'items' => array(
              array(
                'q' => 'Tắt một tool có mất cấu hình đã nhập không?',
                'a' => 'Không. Dữ liệu cấu hình của tool vẫn được giữ nguyên, tắt chỉ ẩn tool đó khỏi trang Tools. Bật lại là dùng tiếp với đúng cấu hình cũ.',
              ),
              array(
                'q' => 'Tắt một Widget thì sao, có ảnh hưởng gì tới trang đang chạy không?',
                'a' => 'Có thể có. Nếu trang nào đó ngoài website đang dùng widget này, tắt đi sẽ khiến phần nội dung đó hiển thị sai/không lên cho tới khi bật lại. Chỉ tắt khi chắc chắn không còn trang nào sử dụng.',
              ),
              array(
                'q' => 'Đã bật tool ở Settings nhưng không thấy ở trang Tools?',
                'a' => 'Kiểm tra lại đã bấm nút Lưu ở đúng khối chưa, sau đó tải lại trang Tools. Nếu vẫn không thấy, có thể tool đó chưa có sẵn giao diện cấu hình trong phiên bản plugin hiện tại.',
              ),
              array(
                'q' => 'Bật nhầm quá nhiều thứ, muốn dọn lại cho gọn thì sao?',
                'a' => 'Vào từng tab, tắt những mục không dùng rồi bấm Lưu. Không có giới hạn số lần bật/tắt, thoải mái điều chỉnh cho tới khi vừa ý.',
              ),
            ),
          ),
        ),
      ),
    ),
  ),
);

$title = $sections_by_context[$context]['title'];
$sections = $sections_by_context[$context]['sections'];
$vnx_help_allowed_html = array('strong' => array(), 'code' => array());
?>
<button type="button" data-vnx-help-open
  class="inline-flex items-center gap-2 rounded border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-600 hover:border-[#38A7FF] hover:text-[#38A7FF]">
  <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20" aria-hidden="true">
    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
      d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z" />
    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
      d="M7.6 7.5a2.4 2.4 0 1 1 3.4 2.2c-.7.35-1 .77-1 1.3v.3" />
    <path stroke="currentColor" stroke-linecap="round" stroke-width="1.8" d="M10 14.5h.01" />
  </svg>
  Hướng dẫn
</button>

<div class="vnx-drawer" id="vnx-help-drawer" hidden>
  <div class="vnx-drawer__backdrop" data-vnx-help-close></div>
  <div class="vnx-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="vnx-help-drawer-title" tabindex="-1">
    <div class="vnx-drawer__header">
      <!-- Cố tình không dùng thẻ <h2> thật: WP core dời admin notice (vd. "Action
           Scheduler...") tới ngay sau h1/h2 đầu tiên trong #wpbody-content khi trang
           không có marker .wp-header-end. Trang Tools/Settings không có h1 nên WP sẽ
           chọn nhầm heading này (luôn có sẵn trong DOM dù đang ẩn) làm điểm chèn. -->
      <div id="vnx-help-drawer-title" class="vnx-drawer__title" role="heading" aria-level="2"><?= esc_html($title) ?></div>
      <button type="button" class="vnx-drawer__close" data-vnx-help-close aria-label="Đóng hướng dẫn">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 20 20" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 5 10 10M15 5 5 15" />
        </svg>
      </button>
    </div>

    <div class="vnx-drawer__body">
      <div class="vnx-subtabs vnx-subtabs--vertical" role="tablist" aria-label="Chủ đề hướng dẫn"
        data-hs-tabs-vertical="true">
        <?php foreach ($sections as $section_index => $section): ?>
          <button type="button" class="vnx-subtab <?= $section_index === 0 ? 'active' : '' ?>" role="tab"
            id="vnx-help-tab-<?= esc_attr($section['id']) ?>" data-hs-tab="#vnx-help-panel-<?= esc_attr($section['id']) ?>"
            aria-controls="vnx-help-panel-<?= esc_attr($section['id']) ?>">
            <i class="fa-solid <?= esc_attr($section['icon']) ?>" aria-hidden="true"></i>
            <?= esc_html($section['label']) ?>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="vnx-drawer__panels">
        <?php foreach ($sections as $section_index => $section): ?>
          <div id="vnx-help-panel-<?= esc_attr($section['id']) ?>" role="tabpanel"
            aria-labelledby="vnx-help-tab-<?= esc_attr($section['id']) ?>"
            class="vnx-drawer__panel-content <?= $section_index === 0 ? '' : 'hidden' ?>">
            <?php foreach ($section['blocks'] as $block): ?>
              <?php if ($block['type'] === 'lead'): ?>
                <p class="vnx-drawer__lead"><?= wp_kses($block['content'], $vnx_help_allowed_html) ?></p>
              <?php elseif ($block['type'] === 'list'): ?>
                <ul class="vnx-drawer__list">
                  <?php foreach ($block['items'] as $item): ?>
                    <li><?= wp_kses($item, $vnx_help_allowed_html) ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php elseif ($block['type'] === 'steps'): ?>
                <ol class="vnx-drawer__steps">
                  <?php foreach ($block['items'] as $item): ?>
                    <li><?= wp_kses($item, $vnx_help_allowed_html) ?></li>
                  <?php endforeach; ?>
                </ol>
              <?php elseif ($block['type'] === 'tip'): ?>
                <div class="vnx-drawer__tip vnx-drawer__tip--<?= esc_attr($block['tip_tone'] ?? 'info') ?>">
                  <i class="fa-solid <?= $block['tip_tone'] === 'warning' ? 'fa-triangle-exclamation' : 'fa-lightbulb' ?>"
                    aria-hidden="true"></i>
                  <p><?= wp_kses($block['content'], $vnx_help_allowed_html) ?></p>
                </div>
              <?php elseif ($block['type'] === 'faq'): ?>
                <dl class="vnx-drawer__faq">
                  <?php foreach ($block['items'] as $qa): ?>
                    <div class="vnx-drawer__faq-item">
                      <dt><?= esc_html($qa['q']) ?></dt>
                      <dd><?= wp_kses($qa['a'], $vnx_help_allowed_html) ?></dd>
                    </div>
                  <?php endforeach; ?>
                </dl>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>