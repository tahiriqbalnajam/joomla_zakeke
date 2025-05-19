<?php
namespace JExtstore\Component\Gdpr\Site\Model;
/**
 * @package GDPR::USER::components::com_gdpr
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2018 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\String\StringHelper;
use Joomla\Event\Event;
use JExtstore\Component\Gdpr\Administrator\Framework\Model as GdprModel;
use JExtstore\Component\Gdpr\Administrator\Framework\Exception as GdprException;

/**
 * Main offline cache resources model class
 *
 * @package GDPR::USER::components::com_gdpr
 * @subpackage models
 * @since 1.0
 */
class UserModel extends GdprModel {
	/**
	 * Load manifest file for this type of data source
	 * @access private
	 * @return mixed
	 */
	private function loadManifest($option) {
		// Load configuration manifest file
		$fileName = JPATH_COMPONENT . '/manifests/' . $option . '.json';
	
		// Check if file exists and is valid manifest
		if(!file_exists($fileName)) {
			return false;
		}
	
		// Load the manifest serialized file and assign to local variable
		$manifest = file_get_contents($fileName);
		$manifestConfiguration = json_decode($manifest);
	
		return $manifestConfiguration;
	}
	
	/**
	 * Delete all entities related to a user in a single operation
	 *
	 * @access public
	 * @param int $userId
	 * @return boolean
	 */
	public function deleteEntities($userId) {
		PluginHelper::importPlugin ( 'user', null, true, $this->app->getDispatcher());
		$deletionMode = $this->getComponentParams()->get('userprofile_delete_mode', 'permanent');
		// Get users data for the users to delete.
		$user_to_delete = Factory::getContainer()->get(\Joomla\CMS\User\UserFactoryInterface::class)->loadUserById($userId);
		
		$table = new \Joomla\CMS\Table\User($this->dbInstance);
		$table->load($userId);

		// Fire the before delete event.
		if (class_exists ( '\\Joomla\\CMS\\Event\\User\\BeforeDeleteEvent' )) {
			// Specific Event class, Joomla 5 and later
			$eventObjectBeforeDelete = new \Joomla\CMS\Event\User\BeforeDeleteEvent ( 'onUserBeforeDelete', [ 
					'subject' => $table->getProperties ()
			] );
		} else {
			// Generic Joomla\Event\Event, Joomla 4
			$eventObjectBeforeDelete = new Event ( 'onUserBeforeDelete', [ 
					$table->getProperties ()
			] );
		}
		$this->app->getDispatcher ()->dispatch ( 'onUserBeforeDelete', $eventObjectBeforeDelete );
		
		// Delete all user informations, profile and tables records
		if($deletionMode == 'permanent') {
			try {
				$query = "DELETE " .
						  $this->dbInstance->quoteName('jusers') . "," .
						  $this->dbInstance->quoteName('userkeys') . "," .
						  $this->dbInstance->quoteName('usernotes') . "," .
				 		  $this->dbInstance->quoteName('userprofiles') . "," .
		 				  $this->dbInstance->quoteName('usergroupmap') . "," .
	 				 	  $this->dbInstance->quoteName('sessiontable') .
						  "\n FROM #__users AS jusers" .
						  "\n LEFT JOIN #__user_keys AS userkeys ON jusers.id = userkeys.user_id " .
						  "\n LEFT JOIN #__user_notes AS usernotes ON jusers.id = usernotes.user_id " .
						  "\n LEFT JOIN #__user_profiles AS userprofiles ON jusers.id = userprofiles.user_id " .
						  "\n LEFT JOIN #__user_usergroup_map AS usergroupmap ON jusers.id = usergroupmap.user_id " .
						  "\n LEFT JOIN #__session AS sessiontable ON jusers.id = sessiontable.userid " .
						  "\n WHERE jusers.id = " . $userId;
				$this->dbInstance->setQuery($query);
				$this->dbInstance->execute();
			} catch (GdprException $e) {
				$this->setException($e);
				return false;
			} catch ( \Exception $e) {
				$gdprException = new GdprException($e->getMessage(), 'error');
				$this->setException($gdprException);
				return false;
			}
		} 

		// Delete all user informations using the Pseudoanonymisation
		if($deletionMode == 'pseudonymisation') {
			// Pseudoanonymisation of the user record
			try {
				$randomPseudonymisationString = md5(microtime() . $userId);
				$query = "UPDATE #__users" .
						 "\n SET " .
						 $this->dbInstance->quoteName('name') . " = " . $this->dbInstance->quote('') . "," .
						 $this->dbInstance->quoteName('username') . " = " . $this->dbInstance->quote($randomPseudonymisationString) . "," .
						 $this->dbInstance->quoteName('email') . " = " . $this->dbInstance->quote($randomPseudonymisationString) . "," .
						 $this->dbInstance->quoteName('password') . " = ''," .
						 $this->dbInstance->quoteName('block') . " = 1," .
						 $this->dbInstance->quoteName('registerDate') . " = " . $this->dbInstance->quote($this->dbInstance->getNullDate()) . "," .
						 $this->dbInstance->quoteName('lastvisitDate') . " = NULL," .
						 $this->dbInstance->quoteName('params') . " = '{}'" .
						 "\n WHERE id = " . $userId;
						 $this->dbInstance->setQuery($query);
						 $this->dbInstance->execute();

				$queryNotes = "UPDATE " . $this->dbInstance->quotename('#__user_notes') .
							  "\n SET " .  $this->dbInstance->quotename('body') . " = " . $this->dbInstance->quote($randomPseudonymisationString) .
						 	  "\n WHERE " .  $this->dbInstance->quotename('user_id') . " = " . $userId .
					 		  "\n AND " .  $this->dbInstance->quotename('catid') . " = " . (int) $this->getComponentParams()->get('log_usernote_privacypolicy_category', 0) .
							  "\n AND " .  $this->dbInstance->quotename('subject') . " = " . $this->dbInstance->quote(Text::_('COM_GDPR_PRIVACY_ACCEPTED_SUBJECT'));
						$this->dbInstance->setQuery($queryNotes);
						$this->dbInstance->execute();
			} catch (GdprException $e) {
				$this->setException($e);
				return false;
			} catch ( \Exception $e) {
				$gdprException = new GdprException($e->getMessage(), 'error');
				$this->setException($gdprException);
				return false;
			}
		}
		
		// Fire the after delete event.
		if (class_exists ( '\\Joomla\\CMS\\Event\\User\\AfterDeleteEvent' )) {
			// Specific Event class, Joomla 5 and later
			$eventObjectAfterDelete = new \Joomla\CMS\Event\User\AfterDeleteEvent ( 'onUserAfterDelete', [
					'subject'        => $user_to_delete->getProperties (),
					'deletingResult' => true,
					'errorMessage'   => ''
			] );
		} else {
			// Generic Joomla\Event\Event, Joomla 4
			$eventObjectAfterDelete = new Event ( 'onUserAfterDelete', [
					$user_to_delete->getProperties (),
					true,
					''
			] );
		}
		$this->app->getDispatcher ()->dispatch ( 'onUserAfterDelete', $eventObjectAfterDelete );
		
		// Check if additional contents must be deleted as well
		if($this->getComponentParams()->get('userprofile_delete_additional_contents', 0) && $deletionMode == 'permanent') {
			try {
				// Delete user generated contents
				$query = "DELETE FROM" .
						 "\n " .  $this->dbInstance->quoteName ('#__content') .
						 "\n WHERE " . $this->dbInstance->quoteName ('created_by') . " = " . $userId;
				$this->dbInstance->setQuery($query);
				$this->dbInstance->execute();
				
				$query = "DELETE FROM" .
						 "\n " .  $this->dbInstance->quoteName ('#__contact_details') .
						 "\n WHERE " . $this->dbInstance->quoteName ('created_by') . " = " . $userId .
						 "\n OR " . $this->dbInstance->quoteName ('user_id') .  " = " . $userId;
				$this->dbInstance->setQuery($query);
				$this->dbInstance->execute();
				
				$query = "DELETE FROM" .
						 "\n " .  $this->dbInstance->quoteName ('#__messages') .
						 "\n WHERE " . $this->dbInstance->quoteName ('user_id_from') . " = " . $userId .
						 "\n OR " . $this->dbInstance->quoteName ('user_id_to') .  " = " . $userId;
				$this->dbInstance->setQuery($query);
				$this->dbInstance->execute();
			} catch ( \Exception $e) {
				// No user exceptions for this stage
			}
		}
		
		// Check for integration with third party apps, and delete them accordingly jomsocial, easysocial, kunena, cbuilder, k2user
		$tpdIntegrations = $this->getComponentParams()->get('3pdintegration', array());
		if(count($tpdIntegrations) && $this->getComponentParams()->get('userprofile_delete_additional_contents', 0) && $deletionMode == 'permanent') {
			foreach ($tpdIntegrations as $integratedExtension) {
				$manifest = $this->loadManifest($integratedExtension);
				if($manifest && is_object($manifest)) {
					foreach ($manifest->delete_profile as $deleteQuery) {
						$query = $deleteQuery . $userId;
						try {
							$this->dbInstance->setQuery($query);
							$this->dbInstance->execute();
						} catch ( \Exception $e) {
							// No exceptions raising for users
						}
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Get all stored pre-existing consent data for both generic checkbox and dynamic checkbox if any for a give tuple of url, form, user
	 *
	 * @access public
	 * @param array $recordData
	 * @return Object&
	 */
	public function loadConsentEntityData($recordData) {
		$user = $this->app->getIdentity();
		if($user->id) {
			// We have a logged in user, track it
			$recordData['user_id'] = $user->id;
		}
		$recordData['session_id'] = session_id();
	
		// Check if we have a duplicated key AKA same url, same formid/or/formname and same user_id/or/session_id
		$where = array();
		// We have a logged in user
		if(isset($recordData['user_id'])) {
			$where[] = "\n " . $this->dbInstance->quoteName('user_id') . " = " . (int)($recordData['user_id']);
		} else {
			$where[] = "\n " . $this->dbInstance->quoteName('session_id') . " = " . $this->dbInstance->quote($recordData['session_id']);
		}
	
		// Identify the form in the page
		if(isset($recordData['formid'])) {
			$where[] = "\n " . $this->dbInstance->quoteName('formid') . " = " . $this->dbInstance->quote($recordData['formid']);
		} elseif(isset($recordData['formname'])) {
			$where[] = "\n " . $this->dbInstance->quoteName('formname') . " = " . $this->dbInstance->quote($recordData['formname']);
		}
	
		// Check the type of the consent origin and if a global scope override is required
		$consentOrigin = $this->app->getInput()->post->get('dynamicCheckbox', null) ? 'dynamic' : 'generic';
		if(!$this->getComponentParams()->get('consent_generic_bypage', 1) && $consentOrigin == 'generic') {
			$recordData['url'] = '*';
		}
		if(!$this->getComponentParams()->get('consent_dynamic_checkbox_bypage', 1) && $consentOrigin == 'dynamic') {
			$recordData['url'] = '*';
		}
	
		$query = "SELECT *" .
				 "\n FROM " . $this->dbInstance->quoteName('#__gdpr_consent_registry') .
				 "\n WHERE " . $this->dbInstance->quoteName('url') . " = " . $this->dbInstance->quote($recordData['url']) .
				 "\n AND "  . implode(" AND ", $where);
		try {
			$consentData = $this->dbInstance->setQuery($query)->loadObject();
		} catch (\Exception $e) {
			// No errors handling for user interface
		}
	
		return $consentData;
	}
	
	/**
	 * Get a pre-existing consent status if any for a give tuple of url, form, user
	 *
	 * @access public
	 * @param array $recordData
	 * @return mixed
	 */
	public function loadConsentEntity($recordData) {
		// Skip if tracking of previous consent is disabled
		if(!$this->getComponentParams()->get('consent_registry_track_previous_consent', 1)) {
			return 0;
		}
		
		$user = $this->app->getIdentity();
		if($user->id) {
			// We have a logged in user, track it
			$recordData['user_id'] = $user->id;
		}
		$recordData['session_id'] = session_id();
	
		// Check if we have a duplicated key AKA same url, same formid/or/formname and same user_id/or/session_id
		$where = array();
		// We have a logged in user
		if(isset($recordData['user_id'])) {
			$where[] = "\n " . $this->dbInstance->quoteName('user_id') . " = " . (int)($recordData['user_id']);
		} else {
			$where[] = "\n " . $this->dbInstance->quoteName('session_id') . " = " . $this->dbInstance->quote($recordData['session_id']);
		}
	
		// Identify the form in the page
		if(isset($recordData['formid'])) {
			$where[] = "\n " . $this->dbInstance->quoteName('formid') . " = " . $this->dbInstance->quote($recordData['formid']);
		} elseif(isset($recordData['formname'])) {
			$where[] = "\n " . $this->dbInstance->quoteName('formname') . " = " . $this->dbInstance->quote($recordData['formname']);
		}
		
		// Check the type of the consent origin and if a global scope override is required
		$consentOrigin = $this->app->getInput()->post->get('dynamicCheckbox', null) ? 'dynamic' : 'generic';
		if(!$this->getComponentParams()->get('consent_generic_bypage', 1) && $consentOrigin == 'generic') {
			$recordData['url'] = '*';
		}
		if(!$this->getComponentParams()->get('consent_dynamic_checkbox_bypage', 1) && $consentOrigin == 'dynamic') {
			$recordData['url'] = '*';
		}
		
		$query = "SELECT" .
				 "\n ". $this->dbInstance->quoteName('id') . "," .
				 "\n ". $this->dbInstance->quoteName('consent_date') .
				 "\n FROM " . $this->dbInstance->quoteName('#__gdpr_consent_registry') .
				 "\n WHERE " . $this->dbInstance->quoteName('url') . " = " . $this->dbInstance->quote($recordData['url']) .
				 "\n AND "  . implode(" AND ", $where);
		try {
			$existentConsentData = $this->dbInstance->setQuery($query)->loadObject();
			
			// Return an empty initialized object for the JS scope
			if(!$existentConsentData) {
				$existentConsentData = new \stdClass();
				$existentConsentData->id = null;
				$existentConsentData->consent_date = null;
			} else {
				$existentConsentData->consent_date = Text::sprintf('COM_GDPR_CONSENT_ACCEPTED_DATE', HTMLHelper::_('date', $existentConsentData->consent_date, Text::_('DATE_FORMAT_LC2')), array('jsSafe'=>true));
			}
		} catch (\Exception $e) {
			// Return an empty initialized object for the JS scope
			$existentConsentData = new \stdClass();
			$existentConsentData->id = null;
			$existentConsentData->consent_date = null;
		}
	
		return $existentConsentData;
	}
	
	/**
	 * Store the consent status for a give tuple of url, form, user
	 *
	 * @access public
	 * @param array $recordData
	 * @return mixed
	 */
	public function storeConsentEntity($recordData) {
		$user = $this->app->getIdentity();
		if($user->id) {
			// We have a logged in user, track it
			$recordData['user_id'] = $user->id;
		}
		$recordData['session_id'] = session_id();
		$recordData['consent_date'] = Date::getInstance()->toSql();

		// If log IP address
		if($this->getComponentParams()->get('log_user_ipaddress', 0)) {
			$recordData['ipaddress'] = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		}
		
		// Check if we have a duplicated key AKA same url, same formid/or/formname and same user_id/or/session_id
		$where = array();
		// We have a logged in user
		if(isset($recordData['user_id'])) {
			$where[] = "\n " . $this->dbInstance->quoteName('user_id') . " = " . (int)($recordData['user_id']);
		} else {
			$where[] = "\n " . $this->dbInstance->quoteName('session_id') . " = " . $this->dbInstance->quote($recordData['session_id']);
		}
		
		// Identify the form in the page
		if(isset($recordData['formid'])) {
			$where[] = "\n " . $this->dbInstance->quoteName('formid') . " = " . $this->dbInstance->quote($recordData['formid']);
		} elseif(isset($recordData['formname'])) {
			$where[] = "\n " . $this->dbInstance->quoteName('formname') . " = " . $this->dbInstance->quote($recordData['formname']);
		}
		
		// Check the type of the consent origin and if a global scope override is required
		$consentOrigin = $this->app->getInput()->post->get('dynamicCheckbox', null) ? 'dynamic' : 'generic';
		if(!$this->getComponentParams()->get('consent_generic_bypage', 1) && $consentOrigin == 'generic') {
			$recordData['url'] = '*';
		}
		if(!$this->getComponentParams()->get('consent_dynamic_checkbox_bypage', 1) && $consentOrigin == 'dynamic') {
			$recordData['url'] = '*';
		}
		
		$query = "SELECT " . $this->dbInstance->quoteName('id') .
				 "\n FROM " . $this->dbInstance->quoteName('#__gdpr_consent_registry') .
				 "\n WHERE " . $this->dbInstance->quoteName('url') . " = " . $this->dbInstance->quote($recordData['url']) .
				 "\n AND "  . implode(" AND ", $where);
		try {
			$existentId = $this->dbInstance->setQuery($query)->loadResult();
		} catch (\Exception $e) {
			// No errors handling for user interface
		}
		
		// Always store the record for a consent if tracking of previous consent is disabled
		if(!$this->getComponentParams()->get('consent_registry_track_previous_consent', 1)) {
			$existentId = false;
		}
		
		// Go on with a new store if no duplicated key detected
		if(!$existentId) {
			$recordDataObject = (object)$recordData;
			try {
				$this->dbInstance->insertObject('#__gdpr_consent_registry', $recordDataObject);
				return $this->dbInstance->insertid();
			} catch(\Exception $e) {
				// No errors handling for user interface
			}
		}
		
		return false;
	}
	
	/**
	 * Delete the consent status for a give tuple of url, form, user
	 *
	 * @access public
	 * @param array $recordData
	 * @return boolean
	 */
	public function deleteConsentEntity($postData) {
		$userId = $this->app->getIdentity()->id;
		$sessionId = session_id();
		$where = array();
		
		// We have a logged in user
		if($userId) {
			$where[] = "\n " . $this->dbInstance->quoteName('user_id') . " = " . (int)($userId);
		} else {
			$where[] = "\n " . $this->dbInstance->quoteName('session_id') . " = " . $this->dbInstance->quote($sessionId);
		}
		
		// Identify the form in the page
		if(isset($postData['formid'])) {
			$where[] = "\n " . $this->dbInstance->quoteName('formid') . " = " . $this->dbInstance->quote($postData['formid']);
		} elseif(isset($postData['formname'])) {
			$where[] = "\n " . $this->dbInstance->quoteName('formname') . " = " . $this->dbInstance->quote($postData['formname']);
		}
		
		// Check the type of the consent origin and if a global scope override is required
		$consentOrigin = $this->app->getInput()->post->get('dynamicCheckbox', null) ? 'dynamic' : 'generic';
		if(!$this->getComponentParams()->get('consent_generic_bypage', 1) && $consentOrigin == 'generic') {
			$postData['url'] = '*';
		}
		if(!$this->getComponentParams()->get('consent_dynamic_checkbox_bypage', 1) && $consentOrigin == 'dynamic') {
			$postData['url'] = '*';
		}
		
		$query = "DELETE FROM " . $this->dbInstance->quoteName('#__gdpr_consent_registry') .
				 "\n WHERE " . $this->dbInstance->quoteName('url') . " = " . $this->dbInstance->quote($postData['url']) . 
				 "\n AND "  . implode(" AND ", $where);
		try {
			$this->dbInstance->setQuery($query);
			$this->dbInstance->execute();
		} catch(\Exception $e) {
			// No errors handling for user interface
		}
	}
	
	/**
	 * Store the consent status for a given category of cookie
	 *
	 * @access public
	 * @param int $cookieCategory
	 * @param int $cookieCategoryState
	 * @return mixed
	 */
	public function storeCookieConsentEntity($cookieCategory, $cookieCategoryState) {
		$user = $this->app->getIdentity();
		if($user->id) {
			// We have a logged in user, track it
			$recordData['user_id'] = $user->id;
		}
		$recordData['session_id'] = session_id();
		$recordData['consent_date'] = Date::getInstance()->toSql();
	
		// Build the db field based on cookie category
		$recordData['generic'] = 1; // Always imply the generic cookie consent active
		$dbCategoryField = 'category' . $cookieCategory;
		$recordData[$dbCategoryField] = $cookieCategoryState;
	
		// Auto repopulate all OTHER CATEGORIES different than this one
		// Allow state, store default checked categories or restore them from the session
		$cParams = $this->getComponentParams();
		$session = $this->app->getSession();
		
		// Category 1
		if($dbCategoryField != 'category1') {
			$sessionStatusCategory1 = $session->get('gdpr_cookie_category_disabled_1', null);
			if(!is_null($sessionStatusCategory1)) {
				$recordData['category1'] = (int)$sessionStatusCategory1 == 1 ? 0 : 1; // Reverse logic 1 = declined -1 = accepted
			} else {
				$recordData['category1'] = $cParams->get('cookie_category1_checked', 1);
			}
		}
		
		// Category 2
		if($dbCategoryField != 'category2') {
			$sessionStatusCategory2 = $session->get('gdpr_cookie_category_disabled_2', null);
			if(!is_null($sessionStatusCategory2)) {
				$recordData['category2'] = (int)$sessionStatusCategory2 == 1 ? 0 : 1; // Reverse logic 1 = declined -1 = accepted
			} else {
				$recordData['category2'] = $cParams->get('cookie_category2_checked', 0);
			}
		}
		
		// Category 3
		if($dbCategoryField != 'category3') {
			$sessionStatusCategory3 = $session->get('gdpr_cookie_category_disabled_3', null);
			if(!is_null($sessionStatusCategory3)) {
				$recordData['category3'] = (int)$sessionStatusCategory3 == 1 ? 0 : 1; // Reverse logic 1 = declined -1 = accepted
			} else {
				$recordData['category3'] = $cParams->get('cookie_category3_checked', 0);
			}
		}
		
		// Category 4
		if($dbCategoryField != 'category4') {
			$sessionStatusCategory4 = $session->get('gdpr_cookie_category_disabled_4', null);
			if(!is_null($sessionStatusCategory4)) {
				$recordData['category4'] = (int)$sessionStatusCategory4 == 1 ? 0 : 1; // Reverse logic 1 = declined -1 = accepted
			} else {
				$recordData['category4'] = $cParams->get('cookie_category4_checked', 0);
			}
		}
		
		// If log IP address
		if($this->getComponentParams()->get('log_user_ipaddress', 0)) {
			$recordData['ipaddress'] = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		}
	
		// Check if we have a duplicated key AKA same user_id/or/session_id
		$where = array();
		// We have a logged in user
		if(isset($recordData['user_id'])) {
			$where[] = "\n " . $this->dbInstance->quoteName('user_id') . " = " . (int)($recordData['user_id']);
		} else {
			$where[] = "\n " . $this->dbInstance->quoteName('session_id') . " = " . $this->dbInstance->quote($recordData['session_id']);
		}
	
		// Always consider a consent valid within a specific time range, once elapsed it start a new consent
		$consentLifetime = $cParams->get('cookie_consent_lifetime', 365);
		$where[] = "\n " . $this->dbInstance->quoteName('consent_date') . " > " . $this->dbInstance->quote(date('Y-m-d', strtotime("-$consentLifetime days", time())));
	
		$query = "SELECT " . $this->dbInstance->quoteName('id') .
				 "\n FROM " . $this->dbInstance->quoteName('#__gdpr_cookie_consent_registry') .
				 "\n WHERE " . implode(" AND ", $where);
		try {
			$existentId = $this->dbInstance->setQuery($query)->loadResult();
		} catch (\Exception $e) {
			// No errors handling for user interface
		}
	
		// Normalize to object
		$recordDataObject = (object)$recordData;
	
		// Go on with a new store if no duplicated key detected
		if(!$existentId) {
			try {
				$this->dbInstance->insertObject('#__gdpr_cookie_consent_registry', $recordDataObject);
			} catch(\Exception $e) {
				// No errors handling for user interface
			}
		} else {
			try {
				$recordDataObject->id = $existentId;
				$this->dbInstance->updateObject('#__gdpr_cookie_consent_registry', $recordDataObject, 'id');
			} catch(\Exception $e) {
				// No errors handling for user interface
			}
		}
	
		return true;
	}
	
	/**
	 * Store the generic cookie consent status and for the related session categories
	 *
	 * @access public
	 * @param int $cookieGenericState
	 * @return mixed
	 */
	public function storeCookieGenericConsentEntity($cookieGenericState) {
		$user = $this->app->getIdentity();
		if($user->id) {
			// We have a logged in user, track it
			$recordData['user_id'] = $user->id;
		}
		$recordData['session_id'] = session_id();
		$recordData['consent_date'] = Date::getInstance()->toSql();
	
		// Build the db field based on cookie category
		$recordData['generic'] = $cookieGenericState;
	
		// Init variables
		$session = $this->app->getSession();
		$cParams = $this->getComponentParams();
		
		// Deny all state
		if(!$cookieGenericState) {
			// Set the session state for the deny all consent
			if($cParams->get('decline_button_behavior', 'hard') == 'hard') {
				$session->set('gdpr_generic_cookie_consent_denyall', 1);
			}
			
			// Is the preserve locked categories enabled?
			$preserveLockedCategories = $cParams->get('preserve_locked_categories', 0);
			
			// Setup the initial locked and checked state of each category
			$category1Checked = $cParams->get('cookie_category1_checked', 1);
			$category1Locked = $cParams->get('cookie_category1_locked', 0);
			
			$category2Checked = $cParams->get('cookie_category2_checked', 0);
			$category2Locked = $cParams->get('cookie_category2_locked', 0);
			
			$category3Checked = $cParams->get('cookie_category3_checked', 0);
			$category3Locked = $cParams->get('cookie_category3_locked', 0);
			
			$category4Checked = $cParams->get('cookie_category4_checked', 0);
			$category4Locked = $cParams->get('cookie_category4_locked', 0);
			
			$recordData['category1'] = $preserveLockedCategories && $category1Checked && $category1Locked ? 1 : 0;
			$recordData['category2'] = $preserveLockedCategories && $category2Checked && $category2Locked ? 1 : 0;
			$recordData['category3'] = $preserveLockedCategories && $category3Checked && $category3Locked ? 1 : 0;
			$recordData['category4'] = $preserveLockedCategories && $category4Checked && $category4Locked ? 1 : 0;
		} else {
			// Clear the session state for the deny all consent
			if($cParams->get('decline_button_behavior', 'hard') == 'hard') {
				$session->remove('gdpr_generic_cookie_consent_denyall');
			}

			// Allow state, store default checked categories or restore them from the session
			// Category 1
			$sessionStatusCategory1 = $session->get('gdpr_cookie_category_disabled_1', null);
			if(!is_null($sessionStatusCategory1)) {
				$recordData['category1'] = (int)$sessionStatusCategory1 == 1 ? 0 : 1; // Reverse logic 1 = declined -1 = accepted
			} else {
				$recordData['category1'] = $cParams->get('cookie_category1_checked', 1);
			}
				
			// Category 2
			$sessionStatusCategory2 = $session->get('gdpr_cookie_category_disabled_2', null);
			if(!is_null($sessionStatusCategory2)) {
				$recordData['category2'] = (int)$sessionStatusCategory2 == 1 ? 0 : 1; // Reverse logic 1 = declined -1 = accepted
			} else {
				$recordData['category2'] = $cParams->get('cookie_category2_checked', 0);
			}
				
			// Category 3
			$sessionStatusCategory3 = $session->get('gdpr_cookie_category_disabled_3', null);
			if(!is_null($sessionStatusCategory3)) {
				$recordData['category3'] = (int)$sessionStatusCategory3 == 1 ? 0 : 1; // Reverse logic 1 = declined -1 = accepted
			} else {
				$recordData['category3'] = $cParams->get('cookie_category3_checked', 0);
			}
				
			// Category 4
			$sessionStatusCategory4 = $session->get('gdpr_cookie_category_disabled_4', null);
			if(!is_null($sessionStatusCategory4)) {
				$recordData['category4'] = (int)$sessionStatusCategory4 == 1 ? 0 : 1; // Reverse logic 1 = declined -1 = accepted
			} else {
				$recordData['category4'] = $cParams->get('cookie_category4_checked', 0);
			}
		}
	
		// If log IP address
		if($this->getComponentParams()->get('log_user_ipaddress', 0)) {
			$recordData['ipaddress'] = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		}
	
		// Check if we have a duplicated key AKA same url, same user_id/or/session_id
		$where = array();
		// We have a logged in user
		if(isset($recordData['user_id'])) {
			$where[] = "\n " . $this->dbInstance->quoteName('user_id') . " = " . (int)($recordData['user_id']);
		} else {
			$where[] = "\n " . $this->dbInstance->quoteName('session_id') . " = " . $this->dbInstance->quote($recordData['session_id']);
		}
	
		// Always consider a consent valid within a specific time range, once elapsed it start a new consent
		$consentLifetime = $cParams->get('cookie_consent_lifetime', 365);
		$where[] = "\n " . $this->dbInstance->quoteName('consent_date') . " > " . $this->dbInstance->quote(date('Y-m-d', strtotime("-$consentLifetime days", time())));
	
		$query = "SELECT " . $this->dbInstance->quoteName('id') .
				 "\n FROM " . $this->dbInstance->quoteName('#__gdpr_cookie_consent_registry') .
				 "\n WHERE " . implode(" AND ", $where);
		try {
			$existentId = $this->dbInstance->setQuery($query)->loadResult();
		} catch (\Exception $e) {
			// No errors handling for user interface
		}
	
		// Normalize to object
		$recordDataObject = (object)$recordData;
	
		// Go on with a new store if no duplicated key detected
		if(!$existentId) {
			try {
				$this->dbInstance->insertObject('#__gdpr_cookie_consent_registry', $recordDataObject);
			} catch(\Exception $e) {
				// No errors handling for user interface
			}
		} else {
			try {
				$recordDataObject->id = $existentId;
				$this->dbInstance->updateObject('#__gdpr_cookie_consent_registry', $recordDataObject, 'id');
			} catch(\Exception $e) {
				// No errors handling for user interface
			}
		}
	
		return true;
	}
	
	/**
	 * Get data for a given dynamic checkbox:
	 * 1: name
	 * 2: formselector
	 * 3: required
	 *
	 * @access public
	 * @param string $placeholderIdentifier
	 * @param string $currentUrl
	 * @return mixed
	 */
	public function getCheckboxData($placeholderIdentifier, $currentUrl) {
		$checkboxData = new \stdClass();
		$recordData = array();
		$user = $this->app->getIdentity();
		if($user->id) {
			// We have a logged in user, track it
			$recordData['user_id'] = $user->id;
		}
		$recordData['session_id'] = session_id();
	
		// Check if we have a duplicated key AKA same url, same formid/or/formname and same user_id/or/session_id
		$where = array();
		// We have a logged in user
		if(isset($recordData['user_id'])) {
			$where[] = "\n " . $this->dbInstance->quoteName('user_id') . " = " . (int)($recordData['user_id']);
		} else {
			$where[] = "\n " . $this->dbInstance->quoteName('session_id') . " = " . $this->dbInstance->quote($recordData['session_id']);
		}
	
		// Identify the checkbox in the page
		$where[] = "\n " . $this->dbInstance->quoteName('formid') . " = " . $this->dbInstance->quote($placeholderIdentifier);
	
		// Check the type of the consent origin and if a global scope override is required
		$consentOrigin = $this->app->getInput()->post->get('dynamicCheckbox', null) ? 'dynamic' : 'generic';
		if(!$this->getComponentParams()->get('consent_generic_bypage', 1) && $consentOrigin == 'generic') {
			$currentUrl = '*';
		}
		if(!$this->getComponentParams()->get('consent_dynamic_checkbox_bypage', 1) && $consentOrigin == 'dynamic') {
			$currentUrl = '*';
		}
		
		$query = "SELECT" .
				 "\n " . $this->dbInstance->quoteName('id') . "," .
				 "\n " . $this->dbInstance->quoteName('consent_date') .
				 "\n FROM " . $this->dbInstance->quoteName('#__gdpr_consent_registry') .
				 "\n WHERE " . $this->dbInstance->quoteName('url') . " = " . $this->dbInstance->quote($currentUrl) .
				 "\n AND "  . implode(" AND ", $where);
		try {
			$existentConsent = $this->dbInstance->setQuery($query)->loadObject();
			
			// Return an empty initialized object for the JS scope
			if(!$existentConsent){
				$existentConsent = new \stdClass();
				$existentConsent->id = null;
				$existentConsent->consent_date = null;
			}
		} catch (\Exception $e) {
			// Return an empty initialized object for the JS scope
			$existentConsent = new \stdClass();
			$existentConsent->id = null;
			$existentConsent->consent_date = null;
		}
	
		$query = "SELECT " . 
				 $this->dbInstance->quoteName('name') . "," .
				 $this->dbInstance->quoteName('formselector') . "," .
				 $this->dbInstance->quoteName('required') .  "," .
				 $this->dbInstance->quoteName('published') .  "," .
				 $this->dbInstance->quoteName('access') .
				 "\n FROM " . $this->dbInstance->quoteName('#__gdpr_checkbox') .
			 	 "\n WHERE " . $this->dbInstance->quoteName('placeholder') . " = " . $this->dbInstance->quote($placeholderIdentifier);
		try {
			$checkboxData = $this->dbInstance->setQuery($query)->loadObject();
			$userAccessLevels = $user->getAuthorisedViewLevels();
			if(in_array($checkboxData->access, $userAccessLevels)) {
				$checkboxData->allowed = 1;
			} else {
				$checkboxData->allowed = 0;
			}
			unset($checkboxData->access);
			
			// Process J Text for 'name' field
			$checkboxData->name = Text::_($checkboxData->name);
		} catch (\Exception $e) {
			// No errors handling for user interface
		}
		
		// Skip if tracking of previous consent is disabled
		if(!$this->getComponentParams()->get('consent_registry_track_previous_consent', 1)) {
			$existentConsent->id = false;
			$existentConsent->consent_date = false;
		}
		
		// Add checkbox status
		$checkboxData->checked = $existentConsent->id;
		if($existentConsent->consent_date) {
			$checkboxData->consent_date = Text::sprintf('COM_GDPR_CONSENT_ACCEPTED_DATE', HTMLHelper::_('date', $existentConsent->consent_date, Text::_('DATE_FORMAT_LC2')), array('jsSafe'=>true));
		}
		
		return $checkboxData;
	}
	
	/**
	 * Get description for the popup fancybox for a given dynamic checkbox:
	 *
	 * @access public
	 * @param string $placeholderIdentifier
	 * @return mixed
	 */
	public function getCheckboxDescription($placeholderIdentifier) {
		$checkboxDescription = null;
		
		$query = "SELECT " .
				 $this->dbInstance->quoteName('descriptionhtml') .
				 "\n FROM " . $this->dbInstance->quoteName('#__gdpr_checkbox') .
				 "\n WHERE " . $this->dbInstance->quoteName('placeholder') . " = " . $this->dbInstance->quote($placeholderIdentifier) .
				 "\n AND "  .  $this->dbInstance->quoteName('published') . " = 1";
				
		try {
			$checkboxDescription = $this->dbInstance->setQuery($query)->loadresult();
		} catch (\Exception $e) {
			// No errors handling for user interface
		}

		return $checkboxDescription;
	}
	
	/**
	 * Get description for the popup fancybox for a given dynamic checkbox:
	 *
	 * @access public
	 * @param string $placeholderIdentifier
	 * @return mixed
	 */
	public function getCookieDescriptions($category) {
		$cookieDescriptions = array();
		
		$query = "SELECT " .
				 $this->dbInstance->quoteName('cookie') . "," .
				 $this->dbInstance->quoteName('descriptionhtml') . "," .
				 $this->dbInstance->quoteName('expiration') . "," .
				 $this->dbInstance->quoteName('alias') . 
				 "\n FROM " . $this->dbInstance->quoteName('#__gdpr_cookie_descriptions') .
				 "\n WHERE " . $this->dbInstance->quoteName('category') . " = " . (int)$category .
				 "\n AND "  .  $this->dbInstance->quoteName('published') . " = 1";
				
		try {
			$cookieDescriptions = $this->dbInstance->setQuery($query)->loadAssocList('cookie');
		} catch (\Exception $e) {
			// No errors handling for user interface
		}

		return $cookieDescriptions;
	}
	
	/**
	 * Get description for the popup fancybox for a given dynamic checkbox:
	 *
	 * @access public
	 * @param string $placeholderIdentifier
	 * @return mixed
	 */
	public function getCheckBulkConsent($ipaddress) {
		$cookieDescriptions = array();
		$consentLifeTime = $this->getComponentParams()->get('cookie_consent_lifetime', 365);
		$dateStartingValidConsent = $this->dbInstance->quote(date('Y-m-d', strtotime("-" . $consentLifeTime . " days", time())));
		
		$query = "SELECT " .
				 $this->dbInstance->quoteName('generic') . "," .
				 $this->dbInstance->quoteName('category1') . "," .
				 $this->dbInstance->quoteName('category2') . "," .
				 $this->dbInstance->quoteName('category3') . "," .
				 $this->dbInstance->quoteName('category4') .
				 "\n FROM " . $this->dbInstance->quoteName('#__gdpr_cookie_consent_registry') .
				 "\n WHERE " . $this->dbInstance->quoteName('ipaddress') . " = " . $this->dbInstance->quote($ipaddress) .
				 "\n AND "  .  $this->dbInstance->quoteName('consent_date') . " >= " . $dateStartingValidConsent .
				 "\n ORDER BY " .  $this->dbInstance->quoteName('consent_date') . " DESC";
				
		try {
			$validBulkConsents = $this->dbInstance->setQuery($query)->loadObject();
		} catch (\Exception $e) {
			// No errors handling for user interface
		}

		return $validBulkConsents;
	}
	
	/**
	 * Get the sum of domains for declined categories
	 *
	 * @access public
	 * @return mixed
	 */
	public function getDomainsSumForDeniedCategories() {
		$recordData = array();
		$domainsSum = array();
		$cParams = $this->getComponentParams();
		$session = $this->app->getSession();
		
		// Category 1
		if($cParams->get('cookie_category1_enable', 0)) {
			$sessionStatusCategory1 = $session->get('gdpr_cookie_category_disabled_1', null);
			if(!is_null($sessionStatusCategory1)) {
				$recordData['category1'] = (int)$sessionStatusCategory1 == 1 ? 0 : 1; // Reverse logic 1 = declined -1 = accepted
			} else {
				$recordData['category1'] = $cParams->get('cookie_category1_checked', 1);
			}
			// Is denied category, AKA = 0?
			if($recordData['category1'] == 0) {
				$domainsInThisCategory = explode(PHP_EOL, $cParams->get('domains_category1_list', ''));
				$domainsSum = array_merge($domainsSum, $domainsInThisCategory);
			}
		}
		
		// Category 2
		if($cParams->get('cookie_category2_enable', 0)) {
			$sessionStatusCategory2 = $session->get('gdpr_cookie_category_disabled_2', null);
			if(!is_null($sessionStatusCategory2)) {
				$recordData['category2'] = (int)$sessionStatusCategory2 == 1 ? 0 : 1; // Reverse logic 1 = declined -1 = accepted
			} else {
				$recordData['category2'] = $cParams->get('cookie_category2_checked', 0);
			}
			// Is denied category, AKA = 0?
			if($recordData['category2'] == 0) {
				$domainsInThisCategory = explode(PHP_EOL, $cParams->get('domains_category2_list', ''));
				$domainsSum = array_merge($domainsSum, $domainsInThisCategory);
			}
		}
		
		// Category 3
		if($cParams->get('cookie_category3_enable', 0)) {
			$sessionStatusCategory3 = $session->get('gdpr_cookie_category_disabled_3', null);
			if(!is_null($sessionStatusCategory3)) {
				$recordData['category3'] = (int)$sessionStatusCategory3 == 1 ? 0 : 1; // Reverse logic 1 = declined -1 = accepted
			} else {
				$recordData['category3'] = $cParams->get('cookie_category3_checked', 0);
			}
			// Is denied category, AKA = 0?
			if($recordData['category3'] == 0) {
				$domainsInThisCategory = explode(PHP_EOL, $cParams->get('domains_category3_list', ''));
				$domainsSum = array_merge($domainsSum, $domainsInThisCategory);
			}
		}
		
		// Category 4
		if($cParams->get('cookie_category4_enable', 0)) {
			$sessionStatusCategory4 = $session->get('gdpr_cookie_category_disabled_4', null);
			if(!is_null($sessionStatusCategory4)) {
				$recordData['category4'] = (int)$sessionStatusCategory4 == 1 ? 0 : 1; // Reverse logic 1 = declined -1 = accepted
			} else {
				$recordData['category4'] = $cParams->get('cookie_category4_checked', 0);
			}
			// Is denied category, AKA = 0?
			if($recordData['category4'] == 0) {
				$domainsInThisCategory = explode(PHP_EOL, $cParams->get('domains_category4_list', ''));
				$domainsSum = array_merge($domainsSum, $domainsInThisCategory);
			}
		}
		
		// Avoid duplicated and empty array elements
		$domainsSum = array_unique($domainsSum);
		$domainsSum = array_filter($domainsSum, 'strlen');
		
		// Ensure to remove all carriage return/line feed
		if(!empty($domainsSum)) {
			foreach ($domainsSum as &$domain) {
				$domain = StringHelper::str_ireplace(array("\r\n", "\n", "\r"), '', $domain);
			}
		}
		
		return $domainsSum;
	}
}