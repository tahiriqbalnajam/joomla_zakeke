<?php
namespace JExtstore\Component\JRealtimeAnalytics\Site\Model;
/**
 *
 * @package JREALTIMEANALYTICS::GARBAGE::components::com_jrealtimeanalytics
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Model\IObservable;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Model\Observer as JRealtimeModelObserver;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Exception as JRealtimeException;

/**
 * Garbage collector class
 *
 * @package JREALTIMEANALYTICS::GARBAGE::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 1.0
 */
class GarbageObsrvModel extends JRealtimeModelObserver {
	/**
	 * Realtime garbage status
	 *
	 * @access private
	 * @var Boolean
	 */
	private $realtimeGarbageEnabled;
	
	/**
	 * Serverstats garbage status
	 *
	 * @access private
	 * @var Boolean
	 */
	private $serverstatsGarbageEnabled;
	
	/**
	 * Max inactivity time for realtime stats garbage
	 *
	 * @access private
	 * @var int
	 */
	private $maxInactivityTime;
	
	/**
	 * Max age for serverstats garbage
	 *
	 * @access private
	 * @var int
	 */
	private $maxStatsAge;
	
	/**
	 * Decide la probabilità che il garbage collector venga avviato
	 *
	 * @access private
	 * @var int
	 */
	private $probability;
	
	/**
	 *
	 * @property Boolean $divisor - Il divisore probabilistico
	 * @access private
	 * @var int
	 */
	private $divisor;
	
	/**
	 * Esegue il calcolo probabilistico vero e proprio
	 *
	 * @access private
	 * @return Boolean
	 */
	private function probabilityFn() {
		$randomNumber = rand ( 0, $this->divisor );
		if (0 < $randomNumber && $randomNumber <= $this->probability) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Costruisce la query di DELETE dal DB in caso di match
	 *
	 * @access private
	 * @param boolean $realtime
	 * @param boolean $serverstats
	 * @return String
	 */
	protected function buildQuery($realtime, $serverstats) {
		$queries = array ();
		$currentTime = time ();
		
		// Realtime garbage active
		if($realtime) {
			// Realtime garbage threshold
			$thresholdRealtime = $currentTime - $this->maxInactivityTime;
			// Realtime garbage query
			$queries ['realtime'] = "DELETE FROM #__realtimeanalytics_realstats" . 
									"\n WHERE " . $this->dbInstance->quoteName ( 'lastupdate_time' ) . " < " . $this->dbInstance->quote ( $thresholdRealtime );
		}
		
		// Serverstats garbage active
		if($serverstats) {
			// Serverstats garbage max age
			$plural = $this->maxStatsAge > 1 ? 's' : '';
			$maxStatsAge = date('Y-m-d', strtotime('-' . $this->maxStatsAge . ' month' . $plural, $currentTime));
			
			$queries ['serverstats'] = "DELETE FROM #__realtimeanalytics_serverstats" .
									   "\n WHERE " . $this->dbInstance->quoteName ( 'visitdate' ) . " < " . $this->dbInstance->quote ( $maxStatsAge );
			$queries ['referral'] = "DELETE FROM #__realtimeanalytics_referral" .
									"\n WHERE " . $this->dbInstance->quoteName ( 'record_date' ) . " < " . $this->dbInstance->quote ( $maxStatsAge );
			$queries ['searches'] = "DELETE FROM #__realtimeanalytics_searches" .
									"\n WHERE " . $this->dbInstance->quoteName ( 'record_date' ) . " < " . $this->dbInstance->quote ( $maxStatsAge );
			$queries ['heatmap'] = "DELETE FROM #__realtimeanalytics_heatmap_clicks" .
								   "\n WHERE " . $this->dbInstance->quoteName ( 'record_date' ) . " < " . $this->dbInstance->quote ( $maxStatsAge );
		}
		
		return $queries;
	}
	
	/**
	 * Execute garbage collector if probability have success
	 *
	 * @param IObservableModel $subject        	
	 * @access public
	 * @return mixed If some exceptions occur return an Exception object otherwise boolean true
	 */
	public function update(IObservable $subject) {
		// Current subject Observable object
		$this->subject = $subject;
		
		// Execute only on initialize from JS APP observable notify
		if (! $this->subject->getState ( 'initialize' )) {
			return true;
		}
		
		// Execute probability function and check for random match
		$match = $this->probabilityFn ();
		
		if ($match && ($this->realtimeGarbageEnabled || $this->serverstatsGarbageEnabled)) {
			// Get requested queries for garbage based on garbage types active
			$queries = $this->buildQuery ($this->realtimeGarbageEnabled, $this->serverstatsGarbageEnabled);
			try {
				// Execute every garbage queries detected
				foreach ( $queries as $query ) {
					$this->dbInstance->setQuery ( $query );
					$this->dbInstance->execute ();
				}
			} catch ( JRealtimeException $e ) {
				return $e;
			} catch ( \Exception $e ) {
				$jrealtimeException = new JRealtimeException ( Text::sprintf ( 'COM_JREALTIME_ERROR_ONDATABASE_GARBAGE', $e->getMessage () ), 'error', 'Garbage collector system' );
				return $jrealtimeException;
			}
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
		// Get dei parametri del componente
		$configParams = ComponentHelper::getParams ( 'com_jrealtimeanalytics' );
		
		// Set realtime parameters
		$this->realtimeGarbageEnabled = ( bool ) $configParams->get ( 'gcenabled', 1 );
		$this->probability = ( int ) $configParams->get ( 'probability', 10 );
		$this->maxInactivityTime = ( int ) $configParams->get ( 'maxlifetime_session', 8 );
		
		// Set serverstats parameters
		$this->serverstatsGarbageEnabled = ( bool ) $configParams->get ( 'gc_serverstats_enabled', 0 );
		$this->maxStatsAge = ( int ) $configParams->get ( 'gc_serverstats_period', 24 );
		
		// Probability divisor
		$this->divisor = 100;
		
		parent::__construct($config, $factory, $app, $input);
	}
} 