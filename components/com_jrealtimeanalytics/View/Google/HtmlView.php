<?php
namespace JExtstore\Component\JRealtimeAnalytics\Site\View\Google;
/**
 * @package JREALTIMEANALYTICS::GOOGLE::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage google
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\View as JRealtimeView;

/**
 * @package JREALTIMEANALYTICS::GOOGLE::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage google
 * @since 2.5
 */
class HtmlView extends JRealtimeView {
	// Template view variables
	protected $menuTitle;
	protected $lists;
	protected $googleData;
	protected $isLoggedIn;
	protected $cparams;
	protected $params;
	
	/**
	 * Prepares the document
	 */
	protected function _prepareDocument() {
		$app = $this->app;
		$document = $this->app->getDocument();
		$menus = $app->getMenu();
		$title = null;
	
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if(is_null($menu)) {
			return;
		}
	
		$this->params = new Registry();
		$menuParams = $menu->getParams()->toString();
		$this->params->loadString($menuParams);
	
		$title = $this->params->get('page_title', Text::_('COM_JREALTIME_GLOBAL_STATS_REPORT'));

		$this->setDocumentTitle($title);
	}
	
	/**
	 * Default display listEntities
	 *        	
	 * @access public
	 * @param string $tpl
	 * @return void
	 */
	public function display($tpl = null) {
		$menu = $this->app->getMenu ();
		$activeMenu = $menu->getActive ();
		if (isset ( $activeMenu )) {
			$this->menuTitle = $activeMenu->title;
		}
		
		// Get main records
		$lists = $this->get ( 'Lists' );
		
		$gaApi = $this->getModel()->getComponentParams()->get('analytics_api', 'analytics');
		
		switch($gaApi) {
			// Retrieve data using the Analitics API
			case 'analytics':
				$googleData = $this->get ( 'DataAnalytics' );
				break;
				
				// Retrieve data using the Reporting API
			case 'reporting':
				$googleData = $this->get ( 'DataReporting' );
				break;
				
				// Retrieve data using the DATA GA4 API
			case 'data':
				$googleData = $this->get ( 'DataData' );
				break;
		}
		
		$this->loadJQuery($this->document);
		$this->loadBootstrap($this->document, null);
		$this->app->getDocument()->getWebAssetManager ()->useStyle ( 'fontawesome' ); // Required for headers icons
		
		HTMLHelper::_('bootstrap.loadCss');
		$this->document->getWebAssetManager()->addInlineScript("
				document.addEventListener('DOMContentLoaded', function(){
					[].slice.call(document.querySelectorAll('*.jes a.hasPopover.google')).map(function (popoverEl) {
						return new bootstrap.Popover(popoverEl,{
							template : '<div class=\"popover\"><div class=\"popover-arrow\"></div><h3 class=\"popover-header\"></h3><div class=\"popover-body\"></div></div>',
							trigger : 'hover',
							placement : 'top',
							html : true
						});
					});
				});
			");
		
		$this->lists = $lists;
		$this->googleData = $googleData;
		$this->option = $this->getModel ()->getState ( 'option' );
		$this->cparams = $this->getModel ()->getComponentParams ();
		$this->isLoggedIn = $this->getModel()->getToken();
		
		// View operations
		$this->_prepareDocument();
		
		parent::display ();
	}
}