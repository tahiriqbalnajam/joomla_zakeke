<?php
/**
 *
 * Handle the Product Custom Fields
 *
 * @package    VirtueMart
 * @subpackage Product
 * @author RolandD, Patrick khol
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - Copyright (C) 2004 - 2022 Virtuemart Team. All rights reserved. VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: product_edit_custom_relatedcf.php 10757 2022-11-29 22:46:16Z Milbo $
 *
 */


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


$relatedType = $this->relatedType;
$relatedDatas = $this->relatedDatas;
$relatedIcon = $this->relatedIcon;


?>
	<div class="uk-card   uk-card-small uk-card-vm">
		<div class="uk-card-header">
			<div class="uk-card-title">
						<span class="md-color-cyan-600 uk-margin-small-right"
								uk-icon="icon: <?php echo ($relatedIcon) ?>; ratio: 1.2"></span>
				<?php echo vmText::_('COM_VIRTUEMART_RELATED_'.strtoupper($relatedType) ); ?>
			</div>
		</div>
		<div class="uk-card-body">

			<!-- BOF  RELATED<?php echo strtoupper($relatedType) ?>   -->
			<div   uk-grid>
				<div class="uk-width-1-1">

					<!-- SEARCH RELATED<?php echo strtoupper($relatedType) ?> -->
					<div class="filter-bar search-related<?php echo ($relatedType) ?>-boundary">

						<div class="uk-navbar-container uk-margin uk-navbar" uk-navbar="">
							<div class="uk-navbar-left">
								<div class="uk-navbar-item">
									<div class="uk-button-group vmuikit-filter-search ">

										<input type="text" size="40" name="search"
												id="related<?php echo $relatedType ?>Search"
												class="vmuikit-js-reset-input-value ui-autocomplete-input"
												placeholder="<?php echo vmText::_('COM_VIRTUEMART_'.strtoupper($relatedType).'_RELATED_SEARCH'); ?>"
												value=""/>
											<!--
										<a class="vmuikit-js-relatedcf-search uk-button uk-button-small uk-button-default"
												data-relatedcf="related<?php echo ($relatedType) ?>"
												type="button">
											<span uk-icon="search"></span>
										</a>
										-->
										<button class="vmuikit-js-reset-value uk-button uk-button-small uk-button-default">
											<span uk-icon="close"></span>
										</button>

									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

					<!-- /SEARCH RELATED<?php echo strtoupper($relatedType) ?> -->


					<!-- DISPLAY RELATED<?php echo strtoupper($relatedType) ?> -->
					<div id="vmuikit-js-related<?php echo ($relatedType) ?>-container"
							class="vmuikit-js-container-removable vmuikit-js-sortable uk-margin-medium-top uk-grid uk-grid-small uk-child-width-auto uk-grid-match"
							uk-grid>
					</div>
					<!-- EOF DISPLAY RELATED<?php echo strtoupper($relatedType) ?> -->



			</div>

			<!-- EOF  RELATED<?php echo strtoupper($relatedType) ?>   -->
		</div>


	</div>


<?php
$adminTemplate = VMPATH_ROOT . '/administrator/templates/vmadmin/html/com_virtuemart/';
$adminTemplatePath = '/administrator/templates/vmadmin/html/com_virtuemart/';


// the template is the same for categories and products
$js = "
	var template = jQuery('#vmuikit-js-relatedcf-template').html()
	var rendered = Mustache.render(template,
			{
				'relatedDatas': " . json_encode($relatedDatas) . " ,
			}
	)
	jQuery('#vmuikit-js-related".$relatedType."-container').html(rendered)
";

vmJsApi::addJScript('related'.$relatedType.'.mustache', $js);
