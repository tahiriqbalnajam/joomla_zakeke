<?php
namespace JExtstore\Component\JRealtimeAnalytics\Site\Controller;
/**
 * @package JREALTIMEANALYTICS::HEATMAP::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Controller as JRealtimeController;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Graph\Generators\Charts as GraphGeneratorsCharts;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Renderers\Adapter\Pdf as RenderersAdapterPdf;

/**
 * Main controller
 * @package JREALTIMEANALYTICS::HEATMAP::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @since 2.4
 */
class HeatmapController extends JRealtimeController {
	/**
	 * Setta il model state a partire dallo userstate di sessione
	 *
	 * @access protected
	 * @param string $scope        	
	 * @param Object $model        	
	 * @param Object $cParams        	
	 * @return object
	 */
	protected function setModelState($scope = 'default', $model = null, $cParams = null): object {
		$option = $this->option;
		
		// Filtro Data DA... Data A... - Valori di default
		// Filter by current month - week - day
		if($cParams->get('default_period_interval', 'week') == 'day') {
			$startPeriod = date ( "Y-m-d" );
			$endPeriod = date ( "Y-m-d" );
		} elseif($cParams->get('default_period_interval', 'week') == 'week') {
			$dt = time();
			$startPeriod = date('N', $dt)==1 ? date('Y-m-d', $dt) : date('Y-m-d', strtotime('last monday', $dt));
			$endPeriod = date('N', $dt)==7 ? date('Y-m-d', $dt) : date('Y-m-d', strtotime('next sunday', $dt));
		} elseif ($cParams->get('default_period_interval', 'week') == 'month') {
			$startPeriod = date ( "Y-m-01", strtotime ( date ( "Y-m-d" ) ) );
			$endPeriod = date ( "Y-m-d", strtotime ( "-1 day", strtotime ( "+1 month", strtotime ( date ( "Y-m-01" ) ) ) ) );
		}
		
		$fromPeriod = $this->getUserStateFromRequest( "$option.$scope.fromperiod", 'fromperiod', strval($startPeriod));
		$toPeriod = $this->getUserStateFromRequest( "$option.$scope.toperiod", 'toperiod', strval($endPeriod));
		
		$model->setState('fromPeriodOriginal', $fromPeriod);
		$model->setState('toPeriodOriginal', $toPeriod);
		
		$filter_state = $this->getUserStateFromRequest ( "$option.$scope.filterstate", 'filter_state', null );
		$filter_catid = $this->getUserStateFromRequest ( "$option.$scope.filtercatid", 'filter_catid', null );
		$search = $this->getUserStateFromRequest ( "$option.$scope.searchword", 'search', null );
		
		$limit = $this->getUserStateFromRequest ( "$option.$scope.limit", 'limit', $this->app->get ( 'list_limit' ), 'int' );
		if(isset($_REQUEST['start'])) {
			$paginationVar = 'start';
		} else {
			$paginationVar = 'limitstart';
		}
		$limitStart = $this->getUserStateFromRequest ( "$option.$scope.limitstart", $paginationVar, 0, 'int' );
		
		// Round del limit al change proof
		$limitStart = ($limit != 0 ? (floor ( $limitStart / $limit ) * $limit) : 0);
	
		$filter_order = $this->getUserStateFromRequest( "$option.$scope.filter_order", 'filter_order', 'numclicks', 'cmd' );
		$filter_order_Dir = $this->getUserStateFromRequest("$option.$scope.filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$graphTheme = $this->getUserStateFromRequest ( "$option.$scope.graphtheme", 'graphtheme', 'Universal' );
		
		// Set model state
		$model->setState ( 'option', $option );
		$model->setState ( 'order', $filter_order);
		$model->setState ( 'order_dir', $filter_order_Dir);
		$model->setState ( 'limit', $limit );
		$model->setState ( 'limitstart', $limitStart );
		$model->setState ( 'searchword', $search );
		$model->setState ( 'fromPeriod', $fromPeriod);
		$model->setState ( 'toPeriod', $toPeriod);
		$model->setState ( 'graphTheme', $graphTheme );
		$model->setState ( 'hasExportPermission', $this->hasGroupsPermissions('exporter_groups', $cParams));
		
		return $model;
	}
	
	/**
	 * Default listEntities
	 * 
	 * @access public
	 * @param $cachable string
	 *       	 the view output will be cached
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		// Get sitemap model and view core
		$document = Factory::getApplication()->getDocument ();
		
		$viewType = $document->getType ();
		$coreName = $this->getName ();
		$viewLayout = 'default';
		
		$view = $this->getView ( $coreName, $viewType, '', array (
				'base_path' => $this->basePath
		) );
		
		// Mixin, add include path for admin side to avoid DRY on model
		$this->addModelPath ( JPATH_COMPONENT_ADMINISTRATOR . '/models', 'JRealtimeModel', 'JRealtimeModel' );
		
		// Get/Create the model
		if ($model = $this->getModel ( $coreName )) {
			// Push the model into the view (as default)
			$view->setModel ( $model, true );
		}
		
		// Set cParams for pdf export usage
		$cParams = $model->getComponentParams ();
		
		// Set model state
		$this->setModelState ( 'heatmap', $model, $cParams );
		
		// Graph Generators interface as Setter Dependency Injection
		$graphGenerator = new GraphGeneratorsCharts ( $model->getState ( 'graphTheme', 'Universal') );
		$model->setState('graphRenderer', $graphGenerator);
		
		if (!in_array($this->task, array('displaypdf'))) {
			// Set the layout
			$view->setLayout ( $viewLayout );
			$view->display ();
		} else {
			// Permissions check
			if (! $model->getState('hasExportPermission')) {
				$this->setRedirect ( \JRealtimeRoute::_("index.php?option=" . $this->option . "&task=" . $this->name . ".display"), Text::_ ( 'COM_JREALTIME_ERROR_ALERT_NOACCESS' ), 'notice' );
				return false;
			}
				
			// Call main template
			$prefixPath = null;
			if ($this->task === 'displaypdf') {
				$prefixPath = 'pdf_';
			}
			$view->setLayout ( $prefixPath . $viewLayout . '_list' );
				
			// Creazione buffer output
			ob_start ();
			// Parent construction and view display
			$view->display ( 'main' );
			$bufferContent = ob_get_contents ();
			ob_end_clean ();
				
				
			// Choose if plain HTML or PDF conversion is required based on tasks instead of document format
			switch ($this->task) {
				case 'displaypdf' :
					// Do conversion to PDF format using adapter
					$pdfRenderer = new RenderersAdapterPdf ($cParams);
					$pdfRenderer->renderContent ( $bufferContent, $model, 'heatmap_stats_report_' );
					break;
				default :
					echo $bufferContent;
			}
		}
	}
}