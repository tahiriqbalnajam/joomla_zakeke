<?php
namespace JExtstore\Component\JRealtimeAnalytics\Site\Model;
/**
 *
 * @package JREALTIMEANALYTICS::STREAM::components::com_jrealtimeanalytics
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Model\Observable as JRealtimeModelObservable;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Helpers\Users as JRealtimeHelpersUsers;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Exception as JRealtimeException;

/**
 * Stream model
 * The entity to perform CRUD operation on, here is the Stream
 * It supports special get/store/delete responsibilities to be a
 * more generic stream resource for every service
 *
 * @package JREALTIMEANALYTICS::STREAM::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 2.0
 */
class StreamModel extends JRealtimeModelObservable {
	/**
	 * Session Object
	 *
	 * @access private
	 * @var Object &
	 */
	private $session;
	
	/**
	 * Me user Object
	 *
	 * @access private
	 * @var Object &
	 */
	private $myUser;
	
	/**
	 * Component config
	 *
	 * @access private
	 * @var Object &
	 */
	private $config;
	
	/**
	 * Max lifetime for inactivity time of Realtime display stats
	 *
	 * @access private
	 * @var int
	 */
	private $maxInactivityTime;
	
	/**
	 * Sess SQL query for the client id
	 * @access private
	 * @var string
	 */
	private $sessClientId;
	
	/**
	 * Array associativo della response HTTP
	 *
	 * @access private
	 * @var array
	 */
	private $response;
	
	/**
	 * Load realtime users on current page posted
	 *
	 * @access protected
	 * @return int
	 */
	protected function getRealtimeUsersOnPage() {
		// Retrieve and ensure that a nowpage for current user is provided
		$nowPage = $this->getState('nowpage', null);
		if(!$nowPage) {
			return 0; // No counter active
		}

		$query = "SELECT COUNT(*) FROM #__realtimeanalytics_realstats AS stats" .
				 "\n INNER JOIN #__session AS sess" .
				 "\n ON sess.session_id = stats.session_id_person" .
				 "\n WHERE " . $this->dbInstance->quoteName('lastupdate_time') . " > " . (int)(time() - $this->maxInactivityTime) .
				 "\n AND " . $this->dbInstance->quoteName('nowpage') . " = " . $this->dbInstance->quote($nowPage) .
				 "\n AND " . $this->sessClientId;
		$this->dbInstance->setQuery($query);
		$result = $this->dbInstance->loadResult();

		return $result;
	}
	
	/**
	 * Load realtime users currently on site in some status
	 *
	 * @access protected
	 * @return int
	 */
	protected function getRealtimeUsersTotal() {
		$query = "SELECT COUNT(*) FROM #__realtimeanalytics_realstats AS stats" .
				 "\n INNER JOIN #__session AS sess" .
				 "\n ON sess.session_id = stats.session_id_person" .
				 "\n WHERE " . $this->dbInstance->quoteName('lastupdate_time') . " > " . (int)(time() - $this->maxInactivityTime) .
				 "\n AND " . $this->sessClientId;
		$this->dbInstance->setQuery($query);
		$result = $this->dbInstance->loadResult();
	
		return $result;
	}
	
	/**
	 * Load realtime users logged on site
	 *
	 * @access protected
	 * @return int
	 */
	protected function getRealtimeUsersLogged() {
		$query = "SELECT COUNT(*) FROM #__realtimeanalytics_realstats AS stats" .
				 "\n INNER JOIN #__session AS sess" .
				 "\n ON sess.session_id = stats.session_id_person" .
				 "\n WHERE " . $this->dbInstance->quoteName('lastupdate_time') . " > " . (int)(time() - $this->maxInactivityTime) .
				 "\n AND " . $this->sessClientId .
				 "\n AND sess.guest = 0";
		$this->dbInstance->setQuery($query);
		$result = $this->dbInstance->loadResult();
	
		return $result;
	}
	
	/**
	 * Load realtime visitors on site
	 *
	 * @access protected
	 * @return int
	 */
	protected function getRealtimeVisitors() {
		$query = "SELECT COUNT(*) FROM #__realtimeanalytics_realstats AS stats" .
				 "\n INNER JOIN #__session AS sess" .
				 "\n ON sess.session_id = stats.session_id_person" .
				 "\n WHERE " . $this->dbInstance->quoteName('lastupdate_time') . " > " . (int)(time() - $this->maxInactivityTime) .
				 "\n AND " . $this->sessClientId .
				 "\n AND sess.guest = 1";
		$this->dbInstance->setQuery($query);
		$result = $this->dbInstance->loadResult();
	
		return $result;
	}
	
	/**
	 * Execute della app logic da controller
	 *
	 * @access public
	 * @return array The response array to be encoded for JS app
	 */
	public function getData(): array {
		$initialize = $this->getState ( 'initialize', false );
		// Store server stats con dependency injected object
		$userName = $this->myUser->name;
		if (! $userName) {
			$userName = JRealtimeHelpersUsers::generateRandomGuestNameSuffix ( $this->session->session_id, $this->cParams );
		}
		$this->setState('username', $userName);
		$this->setState('userid', $this->myUser->id);
		
		// Observers notify
		$observersResponses = $this->notify();
		
		// Manage observers exceptions for JS App debug
		foreach ($observersResponses as $observersResponse) {
			// Found an exception, set in JS app response for client side debug
			if ($observersResponse instanceof JRealtimeException) {
				$this->response['storing'][] = array('corefile'=>$observersResponse->getFile(), 'status'=>false, 'details'=>$observersResponse->getMessage());
			}
		}
		
		// Se è l'initialize = 1 ovvero la prima ajax call store server stats
		if ($initialize) {
			// Inject dei parametri che condizionano la restante parte nella JS APP
			if (! empty ( $this->cParams )) {
				$this->response ['configparams'] = $this->cParams->toObject ();
				unset($this->response['configparams']->rules);
			}
		}
		
		// If Realtime display stats are requested by module populating, go on to retrive realtime data and inject into response
		if($this->cParams->get('realtime_stats', false) && $this->getState('module_available', false)) {
			// Init data bind response
			$this->response['data-bind'] = array();

			// Retrieve realtime informations and manage exceptions for users
			try {
				$this->response['data-bind']['users_onpage'] = $this->getRealtimeUsersOnPage();
				$this->response['data-bind']['users_total'] = $this->getRealtimeUsersTotal();
				$this->response['data-bind']['users_logged'] = $this->getRealtimeUsersLogged();
				$this->response['data-bind']['visitors'] = $this->getRealtimeVisitors();
			} catch (JRealtimeException $e) {
				$this->response['loading'][] = array('corefile'=>$e->getFile(), 'details'=>$e->getMessage());
			} catch (\Exception $e) {
				$jrealtimeException = new JRealtimeException ( Text::sprintf ( 'COM_JREALTIME_ERROR_REALTIME_STATS_DATABASE', $e->getMessage () ), 'error', 'Realtime display stats' );
				$this->response['loading'][] = array('corefile'=>$jrealtimeException->getFile(), 'details'=>$jrealtimeException->getMessage());
			}
		}
		
		return $this->response;
	}
	
	/**
	 * Store banning state for current session id user
	 *
	 * @access public
	 * @param string $resourceType for future REST usage
	 * @return array
	 */
	public function storeEntityResource($resourceType) {
		try {
			// Check if the unique key already exists
			$query = "SELECT " . $this->dbInstance->quoteName('id') .
					 "\n FROM #__realtimeanalytics_heatmap" .
					 "\n WHERE" .
					 "\n " . $this->dbInstance->quoteName('selector') . " = " . $this->dbInstance->quote($this->getState('clicked_element')) .
					 "\n AND " . $this->dbInstance->quoteName('pageurl') . " = " . $this->dbInstance->quote($this->getState('nowpage'));
			$heatmapID = $this->dbInstance->setQuery($query)->loadResult();

			// If it's a new record and the heatmap ID is not existant place a new row and retrieve last ID
			if(!$heatmapID) {
				$query = "INSERT INTO #__realtimeanalytics_heatmap (" .
						 $this->dbInstance->quoteName('selector') . ", " .
						 $this->dbInstance->quoteName('pageurl') .
						 ") VALUES (" .
						 $this->dbInstance->quote($this->getState('clicked_element')) . "," .
						 $this->dbInstance->quote($this->getState('nowpage')) .
						 ")";
				$this->dbInstance->setQuery($query)->execute ();

				// Retrieve last generated ID
				$heatmapID = $this->dbInstance->insertid();
			}

			// Always insert a new click tracking record
			$query = "INSERT INTO #__realtimeanalytics_heatmap_clicks (" .
					$this->dbInstance->quoteName('record_date') . ", " .
					$this->dbInstance->quoteName('heatmap_id') .
					") VALUES (" .
					$this->dbInstance->quote(date('Y-m-d')) . "," .
					(int)$heatmapID .
					")";
			$this->dbInstance->setQuery($query)->execute ();
		} catch (JRealtimeException $e) {
			$this->response['storing'][] = array('corefile'=>$e->getFile(), 'status'=>false, 'details'=>$e->getMessage());
			return $this->response;
	
		} catch (\Exception $e) {
			$jrealtimeException = new JRealtimeException($e->getMessage(), 'error', 'Stream model storeEntityResource');
			$this->response['storing'][] = array('corefile'=>$jrealtimeException->getFile(), 'status'=>false, 'details'=>$jrealtimeException->getMessage());
			return $this->response;
		}
			
		return $this->response;
	}
	
	/**
	 * Class constructor
	 *
	 * @access public
	 * @param array $config        	
	 * @return Object&
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null) {
		// Hold JS client app response
		$this->response = array ();
		
		// Session table
		$this->session = $config ['sessiontable'];
		
		// User object
		$this->myUser = Factory::getApplication ()->getIdentity();
		
		// Component config with override management by model
		$this->cParams = $this->getComponentParams();
		
		// Set max life time for valid session on Realtime display stats
		$this->maxInactivityTime = $this->cParams->get('maxlifetime_session', 8);
		
		parent::__construct ( $config, $factory );
		
		// Evaluate the shared session option for SQL queries
		$sharedSession = (int)$this->app->get('shared_session', null);
		if($sharedSession == 1 && $this->cParams->get('shared_session_support', 1)) {
			$this->sessClientId = '(sess.client_id = 0 OR ISNULL(sess.client_id))';
		} else {
			$this->sessClientId = 'sess.client_id = 0';
		}
	}
}