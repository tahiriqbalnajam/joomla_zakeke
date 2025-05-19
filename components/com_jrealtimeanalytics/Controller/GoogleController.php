<?php
namespace JExtstore\Component\JRealtimeAnalytics\Site\Controller;
/**
 * @package JREALTIMEANALYTICS::OVERVIEW::administrator::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use JExtstore\Component\JRealtimeAnalytics\Administrator\Framework\Controller as JRealtimeController;

/**
 * Main controller
 * @package JREALTIMEANALYTICS::OVERVIEW::administrator::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @since 2.5
 */
class GoogleController extends JRealtimeController {
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
		// Set model state
		$defaultModel->setState ( 'option', $option );
		
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
		// Composer autoloader
		if (PHP_VERSION_ID >= 70205) {
			require_once JPATH_COMPONENT_ADMINISTRATOR. '/Framework/composer/autoload_real.php';
			\ComposerAutoloaderInitcb4c0ac1dedbbba2f0b42e9cdf4d93d7JReal::getLoader();
		} else {
			// Fallback to legacy API
			require_once JPATH_COMPONENT_ADMINISTRATOR. '/Framework/composerlegacy/autoload_real.php';
			\ComposerAutoloaderInitfc5c9af51413a149e4084a610a3ab6deJReal::getLoader();
		}
	
		// Mixin, add include path for admin side to avoid DRY on model
		$this->addModelPath ( JPATH_COMPONENT_ADMINISTRATOR . '/models', 'JRealtimeModel', 'JRealtimeModel' );
		
		$this->setModelState('google');
		parent::display($cachable, $urlparams);
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
			$this->setRedirect ( \JRealtimeRoute::_("index.php?option=" . $this->option . "&view=" . $this->name), Text::_ ( 'COM_JREALTIME_GOOGLE_ERROR_' . 'LOGOUT' ) );
			return false;
		}
	
		$this->setRedirect ( \JRealtimeRoute::_("index.php?option=" . $this->option . "&view=" . $this->name), Text::_ ( 'COM_JREALTIME_GOOGLE_SUCCESS_LOGOUT' ) );
		
		return true;
	}
}