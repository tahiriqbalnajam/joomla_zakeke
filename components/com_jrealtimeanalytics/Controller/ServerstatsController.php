<?php
namespace JExtstore\Component\JRealtimeAnalytics\Site\Controller;
/**
 *
 * @package JREALTIMEANALYTICS::SERVERSTATS::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Controller as JRealtimeController;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Graph\Generators\Charts as GraphGeneratorsCharts;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Renderers\Adapter\Pdf as RenderersAdapterPdf;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Renderers\Adapter\Csv as RenderersAdapterCsv;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Renderers\Adapter\Xls as RenderersAdapterXls;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Helpers\Mailer;

/**
 * Main controller class
 *
 * @package JREALTIMEANALYTICS::SERVERSTATS::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @since 2.1
 */
class ServerstatsController extends JRealtimeController {
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
		if ($cParams->get ( 'default_period_interval', 'week' ) == 'day') {
			$startPeriod = date ( "Y-m-d" );
			$endPeriod = date ( "Y-m-d" );
			
			// Optional override if the datetime is chosen for a single day
			if($cParams->get('stats_calendar_type', 'date') == 'datetime') {
				$endPeriod = date ( "Y-m-d", strtotime ( "+1 day", strtotime ( date ( "Y-m-d" ) ) ) );
			}
		} elseif ($cParams->get ( 'default_period_interval', 'week' ) == 'week') {
			$dt = time ();
			$startPeriod = date ( 'N', $dt ) == 1 ? date ( 'Y-m-d', $dt ) : date ( 'Y-m-d', strtotime ( 'last monday', $dt ) );
			$endPeriod = date ( 'N', $dt ) == 7 ? date ( 'Y-m-d', $dt ) : date ( 'Y-m-d', strtotime ( 'next sunday', $dt ) );
		} elseif ($cParams->get ( 'default_period_interval', 'week' ) == 'month') {
			$startPeriod = date ( "Y-m-01", strtotime ( date ( "Y-m-d" ) ) );
			$endPeriod = date ( "Y-m-d", strtotime ( "-1 day", strtotime ( "+1 month", strtotime ( date ( "Y-m-01" ) ) ) ) );
		}
		
		$fromPeriod = $this->getUserStateFromRequest ( "$option.$scope.fromperiod", 'fromperiod', strval ( $startPeriod ) );
		$toPeriod = $this->getUserStateFromRequest ( "$option.$scope.toperiod", 'toperiod', strval ( $endPeriod ) );
		
		$model->setState('fromPeriodOriginal', $fromPeriod);
		$model->setState('toPeriodOriginal', $toPeriod);
		
		// Optional override if the datetime is chosen
		if($cParams->get('stats_calendar_type', 'date') == 'datetime') {
			$jConfig = Factory::getApplication()->getConfig();
			$joomlaConfigOffset = $jConfig->get('offset');
			$fromPeriodOffset = new \DateTime($fromPeriod, new \DateTimeZone($joomlaConfigOffset));
			$fromPeriodOffset->setTimezone(new \DateTimeZone("UTC"));
			$fromPeriod = $fromPeriodOffset->format("Y-m-d H:i:s");
			
			$toPeriodOffset = new \DateTime($toPeriod, new \DateTimeZone($joomlaConfigOffset));
			$toPeriodOffset->setTimezone(new \DateTimeZone("UTC"));
			$toPeriod = $toPeriodOffset->format("Y-m-d H:i:s");
		}
		
		$graphTheme = $this->getUserStateFromRequest ( "$option.$scope.graphtheme", 'graphtheme', 'Universal' );
		
		// Set model state
		$model->setState ( 'fromPeriod', $fromPeriod );
		$model->setState ( 'toPeriod', $toPeriod );
		$model->setState ( 'graphTheme', $graphTheme );
		$model->setState ( 'hasExportPermission', $this->hasGroupsPermissions('exporter_groups', $cParams));
		
		return $model;
	}
	
	/**
	 * Display the Sitemap
	 *
	 * @access public
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		// Get sitemap model and view core
		$document = Factory::getApplication()->getDocument ();
		
		$viewType = $document->getType ();
		$coreName = $this->getName ();
		$viewLayout = 'graph';
		
		$view = $this->getView ( $coreName, $viewType, '', array (
				'base_path' => $this->basePath 
		) );
		
		// Get/Create the model
		if ($model = $this->getModel ( $coreName )) {
			// Push the model into the view (as default)
			$view->setModel ( $model, true );
		}
		
		// Set model state
		$this->setModelState ( 'serverstats', $model, $model->getComponentParams () );
		
		// Graph Generators interface as Setter Dependency Injection
		$graphGenerator = new GraphGeneratorsCharts ( $model->getState ( 'graphTheme' ) );
		$model->setGraphRenderer ( $graphGenerator );
		
		if (!in_array($this->task, array('displaypdf','displaycsv','displayxls','emailpdf','emailcsv','emailxls'))) {
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
			if ($this->task === 'displaypdf' || $this->task === 'emailpdf') {
				$prefixPath = 'pdf_';
			}
			if($this->task === 'displaycsv' || $this->task === 'emailcsv') {
				$prefixPath = 'csv_';
			}
			if($this->task == 'displayxls' || $this->task === 'emailxls') {
				$prefixPath = 'xls_';
			}
			$view->setLayout ( $prefixPath . $viewLayout );
			
			// Creazione buffer output
			ob_start ();
			// Parent construction and view display
			$view->display ( 'main' );
			$bufferContent = ob_get_contents ();
			ob_end_clean ();
			
			// Check if report by email is required
			$mailer = null;
			$cParams = $model->getComponentParams ();
			if($cParams->get('report_byemail', 0) && strpos($this->task, 'email') !== false) {
				// Root controller -> dependency injection
				$mailer = Mailer::getInstance('Joomla');
			}
			
			// Choose if plain HTML or PDF conversion is required based on tasks instead of document format
			switch ($this->task) {
				case 'displaypdf' :
				case 'emailpdf' :
					// Do conversion to PDF format using adapter
					$pdfRenderer = new RenderersAdapterPdf ($cParams, $mailer);
					$pdfRenderer->renderContent ( $bufferContent, $model, 'global_stats_report_' );
					break;
				case 'displaycsv':
				case 'emailcsv':
					$csvRenderer = new RenderersAdapterCsv($cParams, $mailer);
					$csvRenderer->renderContent( $bufferContent, $model, 'global_stats_report_' );
					break;
				case 'displayxls':
				case 'emailxls':
					$xlsRenderer = new RenderersAdapterXls($cParams, $mailer);
					$xlsRenderer->renderContent($bufferContent, $model, 'global_stats_report_' );
					break;
				default :
					echo $bufferContent;
			}
		}
	}
	
	/**
	 * Details show entity
	 *
	 * @access public
	 * @return void
	 */
	public function showEntity() {
		// Get sitemap model and view core
		$document = Factory::getApplication()->getDocument ();
		
		$viewType = $document->getType ();
		$coreName = $this->getName ();
		$viewLayout = 'graph';
		
		$view = $this->getView ( $coreName, $viewType, '', array (
				'base_path' => $this->basePath 
		) );
		
		$identifier = $this->app->getInput()->get ( 'identifier', null, 'string' );
		$detailType = $this->app->getInput()->get ( 'details' );
		
		// Get/Create the model
		if ($model = $this->getModel ( $coreName )) {
			// Push the model into the view (as default)
			$view->setModel ( $model, true );
		}
		
		// Set model state
		$this->setModelState ( 'serverstats', $model, $model->getComponentParams () );
		
		$detailData = $model->loadStatsEntity ( $identifier, $detailType );
		// Try to load record from model
		if ($detailData === false) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelExceptions = $model->getExceptions ();
			foreach ( $modelExceptions as $exception ) {
				$this->app->enqueueMessage ( $exception->getMessage (), $exception->getExceptionLevel () );
			}
			return false;
		}
		
		// Call main template
		$prefixPath = null;
		if ($this->task === 'showEntitypdf') {
			$prefixPath = 'pdf_';
			// Mixin, add include path for admin side to avoid DRY on view templates
			$view->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR . '/tmpl/serverstats');
		}
		if($this->task === 'showEntitycsv') {
			$prefixPath = 'csv_';
			// Mixin, add include path for admin side to avoid DRY on view templates
			$view->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR . '/tmpl/serverstats');
		}
		if($this->task == 'showEntityxls') {
			$prefixPath = 'xls_';
			// Mixin, add include path for admin side to avoid DRY on view templates
			$view->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR . '/tmpl/serverstats');
		}
		$view->setLayout ( $prefixPath . 'details' );
		
		// Creazione buffer output
		ob_start ();
		// Parent construction and view display
		$view->showEntity ( $detailData, $detailType );
		$bufferContent = ob_get_contents ();
		ob_end_clean ();
		
		// Choose if plain HTML or PDF conversion is required based on tasks instead of document format
		switch ($this->task) {
			case 'showEntitypdf' :
				// Do conversion to PDF format using adapter
				$pdfRenderer = new RenderersAdapterPdf ();
				$pdfRenderer->renderContent ( $bufferContent, $model, $detailType . '_stats_report_' );
				break;
			case 'showEntitycsv':
				$csvRenderer = new RenderersAdapterCsv();
				$csvRenderer->renderContent( $bufferContent, $model, $detailType . '_stats_report_' );
				break;
			case 'showEntityxls':
				$xlsRenderer = new RenderersAdapterXls();
				$xlsRenderer->renderContent($bufferContent, $model, $detailType . '_stats_report_' );
				break;
			default :
				echo $bufferContent;
		}
	}
	
	/**
	 * Class Constructor
	 *
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null) {
		parent::__construct($config, $factory, $app, $input);

		// Routes controller
		$this->registerTask ( 'view', 'display' );
		$this->registerTask ( 'displaypdf', 'display' );
		$this->registerTask ( 'displaycsv', 'display');
		$this->registerTask ( 'displayxls', 'display');
		$this->registerTask ( 'showEntitypdf', 'showEntity' );
		$this->registerTask ( 'showEntitycsv', 'showEntity');
		$this->registerTask ( 'showEntityxls', 'showEntity');
		
		// Mailer tasks
		$this->registerTask ( 'emailpdf', 'display' );
		$this->registerTask ( 'emailcsv', 'display');
		$this->registerTask ( 'emailxls', 'display');
	}
}