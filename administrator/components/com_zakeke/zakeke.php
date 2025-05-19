<?php
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_zakeke'))
{
	return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Require helper file
JLoader::register('ZakekeHelper', __DIR__ . '/helpers/zakeke.php');

$controller = JControllerLegacy::getInstance('Zakeke');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
