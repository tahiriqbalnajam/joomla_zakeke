<?php
/**
 *
 * @package    VirtueMart
 * @subpackage
 * @author Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - Copyright (C) 2004 - 2022 Virtuemart Team. All rights reserved. VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default.php 10935 2023-11-02 19:13:09Z Milbo $
 */
defined('_JEXEC') or die();

$adminTemplate = VMPATH_ROOT . '/administrator/templates/vmadmin/html/com_virtuemart/';
JLoader::register('vmuikitAdminUIHelper', $adminTemplate . 'helpers/vmuikit_adminuihelper.php');
vmuikitAdminUIHelper::startAdminArea($this);

jimport('joomla.filesystem.file');

/* Get the component name */
$option = vRequest::getCmd('option');

/* Load some variables */
$keyword = vRequest::getCmd('keyword', null);
$iconRatio=0.75;
?>
	<form action="index.php?option=com_virtuemart&view=custom" method="post" name="adminForm" id="adminForm">

		<div id="filterbox" class="filter-bar">
			<?php
			$extras = array();

			$extras[] = $this->customsSelect;
			echo adminSublayouts::renderAdminVmSubLayout('filterbar',
				array(
					'search' => array(
						'label' => vmText::_('COM_VIRTUEMART_SEARCH_LBL') . ' ' . vmText::_('COM_VIRTUEMART_TITLE'),
						'name' => 'keyword',
						'value' => vRequest::getVar('keyword')
					),
					'extras' => $extras,
					'resultsCounter' => $this->pagination->getResultsCounter()
				));


			?>

		</div>
		<?php
		$customs = $this->customs->items;
		//$roles = $this->customlistsroles;

		?>

		<table class="uk-table uk-table-small uk-table-striped uk-table-responsive">
			<thead>
			<tr>
				<th><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)"/></th>
				<th><?php echo vmText::_('COM_VIRTUEMART_CUSTOM_GROUP'); ?></th>
				<th><?php echo vmText::_('COM_VIRTUEMART_TITLE'); ?></th>
				<th><?php echo vmText::_('COM_VIRTUEMART_CUSTOM_FIELD_DESCRIPTION'); ?></th>
				<th><?php echo vmText::_('COM_VIRTUEMART_CUSTOM_LAYOUT_POS'); ?></th>
				<th><?php echo vmText::_('COM_VIRTUEMART_CUSTOM_FIELD_TYPE'); ?></th>
				<th class="uk-text-center@m"><?php echo vmText::_('COM_VIRTUEMART_CUSTOM_IS_CART_ATTRIBUTE'); ?></th>
				<th class="uk-text-center@m"><?php echo vmText::_('COM_VIRTUEMART_CUSTOM_ADMIN_ONLY'); ?></th>
				<th class="uk-text-center@m"><?php echo vmText::_('COM_VIRTUEMART_CUSTOM_IS_HIDDEN'); ?></th>
				<?php if (!empty($this->custom_parent_id)) {
					?>
					<th class="uk-text-center@m">
						<?php
						echo $this->sort('ordering');
						echo $this->saveOrder();
						?>
					</th>
					<?php
				}
				?>
				<th class="uk-text-center@m"><?php echo vmText::_('COM_VIRTUEMART_PUBLISHED'); ?></th>
				<th class="uk-text-center@m"><?php echo $this->sort('virtuemart_custom_id', 'COM_VIRTUEMART_ID') ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			if ($n = count($customs)) {

				$i = 0;
				$k = 0;
				foreach ($customs as $key => $custom) {

					$checked = JHtml::_('grid.id', $i, $custom->virtuemart_custom_id, false, 'virtuemart_custom_id');
					if (!is_null($custom->virtuemart_custom_id)) {
						$published = $this->gridPublished($custom, $i);
					} else {
						$published = '';
					}
					?>
					<tr class="row<?php echo $k; ?>">
						<!-- Checkbox -->
						<td><?php echo $checked; ?></td>
						<?php
						$link = "index.php?view=custom&keyword=" . vmURI::urlencode($keyword) . "&custom_parent_id=" . $custom->custom_parent_id . "&option=" . $option;
						?>
						<td>
					<span class="uk-hidden@m uk-margin-small-right md-color-grey-500"
							uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_CUSTOM_GROUP') ?>"
							uk-icon="icon: copy"></span>
							<?php

							$lang = vmLanguage::getLanguage();
							$text = $lang->hasKey($custom->group_title) ? vmText::_($custom->group_title) : $custom->group_title;

							echo JHtml::_('link', JRoute::_($link, FALSE), $text, array('title' => vmText::_('COM_VIRTUEMART_FILTER_BY') . ' ' . htmlentities($text))); ?>
						</td>

						<!-- Product name -->
						<?php
						$link = "index.php?option=com_virtuemart&view=custom&task=edit&virtuemart_custom_id=" . $custom->virtuemart_custom_id;
						if ($custom->is_cart_attribute) {
							$cartIcon = 'default';
						} else {
							$cartIcon = 'default-off';
						}
						?>
						<td>
							<span class="uk-hidden@m uk-margin-small-right md-color-grey-500"
							uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_TITLE') ?>"
							uk-icon="icon: pencil"></span>
							<?php echo JHtml::_('link', JRoute::_($link, FALSE), vmText::_($custom->custom_title), array('title' => vmText::_('COM_VIRTUEMART_EDIT') . ' ' . htmlentities($custom->custom_title))); ?>
						</td>
						<td>
							<span class="uk-hidden@m uk-margin-small-right md-color-grey-500"
							uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_CUSTOM_FIELD_DESCRIPTION') ?>"
							uk-icon="icon: commenting"></span>
							<?php echo vmText::_($custom->custom_desc); ?></td>
						<td>
							<span class="uk-hidden@m uk-margin-small-right md-color-grey-500"
							uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_CUSTOM_LAYOUT_POS') ?>"
							uk-icon="icon: location"></span>
							<?php echo vmText::_($custom->layout_pos); ?></td>
						<td>
							<span class="uk-hidden@m uk-margin-small-right md-color-grey-500"
							uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_CUSTOM_FIELD_TYPE') ?>"
							uk-icon="icon: question"></span>
							<?php echo vmText::_($custom->field_type_display); ?></td>
						<td class="uk-text-center@m">
							<span class="uk-hidden@m uk-margin-small-right md-color-grey-500"
							uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_CUSTOM_IS_CART_ATTRIBUTE') ?>"
							uk-icon="icon: cart2"></span>
							<?php
							if ($custom->is_cart_attribute) {
								$cartIconColor = 'md-color-green-800';
								$cartIconText = 'COM_VIRTUEMART_CUSTOM_IS_CART_ATTRIBUTE';
							} else {
								$cartIconColor = 'md-color-grey-400';
								$cartIconText='COM_VIRTUEMART_CUSTOM_IS_CART_ATTRIBUTE_NO';
							}
							?>
							<span class="<?php echo $cartIconColor ?>"
									uk-tooltip="<?php echo vmText::_($cartIconText) ?>"
									uk-icon="icon: cart2;ratio:<?php echo $iconRatio ?>"></span>
						</td>
						<td class="uk-text-center@m">
							<?php
							if ($custom->admin_only) {
								$adminColor = 'md-color-green-800';
								$adminText = 'COM_VIRTUEMART_CUSTOM_ADMIN_ONLY';
							} else {
								$adminColor = 'md-color-grey-400';
								$adminText='COM_VIRTUEMART_CUSTOM_ADMIN_ONLY_NO';
							}
							?>
							<span class="uk-hidden@m uk-margin-small-right md-color-grey-500"
							uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_CUSTOM_ADMIN_ONLY') ?>"
							uk-icon="icon: 'shop"></span>
							<a href="javascript:void(0);"
									class="uk-icon-button uk-icon-button-small uk-button-default"
									onclick="return Joomla.listItemTask('cb<?php echo $i; ?>','toggle.admin_only')"
									uk-tooltip="<?php echo vmText::_($adminText); ?>">
								<span class="<?php echo $adminColor ?>"
										uk-tooltip="<?php echo vmText::_($adminText) ?>"
										uk-icon="icon: shop2;ratio:<?php echo $iconRatio ?>"></span>

							</a>
						</td>
						<td class="uk-text-center@m">
							<?php
							if ($custom->is_hidden) {
								$hiddenColor = 'md-color-green-800';
								$hiddenText = 'COM_VIRTUEMART_CUSTOM_IS_HIDDEN';
								$hiddenIcon = 'disable';
							} else {
								$hiddenColor = 'md-color-grey-400';
								$hiddenText='COM_VIRTUEMART_CUSTOM_IS_HIDDEN_NO';
								$hiddenIcon = 'eye';
							}
							?>
							<span class="uk-hidden@m uk-margin-small-right md-color-grey-500"
							uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_CUSTOM_IS_HIDDEN') ?>"
							uk-icon="icon: disable"></span>
							<a href="javascript:void(0);"
									class="uk-icon-button uk-icon-button-small uk-button-default"
									onclick="return Joomla.listItemTask('cb<?php echo $i; ?>','toggle.is_hidden')"
									uk-tooltip="<?php echo vmText::_($hiddenText); ?>">
									<span class="<?php echo $hiddenColor ?>"
											uk-tooltip="<?php echo vmText::_($hiddenText) ?>"
											uk-icon="icon: <?php echo $hiddenIcon ?>;ratio:<?php echo $iconRatio ?>"></span>
							</a>
						</td>

						<?php
						if (!empty($this->custom_parent_id)) {
							?>
							<td class="uk-text-center@m order">
								<span class="uk-hidden@m uk-margin-small-right md-color-grey-500"
									uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_ORDERING') ?>"
									uk-icon="icon: arrow-up;ratio:<?php echo $iconRatio ?>"></span>
								<span class="vmicon vmicon-16-move"></span>
								<!--span><?php echo $this->pagination->vmOrderUpIcon($i, $custom->ordering, 'orderUp', vmText::_('COM_VIRTUEMART_MOVE_UP')); ?></span>
						<span><?php echo $this->pagination->vmOrderDownIcon($i, $custom->ordering, $n, true, 'orderDown', vmText::_('COM_VIRTUEMART_MOVE_DOWN')); ?></span-->
								<input class="ordering" type="text" name="order[<?php echo $custom->virtuemart_custom_id ?>]"
										id="order[<?php echo $i ?>]" size="5" value="<?php echo $custom->ordering; ?>"
										style="text-align: center"/>
							</td>
							<?php
						}
						?>


						<td class="uk-text-center@m">
							<span class="uk-hidden@m uk-margin-small-right md-color-grey-500"
							uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_PUBLISHED') ?>"
							uk-icon="icon: eye"></span>
							<?php echo $published; ?>

						</td>
						<td class="uk-text-center@m">
							<span class="uk-hidden@m uk-margin-small-right md-color-grey-500"
							uk-tooltip="<?php echo vmText::_('COM_VIRTUEMART_ID') ?>"
							uk-icon="icon: hashtag"></span>
							<?php echo $custom->virtuemart_custom_id; ?>
						</td>
					</tr>
					<?php
					$k = 1 - $k;
					$i++;
				}
			}
			?>
			</tbody>
			<tfoot>
			<tr>
				<td colspan="16">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
		</table>
		<!-- Hidden Fields -->
		<input type="hidden" name="task" value=""/>
		<?php if (vRequest::getInt('virtuemart_product_id', false)) { ?>
			<input type="hidden" name="virtuemart_product_id"
					value="<?php echo vRequest::getInt('virtuemart_product_id', 0); ?>"/>
		<?php } ?>
		<input type="hidden" name="option" value="com_virtuemart"/>
		<input type="hidden" name="view" value="custom"/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php //echo $this->lists['order']; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php //echo $this->lists['order_Dir']; ?>"/>

		<?php echo JHtml::_('form.token'); ?>
	</form>
<?php AdminUIHelper::endAdminArea();
/// DRAG AND DROP PRODUCT ORDER HACK
if (!empty($this->custom_parent_id)) {

	vmJsApi::addJScript('sortable', 'Virtuemart.sortable;');
	/*vmJsApi::addJScript('sortable','jQuery(function() {

			jQuery( ".adminlist" ).sortable({
				handle: ".vmicon-16-move",
				items: \'tr:not(:first,:last)\',
				opacity: 0.8,
				update: function() {
					var i = 1;
					jQuery(function updatenr(){
						jQuery(\'input.ordering\').each(function(idx) {
							jQuery(this).val(idx);
						});
					});

					jQuery(function updaterows() {
						jQuery(".order").each(function(index){
							var row = jQuery(this).parent(\'td\').parent(\'tr\').prevAll().length;
							jQuery(this).val(row);
							i++;
						});

					});
				}
			});
		});');*/

} ?>