/**
 * catreeajax.js: load category tree by ajax
 *
 * @package	VirtueMart
 * @subpackage Javascript Library
 * @author Max Milbers
 * @copyright Copyright (c) 2020 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
// vmText::sprintf( 'COM_VIRTUEMART_SELECT' ,  vmText::_('COM_VIRTUEMART_CATEGORY'))

//Virtuemart.empty;
//Virtuemart.param;
if (typeof Virtuemart === "undefined")
	var Virtuemart = {};
Virtuemart.startVmLoading = function(a) {
	var msg = '';
	/*if (typeof a.data.msg !== 'undefined') {
	 msg = a.data.msg;
	 }*/
	jQuery('[data-vm="ajax_cat_load"]').addClass('vmLoading');
	if (!jQuery('div.vmLoadingDiv').length) {
		jQuery('body').append('<div class=\"vmLoadingDiv\"><div class=\"vmLoadingDivMsg\">' + msg + '</div></div>');
	}
};

Virtuemart.stopVmLoading = function() {
	if (jQuery('[data-vm="ajax_cat_load"]').hasClass('vmLoading')) {
		jQuery('body').removeClass('vmLoading');
		jQuery('div.vmLoadingDiv').remove();
	}
};

Virtuemart.loadCategoryTree = function(id){
	jQuery('#'+id+'_chzn').remove();
	jQuery('<div data-vm=\"ajax_cat_load\" style=\"display:inline-block;width:220px;background-color:#ddd;height:25px;line-height:25px;padding:0 10px;box-sizing:border-box;background-size:20px\">Loading</div>').insertAfter('select#'+id);
	Virtuemart.startVmLoading('Loading categories');

	if (jQuery('body').is('.admin')) {
		Virtuemart.ajaxCategoryUrl = 'option=com_virtuemart&view=product&type=getCategoriesTree'+Virtuemart.param+'&format=json&vmFE=1'+Virtuemart.vmLang;
		var vmSiteurl = Virtuemart.vmSiteurl + 'administrator/index.php';
	}else{
		Virtuemart.ajaxCategoryUrl = 'option=com_virtuemart&view=category&type=getCategoriesTree'+Virtuemart.param+'&format=json&vmFE=1'+Virtuemart.vmLang;
		var vmSiteurl = Virtuemart.vmSiteurl + 'index.php';
	}
	jQuery.ajax({
		type: 'GET',
		url: vmSiteurl,
		cache: false,
		data: Virtuemart.ajaxCategoryUrl,
		success:function(json){
			jQuery('select#'+id).switchClass('chzn-done','chzn-select');
			jQuery('select#'+id).html('<option value=\"0\">'+Virtuemart.emptyCatOpt+'</option>'+json.value);
			jQuery('select#'+id).chosen({select_some_options_text:Virtuemart.selectSomeCategory});
			jQuery('select#'+id).trigger("chosen:updated");
			Virtuemart.stopVmLoading();
			jQuery('[data-vm="ajax_cat_load"]').remove();
		}
	});
};