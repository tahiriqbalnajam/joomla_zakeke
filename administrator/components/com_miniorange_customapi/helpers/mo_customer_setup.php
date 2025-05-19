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
 * @package        miniOrange rolebasedredirection
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
/**
 * This library is miniOrange Authentication Service.
 * Contains Request Calls to Customer service.
 **/

defined('_JEXEC') or die('Restricted access');

class MocustomapiCustomer
{

    public $email;
    public $phone;
    public $customerKey;
    public $transactionId;

    /*
    ** Initial values are hardcoded to support the miniOrange framework to generate OTP for email.
    ** We need the default value for creating the OTP the first time,
    ** As we don't have the Default keys available before registering the user to our server.
    ** This default values are only required for sending an One Time Passcode at the user provided email address.
    */

    //auth
    private $defaultCustomerKey = "16555";
    private $defaultApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";

    function create_customer()
    {
        if (!MocustomapiUtility::is_curl_installed()) {
            return json_encode(array("apiKey"=>'CURL_ERROR','token'=>'<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
        }
        $hostname = MocustomapiUtility::getHostname();

        $url = $hostname . '/moas/rest/customer/add';
       
        $current_user = JFactory::getUser();
        $customer_details = MocustomapiUtility::getCustomerDetails();

        $this->email = isset($customer_details['email']) ? $customer_details['email'] : '';
        $this->phone = isset($customer_details['admin_phone']) ? $customer_details['admin_phone'] : '';
        $password = isset($customer_details['password']) ? $customer_details['password'] : '';

        $fields = array(
            'companyName' => $_SERVER['SERVER_NAME'],
            'areaOfInterest' => 'Joomla Custom API',
            'firstname' => $current_user->name,
            'lastname' => '',
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => $password
        );
        $field_string = json_encode($fields);

        return self::curl_call($url,$field_string);
    }

    function check_status($code){
		
		$hostname = MocustomapiUtility::getHostname();
		$url = $hostname . '/moas/api/backupcode/verify';

		$customer_details = MocustomapiUtility::getCustomerDetails();
    
		$customerKey = $customer_details['customer_key'];
		$apiKey = $customer_details['api_key'];
	
		$fields = '';
		$fields = array(
			'code' => $code ,
			'customerKey' => $customerKey,
			'additionalFields' => array(
				'field1' => JURI::root()	
			)
		);
	
		$field_string = json_encode($fields);

        return self::curl_call($url,$field_string);

	}


    function get_customer_key($email, $password)
    {
        if (!MocustomapiUtility::is_curl_installed()) {
            return json_encode(array("apiKey" => 'CURL_ERROR', 'token' => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
        }

        $hostname = MocustomapiUtility::getHostname();

        $url = $hostname . "/moas/rest/customer/key";

        $fields = array(
            'email' => $email,
            'password' => $password
        );
        $field_string = json_encode($fields);

        return self::curl_call($url,$field_string);
    }

    function submit_contact_us($q_email, $q_phone, $query)
    {
        if (!MocustomapiUtility::is_curl_installed()) {
            return json_encode(array("status" => 'CURL_ERROR', 'statusMessage' => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
        }
      
        $hostname = MocustomapiUtility::getHostname();
        $customer_details = MocustomapiUtility::getCustomerDetails();
        $customerKey = !empty($customer_details['customer_key'])?$customer_details['customer_key']:$this->defaultCustomerKey;
        $url = $hostname . "/moas/api/notify/send";
        $current_user = JFactory::getUser();
        $subject = "Joomla Custom API Free";
        $content='<div>Hello, <br><br><b>Company :</b><a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br><b>Email :</b><a href="mailto:'.$q_email.'" target="_blank">'.$q_email.'</a><br><br><b>Query: </b>' .$query. '</div>';
        $fields = array(
            'customerKey'	=> $customerKey,
            'sendEmail' 	=> true,
            'email' 		=> array(
                'customerKey' 	=> $customerKey,
                'fromEmail' 	=> $q_email,                
                'fromName' 		=> 'miniOrange',
                'toEmail' 		=> 'joomlasupport@xecurify.com',
                'toName' 		=> 'joomlasupport@xecurify.com',
                'subject' 		=> $subject,
                'content' 		=> $content
            ),
		);
        $field_string = json_encode($fields);
        return self::curl_call($url,$field_string);
    }

    function check_customer($email)
    {
        if (!MocustomapiUtility::is_curl_installed()) {
            return json_encode(array("status" => 'CURL_ERROR', 'statusMessage' => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
        }
        $hostname = MocustomapiUtility::getHostname();
        $url = $hostname . "/moas/rest/customer/check-if-exists";

        $fields = array(
            'email' => $email,
        );
        $field_string = json_encode($fields);

        return self::curl_call($url,$field_string);
    }


    function submit_feedback_form($action)
    {

        $hostname = MocustomapiUtility::getHostname();
        $url = $hostname . '/moas/api/notify/send';

        $customerKey = "16555";
        $apiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
        $customer_details=MocustomapiUtility::getCustomerDetails();
        $dVar=new JConfig();
        $check_email = $dVar->mailfrom;
        $admin_email = !empty($customer_details['admin_email']) ? $customer_details['admin_email'] :$check_email;
        $admin_phone  = isset($details ['admin_phone']) ? $details ['admin_phone'] : '';
        $j_cms_version = MocustomapiUtility::getJoomlaCmsVersion();
        $mo_plugin_version = MocustomapiUtility::GetPluginVersion();
        $php_version = phpversion();

        $ccEmail='arati.chaudhari@xecurify.com'; 
        $bccEmail='somshekhar@xecurify.com';
        $sys_information ='[ Plugin Name '.$mo_plugin_version.' | Joomla ' . $j_cms_version.' | PHP ' . $php_version.'] ';
        $content = '<div >Hello, <br><br>
                    <strong>Company :<a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" ></strong>' . $_SERVER['SERVER_NAME'] . '</a><br><br>
                    <strong>Phone Number :<strong>' . $admin_phone . '<br><br>
                    <strong>Admin Email :<a href="mailto:' . $admin_email . '" target="_blank">' . $admin_email . '</a></strong><br><br>
                    <strong>Action:</strong> '.$action .'<br><br>
                    <strong>System Information:</strong> '.$sys_information .'<br><br>';
        $subject = "miniOrange Joomla Custom API [Free] for Efficiency";

       

        $fields = array(
            'customerKey' => $customerKey,
            'sendEmail' => true,
            'email' => array(
                'customerKey' 	=> $customerKey,
                'fromEmail' 	=> $admin_email,
                'bccEmail' 		=> $bccEmail,
                'fromName' 		=> 'miniOrange',
                'toEmail' 		=> $ccEmail,
                'toName' 		=> $bccEmail,
                'subject' 		=> $subject,
                'content' 		=> $content
            ),
        );
        $field_string = json_encode($fields);

        self::curl_call($url,$field_string);

    }

    function curl_call($url,$field_string)
    {
        $ch = curl_init($url);
        $customer_details = MocustomapiUtility::getCustomerDetails();
        $customerKey = !empty($customer_details['customer_key'])?$customer_details['customer_key']:$this->defaultCustomerKey;
        $apiKey = !empty($customer_details['api_key'])?$customer_details['api_key']:$this->defaultApiKey;

        /* Current time in milliseconds since midnight, January 1, 1970 UTC. */
        $currentTimeInMillis = round(microtime(true) * 1000);
     
        /* Creating the Hash using SHA-512 algorithm */
        $stringToHash = $customerKey . number_format($currentTimeInMillis, 0, '', '') . $apiKey;
        $hashValue = hash("sha512", $stringToHash);

        $customerKeyHeader = "Customer-Key: " . $customerKey;
        $timestampHeader = "Timestamp: " . number_format($currentTimeInMillis, 0, '', '');
        $authorizationHeader = "Authorization: " . $hashValue;
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    # required for https urls

        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
            $timestampHeader, $authorizationHeader));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        $content = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Request Error:' . curl_error($ch);
            exit();
        }
        curl_close($ch);

        return $content;
    }

    
} ?>
