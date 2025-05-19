<?php
/**
*
* Data module for shop coupons
*
* @package	VirtueMart
* @subpackage Coupon
* @author Max Milbers
* @link https://virtuemart.net
* @copyright Copyright (c) 2018 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Model class for shop coupons
 *
 * @package	VirtueMart
 * @subpackage Coupon
 */
class VirtueMartModelInvoice extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('invoices');
	}

	static public function isInvoiceToBeAttachByOrderstats($order_status){

		return (int)(VirtueMartModelInvoice::needInvoiceByOrderstatus($order_status) or VirtueMartModelInvoice::needInvoiceByOrderstatus($order_status,'inv_osr', array('R')));
	}

	static function needInvoiceByOrderstatus($order_status, $confName = 'inv_os', $def = array('C')){

		$orderstatusForInvoice = VmConfig::get($confName,$def);
		if(!is_array($orderstatusForInvoice)) $orderstatusForInvoice = array($orderstatusForInvoice);

		$invoiceOrderStatus = false;
		if(in_array($order_status, $orderstatusForInvoice)){
			$invoiceOrderStatus = true;
		}

		return $invoiceOrderStatus;
	}



	static function checkInvoiceExists($invoiceNumber, $layout){
		$path = self::getInvoicePath();
		$path .= self::getInvoiceName($invoiceNumber, $layout).'.pdf';
		vmdebug('checkInvoiceExists path '.$path);
		//Last check here, does the invoice already exists? else we can just use the old one
		if(file_exists($path)){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks if we need a new invoice and therefore, if we need a new invoice number
	 * Actually at the moment unused
	 * @param $orderId
	 * @param bool $orderDetails
	 */
	static function checkCreateInvoiceNumber($orderId, $order, $layout = 'invoice'){

		$invoiceOrderStatus = self::needInvoiceByOrderstatus($order['details']['BT']->order_status);
		$refundOrderStatus = self::needInvoiceByOrderstatus($order['details']['BT']->order_status,'inv_osr',array('R'));

		if(!$invoiceOrderStatus and !$refundOrderStatus){
			return false;
		}

		$storedOrder = self::getOrder( $orderId );
		if(!$storedOrder){
			return false;
		}

		$hashOrder = self::hashOrder($order);
		$invoice = self::getInvoiceEntry($orderId,true,'*');
		if($storedOrder->invoice_locked or (!empty($storedOrder->o_hash) and $storedOrder->o_hash == $hashOrder)){
			return false;
		} else {

			return self::checkInvoiceExists($invoice['invoice_number'], $layout);

		}
		//Compare hash of orderDetails and stored invoice hash
	}
/*
	static function hashOrder($order){

		$b =vmJsapi::safe_json_encode($order['details']['BT']);
		$s =vmJsapi::safe_json_encode($order['details']['ST']);
		$i =vmJsapi::safe_json_encode($order['items']);
		$h = hash($b + $s + $i);
		vmdebug('hashOrder',$h,$order);
		return $h;
	}
*/
	function createReferencedInvoiceNumber($orderId, $orderDetails = false, $layout = 'invoice', $checkHash = true) {

		if(is_array($orderDetails)){
			if(!empty($orderDetails['invoice_locked'])) return false;
		} else {
			if(!empty($orderDetails->invoice_locked)) return false;
		}


		$invNu = false;
		if($checkHash or !VmConfig::get( 'ChangedInvCreateNewInvNumber', true )){
			//check if there is already an InvoiceEntry
			$invNu = self::getInvoiceEntry( $orderId, true, '*' );
			vmdebug( 'createReferencedInvoiceNumber', $orderId,$invNu );
		}

		//First lets execute a path check, if the invoice was actually already rendered
		if($checkHash and $invNu){
			$exists = self::checkInvoiceExists($invNu['invoice_number'], $layout);
			if(!$exists){
				vmdebug('Current invoice number not rendered yet');
				return false; //No new invoice number created
			} else {
				vmdebug( 'createReferencedInvoiceNumber invoice entry exists already for', $orderId, $invNu );
				if(!empty($orderDetails['o_hash']) and $orderDetails['o_hash']==$invNu['o_hash']){
					vmdebug( 'createReferencedInvoiceNumber hash of invoice entry and invoice to create is the same, break', $orderId, $invNu );
					return false;
				}
			}
		}

		vmdebug( 'createReferencedInvoiceNumber f', $orderId, $invNu );
		if(!VmConfig::get( 'ChangedInvCreateNewInvNumber', true ) and $invNu) {
			$invT = $this->getTable( 'invoices' );
			$invT->bind( $invNu );
			$invT->virtuemart_invoice_id = 0;
			$invT->created_on = '';
			$invT->created_by = 0;
			$invT->check();
			$invT->store();
			$invoice_number = $invT->invoice_number;
		} else {
			$invoice_number = self::createStoreNewInvoiceNumberById($orderId, $orderDetails);
		}

		self::lockOrder($orderDetails['virtuemart_order_id']);

		return $invoice_number;
	}

	function createNewInvoiceNumber($orderDetails, &$invoiceNumber){
		return $this->getExistingIfUnlockedCreateNewInvoiceNumber($orderDetails, $invoiceNumber);
	}

	static function lockOrder($virtuemart_order_id){
		if (empty(VmConfig::get('unlockInvoices',false))) {
			$db = JFactory::getDbo();
			$q = 'UPDATE `#__virtuemart_orders` SET `invoice_locked`="1" WHERE `virtuemart_order_id`= "'.$virtuemart_order_id.'"';
			$db->setQuery($q);
			$db->execute();
			vmdebug('InoviceModel locked invoice for orderId '.$virtuemart_order_id);
		}
	}

	/**
	 * returns true if an invoice number has been created
	 * returns false if an invoice number has not been created  due to some configuration parameters
	 */
	function getExistingIfUnlockedCreateNewInvoiceNumber($orderDetails, &$invoiceNumber, $force_create_invoice = null){

		$orderDetails = (array)$orderDetails;

		if(!isset($orderDetails['virtuemart_order_id'])){
			vmWarn('createInvoiceNumber $orderDetails has no virtuemart_order_id ',$orderDetails);
			vmdebug('createInvoiceNumber $orderDetails has no virtuemart_order_id ',$orderDetails);
		}

		//$invM = VmModel::getModel('invoice');
		$result = $this->getInvoiceEntry($orderDetails['virtuemart_order_id'], true, '*');
		vmdebug('getExistingIfUnlockedCreateNewInvoiceNumber after getInvoiceEntry',$force_create_invoice,$orderDetails['virtuemart_order_id'],$result);

		if( (!$result or empty($result['invoice_number'])) ){

			$invoice_locked = false;
			if(is_array($orderDetails) and !empty($orderDetails['invoice_locked'])){
				$invoice_locked = true;
			} else if(is_object($orderDetails) and !empty($orderDetails->invoice_locked)){
				$invoice_locked = true;
			}
			if(!$invoice_locked){
				$invoiceNumber = $this->createStoreNewInvoiceNumber($orderDetails, $force_create_invoice);
				if(!empty($invoiceNumber)){
					vmdebug('Created new invoiceNumber',$invoiceNumber);
					//Lets Lock the invoice creation
					self::lockOrder($orderDetails['virtuemart_order_id']);

				}

			} else {
				$er= 'No new invoice number created. Invoice is locked';
				//vmError($er,$er);
				vmdebug($er);
				$invoiceNumber = false;
			}
		} else {
			$invoiceNumber = array($result['invoice_number'],$result['created_on']);
		}
		vmdebug('getExistingIfUnlockedCreateNewInvoiceNumber returning $invoiceNumber',$invoiceNumber);
		return $invoiceNumber;
	}

	function createStoreNewInvoiceNumberById($orderId, $orderDetails = false){

		if(!$orderDetails) {
			$order = $this->getOrder( $orderId );
			$orderDetails = $order['details']['BT'];
		} else if(empty($orderDetails['virtuemart_order_id']) or empty($orderDetails['virtuemart_vendor_id'])){
			$order = $this->getOrder( $orderId );
			if(!empty($order['details']['BT']) and is_object($order['details']['BT'])){
				$orderDetails = array_merge($orderDetails,get_object_vars($order['details']['BT']));
			} else {
				vmdebug('createStoreNewInvoiceNumberById could not merge array',$orderId,$order,$orderDetails);
				vmTrace('Hmmm createStoreNewInvoiceNumberById');
			}
		}

		$ret = $this->createStoreNewInvoiceNumber( $orderDetails );
		if(!empty($ret[0])){
			return $ret[0];
		} else {
			return false;
		}
	}

	public function createStoreNewInvoiceNumber($orderDetails, $force_create_invoice = null){

		if(is_object($orderDetails)) $orderDetails = get_object_vars($orderDetails);

		$data['virtuemart_order_id'] = $orderDetails['virtuemart_order_id'];

		$data['order_status'] = $orderDetails['order_status'];

		$data['virtuemart_vendor_id'] = $orderDetails['virtuemart_vendor_id'];

		$data['o_hash'] = $orderDetails['o_hash'];

		if($orderDetails['invoice_locked']) {
			vmdebug('createStoreNewInvoiceNumber invoice locked return false');
			return false;
		}

		$table = $this->getTable('invoices');

		vDispatcher::importVMPlugins('vmpayment');
		JPluginHelper::importPlugin('vmshopper');

		//QuicknDirty
		$deliveryNote = vRequest::getCmd('layout','');
		if($deliveryNote=='deliverynote'){
			$tmp = self::getInvoiceEntry($data['virtuemart_order_id'], true , '*');
			$table->invoice_number = isset($tmp['invoice_number'])? $tmp['invoice_number']:'';
			$table->created_on = isset($tmp['created_on'])? $tmp['created_on']:'';

		} else {

			// plugin returns invoice number, 0 if it does not want an invoice number to be created by Vm
			$plg_datas = vDispatcher::trigger('plgVmOnUserInvoice',array($orderDetails,&$data));

			if(empty($data['invoice_number']) ) {
				// check the default configuration
				$orderstatusForInvoice = VmConfig::get('inv_os',array('C'));
				if(!is_array($orderstatusForInvoice)) $orderstatusForInvoice = array($orderstatusForInvoice); //for backward compatibility 2.0.8e

				if(!isset($force_create_invoice)) $force_create_invoice=vRequest::getCmd('create_invoice', '0');
	vmdebug('createStoreNewInvoiceNumber $force_create_invoice',$force_create_invoice,$orderDetails['order_create_invoice_pass'] );
				if ( in_array($orderDetails['order_status'],$orderstatusForInvoice) or $force_create_invoice==$orderDetails['order_create_invoice_pass'] ){
					$q = 'SELECT COUNT(1) FROM `#__virtuemart_invoices` WHERE `virtuemart_vendor_id`= "'.$orderDetails['virtuemart_vendor_id'].'" '; // AND `order_status` = "'.$orderDetails->order_status.'" ';
					$db = JFactory::getDBO();
					$db->setQuery($q);

					$count = $db->loadResult()+1;
					vmdebug('createStoreNewInvoiceNumber count and query ',$count,$q);

					$date = date("Y-m-d");
					$data['invoice_number'] = str_replace('-', '', substr($date,2,8)).vmCrypt::getHumanToken(4).'0'.$count;

				} else {
					return false;
				}
			}

			$table->bindChecknStore($data);
		}

		return array($table->invoice_number,$table->created_on);
	}

	/**
	 * @author Valérie Isaksen
	 * @author Max Milbers
	 *
	 * @deprecated: use the function VirtueMartModelOrders::getInvoiceEntry instead
	 *
	 */
	static function getInvoiceNumber($virtuemart_order_id) {
		return self::getInvoiceEntry($virtuemart_order_id, true , '`invoice_number`' );
	}

	function getInvoiceNumbers($virtuemart_order_id){
		return $this->getInvoiceEntry($virtuemart_order_id, false, '`invoice_number`' );
	}

	/**
	 * @author Valérie Isaksen
	 * @author Max Milbers
	 */
	static function getInvoiceEntry($virtuemart_order_id, $last = true, $select = '`invoice_number`', $where = 'virtuemart_order_id' ){

		$db = JFactory::getDBO();
		$q = 'SELECT '.$select.' FROM `#__virtuemart_invoices` WHERE `'.$where.'`= "'.$virtuemart_order_id.'" ORDER BY `created_on` DESC ';
		if($last){
			$q .= ' Limit 1';
		}
		$db->setQuery($q);

		$single = true;
		if($select == '*' or strpos($select,',')!=0){
			$single = false;
		}
		if($last){
			if($single ){
				$res =  $db->loadResult();
			} else {
				$res = $db->loadAssoc();
			}
		} else {
			if($single ){
				$res = $db->loadColumn();
			} else {
				$res = $db->loadAssocList();
			}
		}
		//vmdebug('getInvoiceEntry ',$q,$res);
		return $res;
	}

	/**
	 * has Invoice
	 *
	 * @author Valérie Isaksen
	 * @param $order_id Id of the order
	 * @return  false if there is no invoice, $invoiceTable otherwise
	 */
	function hasInvoice($order_id) {
		$invoiceTable = $this->getTable('invoices');
		$invoiceTable->load($order_id,'virtuemart_order_id');
		if(empty($invoiceTable->invoice_number)){
			return false;
		}
		return $invoiceTable;
	}

	function getOrder($orderId){
		$oM = VmModel::getModel('orders');
		$o = $oM->getOrder( $orderId );
		return $o;
	}

	static function getInvoicePath($safePath=null, $created_on = null, $info = true){

		if(!isset($safePath)){
			$safePath = $path = VmConfig::get('forSale_path',0);
		}
vmdebug('getInvoicePath testing for '.$safePath);
		$year = '';
		if(!empty($created_on)){
			$year = self::getYearByUnixTime($created_on).'/';
		}

		if(empty($safePath) ){
			if($info)vmError('No path set to store invoices');
			return false;
		} else {

			$safePath .= $year.self::getInvoiceFolderName().DS;
			if(!file_exists($safePath)){
				if($info) vmError('Path wrong to store invoices, folder invoices does not exist '.$safePath);
				return false;
			} else if(!is_writable( $safePath )){
				if($info) vmError('Cannot store pdf, directory not writeable '.$safePath);
				return false;
			}
		}
		return $safePath;
	}

	static function getInvoiceFolderName() {
		return 'invoices' ;
	}

	/**
	 * Get the file name for the invoice or deliverynote.
	 * The layout argument currently is either 'invoice' or 'deliverynote'
	 * @return The full filename of the invoice/deliverynote without file extension, sanitized not to contain problematic characters like /
	 */
	static function getInvoiceName($invoice_number, $layout='invoice'){

		$tmpT = false;
		vmLanguage::loadJLang('com_virtuemart_orders', true);
		if(VmConfig::get('invoiceNameInShopLang',true)){
			$tmpT = VmConfig::$vmlangTag;
			vmLanguage::setLanguageByTag(VmConfig::$jDefLangTag);
		}
		$prefix = vmText::_('COM_VIRTUEMART_FILEPREFIX_'.strtoupper($layout));
		if($tmpT!=false){
			vmLanguage::setLanguageByTag($tmpT);
		}
		if($prefix == 'COM_VIRTUEMART_FILEPREFIX_'.strtoupper($layout)){
			$prefix = 'vm'.$layout.'_';
		}
		return $prefix.preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $invoice_number);
	}

	/** Rename Invoice  (when an order is deleted)
	 *
	 * @author Valérie Isaksen
	 * @author Max Milbers
	 * @param $order_id Id of the order
	 * @return boolean true if deleted successful, false if there was a problem
	 */
	function renameInvoice($order_id) {
		//$invM = VmModel::getModel('invoice');
		//return $invM->createReferencedInvoiceNumber($order_id);
		$table = $this->getTable('invoices');
		$table->load($order_id,'virtuemart_order_id');
		if(empty($table->invoice_number)){
			return false;
		}

		// rename invoice pdf file
		$path = self::getInvoicePath(VmConfig::get('forSale_path',0));
		$name = shopFunctionsF::getInvoiceName($table->invoice_number);
		$invoice_name_src = $path.DS.$name.'.pdf';

		if(!file_exists($invoice_name_src)){
			// may be it was already deleted when changing order items
			$data['invoice_number'] = $table->invoice_number;
			//$data['invoice_number'] = $data['invoice_number'].' not found.';
		} else {
			$date = date("Ymd");
			// We change the invoice number in the invoice table only. The order's invoice number is not modified!
			$data['invoice_number'] = $table->invoice_number.'_'.$date.'_'.$table->order_status;
			// We the sanitized file name as the invoice number might contain strange characters like 2015/01.
			$invoice_name_dst = $path.DS.$name.'_deprecated'.$date.'_'.$table->order_status.'.pdf';

			if (!JFile::move($invoice_name_src, $invoice_name_dst)) {
				vmError ('Could not rename Invoice '.$invoice_name_src.' to '. $invoice_name_dst );
			}
		}

		$table = $this->getTable('invoices');
		$table->bindChecknStore($data);

		return true;
	}

	static function getYearByUnixTime($unixTime){
		return floor($unixTime/31556926) + 1970;
	}

}

// pure php no closing tag