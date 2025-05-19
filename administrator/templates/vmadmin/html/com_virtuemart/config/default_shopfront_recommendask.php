<?php
/**
 *
 * Description
 *
 * @packageVirtueMart
 * @subpackage Config
 * @author RickG
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - Copyright (C) 2004 - 2022 Virtuemart Team. All rights reserved. VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_shopfront_recommendask.php 11072 2024-10-21 13:53:40Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access'); ?>

<div class="uk-card uk-card-small uk-card-vm">
	<div class="uk-card-header">
		<div class="uk-card-title">
						<span class="md-color-cyan-600 uk-margin-small-right"
								uk-icon="icon: comment; ratio: 1.2"></span>
			<?php echo vmText::_('COM_VIRTUEMART_CFG_RECOMMEND_ASK'); ?>
		</div>
	</div>
	<div class="uk-card-body">

		<?php
		echo VmuikitHtml::row('booleanlist', 'COM_VIRTUEMART_ASK_QUESTION_CAPTCHA', 'ask_captcha', VmConfig::get('ask_captcha', 0));
		echo VmuikitHtml::row('booleanlist', 'COM_VIRTUEMART_ADMIN_SHOW_EMAILFRIEND', 'show_emailfriend', VmConfig::get('show_emailfriend', 0));
		$_recommend_unauth = array(
		'1' => vmText::_('JYES')
		, '0' => vmText::_('JNO')
		, 'customers' => vmText::_('COM_VM_ASK_QUESTION_CUSTOMERS')
		);
		echo VmuikitHtml::row( 'raw', 'COM_VIRTUEMART_RECCOMEND_UNATUH', VmuikitHtml::radioList('recommend_unauth', VmConfig::get('recommend_unauth', 'customers'), $_recommend_unauth));

		//echo VmuikitHtml::row('booleanlist', 'COM_VIRTUEMART_RECCOMEND_UNATUH', 'recommend_unauth', VmConfig::get('recommend_unauth', 0));

		echo VmuikitHtml::row('booleanlist', 'COM_VIRTUEMART_ASK_QUESTION_SHOW', 'ask_question', VmConfig::get('ask_question', 0));
		echo VmuikitHtml::row('booleanlist','COM_VIRTUEMART_ASK_QUESTION_VENDOR_SHOW','ask_question_vendor',VmConfig::get('ask_question_vendor',0));

		echo VmuikitHtml::row('input', 'COM_VIRTUEMART_ASK_QUESTION_MIN_LENGTH', 'asks_minimum_comment_length', VmConfig::get('asks_minimum_comment_length', 50), 'class="uk-form-width-xsmall"', '', 4, 4);
		echo VmuikitHtml::row('input', 'COM_VIRTUEMART_ASK_QUESTION_MAX_LENGTH', 'asks_maximum_comment_length', VmConfig::get('asks_maximum_comment_length', 2000), 'class="uk-form-width-xsmall"', '', 5, 5);
		?>
	</div>
</div>
