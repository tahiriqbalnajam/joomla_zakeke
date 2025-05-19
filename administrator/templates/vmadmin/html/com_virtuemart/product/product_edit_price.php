<?php
/**
 *
 * Main product information
 *
 * @package    VirtueMart
 * @subpackage Product
 * @author Max Milbers
 * @todo Price update calculations
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - Copyright (C) 2004 - 2022 Virtuemart Team. All rights reserved. VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: product_edit_price.php 10935 2023-11-02 19:13:09Z Milbo $
 * http://www.seomoves.org/blog/web-design-development/dynotable-a-jquery-plugin-by-bob-tantlinger-2683/
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access'); ?>

<?php
$rowColor = 0;

$visibility = '';
$visibilityStyle = '';
if(!$this->expertPrices){
	$visibility ='visibility:hidden';
	$visibilityStyle = 'style="'.$visibility.'"';
}

?>
<table class="adminform productPriceTable">

	<tr class="row<?php echo $rowColor ?>">
		<td width="120px">
			<div style="text-align: right; font-weight: bold;">
								<span uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_COST_TIP'); ?>">
									<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_COST');  ?>
								</span>
			</div>
		</td>
		<td width="140px"><input
					type="text"
					class="input-medium"
					name="mprices[product_price][]"
					size="12"
					style="text-align:right;"
					value="<?php echo $this->product->allPrices[$this->product->selectedPrice]['costPrice']; ?>"/>
			<input type="hidden"
					name="mprices[virtuemart_product_price_id][]"
					value="<?php echo $this->product->allPrices[$this->product->selectedPrice]['virtuemart_product_price_id']; ?>"/>
		</td>
		<td width="185px">
			<?php echo $this->lists['currencies']; ?>
		</td>
		<td>
		</td>
		<td style="background: #d5d5d5;padding:0;width:1px;"></td>

		<?php //if($this->expertPrices or !empty($this->lists['shoppergroups'])){ ?>
			<td colspan="2" >
			<span style="font-weight: bold;"
				  uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_SHOPPER_FORM_GROUP_PRICE_TIP'); ?>">
						<?php echo vmText::_('COM_VIRTUEMART_SHOPPER_FORM_GROUP') ?></span>
				<?php echo $this->lists['shoppergroups']; ?>
			</td>
		<?php //} ?>

	</tr>
	<?php $rowColor = 1 - $rowColor; ?>
	<tr class="row<?php echo $rowColor ?>">
		<td>
			<div style="text-align: right; font-weight: bold;">
								<span

										uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_BASE_TIP'); ?>">
									<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_BASE'); echo ' '; echo $this->vendor_currency_symb;?>
								</span>
			</div>
		</td>
		<td><input
					type="text"
					readonly
					class="input-medium"
					name="mprices[basePrice][]"
					size="12"
					value="<?php echo $this->product->allPrices[$this->product->selectedPrice]['basePrice']; ?>"/>&nbsp;

		</td>
		<?php /* <td width="17%"><div style="text-align: right; font-weight: bold;">
					<?php echo vmText::_('COM_VIRTUEMART_RATE_FORM_VAT_ID') ?></div>
				</td> */
		?>

		<td>
			<?php echo $this->lists['taxrates']; ?><br/>
		</td>

		<td>
			<span uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_RULES_EFFECTING_TIP') ?>">
			<?php echo vmText::_('COM_VIRTUEMART_TAX_EFFECTING') . '<br />' . $this->taxRules ?>
			</span>
		</td>
        <td style="background: #d5d5d5;padding:0;width:1px;"></td>
		<?php
		$date = null;
		$text = null;
		if(!empty($this->product->allPrices[$this->product->selectedPrice]['modified_on'])){
			$text = 'COM_VIRTUEMART_ORDER_LIST_MDATE';
			$date = $this->product->allPrices[$this->product->selectedPrice]['modified_on'];
		} else if(!empty($this->product->allPrices[$this->product->selectedPrice]['created_on'])){
			$date = $this->product->allPrices[$this->product->selectedPrice]['created_on'];
			$text = 'COM_VIRTUEMART_CREATED_ON';
		}
		if(isset($date)){
			echo '<td>';

			echo vmText::_($text).' '.$date;
			echo '</td>';
		}
		?>

		<?php if($this->expertPrices){ ?>
		<td>
			<?php ?>
		</td>
		<td>
			<?php ?>
		</td>
		<?php } ?>
	</tr>
	<?php $rowColor = 1 - $rowColor; ?>
	<tr class="row<?php echo $rowColor ?>">
		<td>
			<div style="text-align: right; font-weight: bold;">
				<span uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_FINAL_TIP'); ?>">
					<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_FINAL'); echo ' '; echo $this->vendor_currency_symb;?>
				</span>
			</div>
		</td>
		<td><input
					type="text"
					name="mprices[salesPrice][]"
					class="input-medium"
					size="12"
					style="text-align:right;"
					value="<?php echo $this->product->allPrices[$this->product->selectedPrice]['salesPriceTemp']; ?>"/>
		</td>
		<?php /* <td width="17%"><div style="text-align: right; font-weight: bold;">
					<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_DISCOUNT_TYPE') ?></div>
				</td>*/ 
		?>
		<td>
			<?php //if(empty($this->lists['discounts'])) {
			echo $this->lists['discounts'];
				echo '<input type="checkbox" name="mprices[use_desired_price][' . $this->priceCounter . ']" value="1"/>'
			?>
			<strong>
				<span uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_CALCULATE_PRICE_FINAL_TIP'); ?>">
				<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_CALCULATE_PRICE_FINAL'); ?>
			</span>
			</strong><?php

			//} else {
				//echo $this->lists['discounts'];
			//} ?> <br/>
		</td>
		<td>
			<span uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_RULES_EFFECTING_TIP') ?>">
			<?php if (!empty($this->DBTaxRules)) {
				echo vmText::_('COM_VIRTUEMART_RULES_EFFECTING') . '</span><br />' . $this->DBTaxRules . '<br />';

			}
			if (!empty($this->DATaxRules)) {
				echo vmText::_('COM_VIRTUEMART_RULES_EFFECTING') . '<br />' . $this->DATaxRules;
			}

			// 						vmdebug('my rules',$this->DBTaxRules,$this->DATaxRules); echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_DISCOUNT_EFFECTING').$this->DBTaxRules;  ?>
			</span>
		</td>
		<td style="background: #d5d5d5;padding:0;width:1px;"></td>
		<?php if($this->expertPrices
                or (isset($this->product->allPrices[$this->product->selectedPrice]['product_price_publish_up']) and $this->product->allPrices[$this->product->selectedPrice]['product_price_publish_up']!="0000-00-00 00:00:00")
                or (isset($this->product->allPrices[$this->product->selectedPrice]['product_price_publish_down']) and $this->product->allPrices[$this->product->selectedPrice]['product_price_publish_down']!="0000-00-00 00:00:00")
		    ){ ?>
			<td nowrap >
				<?php echo vmJsApi::jDate($this->product->allPrices[$this->product->selectedPrice]['product_price_publish_up'], 'mprices[product_price_publish_up][]'); ?>
			</td>
			<td nowrap >
				<?php echo vmJsApi::jDate($this->product->allPrices[$this->product->selectedPrice]['product_price_publish_down'], 'mprices[product_price_publish_down][]'); ?>
			</td>
		<?php } ?>

	</tr>

	<?php $rowColor = 1 - $rowColor;


	?>
	<tr class="row<?php echo $rowColor ?>" >

		<td width="60px">
			<div style="text-align: right; font-weight: bold;">
				<span uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_DISCOUNT_OVERRIDE_TIP'); ?>">
					<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_DISCOUNT_OVERRIDE') ?>
				</span>
			</div>
		</td>

		<td colspan="3">
			<div style="margin-right: 20px; display: inline">
				<input type="text"
						class="input-medium"
						size="12"
						style="text-align:right;" name="mprices[product_override_price][]"
						value="<?php echo $this->product->allPrices[$this->product->selectedPrice]['product_override_price'] ?>"/>
				<?php echo $this->vendor_currency_symb; ?>
			</div>
			<?php /*
			$options = array(0 => vmText::_('JNO'), 1 => vmText::_('JYES'));
			// echo VmHtml::radioList ('mprices[use_desired_price][' . $this->priceCounter . ']', $this->product->override, $options);
			echo '<input type="checkbox" name="mprices[use_desired_price][' . $this->priceCounter . ']" value="1"/>'
			?>
			<strong>
			<span uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_CALCULATE_PRICE_FINAL_TIP'); ?>">
			<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_CALCULATE_PRICE_FINAL'); ?>
			</span>
			</strong>
*/ ?>
			<br/>
			<?php
			// 							echo VmHtml::checkbox('override',$this->product->override);
			$options = array(0 => vmText::_('COM_VIRTUEMART_DISABLED'), 1 => vmText::_('COM_VIRTUEMART_OVERWRITE_FINAL'), -1 => vmText::_('COM_VIRTUEMART_OVERWRITE_PRICE_TAX'));

			echo VmHtml::radioList('mprices[override][' . $this->priceCounter . ']', $this->product->allPrices[$this->product->selectedPrice]['override'], $options, '', ' ');
			?>
		</td>
        <?php if($this->expertPrices or !empty($this->product->allPrices[$this->product->selectedPrice]['product_override_price'])
        or !empty($this->product->allPrices[$this->product->selectedPrice]['price_quantity_start'])
        or !empty($this->product->allPrices[$this->product->selectedPrice]['price_quantity_end']) ){
            ?>
		<td style="background: #d5d5d5;padding:0;width:1px;"></td>
		<td>
			<div style="font-weight: bold;">
				<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_PRICE_QUANTITY_RANGE') ?>
			</div>
			<input type="text"
					class="input-mini"
					size="12"
					style="text-align:right;" name="mprices[price_quantity_start][]"
					value="<?php echo $this->product->allPrices[$this->product->selectedPrice]['price_quantity_start'] ?>"/>
		</td>
		<td>
			<br/>
			<input type="text"
					size="12"
					class="input-mini"
					style="text-align:right;" name="mprices[price_quantity_end][]"
					value="<?php echo $this->product->allPrices[$this->product->selectedPrice]['price_quantity_end'] ?>"/>
		</td>
        <?php } ?>


    </tr>
</table>



