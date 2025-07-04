<?php
/** 
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage views
 * @subpackage sitemap
 * @subpackage tmpl
 * @subpackage adapters
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

// Adapter for EShop products and categories route helper
include_once JPATH_BASE . '/components/com_eshop/helpers/helper.php';
include_once JPATH_BASE . '/components/com_eshop/helpers/weight.php';
include_once JPATH_BASE . '/components/com_eshop/helpers/customer.php';
$helperRouteClass= 'EshopRoute';
switch ($targetViewName) {
	case 'product':
		$classMethod = 'getProductRoute';
		$seflink = \JMapRoute::_ ($helperRouteClass::$classMethod($elm->id, $elm->catid, $elm->language));
		break;
			
	case 'category':
		$classMethod = 'getCategoryRoute';
		$seflink = \JMapRoute::_ ($helperRouteClass::$classMethod($elm->id, $elm->language));
		break;
		
	case 'manufacturer':
		$classMethod = 'getManufacturerRoute';
		$seflink = \JMapRoute::_ ($helperRouteClass::$classMethod($elm->id, $elm->language));
		break;
}	

