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
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentRouteHelper;

$imagetitleProcessorRegexp = $this->sourceparams->get('imagetitle_processor', $this->cparams->get('imagetitle_processor', 'title|alt'));
$showPageBreaks = $this->cparams->get ( 'show_pagebreaks', 1 );
$linkableContentCats = $this->sourceparams->get ( 'linkable_content_cats', 0 );
$fakeImages = $this->cparams->get ( 'fake_images_processor', 0 );
$lazyloadImages = $this->cparams->get ( 'lazyload_images_processor', 0 );
$includeDescriptionOnly = $this->cparams->get ( 'include_description_only', 0 );
$cdnProtocol = $this->cparams->get ('cdnprotocol', '');
$filtersMode = $this->cparams->get ('images_filters_mode', 'tag');
$arrayCategoryIDs = array();

// Get default menu - home and check if a single article is linked, if so skip to avoid duplicated content
$homeArticleID = false;
$homeCategoryID = false;
$defaultMenu = $this->application->getMenu()->getDefault($this->app->getLanguage()->getTag());
if(	isset($defaultMenu->query['option']) &&
	isset($defaultMenu->query['view']) &&
	$defaultMenu->query['option'] == 'com_content' &&
	$defaultMenu->query['view'] == 'article') {
		$homeArticleID = (int)$defaultMenu->query['id'];
}
if(	isset($defaultMenu->query['option']) &&
	isset($defaultMenu->query['view']) &&
	$defaultMenu->query['option'] == 'com_content' &&
	$defaultMenu->query['view'] == 'category') {
		$homeCategoryID = (int)$defaultMenu->query['id'];
}

// Init exclusion
$imgFilterInclude = array();
if(trim($this->sourceparams->get ( 'images_filter_include', '' ))) {
	$imgFilterInclude = explode(',', $this->sourceparams->get ( 'images_filter_include', '' )); 
}
$imgFilterExclude = array();
if(trim($this->sourceparams->get ( 'images_filter_exclude', 'pdf,print,email' ))) {
	$imgFilterExclude = explode(',', $this->sourceparams->get ( 'images_filter_exclude', 'pdf,print,email' ));
}

// Init and merge global inclusions filters data sources wide
if($globalImagesFilterInclude = trim($this->cparams->get ( 'images_global_filter_include', '' ))) {
	$imgGlobalFilterInclude = explode(',', $globalImagesFilterInclude);
	$imgFilterInclude = array_merge($imgFilterInclude, $imgGlobalFilterInclude);
}
// Init and merge global exclusions filters data sources wide
if($globalImagesFilterExclude = trim($this->cparams->get ( 'images_global_filter_exclude', '' ))) {
	$imgGlobalFilterExclude = explode(',', $globalImagesFilterExclude);
	$imgFilterExclude = array_merge($imgFilterExclude, $imgGlobalFilterExclude);
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
		
		// Check to ensure is counting valid request
		if(!$this->HTTPClient->isValidRequest()) {
			break;
		}
		
		$elm->slug = $elm->alias ? ($elm->id . ':' . $elm->alias) : $elm->id;
		$seolink = \JMapRoute::_ ( ContentRouteHelper::getArticleRoute ( $elm->slug, $elm->catslug, $elm->language ) );

		// Skip outputting
		if(array_key_exists($seolink, $this->outputtedLinksBuffer)) {
			continue;
		}
		// Else store to prevent duplication
		$this->outputtedLinksBuffer[$seolink] = true;
		$this->imagesOutputtedLinksBuffer = array();
		
		// Store a unique hash of catid
		$arrayCategoryIDs[$elm->catid] = array('modified'=>$elm->cat_modified, 'language'=>$elm->language, 'priority'=>$elm->priority);
		
		// HTTP Request to remote URL '$seolink' to get images 
		$headers = array('Accept'=>'text/html', 'User-Agent'=>'JSitemapbot/1.0');
		$HTTPResponse = $this->HTTPClient->get($this->liveSiteCrawler . $seolink, $headers);
		$pageHtml = $HTTPResponse->body;
		// Images RegExp extraction
		$imagesArrayResults = array();
		preg_match_all ( $this->mainImagesRegex, $pageHtml, $imagesArrayResults );
		$imagesTags = $imagesArrayResults[0];
		$imagesLinks = $imagesArrayResults[4];
		
		// Crawl and merge images also from dummy gallery images?
		if($fakeImages) {
			preg_match_all ( '/(<a)([^>])*(href=["\']([^"\']+\.(jpg|jpeg|gif|png|svg|webp|avif))["\'])([^>])*/i', $pageHtml, $dummyImagesArrayResults );
			$dummyImagesTags = $dummyImagesArrayResults[0];
			$dummyImagesLinks = $dummyImagesArrayResults[4];
			$imagesTags = array_merge($imagesTags, $dummyImagesTags);
			$imagesLinks = array_merge($imagesLinks, $dummyImagesLinks);
		}
		
		// Crawl and merge images also from dummy gallery images?
		if($lazyloadImages) {
			preg_match_all ( '/(<img)([^>])*((data-src|data-lazyload)=["\']([^"\']+\.(jpg|jpeg|gif|png|svg|webp|avif))["\'])([^>])*/i', $pageHtml, $lazyloadImagesArrayResults );
			$lazyloadImagesTags = $lazyloadImagesArrayResults[0];
			$lazyloadImagesLinks = $lazyloadImagesArrayResults[5];
			$imagesTags = array_merge($imagesTags, $lazyloadImagesTags);
			$imagesLinks = array_merge($imagesLinks, $lazyloadImagesLinks);
		}
		
		// Custom images tags and attributes
		if($this->validCustomImagesProcessor) {
			$dynamicRegex = '/(<' . implode('|<', $this->validExplodedTags) . ')([^>])*((' . implode('|', $this->validExplodedAttributes) . ')=["\']([^"\']+\.(jpg|jpeg|gif|png|svg|webp|avif))["\'])([^>])*/i';
			preg_match_all ( $dynamicRegex, $pageHtml, $customFoundImagesArrayResults );
			$customFoundImagesTags = $customFoundImagesArrayResults[0];
			$customFoundImagesLinks = $customFoundImagesArrayResults[5];
			$imagesTags = array_merge($imagesTags, $customFoundImagesTags);
			$imagesLinks = array_merge($imagesLinks, $customFoundImagesLinks);
			
			// Detect correctly the first image in the source/srcset standard tag
			if(in_array('source', $this->validExplodedTags) && in_array('srcset', $this->validExplodedAttributes)) {
				preg_match_all ( '/<source[^>]*srcset=["\']([^,"\']+\.(?:jpg|jpeg|gif|png|svg|webp|avif))[^"\']*["\']/i', $pageHtml, $customFoundImagesArrayResults );
				$customFoundImagesTags = $customFoundImagesArrayResults[0];
				$customFoundImagesLinks = $customFoundImagesArrayResults[1];
				$imagesTags = array_merge($imagesTags, $customFoundImagesTags);
				$imagesLinks = array_merge($imagesLinks, $customFoundImagesLinks);
			}
		}

$bufferImages = null;
$bufferExclusions = $filtersMode == 'tag' ? $imagesTags : $imagesLinks;
if(!empty($imagesLinks)):
ob_start();
foreach ($imagesLinks as $index=>$imageLink):
// Skip outputting
if(array_key_exists($imageLink, $this->imagesOutputtedLinksBuffer)) {
	continue;
}
$validImage = true;
$found = false;
$optionalImageTitle = false;
// Extended images filtering include
if(is_array($imgFilterInclude) && count($imgFilterInclude)):
	foreach ($imgFilterInclude as $filterInclude) :
		if(strstr($bufferExclusions[$index], trim($filterInclude))) {
			$found = true;
			break;
		}
	endforeach;
if(!$found):
$validImage = false;
endif;
endif;

// Extended images filtering exclude
if(is_array($imgFilterExclude) && count($imgFilterExclude)):
	foreach ($imgFilterExclude as $filterExclude) :
		if(strstr($bufferExclusions[$index], trim($filterExclude))) {
			$validImage = false;
			break;
		}
	endforeach;
endif;
// Image not valid so don't insert in sitemap
if(!$validImage)
	continue;
// Check for optional image title
if(stristr($imagesTags[$index], 'title=') || stristr($imagesTags[$index], 'alt=')) {
	$optionalTitleMatches = array();
	// Crawl and merge images also from dummy gallery images?
	if($this->validCustomImagesProcessor) {
		$dynamicTitlesRegex = '/(<img|<a|<' . implode('|<', $this->validExplodedTags) . ')([^>])*(('.$imagetitleProcessorRegexp.')=(["\'])([^\5]*)\5)/iU';
		preg_match($dynamicTitlesRegex, $imagesTags[$index], $optionalTitleMatches);
	} elseif($fakeImages) {
		preg_match('/(<img|<a)([^>])*(('.$imagetitleProcessorRegexp.')=(["\'])([^\5]*)\5)/iU', $imagesTags[$index], $optionalTitleMatches);
	} else {
		preg_match('/(<img)([^>])*(('.$imagetitleProcessorRegexp.')=(["\'])([^\5]*)\5)/iU', $imagesTags[$index], $optionalTitleMatches);
	}
	if(!empty($optionalTitleMatches[6])) {
		$optionalImageTitle = '<image:title>' . htmlspecialchars(html_entity_decode($optionalTitleMatches[6], ENT_NOQUOTES, 'UTF-8'), ENT_NOQUOTES, 'UTF-8', false) . '</image:title>' . PHP_EOL;
	}
}
// Check if image description is required and missing
if(!$optionalImageTitle && $includeDescriptionOnly) {
	continue;
}
$this->imagesOutputtedLinksBuffer[$imageLink] = true;
?>
<image:image>
<image:loc><?php echo htmlspecialchars(preg_match('/^http|^\/\//i', $imageLink) ? ($cdnProtocol && strpos($imageLink, 'http') === false ? $cdnProtocol . $imageLink : $imageLink) : $this->liveSite . '/' . ltrim($imageLink, '/'), ENT_NOQUOTES, 'UTF-8', false);?></image:loc>
<?php echo $optionalImageTitle;?>
</image:image>
<?php 
endforeach;
$bufferImages = ob_get_clean();
endif;

// Se sono presenti immagini
if(isset($bufferImages) && !empty($bufferImages)):
?>
<url>
<loc><?php echo $this->liveSite . $seolink; ?></loc>
<?php echo $bufferImages; ?>
</url>
<?php 
endif;
	if(!empty($elm->expandible) && $showPageBreaks) {
		foreach ($elm->expandible as $index=>$subPageBreak) {
			$seolink = \JMapRoute::_ ( ContentRouteHelper::getArticleRoute ( $elm->slug, $elm->catslug, $elm->language ) . '&limitstart=' . ($index + 1));

			// HTTP Request to remote URL '$seolink' to get images
			$HTTPResponse = $this->HTTPClient->get($this->liveSiteCrawler . $seolink, $headers);
			$pageHtml = $HTTPResponse->body;
			// Images RegExp extraction
			$imagesArrayResults = array();
			preg_match_all ( $this->mainImagesRegex, $pageHtml, $imagesArrayResults );
			$imagesTags = $imagesArrayResults[0];
			$imagesLinks = $imagesArrayResults[4];
			
			// Crawl and merge images also from dummy gallery images?
			if($fakeImages) {
				preg_match_all ( '/(<a)([^>])*(href=["\']([^"\']+\.(jpg|jpeg|gif|png|svg|webp|avif))["\'])([^>])*/i', $pageHtml, $dummyImagesArrayResults );
				$dummyImagesTags = $dummyImagesArrayResults[0];
				$dummyImagesLinks = $dummyImagesArrayResults[4];
				$imagesTags = array_merge($imagesTags, $dummyImagesTags);
				$imagesLinks = array_merge($imagesLinks, $dummyImagesLinks);
			}
			
			// Crawl and merge images also from dummy gallery images?
			if($lazyloadImages) {
				preg_match_all ( '/(<img)([^>])*((data-src|data-lazyload)=["\']([^"\']+\.(jpg|jpeg|gif|png|svg|webp|avif))["\'])([^>])*/i', $pageHtml, $lazyloadImagesArrayResults );
				$lazyloadImagesTags = $lazyloadImagesArrayResults[0];
				$lazyloadImagesLinks = $lazyloadImagesArrayResults[5];
				$imagesTags = array_merge($imagesTags, $lazyloadImagesTags);
				$imagesLinks = array_merge($imagesLinks, $lazyloadImagesLinks);
			}
			
			// Custom images tags and attributes
			if($this->validCustomImagesProcessor) {
				$dynamicRegex = '/(<' . implode('|<', $this->validExplodedTags) . ')([^>])*((' . implode('|', $this->validExplodedAttributes) . ')=["\']([^"\']+\.(jpg|jpeg|gif|png|svg|webp|avif))["\'])([^>])*/i';
				preg_match_all ( $dynamicRegex, $pageHtml, $customFoundImagesArrayResults );
				$customFoundImagesTags = $customFoundImagesArrayResults[0];
				$customFoundImagesLinks = $customFoundImagesArrayResults[5];
				$imagesTags = array_merge($imagesTags, $customFoundImagesTags);
				$imagesLinks = array_merge($imagesLinks, $customFoundImagesLinks);
				
				// Detect correctly the first image in the source/srcset standard tag
				if(in_array('source', $this->validExplodedTags) && in_array('srcset', $this->validExplodedAttributes)) {
					preg_match_all ( '/<source[^>]*srcset=["\']([^,"\']+\.(?:jpg|jpeg|gif|png|svg|webp|avif))[^"\']*["\']/i', $pageHtml, $customFoundImagesArrayResults );
					$customFoundImagesTags = $customFoundImagesArrayResults[0];
					$customFoundImagesLinks = $customFoundImagesArrayResults[1];
					$imagesTags = array_merge($imagesTags, $customFoundImagesTags);
					$imagesLinks = array_merge($imagesLinks, $customFoundImagesLinks);
				}
			}
		 
$bufferImages = null;
$bufferExclusions = $filtersMode == 'tag' ? $imagesTags : $imagesLinks;
if(!empty($imagesLinks)):
ob_start();
foreach ($imagesLinks as $index=>$imageLink):
$validImage = true;
$found = false;
$optionalImageTitle = false;
// Extended images filtering include
if(is_array($imgFilterInclude) && count($imgFilterInclude)):
	foreach ($imgFilterInclude as $filterInclude) :
		if(strstr($bufferExclusions[$index], trim($filterInclude))) {
			$found = true;
			break;
		}
	endforeach;
if(!$found):
$validImage = false;
endif;
endif;

// Extended images filtering exclude
if(is_array($imgFilterExclude) && count($imgFilterExclude)):
	foreach ($imgFilterExclude as $filterExclude) :
		if(strstr($bufferExclusions[$index], trim($filterExclude))) {
			$validImage = false;
			break;
		}
	endforeach;
endif;
// Image not valid so don't insert in sitemap
if(!$validImage)
	continue;
// Check for optional image title
if(stristr($imagesTags[$index], 'title=') || stristr($imagesTags[$index], 'alt=')) {
	$optionalTitleMatches = array();
	// Crawl and merge images also from dummy gallery images?
	if($this->validCustomImagesProcessor) {
		$dynamicTitlesRegex = '/(<img|<a|<' . implode('|<', $this->validExplodedTags) . ')([^>])*(('.$imagetitleProcessorRegexp.')=(["\'])([^\5]*)\5)/iU';
		preg_match($dynamicTitlesRegex, $imagesTags[$index], $optionalTitleMatches);
	} elseif($fakeImages) {
		preg_match('/(<img|<a)([^>])*(('.$imagetitleProcessorRegexp.')=(["\'])([^\5]*)\5)/iU', $imagesTags[$index], $optionalTitleMatches);
	} else {
		preg_match('/(<img)([^>])*(('.$imagetitleProcessorRegexp.')=(["\'])([^\5]*)\5)/iU', $imagesTags[$index], $optionalTitleMatches);
	}
	if(!empty($optionalTitleMatches[6])) {
		$optionalImageTitle = '<image:title>' . htmlspecialchars(html_entity_decode($optionalTitleMatches[6], ENT_NOQUOTES, 'UTF-8'), ENT_NOQUOTES, 'UTF-8', false) . '</image:title>' . PHP_EOL;
	}
}
// Check if image description is required and missing
if(!$optionalImageTitle && $includeDescriptionOnly) {
	continue;
}
?>
<image:image>
<image:loc><?php echo htmlspecialchars(preg_match('/^http|^\/\//i', $imageLink) ? ($cdnProtocol && strpos($imageLink, 'http') === false ? $cdnProtocol . $imageLink : $imageLink) : $this->liveSite . '/' . ltrim($imageLink, '/'), ENT_NOQUOTES, 'UTF-8', false);?></image:loc>
<?php echo $optionalImageTitle;?>
</image:image>
<?php 
endforeach;
$bufferImages = ob_get_clean();
endif;
 
// Se sono presenti immagini
if(isset($bufferImages) && !empty($bufferImages)):
?>
<url>
<loc><?php echo $this->liveSite . $seolink; ?></loc>
<?php echo $bufferImages; ?>
</url>
<?php 
endif;
 
			}
		}
	}
	
// Output categories links if any
if($linkableContentCats && !empty($arrayCategoryIDs)) {
	foreach ($arrayCategoryIDs as $catid=>$catInfo) {
		// Category found as linked to home, skip and avoid duplicate link
		if((int)$catid === $homeCategoryID) {
			continue;
		}
		$seoCatLink = \JMapRoute::_ ( ContentRouteHelper::getCategoryRoute($catid, $catInfo['language'] ) );
		
		$this->imagesOutputtedLinksBuffer = array();
		
		// HTTP Request to remote URL '$seoCatLink' to get images
		$HTTPResponse = $this->HTTPClient->get($this->liveSiteCrawler . $seoCatLink, $headers);
		$pageHtml = $HTTPResponse->body;
		// Images RegExp extraction
		$imagesArrayResults = array();
		preg_match_all ( $this->mainImagesRegex, $pageHtml, $imagesArrayResults );
		$imagesTags = $imagesArrayResults[0];
		$imagesLinks = $imagesArrayResults[4];
		
		// Crawl and merge images also from dummy gallery images?
		if($fakeImages) {
			preg_match_all ( '/(<a)([^>])*(href=["\']([^"\']+\.(jpg|jpeg|gif|png|svg|webp|avif))["\'])([^>])*/i', $pageHtml, $dummyImagesArrayResults );
			$dummyImagesTags = $dummyImagesArrayResults[0];
			$dummyImagesLinks = $dummyImagesArrayResults[4];
			$imagesTags = array_merge($imagesTags, $dummyImagesTags);
			$imagesLinks = array_merge($imagesLinks, $dummyImagesLinks);
		}
		
		// Crawl and merge images also from dummy gallery images?
		if($lazyloadImages) {
			preg_match_all ( '/(<img)([^>])*((data-src|data-lazyload)=["\']([^"\']+\.(jpg|jpeg|gif|png|svg|webp|avif))["\'])([^>])*/i', $pageHtml, $lazyloadImagesArrayResults );
			$lazyloadImagesTags = $lazyloadImagesArrayResults[0];
			$lazyloadImagesLinks = $lazyloadImagesArrayResults[5];
			$imagesTags = array_merge($imagesTags, $lazyloadImagesTags);
			$imagesLinks = array_merge($imagesLinks, $lazyloadImagesLinks);
		}
		
		// Custom images tags and attributes
		if($this->validCustomImagesProcessor) {
			$dynamicRegex = '/(<' . implode('|<', $this->validExplodedTags) . ')([^>])*((' . implode('|', $this->validExplodedAttributes) . ')=["\']([^"\']+\.(jpg|jpeg|gif|png|svg|webp|avif))["\'])([^>])*/i';
			preg_match_all ( $dynamicRegex, $pageHtml, $customFoundImagesArrayResults );
			$customFoundImagesTags = $customFoundImagesArrayResults[0];
			$customFoundImagesLinks = $customFoundImagesArrayResults[5];
			$imagesTags = array_merge($imagesTags, $customFoundImagesTags);
			$imagesLinks = array_merge($imagesLinks, $customFoundImagesLinks);
			
			// Detect correctly the first image in the source/srcset standard tag
			if(in_array('source', $this->validExplodedTags) && in_array('srcset', $this->validExplodedAttributes)) {
				preg_match_all ( '/<source[^>]*srcset=["\']([^,"\']+\.(?:jpg|jpeg|gif|png|svg|webp|avif))[^"\']*["\']/i', $pageHtml, $customFoundImagesArrayResults );
				$customFoundImagesTags = $customFoundImagesArrayResults[0];
				$customFoundImagesLinks = $customFoundImagesArrayResults[1];
				$imagesTags = array_merge($imagesTags, $customFoundImagesTags);
				$imagesLinks = array_merge($imagesLinks, $customFoundImagesLinks);
			}
		}
		
		$bufferImages = null;
		$bufferExclusions = $filtersMode == 'tag' ? $imagesTags : $imagesLinks;
		if(!empty($imagesLinks)):
		ob_start();
		foreach ($imagesLinks as $index=>$imageLink):
			// Skip outputting
			if(array_key_exists($imageLink, $this->imagesOutputtedLinksBuffer)) {
				continue;
			}
			$validImage = true;
			$found = false;
			$optionalImageTitle = false;
			// Extended images filtering include
			if(is_array($imgFilterInclude) && count($imgFilterInclude)):
				foreach ($imgFilterInclude as $filterInclude) :
					if(strstr($bufferExclusions[$index], trim($filterInclude))) {
						$found = true;
						break;
					}
				endforeach;
				if(!$found):
					$validImage = false;
				endif;
			endif;

			// Extended images filtering exclude
			if(is_array($imgFilterExclude) && count($imgFilterExclude)):
				foreach ($imgFilterExclude as $filterExclude) :
					if(strstr($bufferExclusions[$index], trim($filterExclude))) {
						$validImage = false;
						break;
					}
				endforeach;
			endif;
		// Image not valid so don't insert in sitemap
		if(!$validImage)
			continue;
		// Check for optional image title
		if(stristr($imagesTags[$index], 'title=') || stristr($imagesTags[$index], 'alt=')) {
			$optionalTitleMatches = array();
			// Crawl and merge images also from dummy gallery images?
			if($this->validCustomImagesProcessor) {
				$dynamicTitlesRegex = '/(<img|<a|<' . implode('|<', $this->validExplodedTags) . ')([^>])*(('.$imagetitleProcessorRegexp.')=(["\'])([^\5]*)\5)/iU';
				preg_match($dynamicTitlesRegex, $imagesTags[$index], $optionalTitleMatches);
			} elseif($fakeImages) {
				preg_match('/(<img|<a)([^>])*(('.$imagetitleProcessorRegexp.')=(["\'])([^\5]*)\5)/iU', $imagesTags[$index], $optionalTitleMatches);
			} else {
				preg_match('/(<img)([^>])*(('.$imagetitleProcessorRegexp.')=(["\'])([^\5]*)\5)/iU', $imagesTags[$index], $optionalTitleMatches);
			}
			if(!empty($optionalTitleMatches[6])) {
				$optionalImageTitle = '<image:title>' . htmlspecialchars(html_entity_decode($optionalTitleMatches[6], ENT_NOQUOTES, 'UTF-8'), ENT_NOQUOTES, 'UTF-8', false) . '</image:title>' . PHP_EOL;
			}
		}
		// Check if image description is required and missing
		if(!$optionalImageTitle && $includeDescriptionOnly) {
			continue;
		}
		$this->imagesOutputtedLinksBuffer[$imageLink] = true;
	?>
<image:image>
<image:loc><?php echo htmlspecialchars(preg_match('/^http|^\/\//i', $imageLink) ? ($cdnProtocol && strpos($imageLink, 'http') === false ? $cdnProtocol . $imageLink : $imageLink) : $this->liveSite . '/' . ltrim($imageLink, '/'), ENT_NOQUOTES, 'UTF-8', false);?></image:loc>
<?php echo $optionalImageTitle;?>
</image:image>
<?php 
endforeach;
$bufferImages = ob_get_clean();
endif;
 
// Se sono presenti immagini
if(isset($bufferImages) && !empty($bufferImages)):
?>
<url>
<loc><?php echo $this->liveSite . $seoCatLink; ?></loc>
<?php echo $bufferImages; ?>
</url>
<?php 
endif;
		}
	}
}