<?php
/**
 *
 * Data module for user fields
 *
 * @package	VirtueMart
 * @subpackage Userfields
 * @author Max Milbers
 * @author Oscar van Eijk
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2021 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: userfields.php 11068 2024-10-21 13:33:03Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Model class for user fields
 *
 * @package	VirtueMart
 * @subpackage Userfields
 * @author RolandD
 */
class VirtueMartModelUserfields extends VmModel {

	// stAn, this variable is a cached result of  getUserFields
	// where array key is $cache_hash = md5($sec.serialize($_switches).serialize($_skip).$this->_selectedOrdering.$this->_selectedOrderingDir);
    static $_cache_ordered;
	// this variable is a cached result of named fields of last call of getUserFields where the key is $_sec of the function ('registration', 'account', 'shipping'.. etc...)
	// example $_cached_named['registration']['email']
	static $_cache_named;
	// *** code for htmlpurifier ***
	// var $htmlpurifier = '';

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct('virtuemart_userfield_id');
		vmLanguage::loadJLang('com_virtuemart_shoppers',TRUE);
		$this->setMainTable('userfields');

		$this->setToggleName('required');
		$this->setToggleName('cart');
		$this->setToggleName('shipment');
		$this->setToggleName('account');
		// Instantiate the Helper class

		self::$_cache_ordered = null;
		self::$_cache_named = array();

		$this->_selectedOrdering = 'ordering';
		$this->_selectedOrderingDir = 'ASC';
	}


	/**
	 * Prepare a user field for database update
	 */
	public function prepareFieldDataSave($field, &$data, $prefix = '') {


		$fieldType = $field->type;
		$fieldName = $field->name;
		$value = vRequest::getString($prefix.$fieldName,'', $data);//$data[$field->name];
		$params = $field->userfield_params;

		switch(strtolower($fieldType)) {
			case 'webaddress':
				$post = vRequest::getPost();
				if (isset($post[$prefix.$fieldName."Text"]) && ($post[$prefix.$fieldName."Text"])) {
					$oValuesArr = array();
					$oValuesArr[0] = str_replace(array('mailto:','http://','https://'),'', $value);
					$oValuesArr[1] = str_replace(array('mailto:','http://','https://'),'', $post[$prefix.$fieldName."Text"]);
					$value = implode("|*|",$oValuesArr);
				}
				else {
					if ($value = vRequest::filterUrl($value) )
						$value = str_replace(array('mailto:','http://','https://'),'', $value);
				}
				break;
			case 'email':
			case 'emailaddress':
				//vmdebug('emailaddress before filter',$value);
				$value = vRequest::filter( $value, FILTER_VALIDATE_EMAIL, FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH );
				//$value = str_replace('mailto:','', $value);
				//$value = str_replace(array('\'','"',',','%','*','/','\\','?','^','`','{','}','|','~'),array(''),$value);
				//vmdebug('emailaddress after filter',$value);
				break;
			// case 'phone':
				// $value = vmFilter::phone( $value );
				// break;
			case 'multiselect':
			case 'multicheckbox':
			case 'select':
				if (is_array($value)) {
					$value = implode("|*|",$value);
				} else {
					if($fieldName=='virtuemart_state_id'){
						$value = (int) $value;
					}
				}
				break;
			/*case 'age_verification':
				$value = vRequest::getInt('birthday_selector_year')
				.'-'.vRequest::getInt('birthday_selector_month')
				.'-'.vRequest::getInt('birthday_selector_day');
				break;*/
			case 'textarea':
				if(vmAccess::manager('html')){
					$value = vRequest::getHtml($prefix.$fieldName,'', $data);
				} else {
					$value = vRequest::getString($prefix.$fieldName,'', $data);
				}
				break;
			case 'editorta':
				if(vmAccess::manager('html')){
					$value = vRequest::getHtml($prefix.$fieldName,'', $data);
				} else {
					$value = vRequest::getString($prefix.$fieldName,'', $data);
				}
				break;
			default:

				if (strpos($fieldType,'plugin')!==false){

					vDispatcher::importVMPlugins('vmuserfield');
					vDispatcher::trigger('plgVmPrepareUserfieldDataSave',array($fieldType, $fieldName, &$data, &$value, $params) );
					return $value;
				}

			// no HTML TAGS but permit all alphabet

			//$value = vRequest::getString($prefix.$fieldName,'');

			break;
		}
		return $value;
	}

	/**
	 * Retrieve the detail record for the current $id if the data has not already been loaded.
	 */
	function getUserfield($id = null,$name = '') {

		if(isset($id)){
			$id = $this->setId($id);
		} else if(empty($this->_id)){
			$id = $this->setIdByRequest();
		}

		$hash = $id.$name;
		if (empty($this->_cache[$hash])) {
			$this->_cache[$hash] = $this->getTable('userfields');
			if($name !==''){
				$this->_cache[$hash]->load($id, $name);
			} else {
				$this->_cache[$hash]->load($id);
			}
			//vmdebug('getUserfield',$id,$name,$this->_cache[$id]);
			if(strpos($this->_cache[$hash]->type,'plugin')!==false){
				vDispatcher::importVMPlugins('vmuserfield');
				$plgName = substr($this->_cache[$hash]->type,6);
				$retValue = vDispatcher::directTrigger('vmuserfield', $plgName,'plgVmDeclarePluginParamsUserfieldVM3',array(&$this->_cache[$hash]), false);
			}
			/*else if($this->_cache[$hash]->type=='age_verification'){

			}*/
			if(!empty($this->_cache[$hash]->_varsToPushParam)){
				VmTable::bindParameterable($this->_cache[$hash],'userfield_params',$this->_cache[$hash]->_varsToPushParam);
			}
		}
		return $this->_cache[$hash];
	}


	/**
	 * Retrieve the value records for the current $id if available for the current type
	 *
	 * Updated by stAn to get userfieldvalues per specific id regardless on this->_id
	 *
	 * @return array List wil values, or an empty array if none exist
	 */
	function getUserfieldValues($id=null)
	{
	    if (empty($id)) $id = $this->_id;
		//$this->_data = $this->getTable('userfield_values');
		if ($id > 0) {
			$query = 'SELECT * FROM `#__virtuemart_userfield_values` WHERE `virtuemart_userfield_id` = ' . (int)$id
			. ' ORDER BY `ordering`';
			$_userFieldValues = $this->_getList($query);
			return $_userFieldValues;
		} else {
			return array();
		}
	}

	static function getCoreFields(){
		return array( 'name','username', 'email', 'password', 'password2');// , 'agreed');
	}

	/**
	 * Bind the post data to the userfields table and save it
	 *
	 * @return boolean True is the save was successful, false otherwise.
	 */
	function store(&$data){

		if(!vmAccess::manager('userfields')){
			vmWarn('Insufficient permissions to store userfield');
			return false;
		}

		if(!is_array($data)) $data = (array)$data;

		$field      = $this->getTable('userfields');
		$userinfo   = $this->getTable('userinfos');
		$orderinfo  = $this->getTable('order_userinfos');
		
		$data['virtuemart_userfield_id'] = (int)$data['virtuemart_userfield_id'];
		
		$isNew = ($data['virtuemart_userfield_id'] < 1) ? true : false;

		$coreFields = $this->getCoreFields();
		if(in_array($data['name'],$coreFields)){
			$field->load($data['virtuemart_userfield_id']);
			//vmError('Cant store/update core field. They belong to joomla');
			//return false;
		} else {
			if ($isNew) {
				$reorderRequired = false;
				$_action = 'ADD';
			} else {
				
				$field->load($data['virtuemart_userfield_id']);
				$_action = 'CHANGE';

				if ($field->ordering == $data['ordering']) {
					$reorderRequired = false;
				} else {
					$reorderRequired = true;
				}
			}
		}

		$virtuemart_userfield_value_id = isset($data['virtuemart_userfield_value_id'])? $data['virtuemart_userfield_value_id']: 0;
		// Store the fieldvalues, if any, in a correct array
		$fieldValues = $this->postData2FieldValues($data['vNames'], $data['vValues'], $data['virtuemart_userfield_id'], $virtuemart_userfield_value_id );

		if(strpos($data['type'],'plugin')!==false){

			$tb = '#__extensions';
			$ext_id = 'extension_id';
			
			$db = JFactory::getDBO();
			
			$plgName = substr($data['type'],6);
			$q = 'SELECT `' . $ext_id . '` FROM `' . $tb . '` WHERE `folder` = "vmuserfield" and `state`="0" AND `element` = "'.$db->escape($plgName).'"';


			$db->setQuery($q);
			$data['userfield_jplugin_id'] = $db->loadResult();

			$q = 'UPDATE `#__extensions` SET `enabled`= 1 WHERE `extension_id` = "'.(int)$data['userfield_jplugin_id'].'"';
			$db->setQuery($q);
			$db->execute();

			vDispatcher::importVMPlugins('vmuserfield');
			vDispatcher::trigger('plgVmOnBeforeUserfieldSave',array( $plgName , &$data, &$field ) );
		}

		if (!$field->bind($data)) {
			// Bind data
			vmError($field->getError());
			return false;
		}

		//We need this value in the check, so we add it to the table with _
		$field->_nrOfValues = count($fieldValues);
		if (!$field->check()) {
			// Perform data checks
			//vmError($field->getError());
			return false;
		}

		// Get the fieldtype for the database
		$_fieldType = $field->formatFieldType($data);

		if(!in_array($data['name'],$coreFields) && $field->type != 'delimiter'){

			// Alter the user_info table
			if (!$userinfo->_modifyColumn ($_action, $data['name'], $_fieldType)) {
				vmError('userfield store modifyColumn userinfo',$userinfo->getError());
				return false;
			}

			// Alter the order_userinfo table
			if (!$orderinfo->_modifyColumn ($_action, $data['name'], $_fieldType)) {
				vmError('userfield store modifyColumn orderinfo',$orderinfo->getError());
				return false;
			}
		}

		// if new item, order last in appropriate group
		if ($isNew) {
			$field->ordering = $field->getNextOrder();
		}

		$_id = $field->store();
		$_id = $field->virtuemart_userfield_id;
		if ($_id === false) {
			return false;
		}

		if (!$this->storeFieldValues($fieldValues, $_id)) {
			return false;
		}
		if(strpos($data['type'],'plugin')!==false){
			vDispatcher::directTrigger('vmuserfield', $plgName, 'plgVmOnStoreInstallPluginTable', array('userfield' , $data, $field));
		}
		if ($reorderRequired) {
			$field->reorder();
		}
		vmdebug('storing userfield',$_id);
		// Alter the user_info database to hold the values

		return $_id;
	}

	/**
	 * Bind and write all value records
	 *
	 * @param array $_values
	 * @param mixed $_id If a new record is being inserted, it contains the virtuemart_userfield_id, otherwise the value true
	 * @return boolean
	 */
	private function storeFieldValues($_values, $virtuemart_userfield_id) {


		$fieldvalue = $this->getTable('userfield_values');

		// get original values
		$originalvalues = $this->getUserfieldValues($virtuemart_userfield_id);
		vmdebug('storeFieldValues',$virtuemart_userfield_id, $_values, $originalvalues);

		$toKeep = array();
		for ($i = 0; $i < count($_values); $i++) {
			$fieldvalue->virtuemart_userfield_value_id = null;
			$fieldvalue->virtuemart_userfield_id = null;
			vmdebug('storeFieldValues storing ',$_values[$i]);
			if(!empty($_values[$i]['fieldtitle']) and !empty($_values[$i]['fieldvalue'])){
				$_values[$i]['virtuemart_userfield_id'] = (int)$virtuemart_userfield_id;
				if (!$fieldvalue->bindChecknStore($_values[$i])) {
					vmError('There was a problem storing the userfield value ');
					return false;
				} else {
					$toKeep[] = $fieldvalue->virtuemart_userfield_value_id;
				}
			}

		}
		vmdebug('storeFieldValues $toKeep',$toKeep);
		foreach($originalvalues as $j=>$field){
			if(!in_array($field->virtuemart_userfield_value_id, $toKeep)){
				vmdebug('storeFieldValues not in keep, lets delete $toKeep',$field->virtuemart_userfield_value_id, $toKeep);
				$db = JFactory::getDBO();
				$q = 'DELETE from `#__virtuemart_userfield_values` WHERE `virtuemart_userfield_value_id` = ' . (int)$field->virtuemart_userfield_value_id;

				$db->setQuery($q);
				if ($db->execute() === false) {
					vmError($db->getError());
					return false;
				}

			}
		}

		return true;
	}

	/**
	 *
	 * @author Max Milbers
	 */
	public function getUserFieldsFor($layoutName, $type, $register = null){

		static $c = array();
 		//vmdebug('getUserFieldsFor '.$layoutName.' '. $type .' ' . $userId);

		if($register === null){
			$register = false;
			if(VmConfig::get('oncheckout_show_register',1) and $type=='BT'){
				$user = JFactory::getUser();
				if(!empty($user)){
					if(empty($user->id)){
						$register = true;
					}
				} else {
					$register = true;
				}
			}
		}

		$h = $layoutName.$type.(int)$register;
		//return cached
		if(isset($c[$h])){
			//vmTrace('getUserFieldsFor');
			return $c[$h];
		}

		$skips = array();
		//Maybe there is another method to define the skips
		$skips[] = 'address_type';

		$corefields = $this->getCoreFields();
		unset($corefields[2]); //the 2 is for the email field, it is necessary in almost anycase.

		if((!$register or $type =='ST') and $layoutName !='edit'){
			$skips[] = 'name';
			$skips[] = 'username';
			$skips[] = 'password';
			$skips[] = 'password2';
			$skips[] = 'user_is_vendor';
			//$skips[] = 'agreed';
			// MattLG: Added this line because it leaves the empty fieldset with just the label when editing the ST addresses
			// A better solution might be to make this a setting rather than hard coding this whole block here
			$skips[] = 'delimiter_userinfo';
		}

		//Here we get the fields
		if ($type == 'BT') {
			$userFields = $this->getUserFields(
				'account'
			,	array() // Default toggles
			,	$skips// Skips
			);
		} else {
			$userFields = $this->getUserFields(
				 'shipment'
			, array() // Default toggles
			, $skips
			);
		}


		//Small ugly hack to make registering optional //do we still need that? YES !  notice by Max Milbers
		if($register and $type == 'BT' and VmConfig::get('oncheckout_show_register',1) and !VmConfig::get('oncheckout_only_registered',1) and $layoutName!='edit'){
			//vmdebug('Going to set core fields unrequired');
			foreach($userFields as $field){
				if(in_array($field->name,$corefields)){
					if($field->required){
						$field->register = 1;
					}
					$field->required = 0;
					$field->value = '';
					$field->default = '';
				}
			}
		}

		if(!$register and $type!='ST'){
			//vmdebug('Going to set pw fields unrequired');
			foreach($userFields as $field){
				if($field->name == 'password' or $field->name == 'password2'){
					$field->required = 0;
					$field->value = '';
					$field->default = '';

				}
			}
		}

		vDispatcher::importVMPlugins('vmuserfield');
		JPluginHelper::importPlugin('user');

		vDispatcher::trigger('plgVmOnGetUserfields', array($type, &$userFields));

		$c[$h] = $userFields;
		return $userFields;
	}
	/**
	 * Retrieve an array with userfield objects
	 *
	 * @param string $section The section the fields belong to (e.g. 'registration' or 'account')
	 * @param array $_switches Array to toggle these options:
	 *                         * published    published fields only (default: true)
	 *                         * required     Required fields only (default: false)
	 *                         * delimiters   Exclude delimiters (default: false)
	 *                         * captcha      Exclude Captcha type (default: false)
	 *                         * system       System fields filter (no default; true: only system fields, false: exclude system fields)
	 * @param array $_skip Array with fieldsnames to exclude. Default: array('username', 'password', 'password2', 'agreed'),
	 *                     specify array() to skip nothing.
	 * @see getUserFieldsFilled()
	 * @author Oscar van Eijk
	 * @return array
	 */
	public function getUserFields ($_sec = 'registration', $_switches=array(), $_skip = array('username', 'password', 'password2'))
	{

		if(is_array($_sec)){
			$sec = implode ( $_sec);
		} else {
			$sec = $_sec;
		}
		$cache_hash = crc32($sec.json_encode($_switches).json_encode($_skip).$this->_selectedOrdering.$this->_selectedOrderingDir);
		if (isset(self::$_cache_ordered[$cache_hash])) return self::$_cache_ordered[$cache_hash];

		$_q = 'SELECT * FROM `#__virtuemart_userfields` WHERE 1 = 1 ';

		if( !empty($_sec)) {
			if(is_array($_sec)){
				$_q .= 'AND ( ' . implode ('="1" OR ', $_sec) . '="1" ) ';
			} else {
				$_q .= 'AND `'.$_sec.'`="1" ';
			}
		}

		if(array_key_exists('published',$_switches)){
			if ($_switches['published'] !== false ) {
				$_q .= 'AND published = 1 ';
			}
		} else {
			$_q .= 'AND published = 1 ';
		}
		if(array_key_exists('required',$_switches)){
			if ($_switches['required'] === true ) {
				$_q .= "AND required = 1 ";
			}
		}
		if(array_key_exists('delimiters',$_switches)){
			if ($_switches['delimiters'] === true ) {
				$_q .= "AND type != 'delimiter' ";
			}
		}
		if(array_key_exists('captcha',$_switches)){
			if ($_switches['captcha'] === true ) {
				$_q .= "AND type != 'captcha' ";
			}
		}
		if(array_key_exists('sys',$_switches)){
			if ($_switches['sys'] === true ) {
				$_q .= "AND sys = 1 ";
			} else {
				$_q .= "AND sys = 0 ";
			}
		}

		if(array_key_exists('query',$_switches)){
			if (!empty($_switches['query'] )) {
				$_q = $_switches['query'];
			} else {

			}
		}

		if (count($_skip) > 0) {
			$_q .= "AND FIND_IN_SET(name, '".implode(',', $_skip)."') = 0 ";
		}
		$_q .= ' ORDER BY ordering ';

		$_fields = $this->_getList($_q,0,0,'name');
		//vmdebug('Get userfield list query ',$_q,$_fields);
		// We need some extra fields that are not in the userfields table. They will be hidden on the details form
		if (!in_array('address_type', $_skip)) {
			$_address_type = new stdClass();
			$_address_type->virtuemart_userfield_id = 0;
			$_address_type->name = 'address_type';
			$_address_type->title = '';
			$_address_type->description = '' ;
			$_address_type->type = 'hidden';
			$_address_type->maxlength = 0;
			$_address_type->size = 0;
			$_address_type->required = 0;
			$_address_type->ordering = 0;
			$_address_type->cols = 0;
			$_address_type->rows = 0;
			$_address_type->value = '';
			$_address_type->default = 'BT';
			$_address_type->published = 1;
			$_address_type->registration = 1;
			$_address_type->shipment = 0;
			$_address_type->account = 1;
			$_address_type->readonly = 0;
			$_address_type->calculated = 0; // what is this???
			$_address_type->sys = 0;
			$_address_type->virtuemart_vendor_id = 1;
			$_address_type->userfield_params = '';
			$_fields[] = $_address_type;
		}
		// stAn: slow to run the first time:
		self::$_cache_ordered[$cache_hash] = $_fields;
		if (!isset(self::$_cache_named[$sec]))
		self::$_cache_named[$sec] = array();
		foreach ($_fields as &$f)
		 {
		    self::$_cache_named[$sec][$f->name] = $f;
		 }

		return $_fields;
	}

	/**
	 * Return a boolean whethe the userfield is enabled in context of $_sec
	 *
	 * @access public
	 * @param $_field_name: name of the user field such as 'email'
	 * @param $_sec BT or ST, or one of the types of the fields: account, shipment, registration
	 * @author stAn
	 * @return true or false
	 *
	 * Note: this function will return a false result for skipped fields such as agreed, user_is_vendor
	 *
	 * when used from shipment method, you can use
	 * $userFieldsModel =VmModel::getModel('Userfields');
	 * $type = (($cart->STsameAsBT == 0) ? 'ST' : 'BT');
	 * if ($userFieldsModel->fieldPublished('zip', $type)) ....
	*/
	public function fieldPublished($_field_name, $_sec='account')
	 {
		if ($_sec == 'BT') $_sec = 'account';
		else
		if ($_sec == 'ST') $_sec = 'shipment';
		if (isset(self::$_cache_named[$_sec])) return isset(self::$_cache_named[$_sec][$_field_name]);
		$this->getUserFields($_sec, array(), array());
		if (isset(self::$_cache_named[$_sec])) return isset(self::$_cache_named[$_sec][$_field_name]);

		return false;
	 }

	/**
	 * Return an array with userFields in several formats.
	 *
	 * @access public
	 * @param $_selection An array, as returned by getuserFields(), with fields that should be returned.
	 * @param $_userData Array with userdata holding the values for the fields
	 * @param $_prefix string Optional prefix for the formtag name attribute
	 * @author Oscar van Eijk
	 * @return array List with all userfield data in the format:
	 * array(
	 *    'fields' => array(   // All fields
	 *                   <fieldname> => array(
	 *                                     'name' =>       // Name of the field
	 *                                     'value' =>      // Existing value for the current user, or the default
	 *                                     'title' =>      // Title used for label and such
	 *                                     'type' =>       // Field type as specified in the userfields table
	 *                                     'hidden' =>     // True/False
	 *                                     'required' =>   // True/False. If True, the formcode also has the class "required" for the Joomla formvalidator
	 *                                     'formcode' =>   // Full HTML tag
	 *                                  )
	 *                   [...]
	 *                )
	 *    'functions' => array() // Optional javascript functions without <script> tags.
	 *                           // Possible usage: if (count($ar('functions')>0) echo '<script ...>'.join("\n", $ar('functions')).'</script>;
	 *    'scripts'   => array(  // Array with scriptsources for use with JHtml::script();
	 *                      <name> => <path>
	 *                      [...]
	 *                   )
	 *    'links'     => array(  // Array with stylesheets for use with JHtml::stylesheet();
	 *                      <name> => <path>
	 *                      [...]
	 *                   )
	 * )
	 * @example This example illustrates the use of this function. For additional examples, see the Order view
	 * and the User view in the administrator section.
	 * <pre>
	 *   // In the controller, make sure this model is loaded.
	 *   // In view.html.php, make the following calls:
	 *   $_usrDetails = getUserDetailsFromSomeModel(); // retrieve an user_info record, eg from the usermodel or ordermodel
	 *   $_usrFieldList = $userFieldsModel->getUserFields(
	 *                    'registration'
	 *                  , array() // Default switches
	 *                  , array('delimiter_userinfo', 'username', 'email', 'password', 'password2', 'agreed', 'address_type') // Skips
	 *    );
	 *   $usrFieldValues = $userFieldsModel->getUserFieldsFilled(
	 *                      $_usrFieldList
	 *                     ,$_usrDetails
	 *   );
	 *   $this-userfields= $userfields;
	 *   // In the template, use code below to display the data. For an extended example using
	 *   // delimiters, JavaScripts and StyleSheets, see the edit_shopper.php in the user view
	 *   <table class="admintable" width="100%">
	 *     <thead>
	 *       <tr>
	 *         <td class="key" style="text-align: center;"  colspan="2">
	 *            <?php echo vmText::_('COM_VIRTUEMART_TABLE_HEADER') ?>
	 *         </td>
	 *       </tr>
	 *     </thead>
	 *      <?php
	 *        foreach ($this->shipmentfields['fields'] as $_field ) {
	 *          echo '  <tr>'."\n";
	 *          echo '    <td class="key">'."\n";
	 *          echo '      '.$_field['title']."\n";
	 *          echo '    </td>'."\n";
	 *          echo '    <td>'."\n";
	 *
	 *          echo '      '.$_field['value']."\n";    // Display only
	 *       Or:
	 *          echo '      '.$_field['formcode']."\n"; // Input form
	 *
	 *          echo '    </td>'."\n";
	 *          echo '  </tr>'."\n";
	 *        }
	 *      ?>
	 *    </table>
	 * </pre>
	 */
	public function getUserFieldsFilled($_selection, &$_userDataIn = null, $_prefix = '', $defaults = false){

		vmLanguage::loadJLang('com_virtuemart_shoppers',TRUE);

		//We copy the input data to prevent that objects become arrays
		if(empty($_userDataIn)){
			$_userData = array();
		} else {
			$_userData = $_userDataIn;
			$_userData=(array)($_userData);
		}

		$_return = array(
				 'fields' => array()
				,'byDefault' => array()
		);

		$admin = vmAccess::manager();


		if (is_array($_selection)) {

			foreach ($_selection as $_fld) {

				$yOffset = 0;

				if(!empty($_userDataIn) and isset($_fld->default) and $_fld->default!=''){
					if(is_array($_userDataIn)){
						if(!isset($_userDataIn[$_fld->name])){
							$_userDataIn[$_fld->name] = $_fld->default;
							$_return['byDefault'][$_fld->name] = 1;
						}
					} else {
						if(!isset($_userDataIn->{$_fld->name})){
							$_userDataIn->{$_fld->name} = $_fld->default;
							$_return['byDefault'][$_fld->name] = 1;
						}
					}
				}

				if($_userData == null || !array_key_exists($_fld->name, $_userData)){

					if(empty($_fld->default)){
						$valueO = $valueN = $_fld->default;
					} else {
						$_return['byDefault'][$_fld->name] = 1;
						$valueO = $valueN = vmText::_($_fld->default);
					}
				} else {
					$valueO = $valueN = $_userData[$_fld->name];
				}


				//TODO htmlentites creates problems with non-ascii chars, which exists as htmlentity, for example äöü

				if ((!empty($valueN)) && (is_string($valueN))) $valueN = vRequest::vmSpecialChars($valueN);	//was htmlentities

				$_return['fields'][$_fld->name] = array(
					     'name' => $_prefix . $_fld->name
				,'value' => $valueN // htmlspecialchars (was htmlentities) encoded value for all except editorarea and plugins
				,'unescapedvalue'=> $valueO
				,'title' => vmText::_($_fld->title)
				,'type' => $_fld->type
				,'required' => $_fld->required
				,'hidden' => false
				,'formcode' => ''
				,'description' => vmText::_($_fld->description)
				,'register' => (isset($_fld->register)? $_fld->register:0)
				,'htmlentities' => true  // to provide version check agains previous versions
				);

				//I wonder about this one, maybe forgotten debugging code?
				if($defaults and $_fld->name!='virtuemart_country_id' and $_fld->name!='virtuemart_state_id') continue;

				$placeholder = '';
				if( !empty($_fld->placeholder) ) $placeholder = 'placeholder="'.vmText::_($_fld->placeholder).'"';

				//Set the default on the data
				/*if(isset($_userData) and empty($_userData[$_fld->name]) and isset($_fld->default) and $_fld->default!='' ){
					$_userData[$_fld->name] = $_fld->default;
				}*/
				$readonly = '';
				if(!$admin){
					if($_fld->readonly ){
						$readonly = ' readonly="readonly" ';
					}
				}
 				//vmdebug ('getUserFieldsFilled',$_fld->name,$_return['fields'][$_fld->name]['value']);
				// 			if($_fld->name==='email') vmdebug('user data email getuserfieldbyuser',$_userData);
				// First, see if there are predefined fields by checking the name
				switch( $_fld->name ) {

					// 				case 'email':
					// 					$_return['fields'][$_fld->name]['formcode'] = $_userData->email;
					// 					break;
					case 'virtuemart_country_id':

						VmLanguage::loadJLang('com_virtuemart_countries');
						$attrib = array();
						if ($_fld->size) {
							$attrib = array('style'=>"width: ".$_fld->size."px");
						}


						if(!$defaults) {
							if($admin){
								$vmUserTable = VmModel::getTable('vmusers');
								$user = JFactory::getUser();
								$vmUserTable->load($user->id);
								if($vmUserTable->user_is_vendor){
									$attrib['unpublished'] = 1;
								}

							}

							$_return['fields'][$_fld->name]['formcode'] = ShopFunctionsF::renderCountryList($_return['fields'][$_fld->name]['value'], false, $attrib , $_prefix, $_fld->required,'virtuemart_country_id_field');
						}

						if(!empty($_return['fields'][$_fld->name]['value'])){
							// Translate the value from ID to name
							$_return['fields'][$_fld->name]['virtuemart_country_id'] = (int)$_return['fields'][$_fld->name]['value'];

							$countryT = $this->getTable('countries');
							$r = $countryT->load($_return['fields'][$_fld->name]['value'])->loadFieldValues();

							if($r){
								$_return['fields'][$_fld->name]['value'] = !empty($r['country_name'])? $r['country_name']:'' ;
								$_return['fields'][$_fld->name]['country_2_code'] = !empty($r['country_2_code'])? $r['country_2_code']:'' ;
								$_return['fields'][$_fld->name]['country_3_code'] = !empty($r['country_3_code'])? $r['country_3_code']:'' ;

								$lang = vmLanguage::getLanguage();
								$prefix="COM_VIRTUEMART_COUNTRY_";
								if( $lang->hasKey($prefix.$_return['fields'][$_fld->name]['country_3_code']) ){
									$_return['fields'][$_fld->name]['value'] = vmText::_($prefix.$_return['fields'][$_fld->name]['country_3_code']);
								}
							} else {
								vmError('Model Userfields, country with id '.$_return['fields'][$_fld->name]['value'].' not found');
							}
						} else {
							$_return['fields'][$_fld->name]['value'] = '' ;
							$_return['fields'][$_fld->name]['country_2_code'] = '' ;
							$_return['fields'][$_fld->name]['country_3_code'] = '' ;
						}

						//$_return['fields'][$_fld->name]['value'] = vmText::_(shopFunctions::getCountryByID($_return['fields'][$_fld->name]['value']));
						//$_return['fields'][$_fld->name]['state_2_code'] = vmText::_(shopFunctions::getCountryByID($_return['fields'][$_fld->name]['value']));
						break;

					case 'virtuemart_state_id':

						if(!$defaults) {
							$attrib = array();
							if ($_fld->size) {
								$attrib = array('style'=>"width: ".$_fld->size."px");
							}
							$_return['fields'][$_fld->name]['formcode'] =
							shopFunctionsF::renderStateList(	$_return['fields'][$_fld->name]['value'],
							$_prefix,
							false,
							$_fld->required,
							$attrib,
							'virtuemart_state_id_field'
							);
						}

						if(!empty($_return['fields'][$_fld->name]['value'])){
							// Translate the value from ID to name
							$_return['fields'][$_fld->name]['virtuemart_state_id'] = (int)$_return['fields'][$_fld->name]['value'];
							$stateM = VmModel::getModel('state');
							$r = $stateM->getSingleState($_return['fields'][$_fld->name]['value'])->loadFieldValues();
							if($r){
								$_return['fields'][$_fld->name]['value'] = !empty($r['state_name'])? $r['state_name']:'' ;
								$_return['fields'][$_fld->name]['state_2_code'] = !empty($r['state_2_code'])? $r['state_2_code']:'' ;
								$_return['fields'][$_fld->name]['state_3_code'] = !empty($r['state_3_code'])? $r['state_3_code']:'' ;
							} else {
								vmError('Model Userfields, state with id '.$_return['fields'][$_fld->name]['value'].' not found');
							}
						} else {
							$_return['fields'][$_fld->name]['value'] = '' ;
							$_return['fields'][$_fld->name]['state_2_code'] = '' ;
							$_return['fields'][$_fld->name]['state_3_code'] = '' ;
						}

						//$_return['fields'][$_fld->name]['value'] = shopFunctions::getStateByID($_return['fields'][$_fld->name]['value']);
						break;
						//case 'agreed':
						//	$_return['fields'][$_fld->name]['formcode'] = '<input type="checkbox" id="'.$_prefix.'agreed_field" name="'.$_prefix.'agreed" value="1" '
						//		. ($_fld->required ? ' class="required"' : '') . ' />';
						//	break;
					case 'password':
					case 'password2':
						$req = $_fld->required ? 'required' : '';
						$class = 'class="validate-password '.$req.' inputbox form-control"';
						$_return['fields'][$_fld->name]['formcode'] = '<input type="password" id="' . $_prefix.$_fld->name . '_field" name="' . $_prefix.$_fld->name .'" size="30" '.$class.' '.$placeholder.' />'."\n";
					break;
						break;

					//case 'agreed':
					//case 'tos':


						break;
						// It's not a predefined field, so handle it by it's fieldtype
					default:
						if(strpos($_fld->type,'plugin')!==false){

							vDispatcher::importVMPlugins('vmuserfield');
							
							$_return['fields'][$_fld->name]['value'] = $_return['fields'][$_fld->name]['unescapedvalue']; 
							$_return['fields'][$_fld->name]['htmlentities'] = false;
							vDispatcher::trigger('plgVmOnUserfieldDisplay',array($_prefix, $_fld,isset($_userData['virtuemart_user_id'])?$_userData['virtuemart_user_id']:0,  &$_return) );
							break;
						}
					switch( $_fld->type ) {
						case 'hidden':
							$_return['fields'][$_fld->name]['formcode'] = '<input type="hidden" id="'
							. $_prefix.$_fld->name . '_field" name="' . $_prefix.$_fld->name.'" size="' . $_fld->size
							. '" value="' . $_return['fields'][$_fld->name]['value'] .'" '
							. ($_fld->required ? ' class="required"' : '')
							. ($_fld->maxlength ? ' maxlength="' . $_fld->maxlength . '"' : '')
							. $readonly . ' /> ';
							$_return['fields'][$_fld->name]['hidden'] = true;
							break;
						case 'age_verification':
							// Year range MUST start 100 years ago, for birthday
							$currentYear = intval(date('Y'));
							$yOffset = 120;
							$maxmin = 'minDate: "-'.$yOffset.'y", maxDate: "+'.$yOffset.'Y",';
						case 'date':
							$currentYear = intval(date('Y'));
							if(empty($maxmin))$maxmin = 'minDate: -0, maxDate: "+1Y",';
							$_return['fields'][$_fld->name]['formcode'] = vmJsApi::jDate($_return['fields'][$_fld->name]['value'],  $_prefix.$_fld->name,$_prefix.$_fld->name . '_field',false,($currentYear-$yOffset).':'.($currentYear+1),$maxmin);

							$maxmin = '';
							break;
						case 'emailaddress':

							if( VmConfig::isSite()) {
								if(empty($_return['fields'][$_fld->name]['value']) && $_fld->required) {
									$_return['fields'][$_fld->name]['value'] = JFactory::getUser()->email;
								}

								$_return['fields'][$_fld->name]['formcode'] = '<input type="email" id="'
								. $_prefix.$_fld->name . '_field" name="' . $_prefix.$_fld->name.'" size="' . $_fld->size
								. '" value="' . $_return['fields'][$_fld->name]['value'] .'" '
								. ($_fld->required ? ' class="required validate-email form-control"' : ' class="form-control"')
								. ($_fld->maxlength ? ' maxlength="' . $_fld->maxlength . '"' : '')
								. $readonly . '  '.$placeholder.' /> ';
								break;
							}

						case 'text':
						case 'webaddress':

							$_return['fields'][$_fld->name]['formcode'] = '<input type="text" id="'
							. $_prefix.$_fld->name . '_field" name="' . $_prefix.$_fld->name.'" size="' . $_fld->size
							. '" value="' . $_return['fields'][$_fld->name]['value'] .'" '
							. ($_fld->required ? ' class="form-control required"' : ' class="form-control"')
							. ($_fld->maxlength ? ' maxlength="' . $_fld->maxlength . '"' : '')
							. $readonly . ' '.$placeholder.' /> ';

							if($_fld->type == 'webaddress' and !empty($valueN) ){
								$_return['fields'][$_fld->name]['value'] = '<a href="'.$valueN.'">'.$valueN.'</a>';
							}
							break;
						case 'textarea':
							$_return['fields'][$_fld->name]['formcode'] = '<textarea id="'
							. $_prefix.$_fld->name . '_field" name="' . $_prefix.$_fld->name . '" cols="' . $_fld->cols
							. '" rows="'.$_fld->rows . '" class="inputbox form-control'.($_fld->required ? ' required': '' ).'" '
							. ($_fld->maxlength ? ' maxlength="' . $_fld->maxlength . '"' : '')
							. $readonly.' '.$placeholder.' >'
							. $_return['fields'][$_fld->name]['value'] .'</textarea>';
							break;
						case 'editorta':
							jimport( 'joomla.html.editor' );

							$_return['fields'][$_fld->name]['value'] = $_return['fields'][$_fld->name]['unescapedvalue']; 
							$_return['fields'][$_fld->name]['htmlentities'] = false; 
							$_return['fields'][$_fld->name]['formcode'] = VmHtml::editor($_prefix.$_fld->name,$_return['fields'][$_fld->name]['value'], '150', '100', $_fld->cols, $_fld->rows,  array('pagebreak', 'readmore'));
							break;
						case 'checkbox':
							$_return['fields'][$_fld->name]['formcode'] = '<input type="checkbox" name="'
							. $_prefix.$_fld->name . '" id="' . $_prefix.$_fld->name . '_field" value="1" '
							. ($_return['fields'][$_fld->name]['value'] ? 'checked="checked"' : '')
							. ($_fld->required ? ' class="required form-check-input"' : ' class="form-check-input"').' />';
							 if($_return['fields'][$_fld->name]['value']) {
								 $_return['fields'][$_fld->name]['value'] = vmText::_($_prefix.$_fld->title);
							 }
							break;
						case 'custom':

							$_return['fields'][$_fld->name]['value'] = $_return['fields'][$_fld->name]['unescapedvalue']; 
							$_return['fields'][$_fld->name]['htmlentities'] = false; 
							$_return['fields'][$_fld->name]['formcode'] =  shopFunctionsF::renderVmSubLayout($_fld->name,array('field'=>$_return['fields'][$_fld->name],'userData' => $_userData,'prefix' => $_prefix));
							break;
							// /*##mygruz20120223193710 { :*/
						// case 'userfieldplugin': //why not just vmuserfieldsplugin ?
							// JPluginHelper::importPlugin('vmuserfield');
							// $dispatcher = JDispatcher::getInstance();
							// //Todo to adjust to new pattern, using &
							// $html = '' ;
							// $dispatcher->trigger('plgVmOnUserFieldDisplay',array($_return['fields'][$_fld->name], &$html) );
							// $_return['fields'][$_fld->name]['formcode'] = $html;
							// break;
							// /*##mygruz20120223193710 } */
						case 'multicheckbox':
						case 'multiselect':
						case 'select':
						case 'radio':
							$_qry = 'SELECT `fieldtitle`, `fieldvalue` '
							. 'FROM `#__virtuemart_userfield_values` '
							. 'WHERE `virtuemart_userfield_id` = ' . (int)$_fld->virtuemart_userfield_id
							. ' ORDER BY `ordering` ';
							$_values = $this->_getList($_qry);
							// We need an extra lok here, especially for the Bank info; the values
							// must be translated.
							// Don't check on the field name though, since others might be added in the future :-(

							$_selected = null;
							foreach ($_values as $_v) {
								$_v->fieldtitle = vmText::_($_v->fieldtitle);
								if($_selected === null) $_selected = array($_v->fieldvalue);
							}
							$_attribs = array();
							if ($_fld->readonly and !$admin) {
								$_attribs['readonly'] = 'readonly';
							}
							if ($_fld->required) {
								if(!isset($_attribs['class'])){
									$_attribs['class'] = '';
								}
								$_attribs['class'] .= ' required';
							}

							if ($_fld->type == 'radio') {
								if(!isset($_attribs['class'])){
									$_attribs['class'] = '';
								}
								$_attribs['class'] .= ' form-check-input';
							}

							if ($_fld->type == 'radio' or $_fld->type == 'select') {
								$_selected = $_return['fields'][$_fld->name]['value'];
							} else {
								$_attribs['size'] = $_fld->size; // Use for all but radioselects
								if($_return['fields'][$_fld->name]['value']!==null){
									if (!is_array($_return['fields'][$_fld->name]['value'])){
										$_selected = explode("|*|", $_return['fields'][$_fld->name]['value']);
									} else {
										$_selected = $_return['fields'][$_fld->name]['value'];
									}
								}

								
								
							}

							// Nested switch...
							switch($_fld->type) {
								case 'multicheckbox':
								
									// todo: use those
									$_attribs['rows'] = $_fld->rows;
									$_attribs['cols'] = $_fld->cols;
									$formcode = '';
									$field_values="";
									$_idx = 0;
									$separator_form = '<br />';
									$separator_title = ',';
									foreach ($_values as $_val) {
										 if ( in_array($_val->fieldvalue, $_selected)) {
											 $is_selected='checked="checked"';
											 $field_values.= vmText::_($_val->fieldtitle). $separator_title;
										 }  else {
											 $is_selected='';
										 }
										$formcode .= '<div class="form-check"><input class="form-check-input" type="checkbox" name="'
										. $_prefix.$_fld->name . '[]" id="' . $_prefix.$_fld->name . '_field' . $_idx . '" value="'. $_val->fieldvalue . '" '
										. $is_selected .'/> <label class="form-check-label" for="' . $_prefix.$_fld->name . '_field' . $_idx . '">'.vmText::_($_val->fieldtitle) .'</label></div>' . $separator_form;
										$_idx++;
										
										
										
									}
									// remove last br
									$_return['fields'][$_fld->name]['formcode'] =substr($formcode ,0,-strlen($separator_form));
									$_return['fields'][$_fld->name]['value'] = substr($field_values,0,-strlen($separator_title));
									break;
								case 'multiselect':
									$_attribs['multiple'] = 'multiple';
									if(!isset($_attribs['class'])){
										$_attribs['class'] = '';
									}
									$_attribs['class'] .= ' vm-chzn-select form-select';
									$field_values="";
									$_return['fields'][$_fld->name]['formcode'] = JHtml::_('select.genericlist', $_values, $_prefix.$_fld->name.'[]', $_attribs, 'fieldvalue', 'fieldtitle', $_selected);
									$separator_form = '<br />';
									$separator_title = ',';
									foreach ($_values as $_val) {
										 if ( in_array($_val->fieldvalue, $_selected)) {
											 $field_values.= vmText::_($_val->fieldtitle). $separator_title;
										 }
										}
									$_return['fields'][$_fld->name]['value'] = substr($field_values,0,-strlen($separator_title));

									break;
								case 'select':
									if(!isset($_attribs['class'])){
										$_attribs['class'] = '';
									}
									$_attribs['class'] .= ' vm-chzn-select form-select';
									if ($_fld->size) {
										$_attribs['style']= "width: ".$_fld->size."px";
									}
									if(!$_fld->required){
										$obj = new stdClass();
										$obj->fieldtitle = vmText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION');
										$obj->fieldvalue = '';
										array_unshift($_values,$obj);
									}

									$_return['fields'][$_fld->name]['formcode'] = JHTML::_('select.genericlist', $_values, $_prefix.$_fld->name, $_attribs, 'fieldvalue', 'fieldtitle', $_selected,$_prefix.$_fld->name.'_field');
									if ( !empty($_selected)){
										foreach ($_values as $_val) {
											if ( $_val->fieldvalue==$_selected ) {
												// vmdebug('getUserFieldsFilled set empty select to value',$_selected,$_fld,$_return['fields'][$_fld->name]);
												$_return['fields'][$_fld->name]['value'] = vmText::_($_val->fieldtitle);
											}
										}
									}

									break;

								case 'radio':
									$_return['fields'][$_fld->name]['formcode'] =  JHtml::_('select.radiolist', $_values, $_prefix.$_fld->name, $_attribs, 'fieldvalue', 'fieldtitle', $_selected, $_prefix.$_fld->name.'_field');
									if ( !empty($_selected)){
										foreach ($_values as $_val) {
											if (  $_val->fieldvalue==$_selected) {
												$_return['fields'][$_fld->name]['value'] = vmText::_($_val->fieldtitle);
											}
										}
									}

									break;
							}
							break;
					}
					break;
				}
				
				// DWP: Add html_classes to formcode - Begin
				if( !empty($_fld->html_classes) ) {
					$_return['fields'][$_fld->name]['html_classes'] = $_fld->html_classes;
					$pattern = '/(class=\")([ _\-a-zA-Z0-9]*)(\")/';
					$matches = array();
					preg_match($pattern,$_return['fields'][$_fld->name]['formcode'],$matches);
					if (count($matches) > 0) {
						$_return['fields'][$_fld->name]['formcode'] = preg_replace($pattern, ('$1'.trim(implode(' ',array_unique(array_merge(explode(' ',$matches[2]),explode(' ', $_fld->html_classes))))).'$3'), $_return['fields'][$_fld->name]['formcode']);
					} else {
						$_return['fields'][$_fld->name]['formcode'] = preg_replace('/\<* ?\/?>/', (' class="'.$_fld->html_classes.'"$0'), $_return['fields'][$_fld->name]['formcode']);
					}
				}
				// DWP: Add html_classes to formcode - End
				
			}
		} else {
			vmdebug('getUserFieldsFilled $_selection is not an array ',$_selection);
// 			$_return['fields'][$_fld->name]['formcode'] = '';
		}

		return $_return;
	}

	/**
	 * Checks if a single field is required, used in the cart
	 *
	 * @author Max Milbers
	 * @param string $fieldname
	 */
	function getIfRequired($fieldname = 0) {

		static $required = null;

		if($required === null){
			$db = JFactory::getDBO();
			$q = 'SELECT `name` FROM `#__virtuemart_userfields` WHERE `required` = "1" limit 0,100';

			$db->setQuery($q);
			$required = $db->loadColumn();
			if(!isset($required)){
				vmError('userfields getIfRequired '.$q,'Programmer used an unknown userfield '.$fieldname);
			}
			$required = array_flip($required);
			vmdebug('Required output',$required);
		}

		if(empty($fieldname)){
			return $required;
		}
		else if(isset($required[$fieldname])){
			return true;
		}
		else {
			return false;
		}

	}

	/**
	 * Translate arrays form userfield_values to the format expected by the table class.
	 *
	 * stAn Note -> when a field of [0] is deleted (or others), you cannot use count to itenerate the array
	 *
	 * @param array $titles List of titles from the formdata
	 * @param array $values List of values from the formdata
	 * @param int $virtuemart_userfield_id ID of the userfield to relate
	 * @return array Data to bind to the userfield_values table
	 */
	private function postData2FieldValues($titles, $values, $virtuemart_userfield_id, $virtuemart_userfield_value_id  ){

		$_values = array();
		if (is_array($titles) && is_array($values)) {
			// updated by stAn:
			foreach ($values as $i=>$val)
			 {
				$_values[$i] = array(
					'virtuemart_userfield_value_id' => $virtuemart_userfield_value_id[$i]
				,'virtuemart_userfield_id' => $virtuemart_userfield_id
				,'fieldtitle' => $titles[$i]
				,'fieldvalue' => $values[$i]
				,'ordering'   => $i
				);

			 }
			 /*
			for ($i=0; $i < count($titles) ;$i++) {
				if (empty($titles[$i])) {
					continue; // Ignore empty fields
				}

			}
			*/
		}
		return $_values;
	}

	/**
	 * Delete all record ids selected
	 *
	 * @return boolean True is the remove was successful, false otherwise.
	 */
	function remove($fieldIds){

		if(!vmAccess::manager('userfields')){
			vmWarn('Insufficient permissions to remove userfields');
			return false;
		}

		$field      = $this->getTable('userfields');
		$value      = $this->getTable('userfield_values');
		$userinfo   = $this->getTable('userinfos');
		$orderinfo  = $this->getTable('order_userinfos');

		$ok = true;
		$core = $this->getCoreFields();
		foreach($fieldIds as $fieldId) {
			$fieldId = (int)$fieldId; 
			$field->load($fieldId);
			$_fieldName = $field->name;
			if (!in_array($_fieldName, $core)){
				if ($field->type != 'delimiter') {
					// Get the fieldtype for the database
					$_fieldType = $field->formatFieldType();

					// Alter the user_info table
					if ($userinfo->_modifyColumn ('DROP', $_fieldName,$_fieldType) === false) {
						vmdebug('remove $userinfo->_modifyColumn failed',$userinfo);
						vmError('remove $userinfo->_modifyColumn failed id = '.$fieldId.' '.$_fieldName);
						$ok = false;
					}

					// Alter the order_userinfo table
					if ($orderinfo->_modifyColumn ('DROP', $_fieldName,$_fieldType) === false) {
						vmdebug('remove $userinfo->_modifyColumn failed',$userinfo);
						vmError('remove $orderinfo->_modifyColumn failed id = '.$fieldId.' '.$_fieldName);
						$ok = false;
					}
				}

				if (!$field->delete($fieldId)) {
					vmdebug('remove userfields failed',$field);
					vmError('remove userfields failed id = '.$fieldId.' '.$_fieldName);
					$ok = false;
				}
				if (!$value->delete($fieldId)) {
					vmdebug('remove userfield_values failed',$value);
					vmError('remove userfield_values failed id = '.$fieldId.' '.$_fieldName);
					$ok = false;
				}
			} else {
				vmError('Cannot delete core field <i>'.$_fieldName.'</i>! Use unpublish');
			}


		}

		return $ok;
	}

	/**
	 * Get the userfields for the BE list
	 *
	 * @author Max Milbers
	 * @return NULL
	 */
	function getUserfieldsList($type = false){

		if (!$this->_data) {

			if ($type) vRequest::setVar('type', $type);

			$whereString = $this->_getFilter();

			$ordering = $this->_getOrdering();
			$this->_data = $this->exeSortSearchListQuery(0,'*',' FROM `#__virtuemart_userfields`',$whereString,'',$ordering);

		}

		return $this->_data;
	}

	/**
	 * If a filter was set, get the SQL WHERE clase
	 *
	 * @return string text to add to the SQL statement
	 */
	function _getFilter()
	{
		$db = JFactory::getDBO();
		$where = array();

		if ($search = vRequest::getCmd('search', false)) {
			$where[] = ' `name` LIKE "%' . $db->escape( $search, true ) . '%"' ;
		}
		if ($type = vRequest::getCmd('type', false)) {
			$where[] = ' `type` = "' . $type . '"' ;
		}

		if (count ($where) > 0) {
			$whereString = ' WHERE (' . implode (' AND ', $where) . ') ';
		} else {
			$whereString = '';
		}

		return ($whereString);
	}

	/**
	 * Build the query to list all Userfields
	 *
	 *@deprecated
	 * @return string SQL query statement
	 */
	function _getListQuery ()
	{
		$query = 'SELECT * FROM `#__virtuemart_userfields` ';
		$query .= $this->_getFilter();
		$query .= $this->_getOrdering();
		return ($query);
	}
	//*/
}

// No closing tag
