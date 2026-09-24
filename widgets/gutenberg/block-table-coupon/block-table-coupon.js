jQuery(document).ready(function($) {
	// Prevent duplicate event binding
	if (window.vnxTableCouponInitialized) {
		return;
	}
	window.vnxTableCouponInitialized = true;

	$(document).on('click', '.vnx-get-code-btn', function() {
		const $button = $(this);
		const code = $button.data('code');

		// Copy code to clipboard
		navigator.clipboard.writeText(code).then(function() {
			// Check if button is mobile button
			if ($button.hasClass('mobile-btn')) {
				// Mobile button with icons
				const $copyIcon = $button.find('.fa-copy');
				const $checkIcon = $button.find('.fa-check');
				
				$copyIcon.hide();
				$checkIcon.show();
				
				// Reset after 5 seconds
				setTimeout(function() {
					$copyIcon.show();
					$checkIcon.hide();
				}, 5000);
			} else {
				// Desktop button with text
				const originalText = $button.text();
				$button.text('Đã sao chép');
				
				// Reset after 2 seconds
				setTimeout(function() {
					$button.text(originalText);
				}, 2000);
			}
		}).catch(function(err) {
			console.error('Could not copy text: ', err);
		});
	});
});
