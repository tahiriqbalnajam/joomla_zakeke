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
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentRouteHelper;
use Joomla\CMS\Date\Date;

// Get default menu - home and check if a single article is linked, if so skip to avoid duplicated content
$homeArticleID = false;
$nullDate = Factory::getContainer()->get('DatabaseDriver')->getNullDate();
$defaultMenu = $this->application->getMenu()->getDefault($this->app->getLanguage()->getTag());
if(	isset($defaultMenu->query['option']) &&
	isset($defaultMenu->query['view']) &&
	$defaultMenu->query['option'] == 'com_content' &&
	$defaultMenu->query['view'] == 'article') {
	$homeArticleID = (int)$defaultMenu->query['id'];
}

if (count ( $this->source->data ) != 0) {
	foreach ( $this->source->data as $elm ) {
		// Element category empty da right join
		if(!$elm->id) {
			continue;
		}
		
		// Article found as linked to home, skip and avoid duplicate link
		if((int)$elm->id === $homeArticleID) {
			continue;
		}
		
		$elm->slug = $elm->alias ? ($elm->id . ':' . $elm->alias) : $elm->id;
		$seolink = \JMapRoute::_ ( ContentRouteHelper::getArticleRoute ( $elm->slug, $elm->catslug, $elm->language  ) );

		// Skip outputting
		if(array_key_exists($seolink, $this->outputtedLinksBuffer)) {
			continue;
		}
		// Else store to prevent duplication
		$this->outputtedLinksBuffer[$seolink] = true;
		 
		// Normalize and fallback publish up - publication date fields
		$elm->publish_up = (isset($elm->publish_up) && $elm->publish_up && $elm->publish_up != $nullDate && !in_array($elm->publish_up, array('0000-00-00 00:00:00', '1000-01-01 00:00:00')) && $elm->publish_up != -1) ? $elm->publish_up : gmdate('Y-m-d\TH:i:s\Z', time());
?>
<url>
<loc><?php echo $this->liveSite . $seolink; ?></loc>
<news:news>
<news:publication>
<news:name><?php echo htmlspecialchars($this->cparams->get( 'gnews_publication_name', Factory::getApplication()->getCfg('sitename'))); ?></news:name>
<news:language><?php echo $this->sysLang; ?></news:language>
</news:publication>
<?php if($elm->access > 1): ?>
<news:access>Registration</news:access>
<?php endif; ?>
<?php if(!in_array('', $this->sourceparams->get ( 'gnews_genres', array('')))): ?>
<news:genres><?php echo implode(', ', $this->sourceparams->get ( 'gnews_genres' ));?></news:genres>
<?php endif; ?>
<news:publication_date><?php $dateObj = new Date($elm->publish_up); $dateObj->setTimezone(new \DateTimeZone($this->globalConfig->get('offset')));echo $dateObj->toISO8601(true);?></news:publication_date>
<news:title><?php echo htmlspecialchars($elm->title); ?></news:title>
<?php if(trim($elm->metakey)):?>
<news:keywords><?php echo trim(htmlspecialchars($elm->metakey)); ?></news:keywords>
<?php endif; ?>
</news:news>
</url>
<?php
	}
}