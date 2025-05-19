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
    
    $('#vp-inline-stylesheet').remove();
    
    if (!$('.vpdk-dummy-linebreak').length) {
        $('<div class="vpdk-dummy-linebreak clearfix"></div>').insertBefore('.vpdk-info-box');
    }
    
		$('#jform_params_show_social_login[disabled], #jform_params_social_btn_size[disabled]').closest('li').wrapAll('<div id="only-for-vpau" />');
		$('#only-for-vpau').append('<div id="only-for-vpau-overlay" />');
	});
})(jQuery);