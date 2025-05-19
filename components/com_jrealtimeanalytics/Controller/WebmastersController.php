<?php
namespace JExtstore\Component\JRealtimeAnalytics\Site\Controller;
/**
 * @package JREALTIME::GOOGLE::administrator::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Controller as JRealtimeController;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Renderers\Adapter\Xls as RenderersAdapterXls;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Helpers\Mailer;

/**
 * Main controller
 * @package JREALTIME::GOOGLE::administrator::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @since 2.6
 */
class WebmastersController extends JRealtimeController {
	/**
	 * Set model state from session userstate
	 * @access protected
	 * @param string $scope
	 * @return object
	 */
	protected function setModelState($scope = 'default', $ordering = true): object {
		$option = $this->option;
		
		// Get default model
		$defaultModel = $this->getModel();
		
		$defaultStartPeriod = date ( "Y-m-01", strtotime ( date ( "Y-m-d" ) ) );
		$defaultEndPeriod = date ( "Y-m-d", strtotime ( "-1 day", strtotime ( "+1 month", strtotime ( date ( "Y-m-01" ) ) ) ) );
		$fromPeriod = $this->getUserStateFromRequest( "$option.$scope.fromperiod", 'fromperiod', strval($defaultStartPeriod));
		$toPeriod = $this->getUserStateFromRequest( "$option.$scope.toperiod", 'toperiod', strval($defaultEndPeriod));
		
		// Set model state
		$defaultModel->setState('fromPeriod', $fromPeriod);
		$defaultModel->setState('toPeriod', $toPeriod);
		$defaultModel->setState('option', $option );
		$defaultModel->setState('hasExportPermission', $this->hasGroupsPermissions('exporter_groups', $defaultModel->getComponentParams()));
		
		return $defaultModel;
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
		// Mixin, add include path for admin side to avoid DRY on model
		$this->addModelPath ( JPATH_COMPONENT_ADMINISTRATOR . '/models', 'JRealtimeModel', 'JRealtimeModel' );
		
		$model = $this->setModelState('webmasters');
		
		// Switch task for XLS rendering
		if($this->task == 'displayxls' || $this->task == 'emailxls') {
			// Get view always HTML format
			$view =  $this->getView ('webmasters', 'html', '', array('base_path' => $this->basePath, 'layout' => 'default'));
			// Push the model into the view (as default)
			$view->setModel ( $model, true );
			$view->setLayout('xls');
			
			// Mixin, add include path for admin side to avoid DRY on view templates
			$view->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR . '/tmpl/webmasters');
			
			//Creazione buffer output
			ob_start ();
			// Parent construction and view display
			$view->display('webmasters');
			$bufferContent = ob_get_contents ();
			ob_end_clean ();
			
			// Check if report by email is required
			$mailer = null;
			$cParams = $model->getComponentParams ();
			if($cParams->get('report_byemail', 0) && strpos($this->task, 'email') !== false) {
				// Root controller -> dependency injection
				if($model->getToken()) {
					$mailer = Mailer::getInstance('Joomla');
				} else {
					return false;
				}
			}
			
			$xlsRenderer = new RenderersAdapterXls($cParams, $mailer);
			$xlsRenderer->renderContent($bufferContent, $model, 'google_searchconsole_stats_');
		} else {
			parent::display($cachable, $urlparams);
		}
	}
	
	/**
	 * Delete a db table entity
	 *
	 * @access public
	 * @return bool
	 */
	public function deleteEntity(): bool {
		// Mixin, add include path for admin side to avoid DRY on model
		$this->addModelPath ( JPATH_COMPONENT_ADMINISTRATOR . '/models', 'JRealtimeModel', 'JRealtimeModel' );
		
		// Load della model e checkin before exit
		$model = $this->getModel ();

		if (! $model->deleteEntity ( null )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getExceptionLevel () );
			$this->setRedirect ( \JRealtimeRoute::_("index.php?option=" . $this->option . "&view=" . $this->name), Text::_ ( 'COM_JREALTIME_GOOGLE_WEBMASTERS_ERROR_' . 'LOGOUT' ) );
			return false;
		}
	
		$this->setRedirect ( \JRealtimeRoute::_("index.php?option=" . $this->option . "&view=" . $this->name), Text::_ ( 'COM_JREALTIME_GOOGLE_WEBMASTERS_SUCCESS_LOGOUT' ) );
		
		return true;
	}
	
	/**
	 * Class Constructor
	 *
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null) {
		parent::__construct($config, $factory, $app, $input);
		
		$this->registerTask('displayxls', 'display');

		// Composer autoloader
		if (PHP_VERSION_ID >= 70205) {
			require_once JPATH_COMPONENT_ADMINISTRATOR. '/Framework/composer/autoload_real.php';
			\ComposerAutoloaderInitcb4c0ac1dedbbba2f0b42e9cdf4d93d7JReal::getLoader();
		} else {
			// Fallback to legacy API
			require_once JPATH_COMPONENT_ADMINISTRATOR. '/Framework/composerlegacy/autoload_real.php';
			\ComposerAutoloaderInitfc5c9af51413a149e4084a610a3ab6deJReal::getLoader();
		}
	}
}