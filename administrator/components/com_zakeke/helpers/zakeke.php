<?php
defined('_JEXEC') or die;

class ZakekeHelper
{
	public static function getCredentials()
	{
		static $credentials;

		if ($credentials === null) {
			$params = JComponentHelper::getParams('com_zakeke');
			$credentials = new stdClass();
			$credentials->client_id = $params->get('zakeke_client_id');
			$credentials->client_secret = $params->get('zakeke_client_secret');
		}

		return $credentials;
	}

    public static function getAuthHeader()
    {
        $credentials = self::getCredentials();
        if (!empty($credentials->client_id) && !empty($credentials->client_secret)) {
            return 'Basic ' . base64_encode($credentials->client_id . ':' . $credentials->client_secret);
        }
        return null;
    }

	public static function addSubmenu($vName = 'zakeke')
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_ZAKEKE_SUBMENU_MAIN'),
			'index.php?option=com_zakeke',
			$vName == 'zakeke'
		);
        // You can add more submenu items here if needed
	}
}
