jQuery(document).ready(function ($) {
	const __ = wp.i18n.__;

	function toggleDisable(disable, selector = '.typesense-input') {
		$(selector).attr('disabled', disable);
	}

	function resetToggle(show) {
		$('.typesenseserver-pass-wrap .wp-hide-pw')
			.attr({
				'aria-label': show ? __('Show token') : __('Hide token')
			})
			.find('.text')
			.text(show ? __('Show') : __('Hide'))
			.end()
			.find('.dashicons')
			.removeClass(show ? 'dashicons-hidden' : 'dashicons-visibility')
			.addClass(show ? 'dashicons-visibility' : 'dashicons-hidden');
	}

	$('input[name=typesense_enabled]')
		.each(function (_, e) {
			if (e.checked) {
				toggleDisable(e.value === 'default');
			}
		})
		.on('click', function (e) {
			toggleDisable(e.target.value === 'default');
		});

	console.log($('.typesenseserver-pass-wrap .wp-hide-pw'))

	$('.typesenseserver-pass-wrap .wp-hide-pw')
		.show().on('click', function () {
			const $pass1 = $('#typesense-token');

			if ('password' === $pass1.attr('type')) {
				$pass1.attr('type', 'text');
				resetToggle(false);
			} else {
				$pass1.attr('type', 'password');
				resetToggle(true);
			}
		});

});
