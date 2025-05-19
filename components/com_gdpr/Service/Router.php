<?php
namespace JExtstore\Component\Gdpr\Site\Service;
/**
 * Router class for com_gdpr
 *
 * @package GDPR::components::com_gdpr
 * @subpackage Service
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ();
use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\SiteRouter;

/**
 * Router class for com_gdpr
 *
 * @package GDPR::components::com_gdpr
 * @subpackage Service
 * @since 2.0
 */
class Router extends RouterBase {
	/**
	 * Joomla preprocess router, embeds helper route logic
	 *
	 * @package GDPR::components::com_gdpr
	 */
	public function preprocess($query) {
		$app = Factory::getApplication ();
		// Get all site menus
		$menus = $app->getMenu ( 'site' );
		
		// Helper Route here for existing menu item pointing to this $query, so try finding Itemid before all
		if (empty ( $query ['Itemid'] )) {
			$component = ComponentHelper::getComponent ( 'com_gdpr' );
			$menuItems = $menus->getItems ( 'component_id', $component->id );
			if (! empty ( $menuItems )) {
				foreach ( $menuItems as $menuItem ) {
					if (isset ( $menuItem->query ) && isset ( $menuItem->query ['view'] )) {
						if (isset ( $query ['view'] ) && $menuItem->query ['view'] == $query ['view']) {
							// Found a link exact match to sitemap view default html format within a site menu, use the Itemid for alias: component/com_gdpr=>alias
							$query ['Itemid'] = $menuItem->id;
							break;
						}
					}
				}
			}
		}
		
		return $query;
	}
	
	/**
	 * Sitemap Joomla router, embeds little helper route
	 *
	 * @package GDPR::components::com_gdpr
	 */
	function build(&$query) {
		$segments = [];
		
		// Start desetting $query chunks assigning to segments
		// UNSET VIEW
		if (isset ( $query ['view'] )) {
			unset ( $query ['view'] );
		}
		
		// Finally return processed segments
		return $segments;
	}
	
	/**
	 * Parse the segments of a URL with following shapes:
	 *
	 * @param
	 *        	array	The segments of the URL to parse.
	 * @return array URL attributes to be used by the application.
	 */
	function parse(&$segments) {
		$vars = array ();
		$count = count ( $segments );
		
		if ($count) {
			$count --;
			// VIEW-TASK is always 1° segment
			$segment = array_shift ( $segments );
			
			// Found a view/task
			if (strpos ( $segment, '-' )) {
				$vars ['task'] = str_replace ( '-', '.', $segment );
			} else {
				$vars ['view'] = $segment;
			}
		}
		
		return $vars;
	}
}