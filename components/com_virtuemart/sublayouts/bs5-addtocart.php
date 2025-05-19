<?php

/**
 *
 * Show the product details page
 *
 * @package    VirtueMart
 * @subpackage
 * @author Max Milbers, Valerie Isaksen
 * @todo handle child products
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_addtocart.php 7833 2014-04-09 15:04:59Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Router\Route;

/** @var TYPE_NAME $viewData */
$product = $viewData['product'];

if (isset($viewData['rowHeights']))
{
	$rowHeights = $viewData['rowHeights'];
} else {
	$rowHeights['customfields'] = TRUE;
}

if (isset($viewData['position']))
{
	$positions = $viewData['position'];
} else {
	$positions = 'addtocart';
}

if(!is_array($positions)) $positions = array($positions);
?>
<div class="addtocart-area">
	<form class="product js-recalculate h-100 d-flex flex-column" method="post" action="<?php echo Route::_ ('index.php?option=com_virtuemart',false); ?>" autocomplete="off" >
		<div class="vm-customfields-wrap">
			<?php
			if (!empty($rowHeights['customfields']))
			{
				foreach($positions as $pos) {
					echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$product,'position'=>$pos));
				}
			}
			?>
		</div>
		<?php
		if (!VmConfig::get('use_as_catalog', 0)  and !($product->product_discontinued and !VmConfig::get('discontinuedPrdsBrowseable',1)))
		{
			echo shopFunctionsF::renderVmSubLayout('addtocartbar',array('product'=>$product));
		}
		?>
		<input type="hidden" name="option" value="com_virtuemart"/>
		<input type="hidden" name="view" value="cart"/>
		<input type="hidden" name="pname" value="<?php echo $product->product_name ?>"/>
		<input type="hidden" name="pid" value="<?php echo $product->virtuemart_product_id ?>"/>
		<?php
		$itemId=vRequest::getInt('Itemid',false);

		if ($itemId)
		{
			echo '<input type="hidden" name="Itemid" value="'.$itemId.'"/>';
		}
		?>
	</form>
</div>