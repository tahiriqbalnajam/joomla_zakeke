<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.miniOrnage
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

 defined('_JEXEC') or die;

 use Joomla\CMS\Plugin\CMSPlugin;
 use Joomla\CMS\Router\ApiRouter;
 use Joomla\Router\Route;

 class PlgWebservicesMiniorange_customapi extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Registers com_content's API's routes in the application
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onBeforeApiRoute(&$router)
	{

	
		$router->createCRUDRoutes(
			'v1/mini',
			'customapi',
			['component' => 'com_miniorange_customapi']
		);

		$router->createCRUDRoutes(
			'v1/mini',
			'customapi',
			['component' => 'com_miniorange_customapi', 'extension' => 'com_miniorange_customapi']
		);

		// $this->createFieldsRoutes($router);

		$this->createContentHistoryRoutes($router);
	}

	/**
	 * Create fields routes
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function createFieldsRoutes(&$router)
	{
		
	}

	/**
	 * Create contenthistory routes
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function createContentHistoryRoutes(&$router)
	{
		jimport("minicustomapi.utility.Utilities_CustomAPI");
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

 
	