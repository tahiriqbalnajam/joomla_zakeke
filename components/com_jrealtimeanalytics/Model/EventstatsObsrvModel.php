<?php
namespace JExtstore\Component\JRealtimeAnalytics\Site\Model;
/**
 *
 * @package JREALTIMEANALYTICS::EVENTSTATS::components::com_jrealtimeanalytics
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Model\IObservable;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Model\Observer as JRealtimeModelObserver;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Exception as JRealtimeException;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Helpers\Mailer as JRealtimeMailer;

/**
 * Serverstats class frontend implementation <<testable_behavior>>
 *
 * @package JREALTIMEANALYTICS::EVENTSTATS::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 2.0
 */
class EventstatsObsrvModel extends JRealtimeModelObserver {
	/**
	 * Session ID utente in refresh
	 *
	 * @var Object&
	 * @access private
	 */
	private $session;
	
	/**
	 * Joomla config
	 *
	 * @access private
	 * @var Object &
	 */
	private $jConfig;
	
	/**
	 * Cachable component resources functions
	 *
	 * @access private
	 * @var boolean
	 */
	private $cachable;
	
	/**
	 * Notify the event by email to administrators set in the event settings
	 *
	 * @access private
	 * @param Object $event
	 * @return boolean
	 */
	private function notifyEvent($event) {
		// Evaluate the type of notification and act accordingly
		switch($event->goal_notification) {
			// Email notification for each event
			case '1':
				$subject = Text::_('COM_JREALTIME_EVENT_NOTIFICATION_SUBJECT');
				$body = Text::_('COM_JREALTIME_EVENT_NOTIFICATION_BODY');
				break;

			// Email notification when goal is reached
			case '2':
				// Skip entirely if the event has not goal
				if(!$event->hasgoal) {
					return false;
				}
				// Check if the goal has been reached
				$query = "SELECT COUNT(*)" .
						 "\n FROM #__realtimeanalytics_eventstats_track" .
						 "\n WHERE" .
						 "\n" . $this->dbInstance->quoteName('event_id') . " = " . (int)$event->id;
				$eventOccurrences = $this->dbInstance->setQuery($query)->loadResult();
				// Skip entirelly if the event has not reach expectation or already notified
				if($eventOccurrences != $event->goal_expectation) {
					return false;
				}

				$subject = Text::_('COM_JREALTIME_GOAL_NOTIFICATION_SUBJECT');
				$body = Text::_('COM_JREALTIME_GOAL_NOTIFICATION_BODY');
				break;
		}

		$emailAddresses = explode(',', trim($event->goal_notification_emails));
		if(!empty($emailAddresses)) {
			foreach ($emailAddresses as $validEmail) {
				if(filter_var(trim($validEmail), FILTER_VALIDATE_EMAIL)) {
					$validEmailAddresses[] = trim($validEmail);
				}
			}
		}

		// If valid email addresses detected send the notification
		if(!empty($validEmailAddresses)) {
			$mailer = JRealtimeMailer::getInstance('Joomla');

			// Build e-mail message format
			$mailer->setSender(array($this->componentParams->get('report_mailfrom', $this->jConfig->get('mailfrom')),
									 $this->componentParams->get('report_fromname', $this->jConfig->get('fromname'))));
			$mailer->setSubject($subject);

			// Transform the date string, get date time in UTC from DB
			$dateObject = Factory::getDate('now');
			// Set local time zone
			$dateObject->setTimezone(new \DateTimeZone($this->jConfig->get('offset')));

			/**
			 * Format a full body for the notification email
			*/
			$bodyText = Text::sprintf($body, $dateObject->format('Y-m-d H:i:s', true, false), Uri::root(false), Uri::root(false), $event->name, $_SERVER ['REMOTE_ADDR']);

			$mailer->setBody($bodyText);
			$mailer->IsHTML(true);

			// Add recipient
			$mailer->addRecipient($validEmailAddresses);

			// Send the Mail
			$rs	= $mailer->sendUsingExceptions();

			// Check for an error
			return $rs;
		}

		return false;
	}
	
	/**
	 * Physical store of every type of events.
	 * Event records is the same for every type, it's always a record
	 * addressing the user that trigged/registered the event
	 * 
	 * @access private
	 * @param int $eventid
	 * @param string $eventType
	 * @param Object $event
	 * @return boolean
	 */
	 private function storeEvent($eventid, $eventType, $event) {
		$insertIgnoreQuery = "INSERT IGNORE INTO #__realtimeanalytics_eventstats_track" .
						 	 "\n ( " . $this->dbInstance->quoteName ( 'event_id' ) . "," .
						 	 "\n " . $this->dbInstance->quoteName ( 'session_id' ) . "," .
						 	 "\n " . $this->dbInstance->quoteName ( 'eventdate' ) . "," .
						 	 "\n " . $this->dbInstance->quoteName ( 'event_timestamp' ) . " )" .
						 	 "\n VALUES ( " .
						 	 $this->dbInstance->quote ( $eventid ) . "," .
						 	 $this->dbInstance->quote ( $this->session->session_id ) . "," .
						 	 $this->dbInstance->quote ( date('Y-m-d') ) . "," .
						 	 $this->dbInstance->quote ( time () ) . " )";
		
		try {
			$this->dbInstance->setQuery ( $insertIgnoreQuery );
			$this->dbInstance->execute ();

			// New tracking record added, send an email notification if needed
			if($event->goal_notification && trim($event->goal_notification_emails)) {
				$this->notifyEvent($event);
			}
		} catch ( JRealtimeException $e ) { }
		  catch ( \Exception $e ) { }
		
		return true;
	}
	
	/**
	 * Process tracking for a particular visited page Itemid or custom URL
	 *
	 * @access protected
	 * @return boolean
	 */
	protected function trackEventVisitPage() {
		// Load cached events based on type and function args
		if($this->cachable) {
			// By default callback handler
			$cache = $this->getExtensionCache();
			$events = $cache->get('\JExtstore\Component\JRealtimeAnalytics\Site\Model\EventstatsObsrvModel::loadEventsFromDB', array('viewurl'));
		} else {
			$events = static::loadEventsFromDB('viewurl');
		}
		
		// Unserialize params and process
		if(is_array($events) && count($events)) {
			foreach ($events as $event) {
				$trackEvent = false;
				if($event->params) {
					$eventParams = json_decode($event->params);
				}
				// Detected Itemid menupage
				if(isset($eventParams->menupage) && is_numeric($eventParams->menupage)) {
					$currentItemid = $this->app->getInput()->get('Itemid');
					if($currentItemid == (int)$eventParams->menupage) {
						// DO track for this page
						$trackEvent = true;
					}
				} elseif(isset($eventParams->urlpage) && $eventParams->urlpage) {
					$uri = Uri::getInstance();
					$currentBaseUri = $uri->toString();
					if($currentBaseUri == $eventParams->urlpage) {
						// DO track for this page
						$trackEvent = true;
					}
				}
				
				// If event is detected as to be valid tracked
				if($trackEvent) {
			 		$this->storeEvent($event->id, 'viewurl', $event);
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Process tracking for a certain min number of visited pages
	 * in a single user session (addresses day)
	 *
	 * @access protected
	 * @return boolean
	 */
	protected function trackEventMinPageVisited() {
		// Load cached events based on type and function args
		if($this->cachable) {
			// By default callback handler
			$cache = $this->getExtensionCache();
			$events = $cache->get('\JExtstore\Component\JRealtimeAnalytics\Site\Model\EventstatsObsrvModel::loadEventsFromDB', array('minpages'));
		} else {
			$events = static::loadEventsFromDB('minpages');
		}
		
		// Unserialize params and process
		if(is_array($events) && count($events)) {
			foreach ($events as $event) {
				$trackEvent = false;
				if($event->params) {
					$eventParams = json_decode($event->params);
				}
				
				// Limit to a specific component
				if(isset($eventParams->extensions) && $eventParams->extensions) {
					$option = $this->app->getInput()->get('option');
					if($eventParams->extensions != $option) {
						continue;
					}
				}
				
				// Get number of visited page by this user (current session id)
				$query = "SELECT COUNT(*)" .
						 "\n FROM #__realtimeanalytics_serverstats" .
						 "\n WHERE " . $this->dbInstance->quoteName('session_id_person') . " = " . $this->dbInstance->quote($this->session->session_id);
				$this->dbInstance->setQuery($query);
				$results = $this->dbInstance->loadResult();
				
				// Check if current dispatch page is not already in DB Table and is valid as to be inserted in next initialize JS APP exec, if so consider +1 num pages
				$uri = Uri::getInstance();
				$currentBaseUri = $uri->toString();
				$query = $this->dbInstance->getQuery ( true );
				$query->select ( "COUNT(*)" )
					  ->from ( $this->dbInstance->quoteName ( "#__realtimeanalytics_serverstats" ) )
					  ->where ( $this->dbInstance->quoteName ( "session_id_person" ) . " = " . $this->dbInstance->quote ( $this->session->session_id ) )
					  ->where ( $this->dbInstance->quoteName ( "visitdate" ) . " = " . $this->dbInstance->quote ( date ('Y-m-d') ) )
					  ->where ( $this->dbInstance->quoteName ( "visitedpage" ) . " = " . $this->dbInstance->quote ( $currentBaseUri ) );
				// Set the query and execute the insert.
				$this->dbInstance->setQuery ( $query );
				$validNextInsertPage = !$this->dbInstance->loadResult ();
				$additionalCounter = $validNextInsertPage ? 1 : 0;
				
				/** 
				 * Compare needed minimum visited pages to trigger this event, add +1 to align to current visiting page NOT YET added to DB Table
				 * The DB Table adding for visiting page happens on first AJAX call with initialize = 1, so after plugin app dispatch to track this event
				 */
				if((int)$eventParams->minpages == ((int)$results + $additionalCounter)) {
					$this->storeEvent ($event->id, 'minpages', $event);
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Process tracking for a min time spent for a single session visit
	 * on the whole site or on a single specific page by Itemid
	 *
	 * @access protected
	 * @param int $Itemid        	
	 * @return boolean
	 */
	protected function trackEventMinTimeVisit($nowPage = null) {
		// Calculate event type suffix
		$eventTypeSuffix = $nowPage ? 'onpage' : null;
		$eventType = 'mintime' . $eventTypeSuffix;
		
		// Load cached events based on type and function args
		if($this->cachable) {
			// By default callback handler
			$cache = $this->getExtensionCache();
			$events = $cache->get('\JExtstore\Component\JRealtimeAnalytics\Site\Model\EventstatsObsrvModel::loadEventsFromDB', array($eventType));
		} else {
			$events = static::loadEventsFromDB($eventType);
		}
		
		// Unserialize params and process
		if(is_array($events) && count($events)) {
			foreach ($events as $event) {
				$trackEvent = false;
				if($event->params) {
					$eventParams = json_decode($event->params);
				}
				
				// Try to resolve an Itemid from current posted nowpage by JS APP, if event type of mintimeonpage for next comparison
				$wherePage = null;
				if($eventType == 'mintimeonpage' && $nowPage) {
					if(isset($eventParams->urlpage) && $eventParams->urlpage) {
						if($eventParams->urlpage != $nowPage) {
							continue;
						}
					} elseif(isset($eventParams->menupage) && is_numeric($eventParams->menupage)) {
						// Get the router
						$router = Factory::getContainer()->has('SiteRouter') ? Factory::getContainer()->get('SiteRouter'): $this->app::getRouter(); 
						//Avoid risk of recursion on multiple parse on same request
						$clonedRouter = clone($router);
						$newUriInstance = Uri::getInstance($nowPage);
						$queryStringItemid = $newUriInstance->getVar('Itemid');
						if($queryStringItemid) {
							$vars = [];
							$vars['Itemid'] = $queryStringItemid;
						} else {
							$vars = $clonedRouter->parse($newUriInstance);
						}
						if(empty($vars['Itemid'])) {
							continue;
						}
						
						if((int)$eventParams->menupage != (int)$vars['Itemid']) {
							continue;
						}
					}
					
					// Additional WHERE clause
					$wherePage = "\n AND " . $this->dbInstance->quoteName('visitedpage') . " = " . $this->dbInstance->quote($nowPage);
				}
				
				// Get total time spent by this user (current session id) on site
				$query = "SELECT SUM(". $this->dbInstance->quoteName('impulse') . ")".
						 "\n FROM  #__realtimeanalytics_serverstats" .
						 "\n WHERE " . $this->dbInstance->quoteName('session_id_person') . " = " . $this->dbInstance->quote($this->session->session_id) .
						 $wherePage;
				$this->dbInstance->setQuery($query);
				$impulses = $this->dbInstance->loadResult();
		
				// Calculate spent visit time based on daemon refresh
				$totalVisitTime = 0;
				if($impulses) {
					$daemonRefresh = $this->componentParams->get('daemonrefresh', 4);
					$totalVisitTime = $impulses * $daemonRefresh;
				}
				
				// Calculate needed time to trigger mintime event
				$neededTimeToTrigger = ($eventParams->mintime_mins * 60) + $eventParams->mintime_secs;
				
				/**
				 * Compare needed minimum time to trigger event for min time visit exceeded
				 */
				if($totalVisitTime > $neededTimeToTrigger) {
					$this->storeEvent ($event->id, $eventType, $event);
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Load events from DB table if $data for events are not supplied
	 * Once loaded events go on deserializing data and attaching to
	 * object array as a key for fastest array key exists search
	 *
	 * @access public
	 * @param string $type
	 * @return array
	 */
	public static function loadEventsFromDB($type) {
		$db = Factory::getContainer()->get('DatabaseDriver');
		// Load data events if not injected
		$query = "SELECT * FROM " . $db->quoteName('#__realtimeanalytics_eventstats') .
				 "\n WHERE " . $db->quoteName('type') . " = " . $db->quote($type) .
				 "\n AND " . $db->quoteName('published') . " = 1";
		$db->setQuery($query);
	
		try {
			$foundEvents = $db->loadObjectList();
		} catch (\Exception $e) {
			$foundEvents = false;
		}
	
		return $foundEvents;
	}
	
	/**
	 * Metodo di interfaccia pubblica per lo storing dei dati server stats
	 * nel tracking per il dispatch corrente
	 *
	 * @param IObservableModel $subject        	
	 * @access public
	 * @return mixed If some exceptions occur return an Exception object otherwise boolean true
	 */
	public function update(IObservable $subject) {
		// Current subject Observable object
		$this->subject = $subject;
		
		// Execute track event visit page and track event min page visited
		try {
			// Execute only on initialize by appdispatch plugin observable
			if ($this->subject->getState ( 'initialize' ) && $this->subject->getState ( 'appdispatch' )) {
				// Execute tracking for visit page
				$this->trackEventVisitPage ();
				
				// Execute tracking for number of min visited page
				$this->trackEventMinPageVisited ();
			} else {
				// All other cases are good to check if the track event mintime visit(page) have to be tracked if any active
				$this->trackEventMinTimeVisit ();
				$this->trackEventMinTimeVisit ( $this->app->getInput()->get ( 'nowpage', null, 'string' ) );
			}
		} catch ( JRealtimeException $e ) {
			return $e;
		} catch ( \Exception $e ) {
			if($e->getCode() == '404') {
				return true;
			}
			$jrealtimeException = new JRealtimeException ( Text::sprintf ( 'COM_JREALTIME_ERROR_ONDATABASE_EVENTSTATS', $e->getMessage () ), 'error', 'Events stats' );
			return $jrealtimeException;
		}
		
		return true;
	}
	
	/**
	 * Class constructor
	 *
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null) {
		$this->session = $config ['sessiontable'];
		$this->getComponentParams();
		$this->cachable = $this->componentParams->get('caching', false);
		
		parent::__construct($config, $factory, $app, $input);

		$this->jConfig = $this->app->getConfig();
	}
}