<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_latest
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

if (!$list)
{
	return;
}
?>
<ul class="recent_post_item <?php echo $moduleclass_sfx ?? ''; ?>">
<?php foreach ($list as $item) : ?>
	<li class="footer-recent-post-item">

	<img src="<?php echo json_decode($item->images)->image_intro; ?>"/>

	<div class="content">	
	<span class="post-date"><?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC3')); ?></span>
	<h6 class="title"><a href="<?php echo $item->link; ?>" itemprop="url"> <?php echo $item->title; ?> </a></h6>
	</div>
	</li>
<?php endforeach; ?>
</ul>
