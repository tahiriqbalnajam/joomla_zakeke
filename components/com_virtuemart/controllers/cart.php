<?php

/**
 * Controller for the cart
 *
 * @package	VirtueMart
 * @subpackage Cart
 * @author Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2022 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: cart.php 11058 2024-10-01 11:09:19Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the controller framework
jimport('joomla.application.component.controller');

/**
 * Controller for the cart view
 *
 * @package VirtueMart
 * @subpackage Cart
 */
class VirtueMartControllerCart extends JControllerLegacy {

	var $useSSL = null;
	var $useXHTML = false;

	public function __construct() {
		parent::__construct();

		$this->useSSL = vmURI::useSSL();	//VmConfig::get('useSSL', 0);
		$this->useXHTML = false;

	}

	public function display($cachable = false, $urlparams = false){

		if(VmConfig::get('use_as_catalog', 0)){
			// Get a continue link
			$virtuemart_category_id = shopFunctionsF::getLastVisitedCategoryId();
			$categoryLink = '';
			if ($virtuemart_category_id) {
				$categoryLink = '&virtuemart_category_id=' . $virtuemart_category_id;
			}
			$ItemId = shopFunctionsF::getLastVisitedItemId();
			$ItemIdLink = '';
			if ($ItemId) {
				$ItemIdLink = '&Itemid=' . $ItemId;
			}

			$continue_link = JRoute::_('index.php?option=com_virtuemart&view=category' . $categoryLink . $ItemIdLink, FALSE);
			$app = JFactory::getApplication();
			$app ->redirect($continue_link,'This is a catalogue, you cannot acccess the cart');
		}

		$document = JFactory::getDocument();
		$viewType = $document->getType();
		$tmpl = vRequest::getCmd('tmpl',false);
		if ($viewType == 'raw' and $tmpl == 'component') {
			$viewType = 'html';
		}

		$viewName = vRequest::getCmd('view', $this->default_view);
		$viewLayout = vRequest::getCmd('layout', 'default');

		$view = $this->getView($viewName, $viewType, '', array('layout' => $viewLayout));

		$view->assignRef('document', $document);

		$vendorId = NULL;
		$multixcart = VmConfig::get('multixcart',0);
		if($multixcart == 'byselection'){
			$vendorId = vRequest::getInt('virtuemart_vendor_id',NULL);
			vmdebug('Controller cart display, my cart call by vendorId',$vendorId);
		}

		$cart = VirtueMartCart::getCart(false, array(), NULL, $vendorId);

		//New Address is filled here with the data of the cart (we are in the cart)
		$address_type = 'BT';
		$fieldtype = $address_type . 'address';
		$cart->setupAddressFieldsForCart(true);
		$view->userFields = $cart->{$fieldtype};

		$cart->order_language = vRequest::getString('order_language', $cart->order_language);
		if(!isset($force))$force = VmConfig::get('oncheckout_opc',true);
		$cart->prepareCartData(false);

		if(!empty($cart->cartData['couponCode'])){
			vmdebug('Coupon code test now');
			$cart->setCouponCode($cart->cartData['couponCode']);
		}

		$html=true;
		$request = vRequest::getRequest();
		$task = vRequest::getCmd('task');

		if(($task == 'confirm' or isset($request['confirm'])) and !$cart->getInCheckOut()){
			$cart->confirmDone();
			$view = $this->getView('cart', 'html');
			$view->setLayout($cart->layout);
			$cart->_fromCart = false;
			$view->display();
			return true;
		} else {
			//$cart->_inCheckOut = false;
			$redirect = (isset($request['checkout']) or $task=='checkout' );
			if( VmConfig::get('directCheckout',false) and !$redirect and !$cart->getInCheckOut() and !vRequest::getInt('dynamic',0) and !$cart->_dataValidated) {
				$redirect = true;
				vmdebug('directCheckout');
			}

			$cart->_inConfirm = false;
			$cart->checkoutData($redirect);
		}

		$cart->_fromCart = false;

		$view->display();

		return $this;
	}

	public function updateCartNoMethods($html=true,$force = null){
		return $this->updatecart($html, $force, false);
	}

	public function updatecart($html=true,$force = null, $methods = true){

		$cart = VirtueMartCart::getCart();
		$cart->_fromCart = true;
		$cart->_redirected = false;
		if(vRequest::getCmd('cancel',0)){
			$cart->_inConfirm = false;
		}
		if($cart->getInCheckOut()){
			vRequest::setVar('checkout',true);
		}

		$cart->storeCartSession = false;
		$cart->saveCartFieldsInCart();

		if($cart->updateProductCart()){
			//vmInfo('COM_VIRTUEMART_PRODUCT_UPDATED_SUCCESSFULLY');
		}

		if(!empty(vRequest::getEmail('email'))){
			$userC = new VirtueMartControllerUser();
			$userC->saveData($cart);
		}

		$STsameAsBT = vRequest::getInt('STsameAsBT', null);
		if(isset($STsameAsBT)){
			$cart->STsameAsBT = $STsameAsBT;
		}

		$currentUser = JFactory::getUser();
		if(!$currentUser->guest){
			$cart->selected_shipto = vRequest::getVar('shipto', $cart->selected_shipto);
			if(!empty($cart->selected_shipto)){
				$userModel = VmModel::getModel('user');
				$stData = $userModel->getUserAddressList($currentUser->id, 'ST', $cart->selected_shipto);

				if(isset($stData[0]) and is_object($stData[0])){
					$stData = get_object_vars($stData[0]);
					$cart->ST = $stData;
					$cart->STsameAsBT = 0;
				} else {
					$cart->selected_shipto = 0;
				}
			}
			if(empty($cart->selected_shipto)){
				$cart->STsameAsBT = 1;
				$cart->selected_shipto = 0;
				//$cart->ST = $cart->BT;
			}
		} else {
			$cart->selected_shipto = 0;
			if(!empty($cart->STsameAsBT)){
				//$cart->ST = $cart->BT;
			}
		}

		if(!isset($force))$force = VmConfig::get('oncheckout_opc',true);

		if($methods){
			$cart->setShipmentMethod($force, !$html);
			$cart->setPaymentMethod($force, !$html);
		}

		vDispatcher::importVMPlugins('vmcustom');

		vDispatcher::trigger('plgVmOnUpdateCart',array(&$cart, &$force, &$html));

		$cart->prepareCartData();

		$coupon_code = trim(vRequest::getString('coupon_code', ''));
		if(!empty($coupon_code)){
			vmdebug('my cartData["couponCode"]',$cart->cartData);
			if($coupon_code!=$cart->cartData['couponCode']){
				$msg = $cart->setCouponCode($coupon_code);
				if($msg) vmInfo($msg);
				$cart->setOutOfCheckout();
			}
		}

		//We enable storing of the cart again, execute store session and just set the boolean to store the cart to yes, which is then executed in the display function
		$cart->storeCartSession = true;
		$cart->setCartIntoSession(true,true);
		//$cart->storeToDB = true;

		if ($html) {
			$this->display();
		} else {
			$json = new stdClass();
			ob_start();
			$this->display ();
			$json->msg = ob_get_clean();
			echo json_encode($json);
			jExit();
		}

	}


	public function updatecartJS(){
		$this->updatecart(false);
	}


	/**
	 * legacy
	 * @deprecated
	 */
	public function confirm(){
		$this->updatecart();
	}

	public function orderdone(){
		$this->updatecart();
	}

	public function setshipment(){
		$this->updatecart(true,true);
	}

	public function setpayment(){
		$this->updatecart(true,true);
	}

	/**
	 * Add the product to the cart
	 * @access public
	 */
	public function add() {
		$mainframe = JFactory::getApplication();
		if (VmConfig::get('use_as_catalog', 0)) {
			vmInfo('COM_VIRTUEMART_PRODUCT_NOT_ADDED_SUCCESSFULLY');
			$mainframe->redirect('index.php');
		}

		$cart = VirtueMartCart::getCart();
		if ($cart) {
			$virtuemart_product_ids = vRequest::getInt('virtuemart_product_id',0);
			if(empty($virtuemart_product_ids)){
				$sku = vRequest::getVar('sku','');
				if(!empty($sku)){
					$q = 'SELECT virtuemart_product_id FROM #__virtuemart_products WHERE product_sku="'.$sku.'"';
					$db = JFactory::getDbo();
					$db->setQuery($q);
					$virtuemart_product_ids = $db->loadResult();
					vmdebug('my sku '.$sku.'and q '.$q,$virtuemart_product_ids);
				}
			}


			if ($cart->add($virtuemart_product_ids)) {
				$msg = vmText::_('COM_VIRTUEMART_PRODUCT_ADDED_SUCCESSFULLY');
				$type = '';
			} else {
				$msg = vmText::_('COM_VIRTUEMART_PRODUCT_NOT_ADDED_SUCCESSFULLY');
				$type = 'error';
			}

			$mainframe->enqueueMessage($msg, $type);
			$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart', FALSE));

		} else {
			$mainframe->enqueueMessage('Cart does not exist?', 'error');
		}
	}

	/**
	 * Add the product to the cart, with JS
	 * @access public
	 */
	public function addJS() {
		if(VmConfig::showDebug()) {
			vmEcho::$echoDebug = 1;
			ob_start();
		}
		$json = new stdClass();

		$virtuemart_product_ids = vRequest::getInt('virtuemart_product_id');

		if(is_array($virtuemart_product_ids)){
			$prId = reset($virtuemart_product_ids);
		} else {
			$prId = $virtuemart_product_ids;
		}

		$productT = VmModel::getModel('product')->getTable('products')->load($prId);
		$cart = VirtueMartCart::getCart( false, array(), NULL, $productT->virtuemart_vendor_id);

		if ($cart) {
			$view = $this->getView ('cart', 'json');
			$virtuemart_category_id = shopFunctionsF::getLastVisitedCategoryId();

			$products = $cart->add($virtuemart_product_ids );

			$view->setLayout('padded');
			$json->stat = '1';

			if(!$products or count($products) == 0){
				$product_name = vRequest::getWord('pname');
				if(is_array($virtuemart_product_ids)){
					$pId = $virtuemart_product_ids[0];
				} else {
					$pId = $virtuemart_product_ids;
				}
				if($product_name && $pId) {
					$view->product_name = $product_name;
					$view->virtuemart_product_id = $pId;
				} else {
					$json->stat = '2';
				}
				$view->setLayout('perror');
			}
			if(!empty($errorMsg)){
				$json->stat = '2';
				$view->setLayout('perror');
			}

			$view->assignRef('products',$products);
			$view->assignRef('errorMsg',$errorMsg);

			if(!VmConfig::showDebug()) {
				ob_start();
			}
			$view->display ();
			$json->msg = ob_get_clean();
			if(VmConfig::showDebug()) {
				vmEcho::$echoDebug = 0;
			}

		} else {
			$json->msg = '<a href="' . JRoute::_('index.php?option=com_virtuemart', FALSE) . '" >' . vmText::_('COM_VIRTUEMART_CONTINUE_SHOPPING') . '</a>';
			$json->msg .= '<p>' . vmText::_('COM_VIRTUEMART_MINICART_ERROR') . '</p>';
			$json->stat = '0';
		}

		echo json_encode($json);
		jExit();
	}

	/**
	 * Add the product to the cart, with JS
	 *
	 * @access public
	 */
	public function viewJS() {

		$cart = VirtueMartCart::getCart(false);
		$cart -> prepareCartData();
		$data = $cart -> prepareAjaxData(true);

		echo json_encode($data);
		Jexit();
	}

	/**
	 * For selecting couponcode to use, opens a new layout
	 */
	public function edit_coupon() {

		$view = $this->getView('cart', 'html');
		$view->setLayout('edit_coupon');

		// Display it all
		$view->display();
	}

	/**
	 * Store the coupon code in the cart
	 * @author Max Milbers
	 */
	public function setcoupon() {

		$this->updatecart();
	}


	/**
	 * For selecting shipment, opens a new layout
	 */
	public function edit_shipment() {


		$view = $this->getView('cart', 'html');
		$view->setLayout('select_shipment');

		// Display it all
		$view->display();
	}

	/**
	 * To select a payment method
	 */
	public function editpayment() {

		$view = $this->getView('cart', 'html');
		$view->setLayout('select_payment');

		// Display it all
		$view->display();
	}

	/**
	 * Delete a product from the cart
	 * @access public
	 */
	public function delete() {
		$mainframe = JFactory::getApplication();
		/* Load the cart helper */
		$cart = VirtueMartCart::getCart();
		$cart->storeCartSession = true;
		if ($cart->removeProductCart())
			$mainframe->enqueueMessage(vmText::_('COM_VIRTUEMART_PRODUCT_REMOVED_SUCCESSFULLY'));
		else
			$mainframe->enqueueMessage(vmText::_('COM_VIRTUEMART_PRODUCT_NOT_REMOVED_SUCCESSFULLY'), 'error');

		$this->display();
	}

	public function getManager(){
		$id = vmAccess::getBgManagerId();
		return JFactory::getUser( $id );
	}


	/**
	 * Change the shopper
	 *
	 * @author Maik Künnemann
	 */
	public function changeShopper() {
		vRequest::vmCheckToken() or jexit ('Invalid Token');
		$app = JFactory::getApplication();

		$redirect = vRequest::getString('redirect',false);
		if($redirect){
			$red = $redirect;
		} else {
			$red = JRoute::_('index.php?option=com_virtuemart&view=cart');
		}

		$id = vmAccess::getBgManagerId();
		$current = JFactory::getUser( );;
		$manager = vmAccess::manager('user');
		if(!$manager){
			vmdebug('Not manager ',$id,$current);
			$app->enqueueMessage(vmText::sprintf('COM_VIRTUEMART_CART_CHANGE_SHOPPER_NO_PERMISSIONS', $current->name .' ('.$current->username.')'), 'error');
			$app->redirect($red);
			return false;
		}

		$userID = vRequest::getCmd('userID');
		if($manager and !empty($userID) and $userID!=$current->id ){
			if($userID == $id){

			} else if(vmAccess::manager('core',$userID)){
				vmdebug('Manager want to change to  '.$userID,$id,$current);
				//if($newUser->authorise('core.admin', 'com_virtuemart') or $newUser->authorise('vm.user', 'com_virtuemart')){
				$app->enqueueMessage(vmText::sprintf('COM_VIRTUEMART_CART_CHANGE_SHOPPER_NO_PERMISSIONS', $current->name .' ('.$current->username.')'), 'error');
				$app->redirect($red);
			}
		}

		$searchShopper = vRequest::getString('searchShopper');

		if(!empty($searchShopper)){
			$this->display();
			return false;
		}

		//update session
		$session = JFactory::getSession();
		$adminID = $session->get('vmAdminID');
		if(!isset($adminID)) {
			$session->set('vmAdminID', vmCrypt::encrypt($current->id));
		}

		if(!empty($userID)){
			$newUser = JFactory::getUser($userID);
			$session->set('user', $newUser);
			session_write_close();
			session_start();
		} else {
			$newUser = new stdClass();
			$newUser->email = '';
		}

		$cart = VirtueMartCart::getCart();
		//behaviour on admin change shopper
		if (VmConfig::get('ChangeShopperDeleteCart', 1)) {

//		Changing shopper empties all existing cart data and give new cart id
			$cart->resetEntireCart();
			//vmdebug('Cart deleted',$cart);
		}

		if(!empty($userID)){
			$usermodel = VmModel::getModel('user');
			$data = $usermodel->getUserAddressList($userID, 'BT');

			$cart->BT = array();
			$cart->BT['email'] = $newUser->email;
			if(isset($data[0])){
				$cart->saveAddressInCart($data[0], 'BT');
			}
		} else {
			$cart->BT = array();
		}


		$cart->ST = array();
		$cart->STsameAsBT = 1;
		$cart->selected_shipto = 0;
		$cart->virtuemart_shipmentmethod_id = 0;
		$cart->virtuemart_paymentmethod_id = 0;

		$cart->setCartIntoSession();
		$this->resetShopperGroup(false);

		$newName = empty($newUser->name)? '':$newUser->name;
		$newUserName = empty($newUser->username)? '':$newUser->username;
		$msg = vmText::sprintf('COM_VIRTUEMART_CART_CHANGED_SHOPPER_SUCCESSFULLY', $newName .' ('.$newUserName.')');

		if(empty($userID)){
			$red = JRoute::_('index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=BT&new=1');
			$msg = vmText::sprintf('COM_VIRTUEMART_CART_CHANGED_SHOPPER_SUCCESSFULLY','');
		}

		$app->enqueueMessage($msg, 'info');
		$app->redirect($red);
	}

	/**
	 * Change the shopperGroup
	 *
	 * @author Maik Künnemann
	 */
	public function changeShopperGroup() {
		vRequest::vmCheckToken() or jexit ('Invalid Token');
		$app = JFactory::getApplication();

		$redirect = vRequest::getString('redirect',false);
		if($redirect){
			$red = $redirect;
		} else {
			$red = JRoute::_('index.php?option=com_virtuemart&view=cart');
		}

		$jUser = JFactory::getUser( );
		$manager = vmAccess::manager('user');
		if(!$manager){
			$app->enqueueMessage(vmText::sprintf('COM_VIRTUEMART_CART_CHANGE_SHOPPER_NO_PERMISSIONS', $jUser->name .' ('.$jUser->username.')'), 'error');
			$app->redirect($red);
			return false;
		}

		$userModel = VmModel::getModel('user');
		$vmUser = $userModel->getCurrentUser();

		$toAdd = array_diff(vRequest::getInt('virtuemart_shoppergroup_id'), $vmUser->shopper_groups);
		$toRemove = array_diff($vmUser->shopper_groups, vRequest::getInt('virtuemart_shoppergroup_id'));

		//update session
		$session = JFactory::getSession();

		$add = $session->get('vm_shoppergroups_add',array(),'vm');
		if(!empty($add)){
			if(!is_array($add)) $add = (array)$add;
			$toAdd = array_merge($add, $toAdd);
			$toAdd = array_unique($toAdd);
		}
		if(!empty($toRemove)){
			$toAdd = array_diff($toAdd, $toRemove);
		}
		$session->set('vm_shoppergroups_add', $toAdd, 'vm');

		$remove = $session->get('vm_shoppergroups_remove',array(),'vm');
		if($remove!==0){
			if(!is_array($remove)) $remove = (array)$remove;
			$toRemove = array_merge($remove, $toRemove);
			$toRemove = array_unique($toRemove);
		}
		if(!empty($toAdd)){
			$toRemove = array_diff($toRemove,$toAdd);
		}
		$session->set('vm_shoppergroups_remove', $toRemove, 'vm');
		$session->set('vm_shoppergroups_set.' . $vmUser->virtuemart_user_id, TRUE, 'vm');
		$session->set('tempShopperGroups', TRUE, 'vm');

		$msg = vmText::sprintf('COM_VIRTUEMART_CART_CHANGED_SHOPPERGROUP_SUCCESSFULLY');

		$app->enqueueMessage($msg, 'info');
		$app->redirect($red);
	}

	public function resetShopperGroup($exeRedirect = true) {

		$app = JFactory::getApplication();

		$redirect = vRequest::getString('redirect',false);
		if($redirect){
			$red = $redirect;
		} else {
			$red = JRoute::_('index.php?option=com_virtuemart&view=cart');
		}

		$current = JFactory::getUser( );
		$manager = vmAccess::manager('user');
		if(!$manager){
			$app->enqueueMessage(vmText::sprintf('COM_VIRTUEMART_CART_CHANGE_SHOPPER_NO_PERMISSIONS', $current->name .' ('.$current->username.')'), 'error');
			$app->redirect($red);
			return false;
		}

		//update session
		$session = JFactory::getSession();
		$session->set('vm_shoppergroups_add', array(), 'vm');
		$session->set('vm_shoppergroups_remove', array(), 'vm');
		$session->set('tempShopperGroups', FALSE, 'vm');

		$msg = vmText::sprintf('COM_VIRTUEMART_CART_RESET_SHOPPERGROUP_SUCCESSFULLY');

		if($exeRedirect) {
			$app->enqueueMessage($msg, 'info');
			$app->redirect($red);
		}
	}


	function cancel() {

		$cart = VirtueMartCart::getCart();
		if ($cart) {
			$cart->setOutOfCheckout();
		}
		$this->display();
	}

}

//pure php no Tag
