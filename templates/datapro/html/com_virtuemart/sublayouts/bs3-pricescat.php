<?php
/**
 *
 * Show the product prices
 *
 * @package    VirtueMart
 * @subpackage
 * @author Max Milbers, Valerie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_showprices.php 8024 2014-06-12 15:08:59Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined ('_JEXEC') or die('Restricted access');

/** @var TYPE_NAME $viewData */
$product = $viewData['product'];
$currency = $viewData['currency'];
$view = vRequest::getCmd('view');
?>
<div class="product-price" id="productPrice<?php echo $product->virtuemart_product_id ?>" data-vm="product-prices">
<?php
if ($product->prices['salesPrice']<=0 and VmConfig::get ('askprice', 1) and isset($product->images[0]) and !$product->images[0]->file_is_downloadable) {
	$askquestion_url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&task=askquestion&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id . '&tmpl=component', FALSE);
	if ($view != 'productdetails') {
	?>
	<a class="ask-a-question btn btn-link btn-block" href="<?php echo $askquestion_url ?>" rel="nofollow" >
	<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_ASKPRICE') ?>
	</a>
	<?php } else { ?>
	<a class="iframe-src btn btn-default btn-block" href="#form-collapse-anchor" data-href="<?php echo $askquestion_url ?>" title="<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_ENQUIRY_LBL') ?>">
	<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_ASKPRICE') ?>
	</a>
	<?php }
	} else {
	    echo '<div class="vm-basic-prices clearfix">';
		echo $currency->createPriceDiv ('salesPrice', 'inkl. ges. MwSt.', $product->prices);
		//echo $currency->createPriceDiv ('salesPriceTt', 'COM_VIRTUEMART_PRODUCT_SALESPRICE_TT', $product->prices);
		$infotax = vmConfig::get('vm_prices_info_tax', 0);
		$infodelivery = vmConfig::get('vm_prices_info_delivery', 0);
		if ($infotax == 1 or $infodelivery == 1) { ?>
			<div class="vm-prices-info">
			<?php
			if ($infotax == 1) {
				if ($product->prices['priceWithoutTax'] == $product->prices['salesPrice']) {
					$taxclusive = 'COM_VM_TAX_EXCLUSIVE';
				} else {
					$taxclusive = 'COM_VM_TAX_INCLUSIVE';
				}
				echo vmText::_($taxclusive);
			}
			if ($infotax == 1 and $infodelivery == 1) {
				echo vmText::_('COM_VM_PRICES_INFO_DIVIDER');
			}
			if ($infodelivery == 1) {
				echo vmText::_('COM_VM_PRICES_INFO_DELIVERY');
			} ?>
			</div>
			<?php
		}

		if ($product->prices['discountAmount'] != -0) {
			//echo $currency->createPriceDiv ('basePriceWithTax', 'COM_VIRTUEMART_PRODUCT_BASEPRICE_WITHTAX', $product->prices);
            //echo $currency->createPriceDiv ('basePriceWithTax', '', $product->prices);
		}
		echo '</div>';
		//echo $currency->createPriceDiv ('basePrice', 'COM_VIRTUEMART_PRODUCT_BASEPRICE', $product->prices);
		//echo $currency->createPriceDiv ('basePriceVariant', 'COM_VIRTUEMART_PRODUCT_BASEPRICE_VARIANT', $product->prices);
		//echo $currency->createPriceDiv ('variantModification', 'COM_VIRTUEMART_PRODUCT_VARIANT_MOD', $product->prices);
		if (round($product->prices['salesPriceWithDiscount'],$currency->_priceConfig['salesPrice'][1]) != $product->prices['salesPrice']) {
			//echo $currency->createPriceDiv ('salesPriceWithDiscount', 'COM_VIRTUEMART_PRODUCT_SALESPRICE_WITH_DISCOUNT', $product->prices);
		}
		if ($product->prices['discountedPriceWithoutTax'] != $product->prices['priceWithoutTax']) {
			//echo $currency->createPriceDiv ('discountedPriceWithoutTax', 'COM_VIRTUEMART_PRODUCT_SALESPRICE_WITHOUT_TAX', $product->prices);
			//echo $currency->createPriceDiv ('discountedPriceWithoutTaxTt', 'COM_VIRTUEMART_PRODUCT_SALESPRICE_WITHOUT_TAX_TT', $product->prices);
		} else {
			//echo $currency->createPriceDiv ('priceWithoutTax', 'COM_VIRTUEMART_PRODUCT_SALESPRICE_WITHOUT_TAX', $product->prices);
			//echo $currency->createPriceDiv ('priceWithoutTaxTt', 'COM_VIRTUEMART_PRODUCT_SALESPRICE_WITHOUT_TAX_TT', $product->prices);
		}
			//echo $currency->createPriceDiv ('discountAmount', 'COM_VIRTUEMART_PRODUCT_DISCOUNT_AMOUNT', $product->prices);
			//echo $currency->createPriceDiv ('discountAmountTt', 'COM_VIRTUEMART_PRODUCT_DISCOUNT_AMOUNT_TT', $product->prices);
			//echo $currency->createPriceDiv ('taxAmount', 'COM_VIRTUEMART_PRODUCT_TAX_AMOUNT', $product->prices);
			//echo $currency->createPriceDiv ('taxAmountTt', 'COM_VIRTUEMART_PRODUCT_TAX_AMOUNT_TT', $product->prices);
		$unitPriceDescription = vmText::sprintf ('COM_VIRTUEMART_PRODUCT_UNITPRICE', vmText::_('COM_VIRTUEMART_UNIT_SYMBOL_'.strtoupper($product->product_unit)));
		//echo $currency->createPriceDiv ('unitPrice', $unitPriceDescription, $product->prices);
	}
	?>
<div style="float: right; width: 100%; font-size: 12px; text-align: right">zzgl. <a href="lieferung-versand" target="_blank">Versandkosten</a></div>
</div>