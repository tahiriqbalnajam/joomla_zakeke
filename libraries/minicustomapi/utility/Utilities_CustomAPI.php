<?php
/**
 * @package     Joomla
 * @subpackage  lib_minicustoapi
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class Utilities_CustomAPI
{
    public static function getAPIInfo()
	{

		require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_customapi' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_customapi_utility.php';
		$api=JURI::getInstance()->toString();
		$api_path=parse_url($api);
		$api_path_array=explode('/',$api_path['path']);
		$customer_details = MocustomapiUtility::getCustomerDetails();
		$status = $customer_details['status'];


		if(in_array('mini',$api_path_array))
		{
			$path_size=sizeof($api_path_array);
			$api_name=$api_path_array[$path_size-1];
			$api_query=isset($api_path['query'])?$api_path['query']:'';
			$api_information=MocustomapiUtility::fetch_api_info($api_name,'custom');
			$sql_api_information=MocustomapiUtility::fetch_api_info($api_name,'sql');
			//var_dump($api_information);exit;
			$get_param=JFactory::getApplication()->input->get->getArray();
			$server=JFactory::getApplication()->input->server->getArray();
			$request_body=file_get_contents('php://input');
			$request_body_array=array();
  			parse_str($request_body,$request_body_array);




			if(!empty($api_information))
			{
				if($server['REQUEST_METHOD']!="GET")
				{
					return MocustomapiUtility::mo_api_error_msgs('UNSUPPORTED_REQUEST_FORMAT');
				}else
				{
					return self::custom_api_data($api_information,$get_param,$request_body_array,$api_query);
				}


			}
			else if(!empty($sql_api_information))
			{
				if($server['REQUEST_METHOD']!=strtoupper($sql_api_information->api_method))
				{

					return MocustomapiUtility::mo_api_error_msgs('RESOURCE_NOT_FOUND');
				}else
				{
					return self::sql_custom_api_data($sql_api_information,$get_param,$request_body_array,$api_query);
				}
			}
            else
            {
                return MocustomapiUtility::mo_api_error_msgs('RESOURCE_NOT_FOUND');
            }
		}
		else
		{
			return false;
		}




	}


	public static function custom_api_data($api_information, $get_param,$post_param, $api_query)
	{
		if($api_information->col_condition=='None Selected' && !empty($api_query))
		{
			return MocustomapiUtility::mo_api_error_msgs('INVALID_FORMAT');
		}else if($api_information->col_condition!='None Selected' && empty($api_query))
		{
			return MocustomapiUtility::mo_api_error_msgs('INVALID_FORMAT');
		}
		else
		{

			switch($api_information->api_method)
			{
				case "get":
					if(!empty($post_param))
					{
						$response=MocustomapiUtility::mo_api_error_msgs('INVALID_FORMAT');
					}
					else
					{
						$response=MocustomapiUtility::api_get_request($api_information,$get_param);
						$response=array(
							'data' => $response,
						);

						$response=json_encode($response,JSON_PRETTY_PRINT);
					}
					break;

			}
			return $response;

		}
	}

	public static function sql_custom_api_data($sql_api_information, $get_param,$post_param, $api_query)
	{
		if(!isset($sql_api_information->enable_cust_query_param) && (!empty($api_query) || !empty($post_param)))
		{
			return MocustomapiUtility::mo_api_error_msgs('INVALID_FORMAT');
		}else if((isset($sql_api_information->enable_cust_query_param) && $sql_api_information->enable_cust_query_param=='1')  && (empty($api_query) && empty($post_param)))
		{
			return MocustomapiUtility::mo_api_error_msgs('INVALID_FORMAT');
		}
		else
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);

			switch($sql_api_information->api_method)
			{
				case "get":

					if(!empty($post_param))
					{
						$response=MocustomapiUtility::mo_api_error_msgs('INVALID_FORMAT');
					}
					else
					{
						$sql_api_information->sql_query=MocustomapiUtility::setQuery($get_param,$sql_api_information->sql_query);
						try {
							$db->setQuery($sql_api_information->sql_query);
							$results = $db->loadObjectList();
							$response=array(
								'data' => $results,
							);

							$response=json_encode($response,JSON_PRETTY_PRINT);
						} catch (Exception $e) {
							$message = $e->getMessage();
							$response=$message;
						}
					}

					break;
				case "post":

					if(!empty($api_query))
					{
						$response=MocustomapiUtility::mo_api_error_msgs('INVALID_FORMAT');
					}
					else
					{
						$sql_api_information->sql_query=MocustomapiUtility::setQuery($post_param,$sql_api_information->sql_query);
						try {
							$db->setQuery($sql_api_information->sql_query);
							$response=$db->execute();
						} catch (Exception $e) {
							$message = $e->getMessage();
							$response=$message;
						}

					}

					break;
				case "put":

					if(!empty($api_query))
					{
						$sql_api_information->sql_query=MocustomapiUtility::setQuery($get_param,$sql_api_information->sql_query);

					}
					else if(!empty($post_param))
					{
						$sql_api_information->sql_query=MocustomapiUtility::setQuery($post_param,$sql_api_information->sql_query);
					}

					try {
						$db->setQuery($sql_api_information->sql_query);
						$response=$db->execute();
					} catch (Exception $e) {
						$message = $e->getMessage();
						$response=$message;
					}

					break;

				case "delete":

					if(!empty($api_query))
					{
						$sql_api_information->sql_query=MocustomapiUtility::setQuery($get_param,$sql_api_information->sql_query);

					}
					else if(!empty($post_param))
					{
						$sql_api_information->sql_query=MocustomapiUtility::setQuery($post_param,$sql_api_information->sql_query);
					}

					try {
						$db->setQuery($sql_api_information->sql_query);
						$response=$db->execute();
					} catch (Exception $e) {
						$message = $e->getMessage();
						$response=$message;
					}

					break;
			}


			return $response;



		}
	}

}