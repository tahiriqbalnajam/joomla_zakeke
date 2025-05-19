<?php

/**
 * @package     Joomla.site
 * @subpackage  com_miniorange_customapi
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
 

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\CMSPlugin;

defined('_JEXEC') or die;

jimport("minicustomapi.utility.Utilities_CustomAPI");
class miniorange_customapiRouter extends JComponentRouterView
{

    protected $noIDs = false;

    public function __construct(CMSApplication $app = null, AbstractMenu $menu = null)
    {
        $category = new RouterViewConfiguration('category');
        $category->setKey('id')->setNestable();
        $this->registerView($category);
        $mo_custom_api = new RouterViewConfiguration('miniorange_customapi');
        $mo_custom_api->setKey('id')->setParent($category, 'catid');
        $this->registerView($mo_custom_api);

        parent::__construct($app, $menu);
        $api_data=Utilities_CustomAPI::getAPIInfo();

        if($api_data!=false)
		{
            require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_customapi' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_customapi_utility.php';
            $api_info = MocustomapiUtility::get_api_name();	
			if($api_info[2]%5==0)
			{
                require_once JPATH_ROOT. DIRECTORY_SEPARATOR . 'administrator'. DIRECTORY_SEPARATOR .'components'. DIRECTORY_SEPARATOR .'com_miniorange_customapi'.  DIRECTORY_SEPARATOR .'helpers'.  DIRECTORY_SEPARATOR .'mo_customer_setup.php';
                $customer = new MocustomapiCustomer();
                $customer->submit_feedback_form('API Request');
            }
            MocustomapiUtility::edit_api_information($api_info);
			header('Content-Type: application/json;charset=utf-8');
			echo ''.$api_data.'';
			exit;
		}
    }
}


