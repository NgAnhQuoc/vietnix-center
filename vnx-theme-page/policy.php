<?php
/*
 * Template Name: Policy
 * Template Post Type: page
 */

use HelperCenter\View;

get_header();

$data = [
  [
    'vi' => 'Điều khoản sử dụng dịch vụ',
    'en' => 'Terms Of Service',
    'url' => '/dieu-khoan-su-dung-dich-vu/'
  ],
  [
    'vi' => 'Báo cáo lạm dụng',
    'en' => 'Report Abuse',
    'url' => '/bao-cao-lam-dung/'
  ],
  [
    'vi' => 'Cam kết chất lượng dịch vụ',
    'en' => 'Commitments To Services',
    'url' => '/cam-ket-chat-luong-dich-vu/'
  ],
  [
    'vi' => 'Sang nhượng tên miền',
    'en' => 'Domain Name Transfer',
    'url' => '/sang-nhuong-ten-mien/'
  ],
  [
    'vi' => 'Quy định sử dụng tên miền .vn',
    'en' => 'Regulations On The Use Of Domain Name .vn',
    'url' => '/quy-dinh-su-dung-ten-mien-vn/'
  ],
  [
    'vi' => 'Quy định sử dụng tên miền quốc tế',
    'en' => 'Regulations On The Use Of International Domain Name',
    'url' => '/quy-dinh-su-dung-ten-mien-quoc-te/'
  ],
  [
    'vi' => 'Chính sách giải quyết khiếu nại',
    'en' => 'Complaints Handling Policy',
    'url' => '/chinh-sach-giai-quyet-khieu-nai/'
  ],
  [
    'vi' => 'Chính sách hoàn tiền',
    'en' => 'Refund Policy',
    'url' => '/chinh-sach-hoan-tien/'
  ],
  [
    'vi' => 'Chính sách bảo mật thông tin',
    'en' => 'Privacy Policy',
    'url' => '/chinh-sach-bao-mat-thong-tin/'
  ],
  [
    'vi' => 'Hướng dẫn thanh toán',
    'en' => 'Payment Instructions',
    'url' => '/huong-dan-thanh-toan/'
  ],
  [
    'vi' => 'Quy định chống thư rác',
    'en' => 'Anti-Spam Policy',
    'url' => '/quy-dinh-chong-thu-rac/'
  ]
]
?>
<div class="flex flex-col">
  <main class="flex-1">
    <div class="page-policy flex flex-col">
      <div class="header-banner border-b border-gray-200">
        <div class="vnx-spacer-inner-15"></div>
        <div class="container flex flex-col lg:flex-row py-5">
          <div class="w-full flex-1 center">
            <h1 class="vnx-page-title "><?php the_title() ?></h1>
          </div>
          <div class="w-full flex-1 center">
            <img src="https://vietnix.vn/wp-content/uploads/2021/06/policy-image.png" width="auto" height="auto" style="max-height: 320px;" />
          </div>
        </div>
        <div class="vnx-spacer-inner-15"></div>
      </div>
      <div class="vnx-spacer-inner-50"></div>
      <div class="container flex flex-col lg:flex-row py-5">
        <div class="w-full flex">
          <div class="vnx-nav-left pr-2">
            <ul class="nav-link">
              <?php foreach ($data as $item) : ?>
                <li class="nav-item-link">
                  <a href="<?php echo $item['url']; ?>">
                    <?php echo $item['vi']; ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <article class="flex-1 pl-2">
            <div class="article-content">
              <?php while (have_posts()) : the_post(); ?>
                <?php the_content(); ?>
              <?php endwhile; ?>
            </div>
          </article>
        </div>
      </div>
      <div class="vnx-spacer-inner-50"></div>
    </div>
  </main>
</div>

<?php get_footer(); ?>