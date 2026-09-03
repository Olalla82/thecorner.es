;(function($){
	$(document).ready(function($) {
		// Main Tabs
		$('.wpkin-flipbox-plugin-container > div').not('.getting-started-header, .getting-started-menu, .introduction').hide();

		$('.getting-started-menu .menu-item').on('click', function() {
			var target = $(this).data('target');

			// Switch active menu item
			$('.getting-started-menu .menu-item').removeClass('active');
			$(this).addClass('active');

			// Hide all content
			$('.wpkin-flipbox-plugin-container > div').not('.getting-started-header, .getting-started-menu').hide();

			// Show target content by class
			$('.' + target).show();
		});

		// Help Accordion
		$('.faq-body').hide();

		// Toggle FAQ items on header click
		$('.faq-header').click(function() {
			var $body = $(this).next('.faq-body');
			var $icon = $(this).find('i.dashicons');

			if ($body.is(':visible')) {
				// Close if already open
				$body.slideUp(200);
				$icon.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
			} else {
				// Close all other FAQ bodies
				$('.faq-body').slideUp(200);
				$('.faq-header i.dashicons').removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');

				// Open clicked FAQ
				$body.slideDown(200);
				$icon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
			}
		});

		// Changelog Accrodion
		$('#what-new .log-header').click(function(){
			var $header = $(this);
			var $log = $header.parent(); // The .log div
			var $body = $header.next('.log-body');

			// Toggle this log
			$body.slideToggle(200);
			$header.find('i.dashicons').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
			$log.toggleClass('active');

			// Close other logs
			$('#what-new .log').not($log).removeClass('active').find('.log-body').slideUp(200);
			$('#what-new .log').not($log).find('i.dashicons').removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
		});

	});

})(jQuery);