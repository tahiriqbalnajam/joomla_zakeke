<?php
namespace JExtstore\Component\JRealtimeAnalytics\Site\Model;
/**
 *
 * @package JREALTIMEANALYTICS::REALSTATS::components::com_jrealtimeanalytics
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Model\IObservable;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Model\Observer as JRealtimeModelObserver;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Exception as JRealtimeException;

/**
 * Realstats class frontend implementation
 *
 * @package JREALTIMEANALYTICS::REALSTATS::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 2.0
 */
class RealstatsObsrvModel extends JRealtimeModelObserver {
	/**
	 * Session ID utente in refresh
	 *
	 * @var Object&
	 * @access private
	 */
	private $session;
	
	/**
	 * Component config
	 *
	 * @access private
	 * @var Object &
	 */
	private $config;
	
	/**
	 * Ottiene dalla POST AJAX il current URL dove si trova l'utente
	 *
	 * @access protected
	 * @return string
	 */
	protected function getNowPage() {
		// Get current user page
		$currentUserPage = $this->app->getInput()->post->getString ('nowpage', '');
		
		// Do url decoding
		$currentUserPage = urldecode($currentUserPage);
		
		return $currentUserPage;
	}
	
	/**
	 * Ottiene dalla POST AJAX il current OS detected tramite Client Hints
	 *
	 * @access protected
	 * @return string
	 */
	protected function getWindowsVersion() {
		// Client hints detected OS
		$clientHintOS = $this->app->getInput()->post->getString ('windowsver', '');
		
		return $clientHintOS;
	}
	
	/**
	 * Si occupa di aggiornare il record contestuale alla sessione utente connesso
	 * nella tabella 1 a 1 #__realtimeanalytics_realstats -> #__session
	 *
	 * @param IObservableModel $subject
	 * @access public
	 * @return mixed If some exceptions occur return an Exception object otherwise boolean true
	 */
	public function update(IObservable $subject) {
		// Current subject Observable object
		$this->subject = $subject;
		
		// Ottenimento pagina utente corrente
		$nowPage = $this->getNowPage ();
		$clientHintOS = $this->getWindowsVersion();
		
		$currentName = $this->subject->getState('username');
		$field = null;
		$value = null;
		$update = null;
		if ($currentName) {
			$field = ", \n " . $this->dbInstance->quoteName ( 'current_name' );
			$value = "," . $this->dbInstance->quote ( $currentName );
			$update = ", \n " . $this->dbInstance->quoteName ( 'current_name' ) . "=" . $this->dbInstance->quote ( $currentName );
		}
		
		try {
			// Build query insert/update
			$insertUpdateQuery = "INSERT INTO #__realtimeanalytics_realstats" .
								 "\n ( " . $this->dbInstance->quoteName ( 'session_id_person' ) . "," .
								 "\n " . $this->dbInstance->quoteName ( 'nowpage' ) . "," .
								 "\n " . $this->dbInstance->quoteName ( 'lastupdate_time' ) . $field . " )" .
								 "\n VALUES ( " . $this->dbInstance->quote ( $this->session->session_id ) . "," . $this->dbInstance->quote ( $nowPage ) . "," . $this->dbInstance->quote ( time () ) . $value . " )" .
								 "\n ON DUPLICATE KEY UPDATE" . "\n " . $this->dbInstance->quoteName ( 'nowpage' ) . " = " . $this->dbInstance->quote ( $nowPage ) . "," .
								 "\n " . $this->dbInstance->quoteName ( 'lastupdate_time' ) . "=" . $this->dbInstance->quote ( time () ) . $update;
			$this->dbInstance->setQuery ( $insertUpdateQuery );
			$this->dbInstance->execute ();
			
			// Increment impulse timing only after first initialize, aka initialize = 0
			if(!$this->subject->getState('initialize')) {
				$impulseUpdateQuery = "UPDATE #__realtimeanalytics_serverstats" . 
									  "\n SET impulse = impulse  + 1" . 
									  ($clientHintOS ? ", os = " . $this->dbInstance->quote($clientHintOS) : '') .
									  "\n WHERE " . $this->dbInstance->quoteName ( 'session_id_person' ) . "=" . $this->dbInstance->quote ( $this->session->session_id ) .
									  "\n AND " . $this->dbInstance->quoteName ( 'visitdate' ) . "=" . $this->dbInstance->quote ( date ( 'Y-m-d' ) ) .
									  "\n AND " . $this->dbInstance->quoteName ( 'visitedpage' ) . "=" . $this->dbInstance->quote ( $nowPage );
				$this->dbInstance->setQuery ( $impulseUpdateQuery );
				$this->dbInstance->execute ();
			}
		} catch (JRealtimeException $e) {
			return $e;
		} catch (\Exception $e) {
			$jrealtimeException = new JRealtimeException(Text::sprintf('COM_JREALTIME_ERROR_ONDATABASE_REALSTATS', $e->getMessage()), 'error', 'Realtime stats');
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
		$this->config = ComponentHelper::getParams ( 'com_jrealtimeanalytics' );
		
		parent::__construct($config, $factory, $app, $input);
	}
}