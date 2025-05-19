<?php

/**
 *
 * View for the shopping cart
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers
 * @author Oscar van Eijk
 * @author RolandD
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2020 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.html.php 11045 2024-08-14 20:04:08Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * View for the shopping cart
 * @package VirtueMart
 * @author Max Milbers
 * @author Patrick Kohl
 */
class VirtueMartViewCart extends VmView {

	var $pointAddress = false;
	/* @deprecated */
	var $display_title = true;
	/* @deprecated */
	var $display_loginform = true;

	var $html = false;

	public function display($tpl = null) {


		$app = JFactory::getApplication();

		$this->prepareContinueLink();
		if (VmConfig::get('use_as_catalog',0)) {
			vmInfo('This is a catalogue, you cannot access the cart');
			$app->redirect($this->continue_link);
		}

		$pathway = $app->getPathway();
		$document = JFactory::getDocument();
		$document->setMetaData('robots','NOINDEX, NOFOLLOW, NOARCHIVE, NOSNIPPET');

		vmdebug('my layout here ',$this->_layout);
		$this->cart = VirtueMartCart::getCart();//false, array(), NULL, $vendorId);

		if(!empty($this->_layout)){
			$this->layoutName = $this->getBaseLayout();
		} else if(!empty($this->cart->layout)){
			$this->layoutName = $this->cart->layout;
		} else {
			$this->layoutName = VirtueMartCart::getCartLayoutByVmConfig();
		}


		$format = vRequest::getCmd('format');

		$this->cart->prepareVendor();

		if ($this->layoutName == 'select_shipment') {

			$this->cart->prepareCartData();
			$this->lSelectShipment();

			$pathway->addItem(vmText::_('COM_VIRTUEMART_CART_OVERVIEW'), JRoute::_('index.php?option=com_virtuemart&view=cart', FALSE));
			$pathway->addItem(vmText::_('COM_VIRTUEMART_CART_SELECTSHIPMENT'));
			$document->setTitle(vmText::_('COM_VIRTUEMART_CART_SELECTSHIPMENT'));
		} else if ($this->layoutName == 'select_payment') {

			$this->cart->prepareCartData();

			$this->lSelectPayment();

			$pathway->addItem(vmText::_('COM_VIRTUEMART_CART_OVERVIEW'), JRoute::_('index.php?option=com_virtuemart&view=cart', FALSE));
			$pathway->addItem(vmText::_('COM_VIRTUEMART_CART_SELECTPAYMENT'));
			$document->setTitle(vmText::_('COM_VIRTUEMART_CART_SELECTPAYMENT'));
		} else if ($this->layoutName == 'orderdone' or $this->layoutName == 'order_done') {
			vmLanguage::loadJLang( 'com_virtuemart_shoppers', true );
			$this->lOrderDone();

			$pathway->addItem( vmText::_( 'COM_VIRTUEMART_CART_THANKYOU' ) );
			$document->setTitle( vmText::_( 'COM_VIRTUEMART_CART_THANKYOU' ) );
			$this->cart->layout = VirtueMartCart::getCartLayoutByVmConfig();

		} else {
			vmLanguage::loadJLang('com_virtuemart_shoppers', true);

			$this->renderCompleteAddressList();

			$userFieldsModel = VmModel::getModel ('userfields');

			$userFieldsCart = $userFieldsModel->getUserFields(
				'cart'
				, array('captcha' => true, 'delimiters' => true) // Ignore these types
				, array('delimiter_userinfo','user_is_vendor' ,'username','password', 'password2', 'agreed', 'address_type') // Skips
			);

			$this->userFieldsCart = $userFieldsModel->getUserFieldsFilled(
				$userFieldsCart
				,$this->cart->cartfields
			);

			$this->currencyDisplay = CurrencyDisplay::getInstance($this->cart->pricesCurrency);

			$this->customfieldsModel = VmModel::getModel ('Customfields');

			$this->lSelectCoupon();

			$totalInPaymentCurrency = $this->getTotalInPaymentCurrency();

			$this->checkoutAdvertise = $this->cart->getCheckoutAdvertise();

			if ($this->cart->getDataValidated()) {
				if($this->cart->_inConfirm){
					$pathway->addItem(vmText::_('COM_VIRTUEMART_CANCEL_CONFIRM_MNU'));
					$document->setTitle(vmText::_('COM_VIRTUEMART_CANCEL_CONFIRM_MNU'));
					$text = vmText::_('COM_VIRTUEMART_CANCEL_CONFIRM');
					$this->checkout_task = 'cancel';
				} else {
					$pathway->addItem(vmText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU'));
					$document->setTitle(vmText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU'));
					$text = vmText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU');
					$this->checkout_task = 'confirm';
				}
			} else {
				$pathway->addItem(vmText::_('COM_VIRTUEMART_CART_OVERVIEW'));
				$document->setTitle(vmText::_('COM_VIRTUEMART_CART_OVERVIEW'));
				$text = vmText::_('COM_VIRTUEMART_CHECKOUT_TITLE');
				$this->checkout_task = 'checkout';
			}
			$dynUpdate = '';
			if( VmConfig::get('oncheckout_ajax',false)) {
				$dynUpdate=' data-dynamic-update="1" ';
			}

			$this->checkout_link_html = '<button type="submit" id="checkoutFormSubmit" name="'.$this->checkout_task.'" value="1" class="vm-button-correct" '.$dynUpdate.' ><span>' . $text . '</span> </button>';

            $multixcart = VmConfig::get('multixcart',0);
			$vendorId = '';
            if($multixcart == 'byproduct'){
                $vendorId = '&virtuemart_vendor_id='.$this->cart->vendorId;
            }
            $this->orderDoneLink = JRoute::_('index.php?option=com_virtuemart&view=cart&task=orderdone'.$vendorId);

			$forceMethods=vRequest::getInt('forceMethods',false);
			if (VmConfig::get('oncheckout_opc', 1) or $forceMethods) {

				//JPluginHelper::importPlugin('vmshipment');
				//JPluginHelper::importPlugin('vmpayment');
				//vmdebug('cart view oncheckout_opc ');
				$lSelectShipment=$this->lSelectShipment() ;
				$lSelectPayment=$this->lSelectPayment();
				if(!$lSelectShipment or !$lSelectPayment){
					if (!VmConfig::get('oncheckout_opc', 1)) {
						vmInfo('COM_VIRTUEMART_CART_ENTER_ADDRESS_FIRST');
					}
					$this->pointAddress = true;
				}
			} else {
				$this->checkPaymentMethodsConfigured();
				$this->checkShipmentMethodsConfigured();
			}

			if ($this->cart->virtuemart_shipmentmethod_id) {
				$shippingText =  vmText::_('COM_VIRTUEMART_CART_CHANGE_SHIPPING');
			} else {
				$shippingText = vmText::_('COM_VIRTUEMART_CART_EDIT_SHIPPING');
			}
			$this->assignRef('select_shipment_text', $shippingText);

			if ($this->cart->virtuemart_paymentmethod_id) {
				$paymentText = vmText::_('COM_VIRTUEMART_CART_CHANGE_PAYMENT');
			} else {
				$paymentText = vmText::_('COM_VIRTUEMART_CART_EDIT_PAYMENT');
			}
			$this->assignRef('select_payment_text', $paymentText);

			//$this->cart->prepareAddressFieldsInCart();

			if(empty($this->cart->layout) or $this->cart->layout=='orderdone') $this->cart->layout = VirtueMartCart::getCartLayoutByVmConfig();
			$this->layoutName = $this->cart->layout;

			if ($this->cart->layoutPath) {
				$this->addTemplatePath($this->cart->layoutPath);
			}

			if(!empty($this->layoutName) and $this->layoutName!='default'){
				$this->setLayout( strtolower( $this->layoutName ) );
			}
			//set order language
			$lang = vmLanguage::getLanguage();
			$order_language = $lang->getTag();
			$this->assignRef('order_language',$order_language);
		}

		if($this->cart->storeToDB){
			$this->cart->storeCart();
		}

		$this->useSSL = vmURI::useSSL();
		$this->useXHTML = false;

		$this->assignRef('totalInPaymentCurrency', $totalInPaymentCurrency);


		//We set the valid content time to 2 seconds to prevent that the cart shows wrong entries
		$document->setMetaData('expires', '1',true);
		//We never want that the cart is indexed
		$document->setMetaData('robots','NOINDEX, NOFOLLOW, NOARCHIVE, NOSNIPPET');

		if ($this->cart->_inConfirm) vmInfo('COM_VIRTUEMART_IN_CONFIRM');

		$current = JFactory::getUser();
		$this->allowChangeShopper = false;
		$this->adminID = false;
		if(VmConfig::get ('oncheckout_change_shopper')){
			$bgManagerId = vmAccess::getBgManagerId();
			$this->allowChangeShopper = vmAccess::manager('user', $bgManagerId);
		}

		$this->shopperGroupList = false;
		if($this->allowChangeShopper){
			$this->userList = $this->getUserList();
			$this->shopperGroupList = $this->getShopperGroupList();
		}

		if(VmConfig::get('oncheckout_ajax',false)){
			vmJsApi::jDynUpdate('#cart-view');
		}

		parent::display($tpl);
	}



	private function lSelectCoupon() {

		//$this->couponCode = (!empty($this->cart->couponCode) ? $this->cart->couponCode : '');
		$this->coupon_text = $this->cart->cartData['couponCode'] ? vmText::_('COM_VIRTUEMART_COUPON_CODE_CHANGE') : vmText::_('COM_VIRTUEMART_COUPON_CODE_ENTER');
	}

	/**
	* lSelectShipment
	* find al shipment rates available for this cart
	*
	* @author Valerie Isaksen
	*/

	private function lSelectShipment() {

		$this->found_shipment_method = false;
		$this->shipment_not_found_text = vmText::_('COM_VIRTUEMART_CART_NO_SHIPPING_METHOD_PUBLIC');

		$this->shipments_shipment_rates = array();
		if (!$this->checkShipmentMethodsConfigured()) {
			return;
		}

		$selectedShipment = (empty($this->cart->virtuemart_shipmentmethod_id) ? 0 : $this->cart->virtuemart_shipmentmethod_id);

		$d = VmConfig::$_debug;
		if(VmConfig::get('debug_enable_methods',false)){
			VmConfig::$_debug = 1;
		}
		$returnValues = vDispatcher::trigger('plgVmDisplayListFEShipment', array( $this->cart, $selectedShipment, &$this->shipments_shipment_rates));
		VmConfig::$_debug = $d;
		// if no shipment rate defined
		$this->found_shipment_method = count($this->shipments_shipment_rates);

		$ok = true;
		if ($this->found_shipment_method == 0)  {
			$validUserDataBT = $this->cart->validateUserData();

			if ($validUserDataBT===-1) {
				if (VmConfig::get('oncheckout_opc', 1)) {
					vmdebug('lSelectShipment $found_shipment_method === 0 show error');
					$ok = false;
				} else {
					$mainframe = JFactory::getApplication();
					vmWarn('COM_VIRTUEMART_CART_ENTER_ADDRESS_FIRST');
					$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=BT'));
				}
			}

		}

		return $ok;
	}

	/*
	 * lSelectPayment
	* find al payment available for this cart
	*
	* @author Valerie Isaksen
	*/

	private function lSelectPayment() {

		$this->payment_not_found_text = '';
		$this->payments_payment_rates = array();

		$this->found_payment_method = 0;
		$selectedPayment = empty($this->cart->virtuemart_paymentmethod_id) ? 0 : $this->cart->virtuemart_paymentmethod_id;

		$this->paymentplugins_payments = array();
		if (!$this->checkPaymentMethodsConfigured()) {
			return;
		}

		$d = VmConfig::$_debug;
		if(VmConfig::get('debug_enable_methods',false)){
			VmConfig::$_debug = 1;
		}
		$returnValues = vDispatcher::trigger('plgVmDisplayListFEPayment', array($this->cart, $selectedPayment, &$this->paymentplugins_payments));
		VmConfig::$_debug = $d;

		$this->found_payment_method =count($this->paymentplugins_payments);
		if (!$this->found_payment_method) {
			$link=''; // todo
			$this->payment_not_found_text = vmText::sprintf('COM_VIRTUEMART_CART_NO_PAYMENT_METHOD_PUBLIC', '<a href="'.$link.'" rel="nofollow">'.$link.'</a>');
		}

		$ok = true;
		if ($this->found_payment_method == 0 )  {
			$validUserDataBT = $this->cart->validateUserData();
			if ($validUserDataBT===-1) {
				if (VmConfig::get('oncheckout_opc', 1)) {
					$ok = false;
				} else {
					$mainframe = JFactory::getApplication();
					vmInfo('COM_VIRTUEMART_CART_ENTER_ADDRESS_FIRST');
					$mainframe->redirect( JRoute::_( 'index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=BT' ) );
				}
			}
		}


		return $ok;
	}

	private function getTotalInPaymentCurrency() {

		if (empty($this->cart->virtuemart_paymentmethod_id)) {
			return null;
		}

		if (!$this->cart->paymentCurrency or ($this->cart->paymentCurrency==$this->cart->pricesCurrency)) {
			return null;
		}

		$paymentCurrency = CurrencyDisplay::getInstance($this->cart->paymentCurrency);
		$totalInPaymentCurrency = $paymentCurrency->priceDisplay( $this->cart->cartPrices['billTotal'],$this->cart->paymentCurrency) ;
		$this->currencyDisplay = CurrencyDisplay::getInstance($this->cart->pricesCurrency);

		return $totalInPaymentCurrency;
	}


	private function lOrderDone() {

		$this->display_title = !isset($this->display_title) ? vRequest::getBool('display_title', true) : $this->display_title;
		$this->display_loginform = !isset($this->display_loginform) ? vRequest::getBool('display_loginform', true) : $this->display_loginform;

		//Show Thank you page or error due payment plugins like paypal express
		//Do not change this. It contains the payment form
		//$this->html = empty($this->html) ? vRequest::get('html', $this->cart->orderdoneHtml) : $this->html;
        //vmdebug('lOrderDone',$this->cart->orderdoneHtml,$this->html);
        if(!empty($this->cart->orderdoneHtml)){
            $this->html = $this->cart->orderdoneHtml;
        } else if($byRequestHtml= vRequest::get('html', false)){
            $this->html = $byRequestHtml;
        }

		$this->virtuemart_order_id = $this->cart->OrderIdOrderDone;
		//vmdebug('lOrderDone my cart',$this->html);
        if(empty($this->html) or $this->html == vmText::_('COM_VIRTUEMART_ORDER_PROCESSED')){
	        //vmdebug('lOrderDone orderCompleted $cart->virtuemart_order_id ',$this->cart->OrderIdOrderDone);
        	if(!empty($this->cart->OrderIdOrderDone)){

		        $orderM = VmModel::getModel('orders');

		        $order = $orderM->getOrder($this->cart->OrderIdOrderDone);

		        $paymM = VmModel::getModel('paymentmethod');
		        $paym = $paymM->getPayment($this->cart->virtuemart_paymentmethod_id);
		        //$this->_currentMethod = $this->cart->getPluginMethod($this->cart->virtuemart_paymentmethod_id);

		        vmLanguage::loadJLang('com_virtuemart_orders', TRUE);
		        $orderlink='';
		        $tracking = VmConfig::get('ordertracking','guests');
		        if($tracking !='none' and !($tracking =='registered' and empty($order['details']['BT']->virtuemart_user_id) )) {

			        $orderlink = 'index.php?option=com_virtuemart&view=orders&layout=details&order_number=' . $order['details']['BT']->order_number;
			        if ($tracking == 'guestlink' or ($tracking == 'guests' and empty($order['details']['BT']->virtuemart_user_id))) {
				        $orderlink .= '&order_pass=' . $order['details']['BT']->order_pass;
			        }
		        }

		        vmPSPlugin::getPaymentCurrency($paym, $order['details']['BT']->payment_currency_id);
		        $totalInPaymentCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total,$paym->payment_currency);

		        $paymentName ='';
		        vDispatcher::importVMPlugins('vmpayment');
		        $returnValues = vDispatcher::trigger('plgVmOnShowOrderFEPayment',array( $order['details']['BT']->virtuemart_order_id, $order['details']['BT']->virtuemart_paymentmethod_id,  &$paymentName));

		        $this->html = shopFunctionsF::renderVmSubLayout('orderaccessinfo',array(
			        'virtuemart_order_id' => $this->virtuemart_order_id,
			        'order_number' => $order['details']['BT']->order_number,
			        'order_pass' => $order['details']['BT']->order_pass,
			        'payment_name' => $paymentName,
			        'displayTotalInPaymentCurrency' => $totalInPaymentCurrency['display'],
			        'orderlink' =>$orderlink,
			        'method' => $paym
		        ));
			//vmdebug('lOrderDone created $this->html ',$this->html);

	        }
        }

		$this->cart->OrderIdOrderDone = 0;
		$this->cart->orderdoneHtml = '';
		//$this->cart->emptyCart();
	}

	private function checkPaymentMethodsConfigured() {

		if ($this->cart->virtuemart_paymentmethod_id) return true;

		//For the selection of the payment method we need the total amount to pay.
		$paymentModel = VmModel::getModel('Paymentmethod');
		$payments = $paymentModel->getPayments(true, false);
		if (empty($payments)) {

			$text = '';
			if(vmAccess::manager() or vmAccess::isSuperVendor()) {
				$link = JURI::root() . 'administrator/index.php?option=com_virtuemart&view=paymentmethod';
				$text = vmText::sprintf('COM_VIRTUEMART_NO_PAYMENT_METHODS_CONFIGURED_LINK', '<a href="' . $link . '" rel="nofollow">' . $link . '</a>');
			}

			vmInfo('COM_VIRTUEMART_NO_PAYMENT_METHODS_CONFIGURED', $text);
			$this->cart->virtuemart_paymentmethod_id = 0;
			return false;
		}
		return true;
	}

	private function checkShipmentMethodsConfigured() {

		if ($this->cart->virtuemart_shipmentmethod_id) return true;

		//For the selection of the shipment method we need the total amount to pay.
		$shipmentModel = VmModel::getModel('Shipmentmethod');
		$shipments = $shipmentModel->getShipments();
		if (empty($shipments)) {

			$text = '';
			$user = JFactory::getUser();
			if(vmAccess::manager() or vmAccess::isSuperVendor()) {
				$uri = vmURI::getURI();
				$link = $uri->root() . 'administrator/index.php?option=com_virtuemart&view=shipmentmethod';
				$text = vmText::sprintf('COM_VIRTUEMART_NO_SHIPPING_METHODS_CONFIGURED_LINK', '<a href="' . $link . '" rel="nofollow">' . $link . '</a>');
			}

			vmInfo('COM_VIRTUEMART_NO_SHIPPING_METHODS_CONFIGURED', $text);

			$tmp = 0;
			$this->assignRef('found_shipment_method', $tmp);
			$this->cart->virtuemart_shipmentmethod_id = 0;
			return false;
		}
		return true;
	}

	/**
	 * Todo, works only for small stores, we need a new solution there with a bit filtering
	 * For example by time, if already shopper, and a simple search
	 * @return object list of users
	 */
	function getUserList() {

		$result = false;

		if($this->allowChangeShopper){
			$this->adminID = vmAccess::getBgManagerId();
			$superVendor = vmAccess::isSuperVendor($this->adminID,'user');
			if($superVendor){
				$uModel = VmModel::getModel('user');
				$result = $uModel->getSwitchUserList($superVendor,$this->adminID);
			}
		}
		if(!$result) $this->allowChangeShopper = false;
		return $result;
	}

	function getShopperGroupList() {

		$result = false;

		if($this->allowChangeShopper){
			$userModel = VmModel::getModel('user');
			$vmUser = $userModel->getCurrentUser();

			$attrs = array();
			$attrs['style']='width: 220px;';

			$result = ShopFunctions::renderShopperGroupList($vmUser->shopper_groups, TRUE, 'virtuemart_shoppergroup_id', 'COM_VIRTUEMART_DRDOWN_AVA2ALL', $attrs);
		}

		return $result;
	}

	function renderCompleteAddressList(){

		$addressList = false;

		if($this->cart->user->virtuemart_user_id){
			$addressList = array();
			$newBT = vmText::_('COM_VIRTUEMART_ACC_BILL_DEF') . '<br/>';
			foreach($this->cart->user->userInfo as $userInfo){
				$address = $userInfo->loadFieldValues(false);
				if($address->address_type=='BT'){
					$address->virtuemart_userinfo_id = 0;
					$address->address_type_name = $newBT;
					array_unshift($addressList,$address);
				} else {
					$address->address_type_name = !empty($address->zip) ? $address->address_type_name . ' - ' . $address->zip : $address->address_type_name . '<br/>';
					$addressList[] = $address;
				}
			}
			if(count($addressList)==0){
				$addressList[0] = new stdClass();
				$addressList[0]->virtuemart_userinfo_id = 0;
				$addressList[0]->address_type_name = $newBT;
			}

			$_selectedAddress = (
			empty($this->cart->selected_shipto)
				? $addressList[0]->virtuemart_userinfo_id // Defaults to 1st BillTo
				: $this->cart->selected_shipto
			);

			//$usermodel = VmModel::getModel('user');
			//$usermodel->setId($this->cart->user->virtuemart_user_id);
			$this->cart->lists['shipTo'] = JHtml::_('select.radiolist', $addressList, 'shipto', null, 'virtuemart_userinfo_id', 'address_type_name', $_selectedAddress);
			//$this->cart->lists['shipTo'] = ShopFunctionsF::generateStAddressList($this,$usermodel, 'ST');
			$this->cart->lists['billTo'] = empty($addressList[0]->virtuemart_userinfo_id)? 0 : $addressList[0]->virtuemart_userinfo_id;
		} else {
			$this->cart->lists['shipTo'] = false;
			$this->cart->lists['billTo'] = false;
		}
	}

	static public function addCheckRequiredJs(){

		$updF = '';
		if( VmConfig::get('oncheckout_ajax',false)) {
			$updF = 'Virtuemart.updFormS(); return;';
		} else {
			$updF = 'chkOutBtn.html("<span>'.vmText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU').'</span>");
			chkOutBtn.attr("task","confirm");
			form.submit();';
		}

		$cart = VirtueMartCart::getCart();
		$requiredRed = '';

		if(VmConfig::get('CartSetRedBorderUnchecked', true) and count($cart->BT)>3 ){
			$requiredRed = 'Virtuemart.setUnCheckedRedBorder();';
		}
		$j='if (typeof Virtuemart === "undefined")
	var Virtuemart = {};
jQuery(function($) {
	Virtuemart.autocheck = function (){
		var count = 0;
    	var hit = 0;
    	console.log("my autocheck required");
    	$.each($(".required"), function (key, value){
    		count++;
    		if(this.type == "checkbox"){
	            if($(this).prop("checked")){
	                hit++;
	            }
    		} else if (this.type == "radio"){
        	    console.log("my autocheck required radio",this);
	        } else {
	            if(this.value!=""){
		            hit++;
	            }
	            console.log("my autocheck required else",this);
	        }
    		
    	});
    	var chkOutBtn = $("#checkoutFormSubmit");

    	var form = $("#checkoutForm");
    	form.find(\'input[name="task"]\').val("updateCartNoMethods");
    	
    	console.log("Required count and hit",count, hit,form);
    	if(count==hit){
    		'.$updF.'
			
		} else {
			
        	chkOutBtn.attr("task","checkout");
        	chkOutBtn.html("<span>'.vmText::_('COM_VIRTUEMART_CHECKOUT_TITLE').'</span>");
        	form.submit();
        }
	},
	Virtuemart.setUnCheckedRedBorder = function(){

        $(".required")/*.not(":checked")*/.each( function (ind) {
        
        if(this.type == "checkbox"){
        	console.log("my setUnCheckedRedBorder required checkbox",this);
        	if(!this.checked){
				// This line is for the old template tos checkbox
				$(this).parents(".vm-fieldset-tos").addClass("is-invalid");
				// This line is for the new bs5 template checkboxes including tos
				$(this).siblings("label").addClass("is-invalid");
        	}
        } else if (this.type == "radio"){
        	console.log("my setUnCheckedRedBorder required radio",this);
        } else {
        	if(this.value==""){
				$(this).addClass("is-invalid");
        	}
        	console.log("my setUnCheckedRedBorder required else",this);
        }
           
        });
    };
});
		
		
jQuery(document).ready(function( $ ){
	var chkOutBtn = $("#checkoutFormSubmit");
	var form = $("#checkoutForm");
	console.log("Autocheck on ready");

	$("#checkoutForm").find(":radio, :checkbox").bind("change", Virtuemart.autocheck);
	
	$("input[type=radio][name=virtuemart_paymentmethod_id]").unbind("change", Virtuemart.autocheck);
	$("input[type=radio][name=virtuemart_shipmentmethod_id]").unbind("change", Virtuemart.autocheck);
	
	$(".output-shipto").find("input").unbind("change", Virtuemart.autocheck);
	$(".output-shipto").find(":radio").bind("change", function(){
		chkOutBtn.attr("task","checkout");
		
		'.$updF.'
		form.submit();
	});
		'.$requiredRed.'

});';
		vmJsApi::addJScript('autocheck',$j);
	}
}

//no closing tag
