<?php
/**
 * @package     CSVI
 * @subpackage  Frontend
 *
 * @author      RolandD Cyber Produksi <contact@rolandd.com>
 * @copyright   Copyright (C) 2006 - 2024 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://rolandd.com
 */

defined('_JEXEC') or die;

$jinput = JFactory::getApplication()->input;

// Set CLI mode
define('CSVI_CLI', false);

// Define the tmp folder
$config = JFactory::getConfig();

if (!defined('CSVIPATH_TMP'))
{
	$tmpPath = $config->get('tmp_path');

	if (!is_dir($tmpPath))
	{
		$tmpPath = JPath::clean(JPATH_SITE . '/tmp', '/');
	}

	define('CSVIPATH_TMP', $tmpPath . '/com_csvi');
}

if (!defined('CSVIPATH_DEBUG'))
{
	$logPath = $config->get('log_path');

	if (!is_dir($logPath))
	{
		$logPath = JPath::clean(JPATH_SITE . '/logs', '/');
	}

	define('CSVIPATH_DEBUG', $logPath);
}

// Setup the autoloader
JLoader::registerPrefix('Csvi', JPATH_ADMINISTRATOR . '/components/com_csvi');
JLoader::registerPrefix('Rantai', JPATH_ADMINISTRATOR . '/components/com_csvi/rantai');

if (file_exists(JPATH_ADMINISTRATOR . '/components/com_csvi/assets/google/vendor/autoload.php'))
{
	JLoader::import('google.vendor.autoload', JPATH_ADMINISTRATOR . '/components/com_csvi/assets');
}

require_once JPATH_ADMINISTRATOR . '/components/com_csvi/assets/vendor/autoload.php';

// All Joomla loaded, set our exception handler
require_once JPATH_BASE . '/administrator/components/com_csvi/rantai/error/exception.php';

// Execute CSVI
try
{
	// Check if we have an old style URL
	$task = $jinput->get('task');

	if (strpos($task, '.') === false)
	{
		// We have an old style task, let's change it
		$view = $jinput->get('view');

		$jinput->set('task', $view . '.' . $task);
		$jinput->set('view', '');
	}

	require_once JPATH_ADMINISTRATOR . '/components/com_csvi/helper/autoloader.php';
	require_once JPATH_ADMINISTRATOR . '/components/com_csvi/controllers/default.php';
	require_once JPATH_ADMINISTRATOR . '/components/com_csvi/models/default.php';
	require_once JPATH_ADMINISTRATOR . '/components/com_csvi/tables/default.php';

	$language    = JFactory::getLanguage();
	$adminDir    = JPATH_ADMINISTRATOR . '/components/com_csvi';
	$languageTag = $language->getTag();
	$language->load('com_csvi', $adminDir, $languageTag, true);

	JFormHelper::addFormPath(JPATH_ADMINISTRATOR . '/components/com_csvi/models/forms/');
	JFormHelper::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_csvi/models/fields/');

	$controller = JControllerLegacy::getInstance('csvi');
	$controller->execute($jinput->get('task'));
	$controller->redirect();
}
catch (Exception $e)
{
	JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
	JFactory::getApplication()->redirect('index.php');
}
