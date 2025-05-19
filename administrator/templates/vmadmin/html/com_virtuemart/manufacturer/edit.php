<?php
/**
*
*
* @package	VirtueMart
* @subpackage Manufacturer
* @author Patrick Kohl
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - Copyright (C) 2004 - 2022 Virtuemart Team. All rights reserved. VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: edit.php 10649 2022-05-05 14:29:44Z Milbo $
 *
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$adminTemplate = VMPATH_ROOT . '/administrator/templates/vmadmin/html/com_virtuemart/';
JLoader::register('vmuikitAdminUIHelper', $adminTemplate . 'helpers/vmuikit_adminuihelper.php');
vmuikitAdminUIHelper::startAdminArea($this);

?>

<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm" id="adminForm" class="uk-form-horizontal">

<?php // Loading Templates in Tabs
$tabarray = array();
$tabarray['description'] = 'COM_VIRTUEMART_DESCRIPTION';
$tabarray['images'] = 'COM_VIRTUEMART_IMAGES';

vmuikitAdminUIHelper::buildTabs (
		$this,
	$tabarray ,
	$cookieName = 'virtuemart_manufacturer_id',
	$width = 'uk-width-1-1'
);

// Loading Templates in Tabs END ?>

	<input type="hidden" name="virtuemart_manufacturer_id" value="<?php echo $this->manufacturer->virtuemart_manufacturer_id; ?>" />
	<?php echo $this->addStandardHiddenToForm(); ?>
</form>
<?php
echo adminSublayouts::renderAdminVmSubLayout('images_template');
?>
<?php
vmJsApi::addJScript('vm-toggle','
function toggleDisable( elementOnChecked, elementDisable, disableOnChecked ) {
	try {
		if( !disableOnChecked ) {
			if(elementOnChecked.checked==true) {
				elementDisable.disabled=false;
			}
			else {
				elementDisable.disabled=true;
			}
		}
		else {
			if(elementOnChecked.checked==true) {
				elementDisable.disabled=true;
			}
			else {
				elementDisable.disabled=false;
			}
		}
	}
	catch( e ) {}
}

function toggleFullURL() {
	if( jQuery("#manufacturer_full_image_url").val().length>0) document.adminForm.manufacturer_full_image_action[1].checked=false;
	else document.adminForm.manufacturer_full_image_action[1].checked=true;
	toggleDisable( document.adminForm.manufacturer_full_image_action[1], document.adminForm.manufacturer_thumb_image_url, true );
	toggleDisable( document.adminForm.manufacturer_full_image_action[1], document.adminForm.manufacturer_thumb_image, true );
}');

vmuikitAdminUIHelper::endAdminArea(); ?>