<?php
if (!defined('ABSPATH')) exit;

use HelperCenter\View;

class Vnx_Search_To_Popup_Center extends \Bricks\Element
{

  public $category     = 'vietnix';
  public $name         = 'vnx-search-to-popup';
  public $icon         = 'ion-md-search';


  public function get_label()
  {
    return esc_html__('VNX Search To Popup', 'vietnix');
  }
  public function enqueue_scripts()
  {
    wp_register_script('vnx_search_popup-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/vnx_search_popup.js', ['jquery'], '1.0', true);
    wp_enqueue_script('vnx_search_popup-center');
    wp_localize_script(
      'vnx_search_popup-center',
      'vnx_search_popup_array',
      array(
        'admin_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('search_to_popup'),
      )
    );
    // wp_register_style('vnx_search_popup_css-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/css/vnx_search_popup.css', ['bricks-frontend'], '1.0', 'all');
    // wp_enqueue_style('vnx_search_popup_css-center');
  }
  public function set_control_groups()
  {
    $this->control_groups['query_data'] = [
      'title' => esc_html__('Query Data', 'vietnix'),
      'tab'   => 'content',
    ];
    $this->control_groups['search_form_content'] = [
      'title' => esc_html__('Search Form', 'vietnix'),
      'tab'   => 'content',
    ];
    $this->control_groups['popup_content'] = [
      'title' => esc_html__('Popup Content', 'vietnix'),
      'tab'   => 'content',
    ];
  }
  public function set_controls()
  {
    //Query data
    $this->controls['post_type'] = [
      'group' => 'query_data',
      'tab' => 'content',
      'label' => esc_html__('Post Type Key', 'vietnix'),
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
      'default' => esc_html__('post', 'vietnix'),
      'placeholder' => esc_html__('post', 'vietnix'),
    ];
    $this->controls['orderby'] = [
      'group' => 'query_data',
      'tab' => 'content',
      'label' => esc_html__('Order By', 'vietnix'),
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
      'default' => esc_html__('date', 'vietnix'),
      'placeholder' => esc_html__('date, name, post_views, meta_value_num,...', 'vietnix'),
      'description' => esc_html__('Muốn order theo meta key thì nhập meta_value_num hoặc meta_value, rồi điền meta_key vào ô dưới, nên nhập meta_value_num nếu giá trị Meta Key là số. Theo lượt xem thì nhập post_views', 'vietnix'),
    ];
    $this->controls['meta_key'] = [
      'group' => 'query_data',
      'tab' => 'content',
      'label' => esc_html__('Order Meta Key', 'vietnix'),
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
      'default' => esc_html__('', 'vietnix'),
      'placeholder' => esc_html__('price, sale_price,... ', 'vietnix'),
      'description' => esc_html__('Chỉ nhập nếu muốn order theo meta key', 'vietnix'),
    ];
    $this->controls['meta_key_label'] = [
      'group' => 'query_data',
      'tab' => 'content',
      'label' => esc_html__('Label Meta Key', 'vietnix'),
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
      'default' => esc_html__('', 'vietnix'),
      'placeholder' => esc_html__('Độ phổ biến, Xếp theo giá,... ', 'vietnix'),
      'description' => esc_html__('Tên hiển thị của Meta Key ở trên', 'vietnix'),
    ];
    $this->controls['order'] = [
      'group' => 'query_data',
      'tab' => 'content',
      'label' => esc_html__('Order', 'vietnix'),
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
      'default' => esc_html__('', 'vietnix'),
      'placeholder' => esc_html__('Độ phổ biến, Xếp theo giá,... ', 'vietnix'),
    ];
    $this->controls['order'] = [
      'group' => 'query_data',
      'tab' => 'content',
      'label' => esc_html__('Order', 'vietnix'),
      'type' => 'select',
      'options' => [
        'DESC' => esc_html__('DESC', 'vietnix'),
        'ASC'  => esc_html__('ASC', 'vietnix'),
      ],
    ];
    $this->controls['posts_per_page'] = [
      'group' => 'query_data',
      'tab' => 'content',
      'label' => esc_html__('Posts Per Page', 'vietnix'),
      'type' => 'number',
      'min' => 0,
      'max' => 32,
      'step' => 3,
      'default' => 0,
      'description' => esc_html__('Nhập 0 nếu muốn dùng giá trị mặc định của web', 'vietnix'),
    ];
    //Search form
    $this->controls['placeholder'] = [
      'group' => 'search_form_content',
      'tab' => 'content',
      'label' => esc_html__('Placeholder', 'vietnix'),
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
      'default' => esc_html__('Nhập từ khoá tìm kiếm', 'vietnix'),
      'placeholder' => esc_html__('Nhập từ khoá tìm kiếm', 'vietnix'),
    ];
    $this->controls['button_text'] = [
      'group' => 'search_form_content',
      'tab' => 'content',
      'label' => esc_html__('Button Text', 'vietnix'),
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
      'default' => esc_html__('Tìm kiếm', 'vietnix'),
      'placeholder' => esc_html__('Nhập nội dung cho nút tìm kiếm', 'vietnix'),
    ];
    $this->controls['button_icon'] = [
      'group' => 'search_form_content',
      'tab' => 'content',
      'label' => esc_html__('Button Icon', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'themify',
        'icon' => 'ti-star',
      ],
      'css' => [
        [
          'selector' => '.icon-svg',
        ],
      ],
    ];
    //Popup Content
    $this->controls['popup_template'] = [
      'group' => 'popup_content',
      'tab' => 'content',
      'label' => esc_html__('Border Style', 'vietnix'),
      'type' => 'select',
      'options' => [
        '1' => esc_html__('Style 1', 'vietnix'),
      ],
    ];
  }


  public function render()
  {
    View::render("widgets/bricks/vnx-search-to-popup", $this);
  }
}
