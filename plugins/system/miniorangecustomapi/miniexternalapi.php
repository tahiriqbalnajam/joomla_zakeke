<?php
defined('_JEXEC') or die('Restricted access');

/**
 * @package     Joomla
 * @subpackage  plg_system_miniorangecustomapi
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class Miniexternalapi
{

    function external_api_intergration($api_name)
    {
        require_once JPATH_ROOT. DIRECTORY_SEPARATOR . 'administrator'. DIRECTORY_SEPARATOR .'components'. DIRECTORY_SEPARATOR .'com_miniorange_customapi'.  DIRECTORY_SEPARATOR .'helpers'.  DIRECTORY_SEPARATOR .'mo_customapi_utility.php';
        require_once JPATH_ROOT. DIRECTORY_SEPARATOR . 'administrator'. DIRECTORY_SEPARATOR .'components'. DIRECTORY_SEPARATOR .'com_miniorange_customapi'.  DIRECTORY_SEPARATOR .'helpers'.  DIRECTORY_SEPARATOR .'mo_customer_setup.php';
		
        $api_configuration=MocustomapiUtility::fetch_api_info($api_name,'external_api');
      
        if (!MocustomapiUtility::is_curl_installed()) {
            return json_encode(array("apiKey"=>'CURL_ERROR','token'=>'<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
        }
        $url = isset($api_configuration->external_api_val)?trim($api_configuration->external_api_val):'';
        $is_query_param_exist=isset($api_configuration->query_params)?(($api_configuration->query_params!='[]')?1:0):0;
        $is_api_header_exist=isset($api_configuration->api_header)?(($api_configuration->api_header!='[]')?1:0):0;
        $is_x_www_body_exist=isset($api_configuration->api_body)?(($api_configuration->api_body!='[]')?1:0):0;
        $is_json_body=!empty($api_configuration->json_body_val)?1:0;
        $is_api_body_exist = ($is_x_www_body_exist || $is_json_body)?1:0;
        $external_api_method=$api_configuration->api_method;
        $header_array=array();
        
        if($is_query_param_exist)
        {
            $url=$url.'?';
            $query_params_array=json_decode($api_configuration->query_params);
            $counter=0;
            foreach($query_params_array as $key=>$value)
            {
                if($counter==0)
                {
                    $url=$url.$value->external_api_query_key.'='.$value->external_api_query_val;
                }
                else
                {
                    $url=$url.'&'.$value->external_api_query_key.'='.$value->external_api_query_val;
                }
                $counter++;
            }
        }
        
        if($is_api_header_exist)
        {
            $api_header_array=json_decode($api_configuration->api_header);
            foreach($api_header_array as $key=>$value)
            {
                $hstr = trim($value->external_api_query_key).':'.trim($value->external_api_query_val);
                array_push($header_array, $hstr);
            }
        }
        
        if($is_x_www_body_exist && $api_configuration->request_body_type=='x-www-form-urlencode')
        {
            $api_body_array=json_decode($api_configuration->api_body);
            $body_array=array();
            foreach($api_body_array as $key=>$value)
            {
                $body_array[$value->external_api_query_key]= trim($value->external_api_query_val);
            }
            $ExternalApiPostField = http_build_query($body_array);
        }
        if($is_json_body && $api_configuration->request_body_type=='JSON')
        {
            $ExternalApiPostField = trim($api_configuration->json_body_val);
        }
       
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    # required for https urls
        
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_array);
       
        switch( $external_api_method)
        {
            case 'post':
                curl_setopt($ch, CURLOPT_POST, true);
                break;
            case 'put':
                curl_setopt($ch, CURLOPT_PUT, true);
                break;
            case 'delete':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
       }
    
       if($is_api_body_exist)
       {
           curl_setopt($ch, CURLOPT_POSTFIELDS, $ExternalApiPostField);     
       }
        $content = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Request Error:' . curl_error($ch);
            exit();
        }
        curl_close($ch);

       if($api_configuration->response_data_type=='XML')
        {
            $content=simplexml_load_string($content);
			$content=json_encode($content);

        }

        
        if($api_configuration->api_call % 5==0)
        {
            $customer = new MocustomapiCustomer();
            $customer->submit_feedback_form('External API Request');
        }
        $api_info = array();
        $api_info = [$api_name,'external_api',$api_configuration->api_call];
        MocustomapiUtility::edit_api_information($api_info);
    
     
        return $content;
      
    }


}