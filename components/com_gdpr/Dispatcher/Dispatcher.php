<?php
namespace JExtstore\Component\Gdpr\Site\Dispatcher;
/**
 * Frontend entrypoint dispatcher of the component application
 *
 * @package GDPR::components::com_gdpr
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ();

use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Registry\Registry;
use Joomla\Input\Input;
use JExtstore\Component\Gdpr\Administrator\Framework\Loader;

/**
 * Dispatcher class for the component frontend
 */
class Dispatcher extends ComponentDispatcher {
	/**
	 * The extension namespace
	 *
	 * @var string
	 */
	protected $namespace = 'JExtstore\\Component\\Gdpr';
	
	/**
	 * Constructor for Dispatcher
	 *
	 * @param   CMSApplicationInterface     $app                The application instance
	 * @param   Input                       $input              The input instance
	 * @param   MVCFactoryInterface  $mvcFactory  The MVC factory instance
	 *
	 * @since   4.0.0
	 */
	public function __construct(CMSApplicationInterface $app, Input $input, MVCFactoryInterface $mvcFactory) {
		// Set MySql 5.7.8+ strict mode off
		Factory::getContainer()->get('DatabaseDriver')->setQuery ( "SET @@SESSION.sql_mode = ''" )->execute ();
		
		// Kill CSP headers
		$httpHeadersPlugin = PluginHelper::getPlugin('system', 'httpheaders');
		if(is_object($httpHeadersPlugin)) {
			$httpHeadersPluginParams = new Registry($httpHeadersPlugin->params);
			if($httpHeadersPluginParams->get('contentsecuritypolicy', 0)) {
				$app->setHeader('content-security-policy', null, true);
				$app->setHeader('content-security-policy-report-only', null, true);
			}
		}
		
		// Ensure caching is disabled as it depends on the query param in the model
		if($input->get('format') == 'json') {
			$app->allowCache(false);
			$app->set('caching', 0);
		}
		
		$option = $input->get('option') ?? 'com_gdpr';
		
		// Define component path.
		if (!defined('JPATH_COMPONENT')) {
			define('JPATH_COMPONENT', JPATH_BASE . '/components/' . $option);
		}
		
		if (!defined('JPATH_COMPONENT_SITE')) {
			define('JPATH_COMPONENT_SITE', JPATH_SITE . '/components/' . $option);
		}
		
		if (!defined('JPATH_COMPONENT_ADMINISTRATOR')) {
			define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/' . $option);
		}
		
		// Auto loader setup
		// Register autoloader prefix
		require_once JPATH_COMPONENT_ADMINISTRATOR . '/Framework/Loader.php';
		Loader::setup ();
		Loader::registerNamespacePsr4 ( $this->namespace . '\Site', JPATH_COMPONENT );
		Loader::registerNamespacePsr4 ( $this->namespace . '\Administrator', JPATH_COMPONENT_ADMINISTRATOR );
		
		// Manage partial language translations
		$jLang = $app->getLanguage ();
		$jLang->load ( 'com_gdpr', JPATH_COMPONENT, 'en-GB', true, true );
		if ($jLang->getTag () != 'en-GB') {
			$jLang->load ( 'com_gdpr', JPATH_SITE, null, true, false );
			$jLang->load ( 'com_gdpr', JPATH_COMPONENT, null, true, false );
		}
		
		/**
		 * All SMVC logic is based on controller.task correcting the wrong Joomla concept
		 * of base execute on view names.
		 * When task is not specified because Joomla force view query string such as menu
		 * the view value is equals to controller and viewname = controller.display
		 */
		$viewName = $app->getInput()->get ( 'view', null );
		// Only core component views
		if (! in_array ( $viewName, array (
				'user'
		) )) {
			$viewName = null;
		}
		$controller_command = $app->getInput()->get ( 'task', '' );
		if (strpos ( $controller_command, '.' )) {
			// Override always the view for security safe
			list ( $controller_name, $controller_task ) = explode ( '.', $controller_command );
			$app->getInput()->set ( 'view', $controller_name );
		} elseif ($controller_command || $viewName) {
			$controller_name = $controller_command ? $controller_command : $viewName;
			$app->getInput()->set ( 'controller', $controller_name );
			$app->getInput()->set ( 'view', $controller_name );
			$app->getInput()->set ( 'task', 'display' );
		} else {
			// Defaults
			$app->getInput()->set ( 'controller', 'user' );
			$app->getInput()->set ( 'view', 'user' );
			$app->getInput()->set ( 'task', 'display' );
		}
		
		if(isset($controller_name)) {
			$path = JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . ucfirst($controller_name) . 'Controller.php';
			if (!file_exists($path)) {
				$app->enqueueMessage(Text::_('COM_GDPR_ERROR_NO_CONTROLLER_FILE'), 'error');
				$app->redirect(Route::_('index.php?option=com_gdpr&view=user'));
			}
		}
		
		parent::__construct ( $app, $input, $mvcFactory );
	}
}
