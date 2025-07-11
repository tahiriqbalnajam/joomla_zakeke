<?php
/** 
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage views
 * @subpackage sitemap
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Factory;

$priority =  $this->sourceparams->get ( 'priority', '0.5' );
$changefreq = $this->sourceparams->get ( 'changefreq', 'daily' );
$excludePriority = $this->cparams->get ( 'disable_priority', 0 );
$excludeChangefreq = $this->cparams->get ( 'disable_changefreq', 0 );
$linkableCatsMode = $this->sourceparams->get ( 'linkable_content_cats', 1 );
$nullDate = Factory::getContainer()->get('DatabaseDriver')->getNullDate();

// Inject categories links
if($linkableCatsMode && isset($this->source->itemsTree) && isset($this->source->categoriesTree)) {
	foreach ( $this->source->categoriesTree as $itemsOfCategory ) {
		if(count($itemsOfCategory)) {
			foreach ($itemsOfCategory as $itemOfCategory) {
				$itemOfCategory->link = $itemOfCategory->category_link;
				$this->source->data[] = $itemOfCategory;
			}
		}
	}
}

// Inject items links
if (count ( $this->source->data ) != 0) {  
	foreach ( $this->source->data as $item ) {
		// Manage modified date if exists
		$lastmod = null;
		if(isset($item->lastmod) && $item->lastmod && $item->lastmod != -1 && $item->lastmod != $nullDate && !in_array($item->lastmod, array('0000-00-00 00:00:00', '1000-01-01 00:00:00'))) {
			$timestamp = strtotime($item->lastmod);
			$lastmod = gmdate('Y-m-d\TH:i:s\Z', $timestamp);
		}
		
		// Skip outputting
		if(array_key_exists($item->link, $this->outputtedLinksBuffer)) {
			continue;
		}
		
		// Else store to prevent duplication
		$this->outputtedLinksBuffer[$item->link] = true;
		?>
<url>
<loc><?php echo $this->sefSuffixEnabled ? $this->liveSite . str_ireplace('.html', '.' . $this->ampSuffix . '.html', htmlspecialchars($item->link, ENT_NOQUOTES, 'UTF-8', false)) : $this->liveSite . htmlspecialchars($item->link, ENT_NOQUOTES, 'UTF-8', false) . '/' . $this->ampSuffix; ?></loc>
<?php if(isset($lastmod) && trim($lastmod)):?>
<lastmod><?php echo $lastmod; ?></lastmod>
<?php endif; ?>
<?php if(!$excludeChangefreq):?>
<changefreq><?php echo $changefreq;?></changefreq>
<?php endif;?>
<?php if(!$excludePriority):?>
<priority><?php echo $priority;?></priority>
<?php endif;?>
</url>
<?php 
	}
}