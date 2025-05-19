<?php
namespace JExtstore\Component\JRealtimeAnalytics\Site\View\Webmasters;
/**
 * @package JREALTIME::GOOGLE::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage google
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\View as JRealtimeView;

/**
 * @package JREALTIME::GOOGLE::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage google
 * @since 2.6
 */
class HtmlView extends JRealtimeView {
	// Template view variables
	protected $dates;
	protected $globalConfig;
	protected $timeZoneObject;
	protected $lists;
	protected $googleData;
	protected $isLoggedIn;
	protected $statsDomain;
	protected $errorsDomain;
	protected $hasOwnCredentials;
	protected $menuTitle;
	protected $canExport;
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
		
		// Check the Google stats type and retrieve stats data accordingly, supported types are 'analytics' and 'webmasters'
		$googleData = $this->get ( 'DataWebmasters' );
		if(!$this->getModel()->getState('loggedout')) {
			$tpl = 'webmasters';
		}
		
		// jQuery conditional loading
		if($this->getModel()->getComponentParams()->get('includejquery', 1)) {
			$this->loadJQuery($this->document, false);
		}
		$this->loadJQueryUI($this->document); // Required for calendar feature
		$this->loadBootstrap($this->document, null);

		$this->document->getWebAssetManager()->useStyle ( 'fontawesome' ); // Required for headers icons
		$this->document->getWebAssetManager()->registerAndUseScript ( 'jrealtime.tablesorter', 'administrator/components/com_jrealtimeanalytics/js/libraries/tablesorter/jquery.tablesorter.js', [], [], ['jquery']);
		$this->document->getWebAssetManager()->registerAndUseScript ( 'jrealtime.google', 'administrator/components/com_jrealtimeanalytics/js/google.js', [], [], ['jquery']);
		
		$dates = array('start'=>$this->getModel()->getState('fromPeriod'), 'to'=>$this->getModel()->getState('toPeriod'));
		$this->dates = $dates;
		$this->globalConfig = $this->app->getConfig();
		$this->timeZoneObject = new \DateTimeZone($this->globalConfig->get('offset'));
		$this->document->getWebAssetManager()->addInlineScript("var jrealtime_baseURI='" . Uri::root() . "';");
		$this->lists = $lists;
		$this->googleData = $googleData;
		$this->isLoggedIn = $this->getModel()->getToken();
		$this->statsDomain = $this->getModel()->getState('stats_domain', Uri::root());
		$this->errorsDomain = preg_match('/^http/i', $this->statsDomain) ? $this->statsDomain . '/' : Uri::getInstance()->getScheme() . '://' . $this->statsDomain . '/';
		$this->hasOwnCredentials = $this->getModel()->getState('has_own_credentials', false);
		$this->canExport = (bool)$this->getModel()->getState('hasExportPermission', true);
		$this->option = $this->getModel ()->getState ( 'option' );
		$this->cparams = $this->getModel ()->getComponentParams ();
		
		// View operations
		$this->_prepareDocument();
		
		parent::display ($tpl);
	}
}