  <?php

	$id = get_the_ID();
	$link = esc_url(get_the_permalink());
	$demo_link = get_field('custom_link', $id) ? get_field('custom_link', $id) : $link;
	$link_option = get_field('custom_link', $id) ? " target='_blank' rel='nofollow'" : '';
	$title = get_the_title();
	$thumbnail = get_the_post_thumbnail_url($id, 'blog_thumbnail') ?: "https://vietnix.vn/wp-content/uploads/2022/10/thumb-mac-dinh.png";
	?>

  <div id="post-<?= $id ?>" class="relative flex-1 flex flex-col rounded-md overflow-hidden" style="box-shadow: 0px 1px 8px 2px rgba(0, 0, 0, 0.05);">

  	<div class="relative vietnix-theme-post-thumbnail">
  		<div class="aspect-ratio-1200/630">
  			<img src="<?= $thumbnail ?>" class="absolute left-0 top-0 w-full h-full object-cover">
  		</div>
  		<div class="w-full h-full btn-box absolute hidden top-0 p-5 rounded-md">
  			<div class="font-medium text-base relative">
  				<div class="w-full text-center">
  					<a rel="nofollow" href="<?= $link ?>">
  						<button class="border rounded-md py-2.5 mb-5 text-white"><?php echo esc_attr_e('Xem Chi Tiết') ?></button>
  					</a>
  				</div>
  				<div class="w-full text-center">
  					<a href="#cac-dich-vu-tang-kem">
  						<button class="bg-[#38A7FF] rounded-md  py-2.5 text-white"><?php echo esc_attr_e('Đăng ký ngay') ?></button>
  					</a>
  				</div>
  			</div>
  		</div>
  	</div>

  	<div class="flex flex-col px-6 py-4 h-full relative">

  		<div class="justify-between grid grid-cols-3">
  			<a href="<?= $link ?>" title="<?= $title ?>" class="wptheme-name f-roboto text-base font-black leading-normal col-span-2">
  				<?= $title ?>
  			</a>
  			<a class="text-right" href="<?php echo $demo_link; ?>" <?php echo $link_option; ?>> <button class="rounded-md border py-2 px-3 text-xs hover:bg-[#38A7FF] hover:text-white" style="border-color:#38A7FF;color:#38A7FF;"> <?php echo esc_html('Xem Demo') ?></button></a>
  		</div>

  		<div class="mt-4 ">
  			<?php
				if (get_field("lable")) {
				?>
  				<span class="rounded-full py-2 px-3 text-sm text-white" style="background-color:#27AE60;"> <?php the_field("lable") ?></span>
  			<?
				}
				if (get_field("danh_gia_sao")) {
					$rattingStart = get_field("danh_gia_sao");
					$round_rating = (int) $rattingStart;
					$rating_icon = '&#xE934;';
				?>
  				<div class="grid grid-cols-3 gap-4 mt-2">

  					<div class="ratting flex col-span-2">

  						<div class="vnx-theme-start-rating mr-1 flex gap-x-1">
  							<?php for ($ii = 1; $ii <= 5; $ii++) :
									if ($ii <= $round_rating) { ?>
  									<i class="vnx-rating-icon-point"></i>
  								<?php
									} else {
									?>
  									<i class="vnx-rating-icon-empty"></i>
  								<? } ?>
  							<?php endfor; ?>
  						</div>

  						<div class="ratting-count text-xs font-normal">
  							<? if (get_field("luot_danh_gia")) { ?>
  								<?php echo esc_html("(" . number_format($rattingNumber = get_field("luot_danh_gia")) . ")") ?>
  							<? } ?>
  						</div>

  					</div>

  					<div class="brand-logo">
  						<? if (get_field("kho_theme")) { ?>
  							<img class="w-full" src="<?php echo esc_html(get_field("kho_theme")); ?>" alt="">
  						<? } ?>
  					</div>

  				</div>
  			<?
				}
				?>
  		</div>

  	</div>

  </div>

  <style>
  	.vnx-theme-start-rating i {
  		display: inline;
  		position: relative;
  		font-family: 'Font Awesome 6 Pro';
  		font-style: normal;
  		line-height: 1;
  		overflow: hidden;
  		color: #d8d8d8;
  		font-size: 16px;
			width: 18px;
  	}

  	.vnx-theme-start-rating i:before {
  		content: '\f005';
  		font-weight: 900;
  		display: block;
  		position: absolute;
  		top: 0;
  		left: 0;
  		font-size: inherit;
  		font-family: inherit;
  		overflow: hidden;
  		color: #FFBC47;
  	}

  	.vnx-theme-start-rating i.vnx-rating-icon-empty:before {
  		content: '\f005';
			color: #d8d8d8;
  	}

  	.wptheme-name {
  		height: 48px;
  		overflow: hidden;
  	}

  	.btn-box>div {
  		top: 50%;
  		transform: translate(0, -50%);
  	}

  	.btn-box button {
  		width: 50%;
  	}

  	.btn-box {
  		background: hsla(0, 0%, 0%, 0.7);
  		;
  	}

  	.info-box-hover {
  		color: white;
  		background-color: #000;
  	}
  </style>

  <script>
  	jQuery(document).ready(function($) {
  		$(".vietnix-theme-post-thumbnail").on("mouseover", function() {
  				$(this).find('div.btn-box').removeClass("hidden");
  			}, )
  			.on("mouseout", function() {
  				$(this).find('div.btn-box').addClass("hidden");
  			}, );
  	});
  </script>