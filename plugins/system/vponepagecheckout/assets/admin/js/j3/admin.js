/**
 * @package      VP One Page Checkout - Joomla! System Plugin
 * @subpackage   For VirtueMart 3+ and VirtueMart 4+
 *
 * @copyright    Copyright (C) 2012-2024 Virtueplanet Services LLP. All rights reserved.
 * @license      GNU General Public License version 2 or later; see LICENSE.txt
 * @authors      Abhishek Das <info@virtueplanet.com>
 * @link         https://www.virtueplanet.com
 */

(function($) {
	$(function() {
		var $title = $('.vp-extension-description .extension-title [data-text]');
		$title.text($title.data('text'));

		$('.hide-on-j3').closest('.control-group').hide();
		$('#vp-inline-stylesheet').remove();

		$('#jform_params_show_social_login[disabled], #jform_params_social_btn_size[disabled]').closest('.control-group').wrapAll('<div id="only-for-vpau" />');
		$('#only-for-vpau').append('<div id="only-for-vpau-overlay" />');

		$(window).on('load', function() {
			$('#only-for-vpau').width($('#only-for-vpau > .control-group:first-child').width());
		});
		
		$(window).on('resize', function() {
			$('#only-for-vpau').width($('#only-for-vpau > .control-group:first-child').width());
		});

		var title;
		$('label').each(function() {
			title = $(this).attr('title');
			if (title && title.length && title.indexOf('</strong>') && title.indexOf('<br/>')) {
				title = title.replace('<br/>', '');
				if (title.indexOf('<br />') === -1) {
					title = title.replace('</strong>', '</strong><br/>');
				}
				$(this).attr('title', title);
			}
		});

		$('.hasTooltip').tooltip({
			'html': true,
			'container': 'body'
		});
	});
})(jQuery);
