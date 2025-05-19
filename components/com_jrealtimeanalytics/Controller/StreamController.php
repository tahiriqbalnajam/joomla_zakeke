<?php
namespace JExtstore\Component\JRealtimeAnalytics\Site\Controller;
/**
 *
 * @package JREALTIMEANALYTICS::STREAM::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Controller as JRealtimeController;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Helpers\Users as JRealtimeHelpersUsers;

/**
 * Stream data controller class
 * The entity in this MVC core is the stream
 * The stream is a bidirectional entity: it can be for reading data through display method,
 * or to write data through the saveEntity method
 *
 * @package JREALTIMEANALYTICS::STREAM::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @since 2.0
 */
class StreamController extends JRealtimeController {
	/**
	 * Set model state always getting fresh vars from POST request
	 *
	 * @access protected
	 * @param string $scope        	
	 * @param Object $explicitModel        	
	 * @return object
	 */
	protected function setModelState($scope = 'default', $explicitModel = null): object {
		// Set model state for basic stream
		$explicitModel->setState ( 'initialize', $this->app->getInput()->getBool('initialize', false));
		$explicitModel->setState ( 'nowpage', urldecode($this->app->getInput()->post->getString ('nowpage', '')));
		$explicitModel->setState ( 'module_available', $this->app->getInput()->post->getBool ('module_available', false));
		$explicitModel->setState ( 'clicked_element', urldecode($this->app->getInput()->post->getRaw ('clicked_element', '')));
		
		return $explicitModel;
	}
	
	/**
	 * Display data for JS client on stream read/write by POST JS app
	 *
	 * @access public
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		// Initialization
		$document = Factory::getApplication()->getDocument ();
		$viewType = $document->getType ();
		$coreName = $this->getName ();
		
		// Instantiate session object for Dependency Injection into main model
		$userSessionTable = JRealtimeHelpersUsers::getSessiontable ();
		
		// Main Stream model, implements Observable role
		$model = $this->getModel ( $coreName, null, array (
				'sessiontable' => $userSessionTable
		) );
		
		// Evaluate the integration with the GDPR allow cookie and prevent unconsented stats tracking
		if($model->getComponentParams()->get('gdpr_integration', 0)) {
			$componentInstalledCompliance = ComponentHelper::getParams('com_gdpr')->get('compliance_type', null);
			$cookieConsentComplianceCookie = $this->app->getInput()->cookie->get('cookieconsent_status');
			if(($componentInstalledCompliance == 'opt-in' || $componentInstalledCompliance == 'opt-out') && (!$cookieConsentComplianceCookie || $cookieConsentComplianceCookie == 'deny')) {
				return;
			}
		}
		
		// Evaluate the IP address masking by cloudflare
		if($model->getComponentParams()->get('cloudflare_ip_masking', 0)) {
			$_SERVER['REMOTE_ADDR'] = @$_SERVER['HTTP_CF_CONNECTING_IP'];
		}
		// Evaluate the IP address masking or alternatives
		if($serverIpOverride = $model->getComponentParams()->get('server_ip_override', '')) {
			if($serverIpOverride == 'realip') {
				$_SERVER['REMOTE_ADDR'] = @$_SERVER['HTTP_X_REAL_IP'];
			} elseif($serverIpOverride == 'remoteip') {
				$_SERVER['REMOTE_ADDR'] = @$_SERVER['HTTP_X_REMOTE_IP'];
			} elseif($serverIpOverride == 'forwardedip') {
				$_SERVER['REMOTE_ADDR'] = @$_SERVER['HTTP_X_FORWARDED_FOR'];
			}
		}
		
		// GDPR IP pseudonymisation
		if($model->getComponentParams()->get('gdpr_ip_pseudonymisation', 0)) {
			$salt = $this->app->get('secret');
			$_SERVER['REMOTE_ADDR'] = substr(hash('sha256', $_SERVER['REMOTE_ADDR'] . $salt), 0, 32);
		}
		
		// Instantiate Observer objects to attach to main Observable Stream model
		$realStatsModel = $this->getModel ( 'RealstatsObsrv', null, array (
				'sessiontable' => $userSessionTable 
		) );
		
		$serverStatsModel = $this->getModel ( 'ServerstatsObsrv', null, array (
				'sessiontable' => $userSessionTable 
		) );
		
		$eventStatsModel = $this->getModel ( 'EventstatsObsrv', null, array (
				'sessiontable' => $userSessionTable
		) );
		
		$garbageModel = $this->getModel ( 'GarbageObsrv', null );
		
		// Attach observers to main subject
		$model->attach($realStatsModel);
		$model->attach($serverStatsModel);
		$model->attach($eventStatsModel);
		$model->attach($garbageModel);
		
		// Populate model state
		$this->setModelState ( 'stream', $model );
		
		// Try to load record from model
		$streamData = $model->getData ();
		
		// Get view and pushing model
		$view = $this->getView ( $coreName, $viewType, '', array (
				'base_path' => $this->basePath 
		) );
		
		// Format response for JS client as requested
		$view->display ( $streamData );
	}
	
	/**
	 * Bidirectional stream write, currently used to track page clicks for the heatmap tracking
	 *
	 * @access public
	 * @return bool
	 */
	public function saveEntity(): bool {
		// Initialization
		$document = Factory::getApplication()->getDocument();
		$viewType = $document->getType ();
		$coreName = $this->getName ();
		
		// Get the resource type, make the saveEntity extendable based on REST resources paradigm
		$resource = $this->app->getInput()->post->get('resource');
		
		// Instantiate model object with Dependency Injection
		// Instantiate session object for Dependency Injection into main model
		$model = $this->getModel($coreName, null, array('sessiontable'=>null));
		
		// Populate model state
		$this->setModelState ( 'stream', $model );
		
		// Save user click
		$response = $model->storeEntityResource($resource);
	
		// Get view and pushing model
		$view = $this->getView ( $coreName, $viewType, '', array ('base_path' => $this->basePath ) );
		
		// Format response for JS client as requested
		$view->display($response);
		
		return true;
	}
}
 