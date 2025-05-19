<?php

/**
 * @package     Joomla.System
 * @subpackage  plg_system_customapi
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
use Joomla\CMS\Factory;

class plgSystemMiniorangecustomapi extends JPlugin
{
    public function onAfterInitialise()
    {
        $get = JFactory::getApplication()->input->get->getArray();
        $external_api_name = '';
        if (isset($get['api_name']) && !empty($get['api_name'])) {
            $external_api_name = $get['api_name'];
        }
        if (isset($get['morequest']) && $get['morequest'] == 'custom_api') {                       
            $this->externalAPIResult($external_api_name);

        }
    }

    function externalAPIResult($external_api_name)
    {
        require_once JPATH_ROOT. DIRECTORY_SEPARATOR . 'plugins'. DIRECTORY_SEPARATOR .'system'. DIRECTORY_SEPARATOR .'miniorangecustomapi'.  DIRECTORY_SEPARATOR .'miniexternalapi.php';
        require_once JPATH_ROOT. DIRECTORY_SEPARATOR . 'administrator'. DIRECTORY_SEPARATOR .'components'. DIRECTORY_SEPARATOR .'com_miniorange_customapi'.  DIRECTORY_SEPARATOR .'helpers'.  DIRECTORY_SEPARATOR .'mo_customapi_utility.php';
        $customer = new Miniexternalapi();
        $result=$customer->external_api_intergration($external_api_name);
        MocustomapiUtility::testConfigWindow($result);
    }
    
    function onExtensionBeforeUninstall($id)
    {
       
    }
  
}
