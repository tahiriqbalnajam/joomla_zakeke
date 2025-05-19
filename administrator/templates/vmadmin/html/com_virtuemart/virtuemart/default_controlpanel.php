<?php
/**
 *
 * Description
 *
 * @package    VirtueMart
 * @subpackage Config
 * @author RickG, Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - Copyright (C) 2004 - 2022 Virtuemart Team. All rights reserved. VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_controlpanel.php 10649 2022-05-05 14:29:44Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


?>
<div id="cpanel">

	<?php if ($this->manager('report') and $this->report) {
		?>
		<div id="vm_stats_chart" style="width: 100%; height: 300px;"></div><?php
	} ?>
	<div class="clear"></div>

	<ul class="newsfeed">
		<li class="newsfeed-item">
			<button class="uikit-button uikit-button-small uikit-button-default">
				<a href="http://virtuemart.net/news/list-all-news" target="_blank"
						title="<?php echo vmText::_('COM_VIRTUEMART_ALL_NEWS'); ?>"><?php echo vmText::_('COM_VIRTUEMART_ALL_NEWS'); ?></a>
			</button>
		</li>
	</ul>
	<a class="cpanel" style="display: block;"
			href="http://extensions.joomla.org/extensions/e-commerce/shopping-cart/129" target="_blank"
			title=" <?php echo vmText::_('COM_VIRTUEMART_VOTE_JED_DESC') ?>"> <?php echo vmText::_('COM_VIRTUEMART_VOTE_JED_DESC') ?></a>

	<h2 class="cpanel"><?php echo vmText::_('COM_VIRTUEMART_FEED_LATEST_NEWS') ?></h2>
	<div id="feed"></div>
	<div class="clear"></div>
	<h2 class="cpanel">
		<a href="http://extensions.virtuemart.net" target="_blank"
				title="<?php echo vmText::_('COM_VIRTUEMART_ALL_EXTENSIONS') ?>"> <?php echo vmText::_('COM_VIRTUEMART_ALL_EXTENSIONS') ?>
		</a>
	</h2>

</div>
<div class="clear"></div>


