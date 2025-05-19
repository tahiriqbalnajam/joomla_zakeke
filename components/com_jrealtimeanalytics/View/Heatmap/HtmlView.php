<?php
namespace JExtstore\Component\JRealtimeAnalytics\Site\View\Heatmap;
/**
 * @package JREALTIMEANALYTICS::HEATMAP::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage eventstats
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Pagination\Pagination;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\View as JRealtimeView;

/**
 * @package JREALTIMEANALYTICS::HEATMAP::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage eventstats
 * @since 2.4
 */
class HtmlView extends JRealtimeView {
	// Template view variables
	protected $dates;
	protected $pagination;
	protected $searchword;
	protected $userid;
	protected $nocache;
	protected $lists;
	protected $orders;
	protected $items;
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
	 * Default display listEntities
	 *        	
	 * @access public
	 * @param string $tpl
	 * @return void
	 */
	public function display($tpl = null) {
		$this->cparams = $this->getModel ()->getComponentParams ();
		$menu = $this->app->getMenu ();
		$activeMenu = $menu->getActive ();
		if (isset ( $activeMenu )) {
			$this->menuTitle = $activeMenu->title;
		}
		
		// Load jQuery lib
		if($this->cparams->get('includejquery', 1)) {
			$this->loadJQuery($this->document, false);
		}
		
		$doc = $this->app->getDocument();
		$this->loadJQueryUI($doc); // Required for calendar feature
		$this->loadBootstrap($doc, null);
		$this->loadJQFancybox($doc);
		
		$doc->getWebAssetManager()->registerAndUseScript ( 'jrealtime.heatmap', 'administrator/components/com_jrealtimeanalytics/js/heatmap.js', [], [], ['jquery']);
		$this->document->getWebAssetManager()->addInlineScript("
						jQuery.submitbutton = function(pressbutton) {
							jQuery.submitform( pressbutton );
							if (pressbutton == 'heatmap.displaypdf') {
								setTimeout(function(){
									jQuery('#adminForm input[name=task]').val('heatmap.display');
								}, 200);
							}
							return true;
						}
						jQuery(function($){
							$('a[data-role=heatmap]').fancybox({
								width : '100%',
								height : '95%',
								autoScale : false,
								transitionOut : 'none',
								type : 'iframe',
								title : 'Heatmap',
								onComplete: function(element) {
									var titleElement = $('#fancybox-title-float-main');
									titleElement.text('Heatmap');
									$('#fancybox-title').css({'left': '50%', 'margin-left': '-' + parseInt(titleElement.width() / 2) + 'px'});
								}
							});
						});
					");
		
		// Get main records
		$rows = $this->get ( 'Data' );
		$lists = $this->get ( 'Lists' );
		$total = $this->get ( 'Total' );
		
		// Normalize the graph data and generate the graph
		$graphData = array();
		foreach ($rows as $index=>$row) {
			$graphData['#' . ($index + 1)] = $row->numclicks;
		}
		$graphGenerator = $this->getModel()->getState('graphRenderer');
		$graphGenerator->buildGenericBars($graphData, 
										  '_serverstats_heatmap.png', 
										  'COM_JREALTIME_HEATMAP_GRAPH', 
										  array('COM_JREALTIME_NUMCLICKS'));
		
		$orders = array ();
		$orders ['order'] = $this->getModel ()->getState ( 'order' );
		$orders ['order_Dir'] = $this->getModel ()->getState ( 'order_dir' );
		// Pagination view object model state populated
		$pagination = new Pagination ( $total, $this->getModel ()->getState ( 'limitstart' ), $this->getModel ()->getState ( 'limit' ) );
		
		$dates = array('start'=>$this->getModel()->getState('fromPeriod'), 'to'=>$this->getModel()->getState('toPeriod'));
		$this->dates = $dates;
		$this->user = $this->app->getIdentity ();
		$this->pagination = $pagination;
		$this->searchword = $this->getModel ()->getState ( 'searchword' );
		$this->userid = $this->user->id ? $this->user->id : session_id();
		$this->nocache = '?time=' . time();
		$this->lists = $lists;
		$this->orders = $orders;
		$this->items = $rows;
		$this->canExport = (bool)$this->getModel()->getState('hasExportPermission', true);
		$this->option = $this->getModel ()->getState ( 'option' );
		
		// View operations
		$this->_prepareDocument();
		
		// Mixin, add include path for admin side to avoid DRY on view templates
		$this->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR . '/tmpl/heatmap');
		
		parent::display ();
	}
}