<?php
namespace JExtstore\Component\JRealtimeAnalytics\Site\View\Serverstats;
/**
 * @package JREALTIMEANALYTICS::SERVERSTATS::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage serverstats
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Component\ComponentHelper;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\View as JRealtimeView;
use Joomla\CMS\HTML\HTMLHelper;

define ( 'VISITSPERPAGE', 0 );
define ( 'TOTALVISITEDPAGES', 1 );
define ( 'TOTALVISITEDPAGESPERUSER', 2 );
define ( 'TOTALVISITORS', 3 );
define ( 'MEDIUMVISITTIME', 4 );
define ( 'MEDIUMVISITEDPAGESPERSINGLEUSER', 5 );
define ( 'NUMUSERSGEOGROUPED', 6 );
define ( 'NUMUSERSBROWSERGROUPED', 7 );
define ( 'NUMUSERSOSGROUPED', 8 );
define ( 'LEAVEOFF_PAGES', 9 );
define ( 'LANDING_PAGES', 10 );
define ( 'REFERRALTRAFFIC', 11 );
define ( 'SEARCHEDPHRASE', 12 );
define ( 'TOTALVISITEDPAGESPERIPADDRESS', 13 );
define ( 'BOUNCERATE', 14 );
define ( 'TOTALUNIQUEVISITORS', 15 );
define ( 'NUMUSERSDEVICEGROUPED', 16 );

/**
 * Main view class
 *
 * @package JREALTIMEANALYTICS::SERVERSTATS::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage serverstats
 * @since 2.1
 */
class HtmlView extends JRealtimeView {
	// Template view variables
	protected $data;
	protected $dates;
	protected $geotrans;
	protected $userid;
	protected $nocache;
	protected $lists;
	protected $livesite;
	protected $cparams;
	protected $menuTitle;
	protected $canExport;
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
	 * Display the serverstats
	 * @access public
	 * @return void
	 */
	public function display($tpl = null) {
		$this->cparams = $this->getModel ()->getComponentParams ();
		$menu = $this->app->getMenu ();
		$activeMenu = $menu->getActive ();
		if (isset ( $activeMenu )) {
			$this->menuTitle = $activeMenu->title;
		}
		
		// jQuery conditional loading
		if($this->cparams->get('includejquery', 1)) {
			$this->loadJQuery($this->document, true);
			HTMLHelper::_('bootstrap.loadCss');
		}

		$this->loadJQueryUI($this->document); // Required for calendar feature
		$this->loadBootstrap($this->document, null);
		$this->loadJQVMap($this->document);
		$this->loadJQFancybox($this->document);
		
		$this->document->getWebAssetManager()->registerAndUseScript ( 'jrealtime.tablesorter', 'administrator/components/com_jrealtimeanalytics/js/libraries/tablesorter/jquery.tablesorter.js', [], [], ['jquery']);
		$this->document->getWebAssetManager()->registerAndUseScript ( 'jrealtime.serverstats', 'administrator/components/com_jrealtimeanalytics/js/serverstats.js', [], [], ['jquery']);
		
		$this->document->getWebAssetManager()->addInlineScript("
						var jrealtimeIpAddressServerStatsEndpoint = '" . Uri::root() . 'administrator/index.php?option=com_jrealtimeanalytics&task=serverstats.fetchIpinfo&format=raw' . "';
						var jrealtimeBackendHostInfo = " . $this->cparams->get('backend_host_info', 0) . ";
						var jrealtimeStatsCalendarFormat = '" . $this->cparams->get('stats_calendar_type', 'date') . "';
						jQuery.submitbutton = function(pressbutton) {
							jQuery.submitform( pressbutton );
							if (pressbutton == 'serverstats.displaypdf' ||
								pressbutton == 'serverstats.displayxls' ||
								pressbutton == 'serverstats.displaycsv') {
								setTimeout(function(){
									jQuery('#adminForm input[name=task]').val('serverstats.display');
								}, 200);
							}
							return true;
						}
					");
		
		// Get stats data
		$statsData = $this->get('Data');
		// Some exceptions have been triggered
		if(empty($statsData)) {
			return false;
		}
		
		$lists = $this->get('Lists');
		$geoTranslations = $this->get('GeoTranslations');
		// Inject js translations
		$translations = array('COM_JREALTIME_STATS_DETAILS',
							  'COM_JREALTIME_VISUALMAP',
							  'COM_JREALTIME_NUMRESULTS'
		);
		$this->injectJsTranslations($translations, $this->document);
		$this->document->getWebAssetManager()->addInlineScript ( 'var jrealtimeGeoMapData = ' . json_encode ( @$statsData[NUMUSERSGEOGROUPED]['clientside'] ) . ';' .
																 'var jrealtimeGeolocationService = "' . $this->getModel()->getComponentParams()->get('backend_geolocation_service', 'geoiplookup') . '";' );
		$this->document->getWebAssetManager()->addInlineStyle ( 'div.popover.bs-popover-right{min-height:64px}' );
		
		// Enqueue user message se nel periodo selezionato non ci sono pagine visitate AKA statistiche da mostrare
		if(!$statsData[TOTALVISITEDPAGES]) {
			$this->app->enqueueMessage(Text::_('COM_JREALTIME_NO_STATS_IN_PERIOD'));
		}
		
		// Set reference in template
		$dates = array('start'=>$this->getModel()->getState('fromPeriodOriginal'), 'to'=>$this->getModel()->getState('toPeriodOriginal'));
		$this->data = $statsData;
		$this->dates = $dates;
		$this->geotrans = $geoTranslations;
		$this->userid = $this->user->id ? $this->user->id : session_id();
		$this->nocache = '?time=' . time();
		$this->lists = $lists;
		$this->canExport = (bool)$this->getModel()->getState('hasExportPermission', true);
		$this->livesite = Uri::root();
		
		// Set timezone if required
		if($this->cparams->get('offset_type', 'joomla') == 'joomla') {
			$jConfig = $this->app->getConfig();
			date_default_timezone_set($jConfig->get('offset', 'UTC'));
			// Fix for the new Date by logger date_default_timezone_set to UTC, reset always timezone
			$reflection = new \ReflectionProperty('\Joomla\CMS\Date\Date', 'stz');
			$reflection->setAccessible(true);
			$reflection->setValue(null, null);
		}
		
		// View operations
		$this->_prepareDocument();
		
		// Mixin, add include path for admin side to avoid DRY on view templates
		$this->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR . '/tmpl/serverstats');
		
		parent::display ( $tpl );
	}
	
	/**
	 * Show entity details richiesto per visite utente e pagine
	 *
	 * @access public
	 * @param Object& $detailData
	 * @param string $tpl detailType
	 * @return void
	 */
	public function showEntity(&$detailData, $tpl) {
		$doc = $this->app->getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc, null);
		$doc->getWebAssetManager()->registerAndUseScript ( 'jrealtime.tablesorter', 'administrator/components/com_jrealtimeanalytics/js/libraries/tablesorter/jquery.tablesorter.js', [], [], ['jquery']);
		
		$doc->getWebAssetManager()->addInlineScript("
						jQuery(function($) {
							$('table.table-striped').tablesorter({
								cssHeader : ''
							});
						});
					");
		
		// Add scripting for flow tpl feature
		if($tpl == 'flow') {
			$doc->getWebAssetManager()->registerAndUseScript ( 'jrealtime.gojs', 'administrator/components/com_jrealtimeanalytics/js/libraries/gojs/go.js', [], [], ['jquery']);
			$doc->getWebAssetManager()->registerAndUseScript ( 'jrealtime.flow', 'administrator/components/com_jrealtimeanalytics/js/flow.js', [], [], ['jquery', 'jrealtime.gojs']);
		}

		$this->detailData = $detailData;
		$this->cparams = ComponentHelper::getParams('com_jrealtimeanalytics');
		$this->daemonRefresh = $this->cparams->get('daemonrefresh', 2);
		$this->canExport = (bool)$this->getModel()->getState('hasExportPermission', true);
		$this->livesite = Uri::root();
		
		parent::display($tpl);
	}
}