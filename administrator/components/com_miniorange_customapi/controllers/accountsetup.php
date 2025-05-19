<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_miniorange_customapi
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
/**
 * AccountSetup Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_miniorange_customapi
 * @since       0.0.9
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Filesystem\File;


class MiniorangecustomapiControllerAccountsetup extends JControllerForm
{
    function __construct()
    {
        $this->view_list = 'accountsetup';
        parent::__construct();
    }

    function contactUs()
    {

        $post = JFactory::getApplication()->input->post->getArray();

        if (count($post) == 0) {
            $this->setRedirect('index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=account');
            return;
        }

        $query_email = isset($post['query_email']) ? $post['query_email'] : '';
        $query = isset($post['query_support']) ? $post['query_support'] : '';
        $phone = isset($post['query_phone']) ? $post['query_phone'] : '';

        if (MocustomapiUtility::check_empty_or_null($query_email) || MocustomapiUtility::check_empty_or_null($query)) {
            $this->setRedirect('index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=account', 'Please submit your query with email.', 'error');
            return;
        } else {
            $contact_us = new MocustomapiCustomer();
            $submited = json_decode($contact_us->submit_contact_us($query_email, $phone, $query));
            if($submited->status=='SUCCESS')
            {
                $this->setRedirect('index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=overview', 'Thanks for getting in touch! We will get back to you shortly.');
                return;
            }
            else 
            {
                $this->setRedirect('index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=overview', 'Your query could not be submitted. Please try again.', 'error');
                return;
            }
         
        }
    }

    function saveAPIInformation()
    {
        $post = JFactory::getApplication()->input->post->getArray();
        $check_api_exist=MocustomapiUtility::check_api_exist($post['api_name']);

        if(!isset($post['api_name']) || empty($post['api_name']))
        {
            $this->setRedirect('index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=create_custom_api','Please enter the API name to create API', 'error');
            return;
        }

        if($check_api_exist && (isset($post['edit_api']) && $post['edit_api']!=1))
        {
            $this->setRedirect('index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=create_custom_api','API with same name already exists', 'error');
            return;
        }
        else
        {
            $api_configuration =MocustomapiUtility::fetch_other_api_info($post['api_name'], 'custom');
            $db=JFactory::getDBO();
            $prefix=$db->getPrefix();
            $table_name=str_replace($prefix,'#__',$post['mo_table_name']);
            $post["table_name"]=$table_name;
            $new_post=array($post['api_name']=>$post);
            $api_configuration=array_merge($api_configuration,$new_post);
            $database_name = '#__miniorange_customapi_settings';
            $updatefieldsarray = array(
                'mo_custom_apis' => json_encode($api_configuration ),
            );
    
            MocustomapiUtility::generic_update_query($database_name, $updatefieldsarray);
           $this->setRedirect('index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=create_custom_api&api_name='.$post["api_name"].'');
        }

    }

    function createAPI()
    {
        $post = JFactory::getApplication()->input->post->getArray();
        $post['api_call']=0;
        $api_configuration = MocustomapiUtility::fetch_other_api_info($post['api_name'],'custom');
        if(!isset($post['api_name']) || empty($post['api_name']))
        {
            $this->setRedirect('index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=create_custom_api','Please enter the API name to create API', 'error');
            return;
        }
       
        $new_post=array($post['api_name']=>$post);
        $api_configuration=array_merge($api_configuration,$new_post);
        
        $database_name = '#__miniorange_customapi_settings';
        $updatefieldsarray = array(
            'mo_custom_apis' => json_encode($api_configuration),
        );

        MocustomapiUtility::generic_update_query($database_name, $updatefieldsarray);
        $customer = new MocustomapiCustomer();
        if((isset($post['edit_api']) && $post['edit_api']!=1))
        {
            $customer->submit_feedback_form('Created Custom API');
        }else 
        {
            $customer->submit_feedback_form('Edited Custom API'); 
        }
        $message='You have successfully created the '.$post['api_name'].' custom API.';
        $this->setRedirect('index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=view_custom_api&api_name='.$post["api_name"].'',$message);
    }

    function deleteCurrentAPI()
    {
        $post = JFactory::getApplication()->input->post->getArray();
        $plugin_settings=MocustomapiUtility::getConfiguration();
        $api_configuration= (array)json_decode($plugin_settings['mo_custom_apis']);
        unset($api_configuration[$post['api_name']]);
        $database_name = '#__miniorange_customapi_settings';
        $updatefieldsarray = array(
            'mo_custom_apis' => json_encode($api_configuration),
        );

        MocustomapiUtility::generic_update_query($database_name, $updatefieldsarray);
        $customer = new MocustomapiCustomer();
        $customer->submit_feedback_form('deleted custom API');
        $message='You have successfully deleted the '.$api_name.' API.';
        $this->setRedirect('index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=show_custom_apis',$message);
    }

    function createSQLAPI()
    {
        $post = JFactory::getApplication()->input->post->getArray();
        $post['api_call']=0;
        if(!isset($post['api_name']) || empty($post['api_name']))
        {
            $this->setRedirect('index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=create_custom_sql_api','Please enter the API name to create API', 'error');
            return;
        }

        $check_sql_api_exist=MocustomapiUtility::check_api_exist($post['api_name']);
        if($check_sql_api_exist && (isset($post['edit_api']) && $post['edit_api']!=1))
        {
            $this->setRedirect('index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=create_custom_sql_api','API with same name already exists', 'error');
            return;
        }
        else
        {
        $new_post=array($post['api_name']=>$post);
        $database_name = '#__miniorange_customapi_settings';
        $updatefieldsarray = array(
            'mo_custom_sql_apis' => json_encode($new_post),
        );

        MocustomapiUtility::generic_update_query($database_name, $updatefieldsarray);
        $customer = new MocustomapiCustomer();
        if((isset($post['edit_api']) && $post['edit_api']!=1))
        {
            $customer->submit_feedback_form('Created SQL API');
        }else 
        {
            $customer->submit_feedback_form('Edited SQL API'); 
        }
        
        $message='You have successfully created the '.$post['api_name'].' API.';
        $this->setRedirect('index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=view_custom_api&sql_api_name='.$post["api_name"].'',$message);
     }
       
    }

    function deleteSQLCurrentAPI()
    {
        $post = JFactory::getApplication()->input->post->getArray();
        $api_configuration =MocustomapiUtility::fetch_other_api_info($post['api_name'],'sql');
        $database_name = '#__miniorange_customapi_settings';
        $updatefieldsarray = array(
            'mo_custom_sql_apis' => json_encode($api_configuration),
        );

        MocustomapiUtility::generic_update_query($database_name, $updatefieldsarray);
        $customer = new MocustomapiCustomer();
        $customer->submit_feedback_form('deleted sql API');
        $message='You have successfully deleted the '.$post['api_name'].' API.';
        $this->setRedirect('index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=create_sql_apis',$message);
    }

    
    function configureExternalAPI()
    {

        $post = JFactory::getApplication()->input->post->getArray();
        if(!isset($post['api_name']) || empty($post['api_name']))
        {
            $this->setRedirect('index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=configure_external_apis','Please enter the API name to create API', 'error');
            return;
        }
        
        $external_api_query_key = array_key_exists('external_api_query_key', $post) ? $post['external_api_query_key'] : array();
        $external_api_query_val = array_key_exists('external_api_query_val', $post) ? $post['external_api_query_val'] : array();
        $external_api_header_key = array_key_exists('external_api_header_key', $post) ? $post['external_api_header_key'] : array();
        $external_api_header_val = array_key_exists('external_api_header_val', $post) ? $post['external_api_header_val'] : array();
        $external_api_body_key = array_key_exists('external_api_body_key', $post) ? $post['external_api_body_key'] : array();
        $external_api_body_val = array_key_exists('external_api_body_val', $post) ? $post['external_api_body_val'] : array();
        $query_params=MocustomapiUtility::getAttributes($external_api_query_key,$external_api_query_val);
        $query_params = json_encode($query_params);
        $api_header=MocustomapiUtility::getAttributes($external_api_header_key,$external_api_header_val);
        $api_header = json_encode($api_header);
        $api_body=MocustomapiUtility::getAttributes($external_api_body_key,$external_api_body_val);
        $api_body = json_encode($api_body);
        $post['query_params']=$query_params;
        $post['api_header'] = $api_header;
        $post['api_body'] = $api_body;
        $post['api_call']=0;
        $new_post=array($post['api_name']=>$post);
      
        $database_name = '#__miniorange_customapi_settings';
        $updatefieldsarray = array(
            'mo_external_apis' => json_encode($new_post),
        );

        MocustomapiUtility::generic_update_query($database_name, $updatefieldsarray);
        $customer = new MocustomapiCustomer();
        if((isset($post['edit_api']) && $post['edit_api']!=1))
        {
            $customer->submit_feedback_form('Configured External API');
        }else 
        {
            $customer->submit_feedback_form('Edited External API'); 
        }
        $message='You have successfully configured the '.$post['api_name'].' external API.'; 
        $this->setRedirect('index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=configure_external_apis&api_name='.$post["api_name"].'',$message);
 
    }

    function deleteCurrentExternalAPI()
    {
        $post = JFactory::getApplication()->input->post->getArray();
        $api_name=$post['api_name'];
        $api_configuration =MocustomapiUtility::fetch_other_api_info($post['api_name'],'external_api');
        $database_name = '#__miniorange_customapi_settings';
        $updatefieldsarray = array(
            'mo_external_apis' => json_encode($api_configuration),
        );

        MocustomapiUtility::generic_update_query($database_name, $updatefieldsarray);
        $customer = new MocustomapiCustomer();
        $customer->submit_feedback_form('deleted configured external API');
        $message='You have successfully deleted the '.$api_name.' configured external API.';
        $this->setRedirect('index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=custom_external_apis',$message);
    }
}