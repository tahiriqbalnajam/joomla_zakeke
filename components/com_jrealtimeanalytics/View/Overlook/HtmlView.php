<?php
namespace JExtstore\Component\JRealtimeAnalytics\Site\View\Overlook;
/**
 * @package JREALTIMEANALYTICS::OVERVIEW::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage overlook
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\View as JRealtimeView;

/**
 * @package JREALTIMEANALYTICS::OVERVIEW::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage overlook
 * @since 2.4
 */
class HtmlView extends JRealtimeView {
	// Template view variables
	protected $userid;
	protected $nocache;
	protected $lists;
	protected $monthString;
	protected $cparams;
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
		// Get main records
		$monthString = null;
		$monthSelected = $this->getModel()->getState('statsMonth', null);
		if($monthSelected) {
			$graphData = $this->get ('DataByMonth');
			$monthString = '- ' . date('F', mktime(0, 0, 0, $monthSelected));
		} else {
			$graphData = $this->get ( 'Data' );
		}
		$lists = $this->get ( 'Lists' );
		
		$graphTypeMethod = 'buildGeneric' . ucfirst($this->getModel()->getState('graphType', 'Bars'));
		$graphGenerator = $this->getModel()->getState('graphRenderer');
		$graphGenerator->$graphTypeMethod($graphData, '_serverstats_overview.png', 
													  Text::sprintf('COM_JREALTIME_OVERVIEW_STATS', $monthString),
													  array('COM_JREALTIME_TOTAL_VISITED_PAGES', 'COM_JREALTIME_TOTAL_VISITORS'));

		// Load jQuery lib
		if($this->cparams->get('includejquery', 1)) {
			$this->loadJQuery($this->document, false);
		}
		$doc = $this->app->getDocument();
		$this->loadBootstrap($doc, null); // Required for calendar feature
		$this->loadJQFancybox($doc);
		
		$this->document->getWebAssetManager()->addInlineScript("
						jQuery.submitbutton = function(pressbutton) {
							jQuery.submitform( pressbutton );
							if (pressbutton == 'overlook.displaypdf') {
								setTimeout(function(){
									jQuery('#adminForm input[name=task]').val('overlook.display');
								}, 200);
							}
							return true;
						}
						jQuery(function($){
							$('a[data-role=overview]').fancybox({
								transitionOut : 'none'
							});
						});
					");
		
		$dates = array('start'=>$this->getModel()->getState('fromPeriod'), 'to'=>$this->getModel()->getState('toPeriod'));
		$this->user = $this->app->getIdentity ();
		$this->userid = $this->user->id ? $this->user->id : session_id();
		$this->nocache = '?time=' . time();
		$this->lists = $lists;
		$this->monthString = $monthString;
		$this->canExport = (bool)$this->getModel()->getState('hasExportPermission', true);
		$this->option = $this->getModel ()->getState ( 'option' );
		
		// View operations
		$this->_prepareDocument();
		
		// Mixin, add include path for admin side to avoid DRY on view templates
		$this->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR . '/tmpl/overlook');
		
		parent::display ();
	}
}