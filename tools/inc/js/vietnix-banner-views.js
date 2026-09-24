(function ($) {

	'use strict';

	$(function () {

		var viewed_ids = '';

		if ($('.vietnix-banner-item').length) {

			$('.vietnix-banner-item').each(function () {

				var $this = $(this),
					ad_id = $this.attr('data-id');
				if (viewed_ids.indexOf(ad_id) == -1) {
					viewed_ids = viewed_ids + ',' + ad_id;
				}

			});

			/** Ajax count VIEWS number */
			$.post(vietnix_banner_count_js.url, {
				action: 'update_vietnix_banner_view_count_center',
				ids: viewed_ids
			});

			/** Ajax count CLICKS number */
			$(document).on('click', '.vietnix-banner-item', function (event) {
				// Link
				var $this = $(this),
					ad_id = $this.attr('data-id');

				$.post(vietnix_banner_count_js.url, {
					action: 'update_vietnix_banner_click_count_center',
					ad_id: ad_id,
				});
			});
		}
	});

})(jQuery);