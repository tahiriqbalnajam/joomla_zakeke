<?php
/** Copyright (C) 2015  miniOrange
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 * @package        miniOrange Role Based Redirection
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
/**
 * This class contains all the utility functions
 **/
defined('_JEXEC') or die('Restricted access');
?>

<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

class MocustomapiUtility
{
    public static function is_customer_registered()
    {
        $result = self::getCustomerDetails();

        $email 			= isset($result['email']) ? $result['email'] : '';
        $customerKey 	= isset($result['customer_key']) ? $result['customer_key'] : 0;
        $status = isset($result['registration_status']) ? $result['registration_status'] : '';

        if($email && $status == 'SUCCESS'){
            return 1;
        } else{
            return 0;
        }
    }

  

    public static function GetPluginVersion()
    {
        $db = JFactory::getDbo();
        $dbQuery = $db->getQuery(true)
            ->select('manifest_cache')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . " = " . $db->quote('com_miniorange_customapi'));
        $db->setQuery($dbQuery);
        $manifest = json_decode($db->loadResult());
        return($manifest->version);
    }

    public static function isCurrentGroupExist($mapping_value, $role_based_redirect_key_value)
    {
        if (in_array($mapping_value, $role_based_redirect_key_value))
        {
            return 'ALLOW';
        }
        else
        {
            return 'NOT_ALLOWED';
        }
    }

	
	public static function encrypt($str){
		$str = stripcslashes($str);

		
		$key = self::getCustomerToken();
		
		return base64_encode(openssl_encrypt($str, 'aes-128-ecb', $key, OPENSSL_RAW_DATA));
	}
    public static function getUserId($username)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('username') . ' = ' . $db->quote($username));
        $db->setQuery($query, 0, 1);

        try
        {
            $result = $db->loadResult();
        }
        catch (\RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'notice');

            return null;
        }
        return $result;
    }

    public static function getAllGroups()
    {
        $all_groups = self::loadGroups();

        $groups = array();
        foreach ($all_groups as $key => $value) {
            array_push($groups, $value['title']);
        }
        return $groups;
    }

    public static function getConfiguration()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__miniorange_customapi_settings'));
        $db->setQuery($query);

        try
        {
            $result = $db->loadAssoc();
        }
        catch (\RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'notice');

            return null;
        }
        return $result;

    }

    public static function getUserGroupID($groupID)
    {
        $group_id = '';
        foreach ($groupID as $groups)
        {
            $group_id = $groups;
        }
        return $group_id;
    }

    public static function get_role_based_redirect_values($role_mapping_key_value, $currentUserGroup)
    {
        $groups = array();
        foreach ($role_mapping_key_value as $mapping_key => $mapping_value){
            if (!empty($mapping_key)) {
                if($mapping_key == $currentUserGroup){
                    $groups = $mapping_value;
                }
            }
        }
        return $groups;
    }

    public static function check($val)
    {
        if (empty($val))
            return "";
        else
            return self::decrypt($val);
    }

    public static function decrypt($value)
    {
        if (!self::isExtensionInstalled('openssl')) {
            return;
        }
        $customer_token= self::getCustomerToken();

        $string = rtrim(openssl_decrypt(base64_decode($value), 'aes-128-ecb', $customer_token, OPENSSL_RAW_DATA), "\0");
        return trim($string, "\0..\32");
    }

    public static function isExtensionInstalled($name)
    {
        if (in_array($name, get_loaded_extensions())) {
            return true;
        } else {
            return false;
        }
    }

    public static function isBlank( $value )
    {
        if( ! isset( $value ) || empty( $value ) ) return TRUE;
        return FALSE;
    }

    public static function getCustomerDetails()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__miniorange_customapi_customer_details'));
        $query->where($db->quoteName('id') . " = 1");
        $db->setQuery($query);
        return $db->loadAssoc();
    }

    public static function check_empty_or_null($value)
    {
        if (!isset($value) || empty($value)) {
            return true;
        }
        return false;
    }

    public static function is_curl_installed()
    {
        if (in_array('curl', get_loaded_extensions())) {
            return 1;
        } else
            return 0;
    }

    public static function getCustomerToken()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('customer_token');
        $query->from($db->quoteName('#__miniorange_customapi_customer_details'));
        $query->where($db->quoteName('id') . " = 1");
        $db->setQuery($query);

        try
        {
            $result = $db->loadResult();
        }
        catch (\RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'notice');

            return null;
        }
        return $result;
    }

    public static function is_extension_installed($name)
    {
        if (in_array($name, get_loaded_extensions())) {
            return true;
        } else {
            return false;
        }
    }

    public static function getHostname()
    {
        return 'https://login.xecurify.com';
    }

    public static function loadGroups(){
        $db = JFactory::getDbo();
        $db->setQuery($db->getQuery(true)
            ->select('*')
            ->from("#__usergroups")
        );

        try
        {
            $result = $db->loadRowList();
        }
        catch (\RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'notice');

            return null;
        }
        return $result;
    }


    public static function loadUserGroups($user_id){
        $db = JFactory::getDbo();
        $db->setQuery($db->getQuery(true)
            ->select('group_id')
            ->from("#__user_usergroup_map")
            ->where($db->quoteName('user_id'). ' = ' . $db->quote($user_id))
        );
        try
        {
            $result = $db->loadAssocList();
        }
        catch (\RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'notice');

            return null;
        }
        return $result;
    }

    public static function getGroupNameByID($group_id)
    {


        $db = JFactory::getDbo();
        $db->setQuery($db->getQuery(true)
            ->select('title')
            ->from("#__usergroups")
            ->where($db->quoteName('id'). ' = ' . $db->quote($group_id))
        );

        try
        {
            $result = $db->loadAssoc();
        }
        catch (\RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'notice');
            return null;
        }
        return $result['title'];
    }

    public static function loadAllGroups(){
        $db = JFactory::getDbo();
        $db->setQuery($db->getQuery(true)
            ->select('*')
            ->from("#__usergroups")
        );
        try
        {
            $result = $db->loadAssocList();
        }
        catch (\RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'notice');
            return null;
        }
        return $result;
    }


    public static function checkExtensionEnabled($plugin)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('enabled');
        $query->from('#__extensions');
        $query->where($db->quoteName('element') . " = " . $db->quote($plugin));
        $query->where($db->quoteName('type') . " = " . $db->quote('plugin'));
        $db->setQuery($query);
        return($db->loadAssoc());
    }



    public static function generic_update_query($database_name, $updatefieldsarray){

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        foreach ($updatefieldsarray as $key => $value)
        {
            $database_fileds[] = $db->quoteName($key) . ' = ' . $db->quote($value);
        }
        $query->update($db->quoteName($database_name))->set($database_fileds)->where($db->quoteName('id')." = 1");
        $db->setQuery($query);
        $db->execute();
    }

    public static function fetch_api_info($api_name, $type){
        $plugin_settings=self::getConfiguration();

         if('custom'==$type)
         {
             $api_configuration=json_decode($plugin_settings['mo_custom_apis']);
         }else if('sql'== $type)
         {
             $api_configuration=json_decode($plugin_settings['mo_custom_sql_apis']);
         }else if('external_api'== $type)
         {
             $api_configuration=(array) json_decode($plugin_settings['mo_external_apis']);
         }

         if(!empty($api_configuration))
         {
             foreach($api_configuration as $key=>$value){
                 if($api_name==$key)
                 {
                     return $value;
                 }
             }
         }
    }

    public static function fetch_other_api_info($api_name, $type){
        $plugin_settings=self::getConfiguration();
  
        if('custom'== $type)
        {
            $api_configuration= (array)json_decode($plugin_settings['mo_custom_apis']);
        }else if('sql'== $type)
        {
            $api_configuration=(array) json_decode($plugin_settings['mo_custom_sql_apis']);
        }
        else if('external_api'== $type)
        {
            $api_configuration=(array) json_decode($plugin_settings['mo_external_apis']);
        }
     
        $other_apis=array();
        foreach($api_configuration as $key=>$value){
            if($key!=$api_name)
            {
                $key_name=$key;
                $new_post=array($key_name=>$key=$value);
                $other_apis=array_merge($other_apis,$new_post);
            }
        }
        
        return $other_apis;
    }

    public static function check_api_exist($api_name)
    {
        $plugin_settings=self::getConfiguration();
        for($i=0;$i<2;$i++)
        {
            $api_configuration=($i==0)?json_decode($plugin_settings['mo_custom_apis']):json_decode($plugin_settings['mo_custom_sql_apis']);
            foreach($api_configuration as $key=>$value){
                if($api_name==$key)
                {
                    return 1;
                }
            }
        }
    
    
        
        return 0;
    }
    
    
	public static function get_api_path()
	{
		$api=JURI::getInstance()->toString();
		$api_path=parse_url($api);
		$api_path_array=explode('/',$api_path['path']);
		return $api_path_array;
	}

	public static function get_api_name()
	{
		$api_path_array = self::get_api_path();
		if(in_array('mini',$api_path_array))
		{
			$path_size=sizeof($api_path_array);
			$api_name=$api_path_array[$path_size-1];
			$api_info = array();
			$api_information=MocustomapiUtility::fetch_api_info($api_name,'custom');
			$sql_api_information=MocustomapiUtility::fetch_api_info($api_name,'sql');
			if(!empty($api_information))
			{
				$api_info = [$api_name,'custom',$api_information->api_call];	
				return $api_info;

			}
			else if(!empty($sql_api_information))
			{
				$api_info = [$api_name,'sql',$sql_api_information->api_call];	
				return $api_info;
			}


		}
	}

	public static function edit_api_information($api_info)
	{
		$api_information = MocustomapiUtility::fetch_api_info($api_info[0],$api_info[1]);
		$api_information=(array)($api_information); 
		$api_information['api_call'] = $api_information['api_call']+1;
		$other_apis = MocustomapiUtility::fetch_other_api_info($api_info[0],$api_info[1]);
		$api_information=array($api_info[0]=>$api_information);
		$api_information=array_merge($api_information,$other_apis);
		$database_name = '#__miniorange_customapi_settings';
		if($api_info[1]=='custom')
		{
			$updatefieldsarray = array(
				'mo_custom_apis' => json_encode($api_information),
			);

		}
        else if($api_info[1]=='sql')
		{
			$updatefieldsarray = array(
				'mo_custom_sql_apis' => json_encode($api_information),
			);

		}
        else if($api_info[1]=='external_api')
        {
            $updatefieldsarray = array(
				'mo_external_apis' => json_encode($api_information),
			);
        }

        MocustomapiUtility::generic_update_query($database_name, $updatefieldsarray);

	}

    public static function mo_api_error_msgs($error_type)
    {
        $error_response=array();
        switch($error_type)
        {
            case 'INVALID_FORMAT':
                $error_response = array(
                    'title'             => 'invalid_format',
                    'error_description' => 'Required arguments are missing or does not passed in the correct format.',
                );
                break;
            case 'RESOURCE_NOT_FOUND':
                $error_response = array(
                    'title' => 'Resource not found',
                    'code' => '404',
                );
                break;
            case 'INVALID_DATA_FORMAT':
                $error_response = array(
                    "title"=> "Invalid data format",
                    "code" =>"400",
                    "error_description" => "Sorry, You have passed wrong values"
                );
                break;
            case 'TOKEN_ERROR':
                $error_response = array(
                    "title"=> "Forbidden"
                );
                break;
            case 'INVALID_TOKEN':
                $error_response = array(
                    "title"=> "INVALID_TOKEN",
                    "code"=>"401",
                    "error_description" => "Sorry, you are using invalid Token."
                );
                break; 
            case 'UNSUPPORTED_REQUEST_FORMAT':
                $error_response = array(
                    "title"=> "Unsupported Request Format",
                    "error_description" => "POST, PUT, DELETE requests are not supported by the free version of plugin"
                );
                break; 
            case 'INVALID_BODY_FORMAT':
                $error_response = array(
                    "title"=> "Invalid data format",
                    "code" =>"400",
                    "error_description" => "Sorry, You have passed the body data in the wrong format."
                );
                break;
        }

        $error_response=array(
            'error' => $error_response,
        );

        $error_response=json_encode($error_response,JSON_PRETTY_PRINT);
        return $error_response;
    }

    public static function getAPIByVersion()
    {
        $jVersion = new JVersion();
        $jCmsVersion = $jVersion->getShortVersion();
        $jCmsVersion = substr($jCmsVersion, 0, 3);
        $api_name='';
        if($jCmsVersion < 4.0)
        {
            $api_name='index.php/api/v1/mini/';
        }else{
            $api_name='api/index.php/v1/mini/';
        }
        return $api_name;
    }

    public static function get_custom_param($sql_query)
    {
        $pattern = "/{{(.*?)}}/";
        $customparams = [];
        if(preg_match_all($pattern, $sql_query, $reg_array)){
            foreach($reg_array[0] as $attr){
                $mo_regex = substr($attr, 2);
                $mo_regex = substr($mo_regex, 0, -2);
                array_push($customparams, $mo_regex);
            }
        }
        return $customparams;
    }
    
    public static function external_api_method_description($api_method)
    {
        $description='';
        switch($api_method)
        {
            case 'get':
                $description='Fetch external data via API';
                break;
            case 'put':
                $description='Update external data via API';
                break;
            case 'post':
                $description='Insert external data via API';
                break;
            case 'delete':
                $description='Delete external data via API';
                break;   
        }

        return $description;
    }


    public static function getAttributes($external_api_key,$external_api_val)
    {
        $attributes = array();
        foreach ($external_api_key as $key => $value) {
            $trimmed_value = trim($value);
            if (!empty($trimmed_value)) {
                $trimmed_ia_value = trim($external_api_val[$key]);
                $anArray = array();
                $anArray['external_api_query_key'] = $trimmed_value;
                $anArray['external_api_query_val'] = $trimmed_ia_value;
                array_push($attributes , $anArray);
            }
        }
        return $attributes;
    }

    public static function testConfigWindow($result)
    {
        if((is_array(json_decode($result, true)) || is_object(json_decode($result, true))) && (json_last_error() == JSON_ERROR_NONE)){ 

            echo '<div id="Test_configuration" style="font-family:Calibri;padding:0 3%;">';
            echo '<div style="color: #3c763d;
            background-color: #dff0d8; padding:2%;margin-bottom:20px;text-align:center; border:1px solid #AEDB9A; font-size:18pt;">TEST RESULTS</div>';
            echo '<table style="border-collapse:collapse;border-spacing:0; display:table;width:100%; font-size:14pt;background-color:#EDEDED;"><tr style="text-align:center;"><td style="font-weight:bold;border:2px solid #949090;padding:2%;">ATTRIBUTE NAME</td><td style="font-weight:bold;padding:2%;border:2px solid #949090; word-wrap:break-word;">ATTRIBUTE VALUE</td></tr>';
            self::showTestConfig('', json_decode($result));
            echo '</table>';
            echo '<div style="margin:3%;display:block;text-align:center;"><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="self.close();"></div>';
     
        }
        else{
            echo '<div id="Test_configuration" style="font-family:Calibri;padding:0 3%;">';
            echo '<div style="color: #3c763d;
            background-color: #dff0d8; padding:2%;margin-bottom:20px;text-align:center; border:1px solid #AEDB9A; font-size:18pt;">TEST RESULTS</div>';
            echo '<div style="font-family:Calibri;padding:0 3%;">';
            echo $result;
            echo '<div style="margin:3%;display:block;text-align:center;"><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="self.close();"></div>';
        }
        exit;
    }

    public static function showTestConfig($nestedprefix, $api_result)
    {

        $ApiResponseKey=array();
        foreach ($api_result as $key => $resource) {
          
             if(is_int($key)){
                $key++;  
             }
			if (is_array($resource) || is_object($resource)) {
				
				if (!empty($nestedprefix)) {
					$nestedprefix .= '->';
				}
                self::showTestConfig($nestedprefix . $key, $resource);
                $nestedprefix = rtrim($nestedprefix, "->"); 

			} else {
				$completekey = '';
				echo '<tr><td  style="font-weight:bold;border:2px solid #949090;padding:2%;">';
            
               

				if (!empty($nestedprefix)) {
                   
					echo $nestedprefix . '->'; 
					$completekey = $nestedprefix . '->';
				}
               
				echo $key . '</td><td style="padding:2%;border:2px solid #949090; word-wrap:break-word;">' . $resource . '</td></tr>'; // phpcs:ignore
		

			}
          
		}
     
    }

    public static function api_get_request($api_information,$get_param)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName($api_information->SelectedColumn));
        $query->from($db->quoteName($api_information->table_name));
        if($api_information->col_condition!='None Selected')
        {
            if($api_information->col_condition_name=='Less Than')
            {
                $api_information->col_condition_name="<";
            }
            $query->where($db->quoteName($api_information->col_condition) . $api_information->col_condition_name . $db->quote($get_param[$api_information->col_condition]));
        }

        try {
            $db->setQuery($query);
            $results = $db->loadObjectList();
        } catch (Exception $e) {
            $message = $e->getMessage();
            $results=$message;
        }
    
        return $results;
    }
   
    public static function setQuery($params,$sql_query)
    {
        foreach($params as $key=> $value)
        {
            $sql_query=str_replace('{{'.$key.'}}',strval($params[$key]),$sql_query);
        }

        
        return $sql_query;
    }
    
    public static function getGuideLinks($tab_name,$api_method,$view_tab)
    {
        //will update guide links once created
        $guide_link='https://plugins.miniorange.com/setup-custom-api-for-joomla';
        if($tab_name=='show_custom_apis')
        {
            switch($api_method)
            {
                case 'get':
                    $guide_link.=($view_tab==1)?'#customapi_get_how_to_use':'#customapi_get';
                    break;
                case 'put':
                    $guide_link.=($view_tab==1)?'#customapi_put_how_to_use':'#customapi_put';
                    break;
                case 'post':
                    $guide_link.=($view_tab==1)?'#customapi_post_how_to_use':'#customapi_post';
                    break;
                case 'delete':
                    $guide_link.=($view_tab==1)?'#customapi_delete_how_to_use':'#customapi_delete';
                    break;
            }
        }else if($tab_name=='create_sql_apis')
        {
            switch($api_method)
            {
                case 'get':
                    $guide_link.=($view_tab==1)?'#customsqlapi_get_how_to_use':'#customsqlapi_get';
                    break;
                case 'put':
                    $guide_link.=($view_tab==1)?'#customsqlapi_put_how_to_use':'#customsqlapi_put';
                    break;
                case 'post':
                    $guide_link.=($view_tab==1)?'#customsqlapi_post_how_to_use':'#customsqlapi_post';
                    break;
                case 'delete':
                    $guide_link.=($view_tab==1)?'#customsqlapi_delete_how_to_use':'#customsqlapi_delete';
                    break;
            }
        }

     
        return $guide_link;
    }

    public static function fetch_table_name($sql_query)
    {
        $pattern = '/#__(.*)/'; 
        if (preg_match($pattern, $sql_query, $matches)) {
            $name = $matches[1];
        } else {
            $name='';
        }
        if(!empty($name))
        {
            $name=explode(" ",$matches[1]);
            $name=$name[0];
        }
       
        return $name;
    }

    public static function create_request_parameter_string($customparams)
	{
		$custom_data='';
		for ($i=0; $i< sizeof($customparams); $i++) {
			$custom_data = $custom_data . $customparams[$i] . '={' . $customparams[$i] . '_value}';
			if($i != sizeof($customparams) - 1){
				$custom_data = $custom_data . '& ';
			}                
		}
		return $custom_data;
	}
    public static function api_method_description($api_method)
    {
        $description='';
        switch($api_method)
        {
            case 'get':
                $description='Fetch data via API';
                break;
            case 'put':
                $description='Update data via API';
                break;
            case 'post':
                $description='Insert data via API';
                break;
            case 'delete':
                $description='Delete data via API';
                break;   
        }

        return $description;
    }

    
    public static function getJoomlaCmsVersion()
    {
        $jVersion   = new JVersion;
        return($jVersion->getShortVersion());
    }

}
?>