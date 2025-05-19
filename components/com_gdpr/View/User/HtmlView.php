<?php
namespace JExtstore\Component\Gdpr\Site\View\User;
/**
 * @package GDPR::USER::components::com_gdpr
 * @subpackage views
 * @subpackage user
 * @author Joomla! Extensions Store
 * @copyright (C) 2018 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use JExtstore\Component\Gdpr\Administrator\Framework\View as GdprView;

/**
 * @package GDPR::USER::components::com_gdpr
 * @subpackage views
 * @subpackage user
 * @since 2.5
 */
class HtmlView extends GdprView {
	/**
	 * Prepares the document
	 */
	protected function _prepareDocument() {
		$app = Factory::getApplication();
		$document = $app->getDocument();
		$menus = $app->getMenu();
		$title = null;
	
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if(is_null($menu)) {
			return;
		}
	
		$this->params = new Registry();
		if(!is_null($menu)) {
			$menuParams = $menu->getParams()->toString();
			$this->params->loadString($menuParams);
		}
	
		$title = $this->params->get('page_title', Text::_('COM_GDPR_COOKIE_SETTINGS'));
		$this->setDocumentTitle($title);
	
		if ($this->params->get('menu-meta_description')) {
			$document->setDescription($this->params->get('menu-meta_description'));
		}
	
		if ($this->params->get('menu-meta_keywords')) {
			$document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
	
		if ($this->params->get('robots')) {
			$document->setMetadata('robots', $this->params->get('robots'));
		}
	}
	
	/**
	 * Default display listEntities
	 *        	
	 * @access public
	 * @param string $tpl
	 * @return void
	 */
	public function display($tpl = null) {
		// Do not display nothing by server side, only add custom CSS overrides
		$cssOverrides = '';
		
		$menuViewParams = $this->app->getParams ( 'com_gdpr' );
		$gdprComponentViewBgColor = $menuViewParams->get('gdpr_component_view_bg_color', '');
		if($gdprComponentViewBgColor) {
			$cssOverrides .= "div.gdpr-component-view{background-color:$gdprComponentViewBgColor}";
		}
		
		$gdprComponentViewTextColor = $menuViewParams->get('gdpr_component_view_text_color', '');
		if($gdprComponentViewTextColor) {
			$cssOverrides .= "div.gdpr-component-view div,div.gdpr-component-view p,div.gdpr-component-view fieldset.cc-cookie-list-title > legend{color:$gdprComponentViewTextColor}";
		}
		
		$cookieListitemBgColor = $menuViewParams->get('cookie_listitem_bg_color', '');
		if($cookieListitemBgColor) {
			$cssOverrides .= "div.gdpr-component-view ul.cc-cookie-category-list li,#main div.gdpr-component-view ul.cc-cookie-category-list>li,div.gdpr-component-view fieldset.cc-service-list-title ul.cc-cookie-category-list li,div.gdpr-component-view fieldset.cc-service-list-title > legend{background-color:$cookieListitemBgColor}";
		}

		$cookieListitemTextColor = $menuViewParams->get('cookie_listitem_text_color', '');
		if($cookieListitemTextColor) {
			$cssOverrides .= "div.gdpr-component-view ul.cc-cookie-category-list li span,div.gdpr-component-view ul.cc-cookie-category-list div.cc-cookie-descriptions,div.gdpr-component-view div.cc-cookie-descriptions div.cc-cookie-expiration,div.gdpr-component-view fieldset.cc-service-list-title > legend{color:$cookieListitemTextColor}";
		}
		
		$gdprComponentViewBorderRadius = $menuViewParams->get('gdpr_component_view_border_radius', '');
		if($gdprComponentViewBorderRadius !== '') {
			$cssOverrides .= 'div.gdpr-component-view,div.gdpr-component-view ul.cc-cookie-category-list li,div.gdpr-component-view fieldset.cc-service-list-title > legend{border-radius:' . $gdprComponentViewBorderRadius . 'px !important}';
		}
		
		$gdprAllowBtnBgColor = $menuViewParams->get('allow_btn_bg_color', '');
		if($gdprAllowBtnBgColor) {
			$cssOverrides .= "div.cc-gdpr-component-view a.cc-btn.cc-allow,div.cc-gdpr-component-view a.cc-btn.cc-allow:hover,div.cc-gdpr-component-view a.cc-btn.cc-allow:focus{background-color:$gdprAllowBtnBgColor}";
		}
		
		$gdprAllowBtnTextColor = $menuViewParams->get('allow_btn_text_color', '');
		if($gdprAllowBtnTextColor) {
			$cssOverrides .= "div.cc-gdpr-component-view a.cc-btn.cc-allow,div.cc-gdpr-component-view a.cc-btn.cc-allow:hover,div.cc-gdpr-component-view a.cc-btn.cc-allow:focus{color:$gdprAllowBtnTextColor}";
		}
		
		$gdprAllowBtnBorderColor = $menuViewParams->get('allow_btn_border_color', '');
		if($gdprAllowBtnBorderColor) {
			$cssOverrides .= "div.cc-gdpr-component-view a.cc-btn.cc-allow,div.cc-gdpr-component-view a.cc-btn.cc-allow:hover,div.cc-gdpr-component-view a.cc-btn.cc-allow:focus{border-color:$gdprAllowBtnBorderColor}";
		}
		
		$gdprAllowallBtnBgColor = $menuViewParams->get('allowall_btn_bg_color', '');
		if($gdprAllowallBtnBgColor) {
			$cssOverrides .= "div.cc-gdpr-component-view a.cc-btn.cc-allowall,div.cc-gdpr-component-view a.cc-btn.cc-allowall:hover,div.cc-gdpr-component-view a.cc-btn.cc-allowall:focus{background-color:$gdprAllowallBtnBgColor}";
		}
		
		$gdprAllowallBtnTextColor = $menuViewParams->get('allowall_btn_text_color', '');
		if($gdprAllowallBtnTextColor) {
			$cssOverrides .= "div.cc-gdpr-component-view a.cc-btn.cc-allowall,div.cc-gdpr-component-view a.cc-btn.cc-allowall:hover,div.cc-gdpr-component-view a.cc-btn.cc-allowall:focus{color:$gdprAllowallBtnTextColor}";
		}
		
		$gdprAllowallBtnBorderColor = $menuViewParams->get('allowall_btn_border_color', '');
		if($gdprAllowallBtnBorderColor) {
			$cssOverrides .= "div.cc-gdpr-component-view a.cc-btn.cc-allowall,div.cc-gdpr-component-view a.cc-btn.cc-allowall:hover,div.cc-gdpr-component-view a.cc-btn.cc-allowall:focus{border-color:$gdprAllowallBtnBorderColor}";
		}
		
		$gdprDenyBtnBgColor = $menuViewParams->get('deny_btn_bg_color', '');
		if($gdprDenyBtnBgColor) {
			$cssOverrides .= "div.cc-gdpr-component-view a.cc-btn.cc-deny,div.cc-gdpr-component-view a.cc-btn.cc-deny:hover,div.cc-gdpr-component-view a.cc-btn.cc-deny:focus{background-color:$gdprDenyBtnBgColor}";
		}
		
		$gdprDenyBtnTextColor = $menuViewParams->get('deny_btn_text_color', '');
		if($gdprDenyBtnTextColor) {
			$cssOverrides .= "div.cc-gdpr-component-view a.cc-btn.cc-deny,div.cc-gdpr-component-view a.cc-btn.cc-deny:hover,div.cc-gdpr-component-view a.cc-btn.cc-deny:focus{color:$gdprDenyBtnTextColor}";
		}
		
		$gdprDenyBtnBorderColor = $menuViewParams->get('deny_btn_border_color', '');
		if($gdprDenyBtnBorderColor) {
			$cssOverrides .= "div.cc-gdpr-component-view a.cc-btn.cc-deny,div.cc-gdpr-component-view a.cc-btn.cc-deny:hover,div.cc-gdpr-component-view a.cc-btn.cc-deny:focus{border-color:$gdprDenyBtnBorderColor}";
		}
		
		$gdprButtonsBorderRadius = $menuViewParams->get('buttons_border_radius', '');
		if($gdprButtonsBorderRadius !== '') {
			$cssOverrides .= 'div.cc-gdpr-component-view a.cc-btn{border-radius:' . $gdprButtonsBorderRadius . 'px}';
		}
		
		if($cssOverrides) {
			$this->document->getWebAssetManager()->addInlineStyle($cssOverrides);
		}
		
		$this->_prepareDocument();
	}
}