<?php
namespace JExtstore\Component\Gdpr\Site\Controller;
/**
 *
 * @package GDPR::USER::components::com_gdpr
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2018 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Date\Date;
use Joomla\Registry\Registry;
use Joomla\Event\Event;
use Joomla\String\StringHelper;
use JExtstore\Component\Gdpr\Administrator\Framework\Controller as GdprController;

/**
 * Controller for links entity tasks
 *
 * @package GDPR::USER::components::com_gdpr
 * @subpackage controllers
 *             * @since 1.0
 */
class UserController extends GdprController {
	/**
	 *  Retrieve the user profile form URL
	 *
	 * @param lang
	 * @param Itemid
	 * @param splitted
	 */
	private function getUserFormUrl($lang, $Itemid, $original_option, $original_view, $original_task, $original_layout) {
		// Format redirect URI to the com_users if some error occurs
		$url = array (
				'option' => $original_option,
				'view' => $original_view
		);
		if ($lang) {
			if (strlen ( $lang ) > 2) {
				$splitted = explode ( '-', $lang );
				$lang = $splitted [0];
			}
			$url ['lang'] = $lang;
		}
		if($original_task) {
			$url ['task'] = $original_task;
		}
		if($original_layout) {
			$url ['layout'] = $original_layout;
		}
		if ($Itemid) {
			$url ['Itemid'] = $Itemid;
		}
		$redirectUrl = http_build_query ( $url );
	
		return $redirectUrl;
	}
	
	/**
	 * Check a specific feature exclusion by group
	 *
	 * @param string $feature
	 * @param Object $cParams
	 * @access private
	 * @return bool
	 */
	private function checkExclusionPermissions($feature, $cParams) {
		static $userGroups;
	
		$isExcluded = false;
	
		if(!$userGroups) {
			$userGroups = $this->user->getAuthorisedGroups();
		}
	
		$featureExcludedGroups = $cParams->get($feature, array(0));
	
		if(is_array($featureExcludedGroups) && !in_array(0, $featureExcludedGroups, false)) {
			$intersectResult = array_intersect($userGroups, $featureExcludedGroups);
			$isExcluded = (int)(count($intersectResult));
		}
	
		return $isExcluded;
	}
	
	/**
	 * Send an email notification and return message no model
	 *
	 * @param $type
	 * @access private
	 * return bool
	 */
	private function sendEmailNotification($type, $model, $userId) {
		// Joomla global configuration
		$jConfig = $this->app->getConfig();
		$cParams = $model->getComponentParams();
		$user = Factory::getContainer()->get(\Joomla\CMS\User\UserFactoryInterface::class)->loadUserById($userId);

		// Integration with Joomla Privacy tool suite, add a record to the com_privacy requests manager
		if($cParams->get('integrate_comprivacy', 1)) {
			// Search for an open information request matching the email and type
			$db = $model->getDbo();
			$fullUser = $this->app->getIdentity ();
			$requestType = $type == 'delete' ? 'remove' : 'export';
			
			$query = method_exists ( $db, 'createQuery' ) ? $db->createQuery () : $db->getQuery ( true );
			$query = $query
					 ->select('COUNT(id)')
					 ->from('#__privacy_requests')
					 ->where('email = ' . $db->quote($fullUser->email))
					 ->where('request_type = ' . $db->quote($requestType))
					 ->where('status IN (0, 1)');
			try {
				$result = (int) $db->setQuery($query)->loadResult();
			}
			catch (\Exception $e) {
				// No error handling for the user
				$result = false;
			}
			
			if (!$result) {
				// Everything is good to go, create the request
				$token = ApplicationHelper::getHash(UserHelper::genRandomPassword());
				$hashedToken = UserHelper::hashPassword($token);
				$userRequest = (object) array(
						'email' => $fullUser->email,
						'requested_at' => Date::getInstance()->toSql(),
						'status' => 1,
						'request_type' => $requestType,
						'confirm_token' => $hashedToken,
						'confirm_token_created_at' => Date::getInstance()->toSql()
				);
				
				try {
					$db->insertObject('#__privacy_requests', $userRequest);
				} catch(\Exception $e) {
					// No errors during the create request record phase
				}
			}
		}
		
		// Check for notify email addresses
		$validEmailAddresses = array();
		$emailAddresses = $cParams->get('logs_emails', '');
		$emailAddresses = explode(',', $emailAddresses);
		if(!empty($emailAddresses)) {
			foreach ($emailAddresses as $validEmail) {
				if(filter_var(trim($validEmail), FILTER_VALIDATE_EMAIL)) {
					$validEmailAddresses[] = trim($validEmail);
				}
			}
		}
	
		if(!empty($validEmailAddresses)) {
			// Build the email subject and message
			$sitename = $jConfig->get('sitename');
			$subject  = Text::sprintf('COM_GDPR_USER_REQUESTED_' . strtoupper($type) . '_OWN_PROFILE_SUBJECT', $sitename);
			$dateTimeRequest = HTMLHelper::_('date', 'now', Text::_('COM_GDPR_DATE_FORMAT_FILTER_DATETIME'));
			if($type == 'export') {
				$reportFormat = Text::_('COM_GDPR_' . strtoupper($this->app->getInput()->getString('reportformat')));
				$msg = Text::sprintf('COM_GDPR_USER_REQUESTED_' . strtoupper($type) . '_OWN_PROFILE_MSG', $user->name, $reportFormat, $sitename, $user->name, $user->username, $user->email, $dateTimeRequest);
			} else {
				$msg = Text::sprintf('COM_GDPR_USER_REQUESTED_' . strtoupper($type) . '_OWN_PROFILE_MSG', $user->name, $sitename, $user->name, $user->username, $user->email, $dateTimeRequest);
			}
			
			// Send the email
			if(Factory::getContainer()->has(\Joomla\CMS\Mail\MailerFactoryInterface::class)) {
				$mailer = Factory::getContainer()->get(\Joomla\CMS\Mail\MailerFactoryInterface::class)->createMailer($this->app->getConfig());
			} else {
				$mailer = Factory::getMailer();
			}
			$mailer->isHtml(true);
			$mailer->addReplyTo($user->email, $user->name);
	
			$mailer->setSender(array($cParams->get('logs_mailfrom', $jConfig->get('mailfrom')),
									 $cParams->get('logs_fromname', $jConfig->get('fromname'))));
	
			$mailer->addRecipient($validEmailAddresses);
	
			$mailer->setSubject($subject);
			$mailer->setBody($msg);
	
			// The Send method will raise an error via JError on a failure, we do not need to check it ourselves here
			try {
				$mailer->Send();
				return true;
			} catch (\Exception $e) {
				return false;
			}
		}
	}

	/**
	 * Send an email confirmation after that a user self-deleted his own profile
	 *
	 * @param string $deletedUserEmail
	 * @param Object $model
	 * @access private
	 * return bool
	 */
	private function sendEmailConfirmation($deletedUserEmail, $model) {
		// Joomla global configuration
		$jConfig = $this->app->getConfig();
		$cParams = $model->getComponentParams();
	
		// Check for notify email addresses
		$validEmailAddresses = array();
		if(filter_var(trim($deletedUserEmail), FILTER_VALIDATE_EMAIL)) {
			$validEmailAddresses[] = trim($deletedUserEmail);
		}

		if(!empty($validEmailAddresses)) {
			// Build the email subject and message
			$sitename = $jConfig->get('sitename');
			$subject  = Text::sprintf('COM_GDPR_USER_SUCCESS_DELETED_OWN_PROFILE_SUBJECT', $sitename);
			$msg      = Text::sprintf('COM_GDPR_USER_SUCCESS_DELETED_OWN_PROFILE_MSG', $sitename, HTMLHelper::_('date', 'now', Text::_('COM_GDPR_DATE_FORMAT_FILTER_DATETIME')));
	
			// Send the email
			if(Factory::getContainer()->has(\Joomla\CMS\Mail\MailerFactoryInterface::class)) {
				$mailer = Factory::getContainer()->get(\Joomla\CMS\Mail\MailerFactoryInterface::class)->createMailer($this->app->getConfig());
			} else {
				$mailer = Factory::getMailer();
			}
			$mailer->isHtml(true);
	
			$mailer->setSender(array($cParams->get('logs_mailfrom', $jConfig->get('mailfrom')),
									 $cParams->get('logs_fromname', $jConfig->get('fromname'))));
	
			$mailer->addRecipient($validEmailAddresses);
	
			$mailer->setSubject($subject);
			$mailer->setBody($msg);
	
			// The Send method will raise an error via JError on a failure, we do not need to check it ourselves here
			try {
				$mailer->Send();
				return true;
			} catch (\Exception $e) {
				return false;
			}
		}
	}

	/**
	 * Send an email confirmation after that a user revoked a generic or dynamic checkbox consent
	 *
	 * @param string $deletedUserEmail
	 * @param Object $model
	 * @access private
	 * return bool
	 */
	private function sendEmailConsentsRevoked($data, $model) {
		// Retrieve the current stored data 
		$dataConsentObjectToFormat = $model->loadConsentEntityData($data);
		
		// Joomla global configuration
		$jConfig = $this->app->getConfig();
		$cParams = $model->getComponentParams();
	
		// Check for notify email addresses
		$validEmailAddresses = array();
		$emailAddresses = $cParams->get('logs_emails', '');
		$emailAddresses = explode(',', $emailAddresses);
		if(!empty($emailAddresses)) {
			foreach ($emailAddresses as $validEmail) {
				if(filter_var(trim($validEmail), FILTER_VALIDATE_EMAIL)) {
					$validEmailAddresses[] = trim($validEmail);
				}
			}
		}
		
		if(!empty($validEmailAddresses)) {
			// Build the email subject and message
			$sitename = $jConfig->get('sitename');
			$subject  = Text::sprintf('COM_GDPR_USER_REVOKED_CONSENT_SUBJECT', $sitename);
			
			$url = $dataConsentObjectToFormat->url != '*' ? $dataConsentObjectToFormat->url : Text::_('COM_GDPR_CONSENTS_REGISTRY_URL_ALL_PAGES');
			$formIdentifier = isset($data['formid']) ? $data['formid'] : (isset($data['formname']) ? $data['formname'] : '-');
			
			// Setup user informations if he's a registered one
			$userInfo = Text::_('COM_GDPR_LOGS_NA');
			if($dataConsentObjectToFormat->user_id) {
				$user = Factory::getContainer()->get(\Joomla\CMS\User\UserFactoryInterface::class)->loadUserById($dataConsentObjectToFormat->user_id);
				$userInfo = '(ID: ' . $user->id . ') (' . 
							Text::_('COM_GDPR_LOGS_NAME') . ': ' . $user->name . ') (' . 
							Text::_('COM_GDPR_LOGS_USERNAME') . ': ' . $user->username . ') (' .
							Text::_('COM_GDPR_LOGS_EMAIL') . ': ' . $user->email . ')';
			}
			
			$formFields = null;
			$formFieldsFormatted = null;
			if($dataConsentObjectToFormat->formfields) {
				try {
					$formFields = json_decode($dataConsentObjectToFormat->formfields, true);
				} catch(\Exception $e) {
					// Don't stop operation, go on anyway
				}
			}
			if(is_array($formFields) && count($formFields)) {
				foreach ($formFields as $formFieldName=>$formFieldValue) {
					$formFieldsFormatted .= ' ( ' . ucfirst($formFieldName) . ': ';
						$cellValue = null;
						switch($formFieldValue){
							case null:
							case '0':
								$cellValue = Text::_('COM_GDPR_LOGS_NA');
								break;
								
							default:
								$cellValue = $formFieldValue;
						}
					$formFieldsFormatted .= $cellValue . ' ) ';
				}
			} else {
				$formFieldsFormatted = Text::_('COM_GDPR_LOGS_NA');
			}
			
			$msg = Text::sprintf('COM_GDPR_USER_REVOKED_CONSENT_MSG', $url, $formIdentifier, $userInfo, HTMLHelper::_('date', $dataConsentObjectToFormat->consent_date, Text::_('COM_GDPR_DATE_FORMAT_FILTER_DATETIME')), HTMLHelper::_('date', 'now', Text::_('COM_GDPR_DATE_FORMAT_FILTER_DATETIME')), $formFieldsFormatted);
	
			// Send the email
			if(Factory::getContainer()->has(\Joomla\CMS\Mail\MailerFactoryInterface::class)) {
				$mailer = Factory::getContainer()->get(\Joomla\CMS\Mail\MailerFactoryInterface::class)->createMailer($this->app->getConfig());
			} else {
				$mailer = Factory::getMailer();
			}
			$mailer->isHtml(true);
	
			$mailer->setSender(array($cParams->get('logs_mailfrom', $jConfig->get('mailfrom')),
									 $cParams->get('logs_fromname', $jConfig->get('fromname'))));
	
			$mailer->addRecipient($validEmailAddresses);
	
			$mailer->setSubject($subject);
			$mailer->setBody($msg);
	
			// The Send method will raise an error via JError on a failure, we do not need to check it ourselves here
			try {
				$mailer->Send();
				return true;
			} catch (\Exception $e) {
				return false;
			}
		}
	}
	
	/**
	 * Instantiate an event object based on native or generic base class
	 *
	 * @access private
	 * @param dummyParams
	 * @param elm
	 * @return object
	 */
	private function createEventObject($dummyParams, $elm) {
		// Evaluate if native ContentPrepare Event exists, Joomla >= 5
		if(class_exists('\\Joomla\\CMS\\Event\\Content\\ContentPrepareEvent')) {
			$eventObject = new \Joomla\CMS\Event\Content\ContentPrepareEvent ( 'onContentPrepare', [
					'context' => 'com_content.article',
					'subject' => &$elm,
					'params'  => &$dummyParams,
					'page'    => 0
			]);
		} else {
			// Generic Joomla\Event\Event, Joomla 4
			$eventObject = new Event ( 'onContentPrepare', [
					'com_content.article',
					&$elm,
					&$dummyParams,
					0
			]);
		}
		return $eventObject;
	}

	/**
	 * Manage rendering of offline cache manifest generating on the fly for the current page resources
	 *
	 * @access public
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		$cParams = $this->getModel ()->getComponentParams();
		
		if($cParams->get('cookie_category1_enable', 0)) {
			$this->getCookieCategoryDescription(1);
		}
		if($cParams->get('cookie_category2_enable', 0)) {
			$this->getCookieCategoryDescription(2);
		}
		if($cParams->get('cookie_category3_enable', 0)) {
			$this->getCookieCategoryDescription(3);
		}
		if($cParams->get('cookie_category4_enable', 0)) {
			$this->getCookieCategoryDescription(4);
		}
		
		// Parent construction and view display
		parent::display($cachable, $urlparams);
	}
	
	/**
	 * Delete a db table entity
	 *
	 * @access public
	 * @return bool
	 */
	public function deleteEntity(): bool {
		// Check for request forgeries.
		$this->checkToken ();
		
		$original_option = $this->app->getInput()->getCmd ( 'original_option', null );
		$original_view = $this->app->getInput()->getCmd ( 'original_view', null );
		$original_task = $this->app->getInput()->getCmd ( 'original_task', null );
		$original_layout = $this->app->getInput()->getCmd ( 'original_layout', null );
		$lang = $this->app->getInput()->get ( 'lang', null );
		$Itemid = $this->app->getInput()->getInt ( 'Itemid', null );
		
		// Find the user id in the jform posted array if not present in the root post
		$userId = 0;
		$jFormArray = $this->app->getInput()->get ( 'jform', array (), 'array' );
		if(isset($jFormArray['id']) && $original_option == 'com_users') {
			$userId = ( int ) $jFormArray ['id'];
		}
		
		if (! $userId) {
			$userId = $this->app->getInput()->getInt ( 'original_userid', null);
		}
		
		$redirectUrl = $this->getUserFormUrl ( $lang, $Itemid, $original_option, $original_view, $original_task, $original_layout );
		
		// Get current user id
		$currentUser = $this->app->getIdentity ();
		if ($currentUser->id != $userId) {
			$this->setRedirect ( Route::_ ( "index.php?" . $redirectUrl, false ), Text::_ ( 'COM_GDPR_CANT_DELETE_OTHER_USERS' ) );
			return false;
		}
		
		// Load della model e checkin before exit
		$model = $this->getModel ();
		
		// Check permissions exclusions
		if($this->checkExclusionPermissions('disallow_deleteprofile', $model->getComponentParams())) {
			$this->setRedirect ( Route::_ ( "index.php?" . $redirectUrl, false ), Text::_ ( 'COM_GDPR_NOT_ALLOWED' ) );
			return false;
		}
		
		// If a delete notification only is requested, send email and redirect here
		if($model->getComponentParams()->get('userprofile_buttons_workingmode', 0)) {
			$resultNotification = $this->sendEmailNotification('delete', $model, $userId);
			$userMessage = $resultNotification ? Text::_ ( 'COM_GDPR_REQUEST_SUCCESS' ) : Text::_ ( 'COM_GDPR_REQUEST_ERROR' );
			$this->setRedirect ( Route::_ ( "index.php?" . $redirectUrl, false ), $userMessage );
			return true;
		}
		
		$result = $model->deleteEntities ( $userId );
		if (! $result) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getExceptionLevel () );
			$this->setRedirect ( Route::_ ( "index.php?" . $redirectUrl, false ), Text::_ ( 'COM_GDPR_ERROR_DELETE' ) );
			return false;
		} else {
			// The user has been deleted correctly, check if an email confirmation to the user must be sent
			if($model->getComponentParams()->get('userprofile_self_delete_confirmation', 0)) {
				$this->sendEmailConfirmation($currentUser->email, $model);
			}
		}
		
		// Perform the user logout and the final redirect to the home page after a delete and a logout, success message is shown
		try {
			$options = array (
					'clientid' => $this->app->get ( 'shared_session', '0' ) ? null : 0 
			);
			// Perform the log out.
			$this->app->logout ( null, $options );
		} catch ( \Exception $e ) {
			// No exceptions raising for users
		}
		
		$menuItemRedirect = $model->getComponentParams()->get('userprofile_delete_redirect', '');
		if(!$menuItemRedirect) {
			// Default redirect to the home page
			$deleteRedirect = 'index.php';
		} else {
			//Custom menu item redirect
			$deleteRedirect = 'index.php?Itemid=' . $menuItemRedirect;
		}
		
		$this->app->enqueueMessage( Text::_('COM_GDPR_USERDELETED_CORRECTLY') );
		$this->app->redirect ( Route::_($deleteRedirect) );
		
		return true;
	}
	
	/**
	 * Export user profile data
	 *
	 * @access public
	 * @return void
	 */
	public function exportEntity() {
		// Check for request forgeries.
		$this->checkToken ();
		
		$original_option = $this->app->getInput()->getCmd ( 'original_option', null );
		$original_view = $this->app->getInput()->getCmd ( 'original_view', null );
		$original_task = $this->app->getInput()->getCmd ( 'original_task', null );
		$original_layout = $this->app->getInput()->getCmd ( 'original_layout', null );
		$lang = $this->app->getInput()->get ( 'lang', null );
		$Itemid = $this->app->getInput()->getInt ( 'Itemid', null );
		$reportFormat = $this->app->getInput()->getCmd ( 'reportformat', null );
		
		// Find the user id in the jform posted array if not present in the root post
		$userId = 0;
		$jFormArray = $this->app->getInput()->get ( 'jform', array (), 'array' );
		if(isset($jFormArray['id']) && $original_option == 'com_users') {
			$userId = ( int ) $jFormArray ['id'];
		}
		
		if (! $userId) {
			$userId = $this->app->getInput()->getInt ( 'original_userid', null);
		}

		$redirectUrl = $this->getUserFormUrl ( $lang, $Itemid, $original_option, $original_view, $original_task, $original_layout );
		
		// Set file date
		$dataExport = HTMLHelper::_('date', time (), 'Y-m-d_H:i:s');
		$cParams = $this->getModel ()->getComponentParams();
		$revokablePrivacyPolicy = $cParams->get('revokable_privacypolicy', 0);
		
		// Get current user id
		$currentUser = $this->app->getIdentity ();
		if ($currentUser->id != $userId) {
			$this->setRedirect ( Route::_ ( "index.php?" . $redirectUrl, false ), Text::_ ( 'COM_GDPR_CANT_EXPORT_OTHER_USERS' ) );
			return false;
		}
		
		// Check permissions exclusions
		if($this->checkExclusionPermissions('disallow_exportprofile', $cParams)) {
			$this->setRedirect ( Route::_ ( "index.php?" . $redirectUrl, false ), Text::_ ( 'COM_GDPR_NOT_ALLOWED' ) );
			return false;
		}
		
		// Load della model e checkin before exit
		$model = $this->getModel ();
		
		// If a delete notification only is requested, send email and redirect here
		if($model->getComponentParams()->get('userprofile_buttons_workingmode', 0)) {
			$resultNotification = $this->sendEmailNotification('export', $model, $userId);
			$userMessage = $resultNotification ? Text::_ ( 'COM_GDPR_REQUEST_SUCCESS' ) : Text::_ ( 'COM_GDPR_REQUEST_ERROR' );
			$this->setRedirect ( Route::_ ( "index.php?" . $redirectUrl, false ), $userMessage );
			return true;
		}
		
		$headerFields = array(
				Text::_('COM_GDPR_LOGS_NAME'),
				Text::_('COM_GDPR_LOGS_USERNAME'),
				Text::_('COM_GDPR_LOGS_EMAIL'),
				Text::_('COM_GDPR_LOGS_REGISTERDATE'),
				Text::_('COM_GDPR_LOGS_LASTVISITDATE'),
				Text::_('COM_GDPR_LOGS_BLOCK'),
				Text::_('COM_GDPR_LOGS_SENDEMAIL'),
				Text::_('COM_GDPR_LOGS_LANGUAGE'),
				Text::_('COM_GDPR_LOGS_EDITOR'),
				Text::_('COM_GDPR_LOGS_TIMEZONE')
		);
		
		$hasAdminFields = false;
		$hasPrivacyFields = false;
		$hasProfileFields = false;
		$hasProfileCustomFields = false;
		$hasProfileRawFields = false;
		$fulldata = array();
		$nullDate = Factory::getContainer()->get('DatabaseDriver')->getNullDate();
		$fieldsToLoadArray = array('name', 'username', 'email', 'registerDate', 'lastvisitDate','block','sendEmail','params');
		foreach ($fieldsToLoadArray as $fieldToLoad) {
			if(stripos($fieldToLoad, 'date')) {
				if(!$currentUser->$fieldToLoad || $currentUser->$fieldToLoad == $nullDate || in_array($currentUser->$fieldToLoad, array('0000-00-00 00:00:00', '1000-01-01 00:00:00'))) {
					$fulldata[] = Text::_('COM_GDPR_NEVER');
				} else {
					$fulldata[] = HTMLHelper::_('date', $currentUser->$fieldToLoad, Text::_('COM_GDPR_DATE_FORMAT_FILTER_DATETIME'));
				}
			} elseif($fieldToLoad == 'params') {
				$decodedParams = json_decode($currentUser->$fieldToLoad, true);
				$fulldata[] = isset($decodedParams['language']) ? $decodedParams['language'] : Text::_('COM_GDPR_DEFAULT');
				$fulldata[] = isset($decodedParams['editor']) ? $decodedParams['editor'] : Text::_('COM_GDPR_DEFAULT');
				$fulldata[] = isset($decodedParams['timezone']) ? $decodedParams['timezone'] : Text::_('COM_GDPR_DEFAULT');
				// Detect Admin fields
				if(isset($decodedParams['admin_language'])) {
					$fulldata[] = isset($decodedParams['admin_style']) ? $decodedParams['admin_style'] : Text::_('COM_GDPR_DEFAULT');
					$fulldata[] = isset($decodedParams['admin_language']) ? $decodedParams['admin_language'] : Text::_('COM_GDPR_DEFAULT');
					$fulldata[] = isset($decodedParams['helpsite']) ? $decodedParams['helpsite'] : Text::_('COM_GDPR_DEFAULT');
					$headerFields[] = Text::_('COM_GDPR_LOGS_ADMIN_TEMPLATE');
					$headerFields[] = Text::_('COM_GDPR_LOGS_ADMIN_LANGUAGE');
					$headerFields[] = Text::_('COM_GDPR_LOGS_HELPSITE');
					$hasAdminFields = true;
				}
				// Integration with Joomla Privacy tool suite, if Joomla detect if an admin has user action log options to export as well
				if(isset($decodedParams['logs_notification_option'])) {
					$fulldata[] = $decodedParams['logs_notification_option'] == '1' ? Text::_('JYES') : Text::_('JNO');
					$fulldata[] = implode(', ', $decodedParams['logs_notification_extensions']);
					$headerFields[] = Text::_('COM_GDPR_LOGS_LOGS_NOTIFICATION_OPTION');
					$headerFields[] = Text::_('COM_GDPR_LOGS_LOGS_NOTIFICATION_EXTENSIONS');
					$hasPrivacyFields = true;
				}
			} else {
				if($currentUser->$fieldToLoad == '0') {
					$fulldata[] = Text::_('JYES');
				} elseif ($currentUser->$fieldToLoad == '1') {
					$fulldata[] = Text::_('JNO');
				} else {
					$fulldata[] = $currentUser->$fieldToLoad;
				}
			}
		}
		
		// Evaluate the addition of the privacy policy field and value
		if($revokablePrivacyPolicy) {
			$headerFields[] = Text::_('COM_GDPR_LOGS_PRIVACY_POLICY');
			$db = Factory::getContainer()->get('DatabaseDriver');
			$query = "SELECT " . $db->quoteName('profile_value') .
					 "\n FROM " . $db->quoteName('#__user_profiles') .
					 "\n WHERE " .  $db->quoteName('user_id') . " = " . (int) $currentUser->id .
					 "\n AND " .  $db->quoteName('profile_key') . " = " . $db->quote('gdpr_consent_status');
			$latestPrivacyPolicy = $db->setQuery($query)->loadResult();
			$fulldata[] = $latestPrivacyPolicy ? Text::_('JYES') : Text::_('JNO');
		}
		
		// Manage additional profile data field, generated by the user profile plugin if enabled
		if(isset($jFormArray['profile'])) {
			foreach ($jFormArray['profile'] as $profileField=>$profileValue) {
				$headerFields[] = Text::_('COM_GDPR_LOGS_' . strtoupper($profileField) . '_PROFILE');
				$fulldata[] = $profileValue;
			}
			$hasProfileFields = true;
		}
		
		// Manage additional custom fields
		if(isset($jFormArray['com_fields'])) {
			foreach ($jFormArray['com_fields'] as $profileCustomField=>$profileCustomValue) {
				$headerFields[] = $profileCustomField;
				$fulldata[] = $profileCustomValue;
			}
			$hasProfileCustomFields = true;
		}

		// Export all the raw form fields
		if ($this->getModel ()->getComponentParams ()->get ( 'include_raw_post_fields', 0 )) {
			$headerFields [] = Text::_ ( 'COM_GDPR_LOGS_RAW_FIELDS' );
			$dirtyArray = $this->app->getInput()->post->getArray ();
			$cleanArray = array_filter ( $dirtyArray, function ($arrayKey) {
				if (stripos ( $arrayKey, 'original_' ) !== false) {
					return false;
				}
				if (stripos ( $arrayKey, 'gdpr_' ) !== false) {
					return false;
				}
				if (in_array ( $arrayKey, array (
						'option',
						'task',
						'view',
						'controller',
						'reportformat'
				) )) {
					return false;
				}
				
				return true;
			}, ARRAY_FILTER_USE_KEY );
			$fulldata [] = json_encode ( $cleanArray, JSON_UNESCAPED_UNICODE );
			$hasProfileRawFields = true;
		}
		
		if($reportFormat == 'exportcsv_btn') {
			$componentConfig = $this->getModel()->getComponentParams();
			$delimiter = $componentConfig->get('csv_delimiter', ';');
			$enclosure = $componentConfig->get('csv_enclosure', '"');
			
			// Clean dirty buffer
			ob_end_clean();
			// Open buffer
			ob_start();
			// Open out stream
			$outstream = fopen("php://output", "w");
			// Funzione di scrittura nell'output stream
			function __outputCSV(&$vals, $key, $userData) {
				fputcsv($userData[0], $vals, $userData[1], $userData[2]); // add parameters if you want
			}
			__outputCSV($headerFields, null, array($outstream, $delimiter, $enclosure));
			__outputCSV($fulldata, null, array($outstream, $delimiter, $enclosure));
			fclose($outstream);
			
			// Recupero output buffer content
			$contents = ob_get_clean();
			$exportFileExtension = '.csv';
			$contentType = 'text/plain';
		} elseif ($reportFormat == 'exportxls_btn') {
			if($cParams->get('xls_format', 1)) {
				$exportFileExtension = '.xls';
				$contentType = 'application/vnd.ms-excel';
			} else {
				$exportFileExtension = '.html';
				$contentType = 'text/html';
			}
			$indexIncrement = 0;
			$reportTitle = Text::sprintf('COM_GDPR_LOGS_REPORT_XLS_TITLE', str_replace('_', ' ', $dataExport));

			// Additional admin fields
			if($hasAdminFields) {
				$adminFieldsHeader = "<td><font color='#FFFFFF'>{$headerFields[10]}</font></td>" .
									 "<td><font color='#FFFFFF'>{$headerFields[11]}</font></td>" .
									 "<td><font color='#FFFFFF'>{$headerFields[12]}</font></td>";
				$adminFieldsRow = "<td>{$fulldata[10]}</td>" .
								  "<td>{$fulldata[11]}</td>" .
								  "<td>{$fulldata[12]}</td>";
				$indexIncrement += 3;
			} else {
				$adminFieldsHeader = '';
				$adminFieldsRow = '';
			}

			// Additional Joomla privacy tool suite admin fields
			if($hasPrivacyFields) {
				$privacyFieldsHeader = "<td><font color='#FFFFFF'>{$headerFields[10 + $indexIncrement]}</font></td>" .
									   "<td><font color='#FFFFFF'>{$headerFields[11 + $indexIncrement]}</font></td>";
				$privacyFieldsRow = "<td>{$fulldata[10 + $indexIncrement]}</td>" .
								    "<td>{$fulldata[11 + $indexIncrement]}</td>";
				$indexIncrement += 2;
			} else {
				$privacyFieldsHeader = '';
				$privacyFieldsRow = '';
			}

			// Privacy policy field
			if($revokablePrivacyPolicy) {
				$privacyPolicyHeader = "<td><font color='#FFFFFF'>{$headerFields[10 + $indexIncrement]}</font></td>";
				$privacyPolicyRow = "<td>{$fulldata[10 + $indexIncrement]}</td>";
				$indexIncrement += 1;
			} else {
				$privacyPolicyHeader = '';
				$privacyPolicyRow = '';
			}

			$profileFields = '';
			$profileValues = '';
			if($hasProfileFields) {
				for($i=10+$indexIncrement;$i<count($headerFields);$i++) {
					$profileFields .= "<td><font color='#FFFFFF'>{$headerFields[$i]}</font></td>";
					$profileValues .= "<td>{$fulldata[$i]}</td>";
					$indexIncrement++;
				}
			}
			
			// Add support for custom fields
			$profileCustomFields = '';
			$profileCustomValues = '';
			if($hasProfileCustomFields) {
				for($i=10+$indexIncrement;$i<count($headerFields);$i++) {
					$profileCustomFields .= "<td><font color='#FFFFFF'>{$headerFields[$i]}</font></td>";
					$profileCustomValues .= "<td>{$fulldata[$i]}</td>";
					$indexIncrement++;
				}
			}
			
			// Add final raw fields
			$rawFieldsHeader = '';
			$rawFieldsRow = '';
			if($hasProfileRawFields && isset($headerFields[10 + $indexIncrement])) {
				$rawFieldsHeader = "<td><font color='#FFFFFF'>{$headerFields[10 + $indexIncrement]}</font></td>";
				$rawFieldsRow = "<td>{$fulldata[10 + $indexIncrement]}</td>";
				$indexIncrement += 1;
			}
			
			$contents = <<<EOT
						<html>
						<head>
						<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
						</head>
						<body>
						<table>
							<tr><td><font size="4" color="#CE1300">$reportTitle</font></td></tr>
							<tr><td></td></tr>
							<tr bgcolor="#0066ff">
								<td><font color="#FFFFFF">{$headerFields[0]}</font></td>
								<td><font color="#FFFFFF">{$headerFields[1]}</font></td>
								<td><font color="#FFFFFF">{$headerFields[2]}</font></td>
								<td><font color="#FFFFFF">{$headerFields[3]}</font></td>
								<td><font color="#FFFFFF">{$headerFields[4]}</font></td>
								<td><font color="#FFFFFF">{$headerFields[5]}</font></td>
								<td><font color="#FFFFFF">{$headerFields[6]}</font></td>
								<td><font color="#FFFFFF">{$headerFields[7]}</font></td>
								<td><font color="#FFFFFF">{$headerFields[8]}</font></td>
								<td><font color="#FFFFFF">{$headerFields[9]}</font></td>
								$adminFieldsHeader
								$privacyFieldsHeader
								$privacyPolicyHeader
								$profileFields
								$profileCustomFields
								$rawFieldsHeader
							</tr>
								
							<tr>
								<td>{$fulldata[0]}</td>
								<td>{$fulldata[1]}</td>
								<td>{$fulldata[2]}</td>
								<td>{$fulldata[3]}</td>
								<td>{$fulldata[4]}</td>
								<td>{$fulldata[5]}</td>
								<td>{$fulldata[6]}</td>
								<td>{$fulldata[7]}</td>
								<td>{$fulldata[8]}</td>
								<td>{$fulldata[9]}</td>
								$adminFieldsRow
								$privacyFieldsRow
								$privacyPolicyRow
								$profileValues
								$profileCustomValues
								$rawFieldsRow
							</tr>
						</table>
						</body>	
						</html>
			EOT;
		}
	
		// Recupero output buffer content
		$exportedFileName = 'profile_data_' . $dataExport . $exportFileExtension;
		
		header ( 'Pragma: public' );
		header ( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header ( 'Expires: ' . gmdate ( 'D, d M Y H:i:s' ) . ' GMT' );
		header ( 'Content-Disposition: attachment; filename="' . $exportedFileName . '"' );
		header ( 'Content-Type: ' . $contentType );
		echo $contents;
			
		exit ();
	}
	
	/**
	 * Returns the contents of the cookie policy to an ajax request
	 *
	 * @access public
	 * @return void
	 */
	public function getCookiePolicy() {
		$cookiePolicyText = $this->getModel ()->getComponentParams()->get('cookie_policy_contents', null);
		
		PluginHelper::importPlugin('content', null, true, $this->app->getDispatcher());
		$dummyParams = new Registry();
		$elm = new \stdClass();
		$elm->id = $elm->catid = $elm->language = $elm->title = null;
		$elm->text = $cookiePolicyText;
		if($this->getModel ()->getComponentParams()->get('popup_prepare_contents', 0)) {
			$eventObject = $this->createEventObject ( $dummyParams, $elm );
			
			$this->app->getDispatcher ()->dispatch ( 'onContentPrepare', $eventObject );
		}
		
		echo '<div>' . Text::_($elm->text) . '</div>';
	}
	
	/**
	 * Returns the contents of the privacy policy to an ajax request
	 *
	 * @access public
	 * @return void
	 */
	public function getPrivacyPolicy() {
		$privacyPolicyText = $this->getModel ()->getComponentParams()->get('privacy_policy_contents', null);
		
		PluginHelper::importPlugin('content', null, true, $this->app->getDispatcher());
		$dummyParams = new Registry();
		$elm = new \stdClass();
		$elm->id = $elm->catid = $elm->language = $elm->title = null;
		$elm->text = $privacyPolicyText;
		if($this->getModel ()->getComponentParams()->get('popup_prepare_contents', 0)) {
			$eventObject = $this->createEventObject ( $dummyParams, $elm );
			
			$this->app->getDispatcher ()->dispatch ( 'onContentPrepare', $eventObject );
		}
		
		echo '<div>' . Text::_($elm->text) . '</div>';
	}
	
	/**
	 * Returns the contents of the checkbox privacy policy to an ajax request
	 *
	 * @access public
	 * @return void
	 */
	public function getCheckboxPolicy() {
		$checkboxPolicyText = $this->getModel ()->getComponentParams()->get('checkbox_contents', null);
	
		PluginHelper::importPlugin('content', null, true, $this->app->getDispatcher());
		$dummyParams = new Registry();
		$elm = new \stdClass();
		$elm->id = $elm->catid = $elm->language = $elm->title = null;
		$elm->text = $checkboxPolicyText;
		if($this->getModel ()->getComponentParams()->get('popup_prepare_contents', 0)) {
			$eventObject = $this->createEventObject ( $dummyParams, $elm );
			
			$this->app->getDispatcher ()->dispatch ( 'onContentPrepare', $eventObject );
		}
	
		echo '<div>' . Text::_($elm->text) . '</div>';
	}
	
	/**
	 * Store the consent for a given form checkbox of the privacy policy
	 *
	 * @access public
	 * @return void
	 */
	public function getConsent() {
		$model = $this->getModel();
	
		// Retrieve, sanitize and build posted data
		$data = array();
		$data['url'] = urldecode($this->app->getInput()->post->getString ('url', null));
		if($formId = $this->app->getInput()->post->getString('formid', null)) {
			$data['formid'] = $formId;
		}
		if($formName = $this->app->getInput()->post->get('formname', null)) {
			$data['formname'] = $formName;
		}
	
		try {
			$consented = $model->loadConsentEntity($data);
		} catch(\Exception $e) {
			// No exception thrown
		}
	
		header('Content-Type: application/json');
		echo json_encode($consented);
		jexit();
	}
	
	/**
	 * Store the consent for a given form checkbox of the privacy policy
	 *
	 * @access public
	 * @return void
	 */
	public function storeConsent() {
		$model = $this->getModel();
		$lastId = 0;
		
		// Retrieve, sanitize and build posted data
		$data = array();
		$data['url'] = urldecode($this->app->getInput()->post->getString ('url', null));
		if($formId = $this->app->getInput()->post->getString('formid', null)) {
			$data['formid'] = $formId; 
		}
		if($formName = $this->app->getInput()->post->get('formname', null)) {
			$data['formname'] = $formName;
		}
		if($formFields = $this->app->getInput()->post->get('formfields', array (), 'array' )) {
			$data['formfields'] = json_encode($formFields);
		}
		
		try {
			$lastId = $model->storeConsentEntity($data);
		} catch(\Exception $e) {
			// No exception thrown
		}
		
		echo $lastId;
		jexit();
	}
	
	/**
	 * Store the consent for a given form checkbox of the privacy policy
	 *
	 * @access public
	 * @return void
	 */
	public function storeCookiesChoices() {
		$session = $this->app->getSession();
		
		// Retrieve, sanitize and build posted data
		$cookiesChoicesString = $this->app->getInput()->post->getString ('cookieschoices', null);
		
		$session->set('cookieschoices', $cookiesChoicesString);
		
		// Check if the accepting date must be posted back
		$model = $this->getModel();
		$cParams = $model->getComponentParams();
		if($cParams->get('track_consent_date', 0) && $cookiesChoicesString) {
			header('Content-Type: application/json');
			$cookieAcceptDate = array('acceptdate' => Text::sprintf('COM_GDPR_CONSENT_ACCEPTED_DATE', HTMLHelper::_('date', 'now', Text::_('DATE_FORMAT_LC2'))));
			echo json_encode($cookieAcceptDate);
		}
		
		jexit();
	}
	
	/**
	 * Delete the consent for a given form checkbox of the privacy policy
	 *
	 * @access public
	 * @return void
	 */
	public function deleteConsent() {
		$model = $this->getModel();
		
		// Retrieve, sanitize and build posted data
		$data = array();
		$data['url'] = urldecode($this->app->getInput()->post->getString ('url', null));
		if($formId = $this->app->getInput()->post->getString('formid', null)) {
			$data['formid'] = $formId;
		}
		if($formName = $this->app->getInput()->post->get('formname', null)) {
			$data['formname'] = $formName;
		}
		
		try {
			// Notify admins that someone revoked a consent
			if($model->getComponentParams()->get('notify_revoked_consents', 0)) {
				$this->sendEmailConsentsRevoked($data, $model);
			}
			
			$model->deleteConsentEntity($data);
		} catch(\Exception $e) {
			// No exception thrown
		}
		
		jexit();
	}
	
	/**
	 * Returns the contents of the cookie policy to an ajax request
	 *
	 * @param int $explicitCategory
	 * @access public
	 * @return void
	 */
	public function getCookieCategoryDescription($explicitCategory = null) {
		$cParams = $this->getModel ()->getComponentParams();
		$category = $this->app->getInput()->getInt('gdpr_cookie_category');
		$containerClass = '';
		$sessionCookieDesired = $cParams->get('include_joomla_session_cookie', 0) && $category == 1;
		
		// Override view frontend programmatically
		if($explicitCategory) {
			$category = $explicitCategory;
			$containerClass = ' class="gdpr-component-view"';
		}
		
		$cookieCategoryDescription = $cParams->get('cookie_category' . $category . '_description', null);
	
		PluginHelper::importPlugin('content', null, true, $this->app->getDispatcher());
		$dummyParams = new Registry();
		$elm = new \stdClass();
		$elm->id = $elm->catid = $elm->language = $elm->title = null;
		$elm->text = $cookieCategoryDescription;
		if($cParams->get('popup_prepare_contents', 0)) {
			$eventObject = $this->createEventObject ( $dummyParams, $elm );
			
			$this->app->getDispatcher ()->dispatch ( 'onContentPrepare', $eventObject );
		}

		// Setup the template for the cookie switcher
		$switcherTemplate = '';
		$optOutIndividualResources = $cParams->get('optout_individual_resources', 0);
		if($optOutIndividualResources) {
			$isDisabledLockedCategory = $cParams->get('cookie_category' . $category . '_locked', 0) && $cParams->get('optout_individual_resources_exclude_locked_categories', 0);
			if(!$isDisabledLockedCategory) {
				$switcherTemplate = '<div class="gdpr_onoffswitchcookie gdpr_cookie_switcher" data-bind="{identifier}" data-type="{resource}">' .
										'<label class="gdpr_onoffswitch-label"><span class="gdpr_onoffswitch-inner"></span><span class="gdpr_onoffswitch-switch"></span></label>' .
									'</div>';
			}
		}

		echo '<div' . $containerClass . '>';
		echo '<div class="cc-cookie-category-title" data-categoryid="' . $category . '">' . Text::_($cParams->get('cookie_category' . $category . '_name')) . '</div>';
		echo '<div class="cc-cookie-category-description">' . Text::_(trim($elm->text)) . '</div>';
		
		// Output the cookies in this category
		if($cParams->get('cookie_category' . $category . '_include_list', 1)) {
			$cookiesStringInThisCategory = trim($cParams->get('cookie_category' . $category . '_list', ''));
			if($sessionCookieDesired) {
				$sessionCookieName = $this->app->getSession()->getName();
				$cookiesStringInThisCategory = $sessionCookieName . PHP_EOL . $cookiesStringInThisCategory;
			}
			if($cookiesStringInThisCategory) {
				$cookiesInThisCategory = explode(PHP_EOL, $cookiesStringInThisCategory);
				if(!empty($cookiesInThisCategory)) {
					// Retrieve cookie description for this category if any
					$cookieDescriptions = $this->getModel()->getCookieDescriptions($category);
					
					// Inject the session cookie description
					if($sessionCookieDesired) {
						$cookieDescriptions[$sessionCookieName] = [
								'cookie' => $sessionCookieName,
								'descriptionhtml' => Text::_('COM_GDPR_JOOMLA_SESSION_COOKIE_DESCRIPTION'),
								'expiration' => Text::_('COM_GDPR_JOOMLA_SESSION_COOKIE_EXPIRATION'),
								'alias' => ''
						];
					}
					
					echo '<fieldset class="cc-cookie-list-title"><legend>' . Text::_('COM_GDPR_COOKIE_LIST') . '</legend>';
					echo '<ul class="cc-cookie-category-list">';
					foreach ($cookiesInThisCategory as &$cookieInThisCategory) {
						$cookieInThisCategory = trim($cookieInThisCategory);
						if($cookieInThisCategory == '') {
							continue;
						}
						
						// Build the extra HTML code for cookie details description
						$alias = null;
						$detailsToggler = null;
						$detailsDescription = null;
						if(array_key_exists($cookieInThisCategory, $cookieDescriptions)) {
							$detailsToggler = '<span class="cc-cookie-descriptions-toggler">&#x25EE;</span>';
							$detailsExpiration = '';
							$detailsExpirationValue = '';
							if(StringHelper::strlen($cookieDescriptions[$cookieInThisCategory]['expiration'])) {
								$detailsExpirationValue = '<span>' . Text::sprintf('COM_GDPR_COOKIE_EXPIRATION', Text::_($cookieDescriptions[$cookieInThisCategory]['expiration'])) . '</span>';
							}
							$detailsExpiration = '<div class="cc-cookie-expiration">' . $detailsExpirationValue . '<span>' . Text::sprintf('COM_GDPR_COOKIE_DOMAIN_TYPE', Text::_('COM_GDPR_COOKIE_TYPE_VALUE')) . '</span></div>';
							
							if(StringHelper::strpos($cookieDescriptions[$cookieInThisCategory]['descriptionhtml'], 'COM_GDPR_') !== false) {
								$cookieDescriptions[$cookieInThisCategory]['descriptionhtml'] = strip_tags($cookieDescriptions[$cookieInThisCategory]['descriptionhtml']);
							}
							$detailsDescription = '<div class="cc-cookie-descriptions">' . Text::_($cookieDescriptions[$cookieInThisCategory]['descriptionhtml']) . $detailsExpiration . '</div>';
							$alias = Text::_($cookieDescriptions[$cookieInThisCategory]['alias']);
						}
						
						if($optOutIndividualResources) {
							$switcherCookieTemplateBinded = StringHelper::str_ireplace('{identifier}', htmlspecialchars($cookieInThisCategory, ENT_COMPAT, 'UTF-8', false), $switcherTemplate);
							$switcherCookieTemplateBinded = StringHelper::str_ireplace('{resource}', 'cookie',  $switcherCookieTemplateBinded);
							echo '<li>' . $detailsToggler . '<span class="cc-cookie-category-name">' . ($alias ? $alias : $cookieInThisCategory) . '</span>' . $switcherCookieTemplateBinded . $detailsDescription . '</li>';
						} else {
							echo '<li>' . $detailsToggler . '<span class="cc-cookie-category-name">' . ($alias ? $alias : $cookieInThisCategory) . '</span>' . $detailsDescription . '</li>';
						}
					}
					echo '</ul></fieldset>';
				}
			} else {
				echo '<fieldset class="cc-cookie-list-title"><legend>' . Text::_('COM_GDPR_NO_COOKIE_IN_THIS_CATEGORY') . '</legend></fieldset>';
			}
		}
		
		// Output the domains in this category
		if($cParams->get('domains_category' . $category . '_include_list', 1)) {
			$domainsStringInThisCategory = trim($cParams->get('domains_category' . $category . '_list', ''));
			if($domainsStringInThisCategory) {
				$domainsInThisCategory = explode(PHP_EOL, $domainsStringInThisCategory);
				if(!empty($domainsInThisCategory)) {
					// Retrieve cookie description for this category if any
					$domainDescriptions = $this->getModel()->getCookieDescriptions($category);
					
					echo '<fieldset class="cc-cookie-list-title"><legend>' . Text::_('COM_GDPR_DOMAINS_LIST') . '</legend>';
					echo '<ul class="cc-cookie-category-list">';
					foreach ($domainsInThisCategory as &$domainInThisCategory) {
						$domainInThisCategory = trim($domainInThisCategory);
						if($domainInThisCategory == '') {
							continue;
						}
						
						// Build the extra HTML code for cookie details description
						$alias = null;
						$detailsToggler = null;
						$detailsDescription = null;
						if(array_key_exists($domainInThisCategory, $domainDescriptions)) {
							$detailsToggler = '<span class="cc-cookie-descriptions-toggler">&#x25EE;</span>';
							$detailsExpiration = '';
							$detailsExpirationValue = '';
							if(StringHelper::strlen($domainDescriptions[$domainInThisCategory]['expiration'])) {
								$detailsExpirationValue = '<span>' . Text::sprintf('COM_GDPR_COOKIE_EXPIRATION', Text::_($domainDescriptions[$domainInThisCategory]['expiration'])) . '</span>';
							}
							$detailsExpiration = '<div class="cc-cookie-expiration">' . $detailsExpirationValue . '<span>' . Text::sprintf('COM_GDPR_COOKIE_DOMAIN_TYPE', Text::_('COM_GDPR_DOMAIN_TYPE_VALUE')) . '</span></div>';
							
							if(StringHelper::strpos($domainDescriptions[$domainInThisCategory]['descriptionhtml'], 'COM_GDPR_') !== false) {
								$domainDescriptions[$domainInThisCategory]['descriptionhtml'] = strip_tags($domainDescriptions[$domainInThisCategory]['descriptionhtml']);
							}
							$detailsDescription = '<div class="cc-cookie-descriptions">' . Text::_($domainDescriptions[$domainInThisCategory]['descriptionhtml']) . $detailsExpiration . '</div>';
							$alias = Text::_($domainDescriptions[$domainInThisCategory]['alias']);
						}
						
						if($optOutIndividualResources) {
							$switcherDomainTemplateBinded = StringHelper::str_ireplace('{identifier}', htmlspecialchars($domainInThisCategory, ENT_COMPAT, 'UTF-8', false), $switcherTemplate);
							$switcherDomainTemplateBinded = StringHelper::str_ireplace('{resource}', 'domain',  $switcherDomainTemplateBinded);
							echo '<li>' . $detailsToggler . '<span class="cc-cookie-category-name">' . ($alias ? $alias : $domainInThisCategory) . '</span>' . $switcherDomainTemplateBinded . $detailsDescription . '</li>';
						} else {
							echo '<li>' . $detailsToggler . '<span class="cc-cookie-category-name">' . ($alias ? $alias : $domainInThisCategory) . '</span>' . $detailsDescription . '</li>';
						}
					}
					echo '</ul></fieldset>';
				}
			} else {
				echo '<fieldset class="cc-cookie-list-title"><legend>' . Text::_('COM_GDPR_NO_DOMAINS_IN_THIS_CATEGORY') . '</legend></fieldset>';
			}
		}
		
		// Output the services in this category
		if($cParams->get('services_category' . $category . '_include_list', 0)) {
			$servicesArrayInThisCategory = $cParams->get('services_category' . $category . '_list', array());
			if(!empty($servicesArrayInThisCategory)) {
				// Retrieve cookie description for this category if any
				$cookieDomainDescriptions = $this->getModel()->getCookieDescriptions($category);
				
				echo '<fieldset class="cc-cookie-list-title"><legend>' . Text::_('COM_GDPR_SERVICES_LIST') . '</legend>';
				foreach ($servicesArrayInThisCategory as $serviceIndex=>$serviceArray) {
					// Skip empty services
					if(!trim($serviceArray->name)) {
						continue;
					}
					$cookiesInThisService = array();
					$domainsInThisService = array();
					if(trim($serviceArray->cookies)) {
						$cookiesInThisService = explode(PHP_EOL, trim($serviceArray->cookies));
					}
					if(trim($serviceArray->domains)) {
						$domainsInThisService = explode(PHP_EOL, trim($serviceArray->domains));
					}
					
					$switcherServiceTemplateBinded = null;
					if($optOutIndividualResources) {
						$switcherServiceTemplateBinded = StringHelper::str_ireplace('{identifier}', htmlspecialchars($serviceArray->name, ENT_COMPAT, 'UTF-8', false), $switcherTemplate);
						$switcherServiceTemplateBinded = StringHelper::str_ireplace('{resource}', 'service',  $switcherServiceTemplateBinded);
					}
					
					// Handle the services slide toggler
					$serviceFieldsetStateClass = '';
					$servicesToggler = '';
					if($cParams->get('toggle_services', 0)) {
						$hasCookiesDomains = ($cParams->get('cookie_category' . $category . '_include_list', 1) && !empty($cookiesInThisService)) || ($cParams->get('domains_category' . $category . '_include_list', 1) && !empty($domainsInThisService));
						if($hasCookiesDomains) {
							$serviceFieldsetStateClass = ' cc-service-collapsed';
							$servicesToggler = '<span class="cc-services-descriptions-toggler">&#x25EE;</span> ';
						}
					}
					
					// Check if a description is also assigned to a service itself, renders it in that case as well
					if(array_key_exists($serviceArray->name, $cookieDomainDescriptions)) {
						if(StringHelper::strpos($cookieDomainDescriptions[$serviceArray->name]['descriptionhtml'], 'COM_GDPR_') !== false) {
							$cookieDomainDescriptions[$serviceArray->name]['descriptionhtml'] = strip_tags($cookieDomainDescriptions[$serviceArray->name]['descriptionhtml']);
						}
						$serviceDetailsDescription = '<div class="cc-service-descriptions">' . Text::_($cookieDomainDescriptions[$serviceArray->name]['descriptionhtml']) . '</div>';
						
						echo '<fieldset class="cc-service-list-title' . $serviceFieldsetStateClass . '"><legend>' . $servicesToggler . Text::_($serviceArray->name) . $switcherServiceTemplateBinded . $serviceDetailsDescription . '</legend>';
					} else {
						echo '<fieldset class="cc-service-list-title' . $serviceFieldsetStateClass . '"><legend>' . $servicesToggler . Text::_($serviceArray->name) . $switcherServiceTemplateBinded . '</legend>';
					}

					if(!empty($cookiesInThisService)) {
						$includeCookieList = $cParams->get('cookie_category' . $category . '_include_list', 1) ? '' : ' cc-service-list-hidden';
						echo '<div class="cc-service-cookie-list-title' . $includeCookieList . '">' . Text::_('COM_GDPR_SERVICES_COOKIES_LIST') . '</div>';
						echo '<ul class="cc-cookie-category-list' . $includeCookieList . '">';
						foreach ($cookiesInThisService as $cookieInThisService) {
							// Remove any carriage return
							$cookieInThisService = StringHelper::str_ireplace(array("\r\n", "\n", "\r"), '', $cookieInThisService);
							
							// Build the extra HTML code for cookie/domain details description
							$alias = null;
							$cookieDetailsToggler = null;
							$cookieDetailsDescription = null;
							if(array_key_exists($cookieInThisService, $cookieDomainDescriptions)) {
								$cookieDetailsToggler = '<span class="cc-cookie-descriptions-toggler">&#x25EE;</span>';
								$detailsExpiration = '';
								$detailsExpirationValue = '';
								if(StringHelper::strlen($cookieDomainDescriptions[$cookieInThisService]['expiration'])) {
									$detailsExpirationValue = '<span>' . Text::sprintf('COM_GDPR_COOKIE_EXPIRATION', Text::_($cookieDomainDescriptions[$cookieInThisService]['expiration'])) . '</span>';
								}
								$detailsExpiration = '<div class="cc-cookie-expiration">' . $detailsExpirationValue . '<span>' . Text::sprintf('COM_GDPR_COOKIE_DOMAIN_TYPE', Text::_('COM_GDPR_COOKIE_TYPE_VALUE')) . '</span></div>';
								
								if(StringHelper::strpos($cookieDomainDescriptions[$cookieInThisService]['descriptionhtml'], 'COM_GDPR_') !== false) {
									$cookieDomainDescriptions[$cookieInThisService]['descriptionhtml'] = strip_tags($cookieDomainDescriptions[$cookieInThisService]['descriptionhtml']);
								}
								$cookieDetailsDescription = '<div class="cc-cookie-descriptions">' . Text::_($cookieDomainDescriptions[$cookieInThisService]['descriptionhtml']) . $detailsExpiration . '</div>';
								$alias = Text::_($cookieDomainDescriptions[$cookieInThisService]['alias']);
							}
							if($optOutIndividualResources) {
								$switcherCookieTemplateBinded = StringHelper::str_ireplace('{identifier}', htmlspecialchars($cookieInThisService, ENT_COMPAT, 'UTF-8', false), $switcherTemplate);
								$switcherCookieTemplateBinded = StringHelper::str_ireplace('{resource}', 'cookie',  $switcherCookieTemplateBinded);
								echo '<li>' . $cookieDetailsToggler . '<span class="cc-cookie-category-name">' . ($alias ? $alias : $cookieInThisService) . '</span>' . $switcherCookieTemplateBinded . $cookieDetailsDescription . '</li>';
							} else {
								echo '<li>' . $cookieDetailsToggler . '<span class="cc-cookie-category-name">' . ($alias ? $alias : $cookieInThisService) . '</span>' . $cookieDetailsDescription . '</li>';
							}
						}
						echo '</ul>';
					}

					if(!empty($domainsInThisService)) {
						$includeDomainList = $cParams->get('domains_category' . $category . '_include_list', 1) ? '' : ' cc-service-list-hidden';
						echo '<div class="cc-service-cookie-list-title' . $includeDomainList . '">' . Text::_('COM_GDPR_SERVICES_DOMAINS_LIST') . '</div>';
						echo '<ul class="cc-cookie-category-list' . $includeDomainList . '">';
						foreach ($domainsInThisService as $domainInThisService) {
							// Remove any carriage return
							$domainInThisService = StringHelper::str_ireplace(array("\r\n", "\n", "\r"), '', $domainInThisService);
							
							// Build the extra HTML code for cookie/domain details description
							$alias = null;
							$domainDetailsToggler = null;
							$domainDetailsDescription = null;
							if(array_key_exists($domainInThisService, $cookieDomainDescriptions)) {
								$domainDetailsToggler = '<span class="cc-cookie-descriptions-toggler">&#x25EE;</span>';
								$detailsExpiration = '';
								$detailsExpirationValue = '';
								if(StringHelper::strlen($cookieDomainDescriptions[$domainInThisService]['expiration'])) {
									$detailsExpirationValue = '<span>' . Text::sprintf('COM_GDPR_COOKIE_EXPIRATION', Text::_($cookieDomainDescriptions[$domainInThisService]['expiration'])) . '</span>';
								}
								$detailsExpiration = '<div class="cc-cookie-expiration">' . $detailsExpirationValue . '<span>' . Text::sprintf('COM_GDPR_COOKIE_DOMAIN_TYPE', Text::_('COM_GDPR_DOMAIN_TYPE_VALUE')) . '</span></div>';
								
								if(StringHelper::strpos($cookieDomainDescriptions[$domainInThisService]['descriptionhtml'], 'COM_GDPR_') !== false) {
									$cookieDomainDescriptions[$domainInThisService]['descriptionhtml'] = strip_tags($cookieDomainDescriptions[$domainInThisService]['descriptionhtml']);
								}
								$domainDetailsDescription = '<div class="cc-cookie-descriptions">' . Text::_($cookieDomainDescriptions[$domainInThisService]['descriptionhtml']) . $detailsExpiration . '</div>';
								$alias = Text::_($cookieDomainDescriptions[$domainInThisService]['alias']);
							}
							if($optOutIndividualResources) {
								$switcherDomainTemplateBinded = StringHelper::str_ireplace('{identifier}', htmlspecialchars($domainInThisService, ENT_COMPAT, 'UTF-8', false), $switcherTemplate);
								$switcherDomainTemplateBinded = StringHelper::str_ireplace('{resource}', 'domain',  $switcherDomainTemplateBinded);
								echo '<li>' . $domainDetailsToggler . '<span class="cc-cookie-category-name">' . ($alias ? $alias : $domainInThisService) . '</span>' . $switcherDomainTemplateBinded . $domainDetailsDescription . '</li>';
							} else {
								echo '<li>' . $domainDetailsToggler . '<span class="cc-cookie-category-name">' . ($alias ? $alias : $domainInThisService) . '</span>' . $domainDetailsDescription . '</li>';
							}
						}
						echo '</ul>';
					}
					echo '</fieldset>';
				}
				echo '</fieldset>';
			} else {
				echo '<fieldset class="cc-cookie-list-title"><legend>' . Text::_('COM_GDPR_NO_SERVICES_IN_THIS_CATEGORY') . '</legend></fieldset>';
			}
		}
		
		echo '</div>';
	}
	
	/**
	 * Process the cookie categories on an ajax request
	 *
	 * @access public
	 * @return void
	 */
	public function processCookieCategory() {
		$category = $this->app->getInput()->getInt('gdpr_cookie_category');
		$categoryState = $this->app->getInput()->getInt('gdpr_cookie_category_state');
		
		$session = $this->app->getSession();
		switch ($categoryState) {
			case 0:
				$session->set('gdpr_cookie_category_disabled_' . $category, -1);
				break;
				
			case 1:
				$session->set('gdpr_cookie_category_disabled_' . $category, 1);
				break;
		}
		
		// Cookie consent tracking
		$model = $this->getModel();
		$cParams = $model->getComponentParams();
		
		// Retrieve, sanitize and build posted data based on reverse logic
		if($cParams->get('enable_log_cookie_consent', 1)) {
			$cookieCategory = $this->app->getInput()->post->getInt ('gdpr_cookie_category', 0);
			$cookieCategoryState = !$this->app->getInput()->post->getInt ('gdpr_cookie_category_state', 0);
			try {
				$model->storeCookieConsentEntity($cookieCategory, $cookieCategoryState);
			} catch(\Exception $e) {
				// No exception thrown
			}
		}
		
		// Optional propagate recover of single cookies/domains
		// Retrieve, sanitize and build posted data
		$cookiesChoicesString = $this->app->getInput()->post->getString ('cookieschoices', null);
		if($cookiesChoicesString) {
			$session->set('cookieschoices', $cookiesChoicesString);
		}
		
		// Check if the accepting date must be posted back
		if($cParams->get('track_consent_date', 0) && $categoryState == 0) {
			header('Content-Type: application/json');
			$checkboxAcceptDate = array('acceptdate' => Text::sprintf('COM_GDPR_CONSENT_ACCEPTED_DATE', HTMLHelper::_('date', 'now', Text::_('DATE_FORMAT_LC2'))));
			echo json_encode($checkboxAcceptDate);
		} elseif(!$cParams->get('track_consent_date', 0) && $categoryState == 0) {
			echo 1;
		}
		
		jexit();
	}
	
	/**
	 * Process the cookie categories on an ajax request
	 *
	 * @access public
	 * @return void
	 */
	public function processGenericCookieCategories() {
		// Check if cookie consent tracking is enabled as well
		$model = $this->getModel();
			
		// Retrieve, sanitize and build posted data based on reverse logic
		$cookieGenericState = $this->app->getInput()->post->getInt ('gdpr_generic_cookie_consent', 0);
		try {
			$model->storeCookieGenericConsentEntity($cookieGenericState);
		} catch(\Exception $e) {
			// No exception thrown
		}
	
		jexit();
	}
	
	/**
	 * Retrieve data for a given dynamic checkbox starting from a unique identifier 'placeholder'
	 *
	 * @access public
	 * @return mixed
	 */
	public function getCheckbox() {
		$model = $this->getModel();
		
		// Retrieve, sanitize and build posted data
		$placeholder = $this->app->getInput()->post->getString ('checkbox_placeholder', null);
		$currentUrl = urldecode($this->app->getInput()->post->getString ('url', null));
		
		try {
			$checkboxData = $model->getCheckboxData($placeholder, $currentUrl);
		} catch(\Exception $e) {
			// No exception thrown
		}
		
		header('Content-Type: application/json');
		echo json_encode($checkboxData);
		jexit();
	}
	
	/**
	 * Retrieve description for a given dynamic checkbox starting from a unique identifier 'placeholder'
	 *
	 * @access public
	 * @return mixed
	 */
	public function getCheckboxDescription() {
		$model = $this->getModel();
	
		// Retrieve, sanitize and build posted data
		$placeholder = $this->app->getInput()->getString ('checkbox_placeholder', null);
	
		try {
			$checkboxDescription = $model->getCheckboxDescription($placeholder);
		} catch(\Exception $e) {
			// No exception thrown
		}
	
		PluginHelper::importPlugin('content', null, true, $this->app->getDispatcher());
		$dummyParams = new Registry();
		$elm = new \stdClass();
		$elm->id = $elm->catid = $elm->language = $elm->title = null;
		$elm->text = $checkboxDescription;
		if($this->getModel ()->getComponentParams()->get('popup_prepare_contents', 0)) {
			$eventObject = $this->createEventObject ( $dummyParams, $elm );
			
			$this->app->getDispatcher ()->dispatch ( 'onContentPrepare', $eventObject );
		}
		
		echo '<div>' . Text::_($elm->text) . '</div>';
	}
	
	/**
	 * Retrieve data for a given dynamic checkbox starting from a unique identifier 'placeholder'
	 *
	 * @access public
	 * @return mixed
	 */
	public function checkBulkConsent() {
		$model = $this->getModel();
		
		// Retrieve, sanitize and build posted data
		$ipAddressToCheck = $this->app->getInput()->get->getString ('client_ipaddress', null);
		
		$key = $this->app->getInput()->get->getString ('key', null);
		if($key != md5($ipAddressToCheck)) {
			header("HTTP/1.1 403 Forbidden Access");
			jexit();
		}
		
		try {
			$bulkConsentData = $model->getCheckBulkConsent($ipAddressToCheck);
		} catch(\Exception $e) {
			// No exception thrown
		}
		
		if($bulkConsentData) {
			header('Content-Type: application/json');
			echo json_encode($bulkConsentData);
		}
		
		jexit();
	}
	
	/**
	 * Retrieve data for a given dynamic checkbox starting from a unique identifier 'placeholder'
	 *
	 * @access public
	 * @return mixed
	 */
	public function getDomainsSum() {
		$model = $this->getModel();
		
		try {
			$domainsNotAccepted = $model->getDomainsSumForDeniedCategories();
		} catch(\Exception $e) {
			// No exception thrown
		}
		
		header('Content-Type: application/json');
		echo json_encode($domainsNotAccepted);
		jexit();
	}
}