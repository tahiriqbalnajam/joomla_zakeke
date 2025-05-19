<?php
/**
 *
 * Lists all the categories in the shop
 *
 * @package    VirtueMart
 * @subpackage Category
 * @author RickG, jseros, RolandD, Max Milbers, Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - Copyright (C) 2004 - 2022 Virtuemart Team. All rights reserved. VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default.php 10924 2023-09-29 08:36:50Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$adminTemplate = VMPATH_ROOT . '/administrator/templates/vmadmin/html/com_virtuemart/';
JLoader::register('vmuikitAdminUIHelper', $adminTemplate . 'helpers/vmuikit_adminuihelper.php');
vmuikitAdminUIHelper::startAdminArea($this);
?>

<form action="index.php?option=com_virtuemart&view=category" method="post" name="adminForm" id="adminForm">

	<?php if ($this->task == 'massxref_cats' or $this->task == 'massxref_cats_exe') : ?>
		<div id="massxref_task">
			<table class="">
				<tr>
					<td align="left">
						<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_XREF_TASK') ?>
					</td>
					<td>
						<?php
						$options = array(
							'replace' => vmText::_('COM_VIRTUEMART_PRODUCT_XREF_TASK_REPLACE'),
							'add' => vmText::_('COM_VIRTUEMART_PRODUCT_XREF_TASK_ADD'),
							'remove' => vmText::_('COM_VIRTUEMART_PRODUCT_XREF_TASK_REMOVE')
						);
						echo VmHTML::selectList('massxref_task', 'add', $options);
						?>
					</td>
				</tr>
			</table>
		</div>
	<?php endif; ?>

	<div id="filterbox" class="filter-bar">
		<?php
		$extras = array();
		$extras[] = '<select class="inputbox" id="top_category_id" name="top_category_id" onchange="this.form.submit(); return false;">
				<option value=""><?php echo vmText::sprintf( "COM_VIRTUEMART_SELECT" ,  vmText::_("COM_VIRTUEMART_CATEGORY_FORM_TOP_LEVEL")) ; ?></option>
			</select>';
		if ($this->showVendors() and !empty($this->lists['vendors'])) {
			$extras[] = $this->lists['vendors'];
		}
		//$extras[]=$this->catpagination->getLimitBox();
		echo adminSublayouts::renderAdminVmSubLayout('filterbar',
			array(
				'search' => array(
					'label' => 'COM_VIRTUEMART_NAME',
					'name' => 'search',
					'value' => $this->lists['search']
				),
				'extras' => $extras,
				'resultsCounter' => $this->catpagination->getResultsCounter(),
				'limitBox' => $this->catpagination->getLimitBox()
			));


		?>

	</div>


	<table class="uk-table uk-table-small uk-table-striped uk-table-responsive">
		<thead>
		<tr>

			<th>
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)"/>
			</th>
			<th>
				<?php echo $this->sort('category_name') ?>
			</th>
			<th>
				<?php echo $this->sort('category_description', 'COM_VIRTUEMART_DESCRIPTION'); ?>
			</th>
			<th class="uk-text-center">
				<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_S'); ?>
			</th>

			<th class="uk-table-shrink">
				<?php echo $this->sort('c.ordering', 'COM_VIRTUEMART_ORDERING') ?>
				<?php echo $this->saveOrder(); ?>
			</th>
			<th class="uk-text-center">
				<?php echo $this->sort('c.published', 'COM_VIRTUEMART_PUBLISHED') ?>
			</th>
			<?php if ($this->showVendors()) { ?>
				<th>
					<?php echo $this->sort('cx.category_shared', 'COM_VIRTUEMART_SHARED') ?>
				</th>
			<?php } ?>

			<th><?php echo $this->sort('virtuemart_category_id', 'COM_VIRTUEMART_ID') ?></th>
			<!--th></th-->
		</tr>
		</thead>
		<tbody>
		<?php
		$k = 0;
		$repeat = 0;

		$nrows = count($this->categories);

		if ($this->catpagination->limit < $nrows) {
			if (($this->catpagination->limitstart + $this->catpagination->limit) < $nrows) {
				$nrows = $this->catpagination->limitstart + $this->catpagination->limit;
			}
		}

		foreach ($this->categories as $i => $cat) {

			$checked = JHtml::_('grid.id', $i, $cat->virtuemart_category_id);
			$published = $this->gridPublished($cat, $i);

			$editlink = JRoute::_('index.php?option=com_virtuemart&view=category&task=edit&cid=' . $cat->virtuemart_category_id, FALSE);
			if (empty($cat->category_name)) {
				$cat->category_name = vmText::sprintf('COM_VM_TRANSLATION_MISSING', 'virtuemart_category_id', $cat->virtuemart_category_id);
			}
// 			$statelink	= JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $cat->virtuemart_category_id);
			$showProductsLink = JRoute::_('index.php?option=com_virtuemart&view=product&virtuemart_category_id=' . $cat->virtuemart_category_id, FALSE);
			$shared = $this->toggle($cat->shared, $i, 'toggle.shared');

			$categoryLevel = '';
			if (!isset($cat->level)) {
				if ($cat->category_parent_id) {
					$cat->level = 1;
				} else {
					$cat->level = 0;
				}

			}
			$repeat = $cat->level;

			if ($repeat > 0) {
				if(!empty($this->lists['search'])){

					$parentCatName = '';
					if($cat->category_parent_id){
						$parentCat = $this->catmodel->getCategory($cat->category_parent_id);
						$parentCatName = $parentCat->category_name;
						if($parentCat->category_parent_id){
							$categoryLevel = str_repeat(".&nbsp;&nbsp;&nbsp;", $cat->level - 1 );
							$categoryLevel .= "<sup>|_</sup>&nbsp;".$parentCatName."|_>&nbsp;";
                        } else {
							$categoryLevel = str_repeat(".&nbsp;&nbsp;&nbsp;", $cat->level -1 );
							$categoryLevel .= $parentCatName."&nbsp;|_>&nbsp;";
                        }

					} else {
						$categoryLevel = str_repeat(".&nbsp;&nbsp;&nbsp;", $cat->level -1 );
						$categoryLevel .= "<sup>|_</sup>&nbsp;";
                    }

				} else {
					$categoryLevel = str_repeat(".&nbsp;&nbsp;&nbsp;", $repeat - 1);
					$categoryLevel .= "<sup>|_</sup>&nbsp;";
				}

			}
			?>
			<tr class="<?php echo "row" . $k; ?>">

				<td><?php echo $checked; ?></td>
				<td>
					<span class="uk-hidden@m uk-margin-small-right md-color-grey-500"
							uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_CATEGORY_NAME') ?>"
							uk-icon="icon: pencil"></span>
					<span class="categoryLevel"><?php echo $categoryLevel; ?></span>
					<a href="<?php echo $editlink; ?>"><?php echo $cat->category_name; ?></a>
				</td>
				<td>
					<span class="uk-hidden@m uk-margin-small-right md-color-grey-500"
							uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_DESCRIPTION') ?>"
							uk-icon="icon: commenting"></span>
					<?php
					/*$descr = htmlspecialchars_decode($cat->category_description);
					echo shopFunctionsF::limitStringByWord(JFilterOutput::cleanText($descr),200);*/
					echo shopFunctionsF::limitStringByWord(strip_tags($cat->category_description), 200); ?>
				</td>
				<td>
					<span class="uk-hidden@m uk-margin-small-right md-color-grey-500"
							uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_S') ?>"
							uk-icon="icon: eye"></span>
					<?php //echo ShopFunctions::countProductsByCategory($cat->virtuemart_category_id);
					echo $this->categories[$i]->productcount;
					?>
					&nbsp;<a href="<?php echo $showProductsLink; ?>" class="nowrap">[ <?php echo vmText::_('COM_VIRTUEMART_SHOW'); ?> ]</a>
				</td>
				<td class="uk-text-center@m vm-order">
					<span class="uk-hidden@m uk-margin-small-right md-color-grey-500"
							uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_ORDERING') ?>"
							uk-icon="icon: arrow-up"></span>
					<?php if ($this->showDrag) { ?>
						<span class="vmicon vmicon-16-move"></span>
					<?php }

					if ($cat->showOrderUp) {
						echo '<span style="float: left;">' . $this->catpagination->vmOrderUpIcon($i, $cat->ordering, 'orderUp', vmText::_('COM_VIRTUEMART_MOVE_UP')) . '</span>';
						if($cat->showOrderDown){
							//echo '<br>';
						}
					}
					?> <input class="ordering" type="text" name="order[<?php echo  $cat->virtuemart_category_id ?>]" id="order[<?php echo $i ?>]"
							size="5" value="<?php echo $cat->ordering; ?>" style="text-align: center"/>  <?php
					if ($cat->showOrderDown) {
						echo '<span style="float: left;">' . JHtml::_('jgrid.orderDown', $i, 'orderDown', '', 'COM_VIRTUEMART_MOVE_DOWN', true, 'cb') . '</span>';
					}

					?>

				</td>
				<td class="uk-text-center@m">
					<span class="uk-hidden@m uk-margin-small-right md-color-grey-500"
							uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_PUBLISHED') ?>"
							uk-icon="icon: eye"></span>
					<?php echo $published; ?>
				</td>
				<?php
				if ((Vmconfig::get('multix', 'none') != 'none')) {
					?>
					<td class="uk-text-center@m">
						<?php echo $shared; ?>
					</td>
					<?php
				}
				?>
				<td>
					<span class="uk-hidden@m uk-margin-small-right md-color-grey-500"
							uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_ID') ?>"
							uk-icon="icon: hashtag"></span>
					<?php echo $cat->virtuemart_category_id; // echo $product->vendor_name; ?>
				</td>
				<!--td >
					<span class="vmicon vmicon-16-move"></span>
				</td-->
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</tbody>
		<tfoot>
		<tr>
			<td colspan="10">
				<?php echo $this->catpagination->getListFooter(false); ?>
			</td>
		</tr>
		</tfoot>
	</table>


	<?php

	echo $this->addStandardHiddenToForm($this->_name, $this->task);

	?>
</form>

<?php
// Removed for the moment,categories can only be drag and drop within their sublevel
//DragnDrop by StephanBais
//if ($this->virtuemart_category_id ) { ?>
<!--script>

	jQuery(function() {

		jQuery( ".adminlist" ).sortable({
			handle: ".vmicon-16-move",
			items: 'tr:not(:first,:last)',
			opacity: 0.8,
			update: function(event, ui) {
				var i = 1;
				jQuery(function updaterows() {
					jQuery(".order").each(function(index){
						var row = jQuery(this).parent('td').parent('tr').prevAll().length;
						jQuery(this).val(row);
						i++;
					});

				});
			},
			stop: function () {
				var inputs = jQuery('input.ordering');
				var rowIndex = inputs.length;
				jQuery('input.ordering').each(function(idx) {
					jQuery(this).val(idx + 1);
				});
			}

		});
	});
	jQuery('input.ordering').css({'color': '#666666', 'background-color': 'transparent','border': 'none' }).attr('readonly', true);
</script-->

<?php // } ?>

<?php AdminUIHelper::endAdminArea(); ?>
