<?php
/**
 *
 * Description
 *
 * @package    VirtueMart
 * @subpackage
 * @author Max Milbers, Stan
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2020 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: productdetails.php 11074 2024-10-21 14:02:09Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined ('_JEXEC') or die('Restricted access');

// Load the controller framework
jimport ('joomla.application.component.controller');

/**
 * VirtueMart Component Controller
 *
 * @package VirtueMart
 * @author Max Milbers
 */
class VirtueMartControllerProductdetails extends JControllerLegacy {

	public function __construct () {

		parent::__construct ();
		$this->registerTask ('recommend', 'MailForm');
		$this->registerTask ('askquestion', 'MailForm');
	}

	function display($cachable = false, $urlparams = false) {

		$format = vRequest::getCmd ('format', 'html');
		$tmpl = vRequest::getCmd('tmpl',false);

		$viewName = 'Productdetails';
		if ($format == 'pdf') {
			$viewName = 'Pdf';
		} else	//We override the format here, because we need actually the same data.
			if ($format == 'raw' and $tmpl == 'component') {
			$format = 'html';
		}

		$view = $this->getView ($viewName, $format);

		$view->display ();
	}

	/**
	 * Send the ask question email.
	 *
	 * @author Kohl Patrick, Christopher Roussel
	 * @author Max Milbers
	 */
	public function mailAskquestion () {

		JSession::checkToken () or jexit ('Invalid Token');

		$app = JFactory::getApplication ();
		if(empty(VmConfig::get('ask_question',false)) and empty(VmConfig::get ('askprice', false))){
			vmWarn('Function disabled');
			$app->redirect (JRoute::_ ('index.php?option=com_virtuemart&tmpl=component&view=productdetails&task=askquestion&virtuemart_product_id=' . vRequest::getInt ('virtuemart_product_id', 0)) );
			return; 
		}

		$view = $this->getView ('askquestion', 'html');

		$vars = array();
		$min = VmConfig::get ('asks_minimum_comment_length', 50) + 1;
		$max = VmConfig::get ('asks_maximum_comment_length', 2000) - 1;
		$commentSize = vRequest::getString ('comment');
		if (function_exists('mb_strlen')) {
			$commentSize =  mb_strlen($commentSize);
		} else {
			$commentSize =  strlen($commentSize);
		}

		$validMail = filter_var (vRequest::getVar ('email'), FILTER_VALIDATE_EMAIL);

		if ($commentSize < $min or $commentSize > $max or !$validMail) {
			$errmsg = vmText::_ ('COM_VIRTUEMART_COMMENT_NOT_VALID_JS');
			if ($commentSize < $min) {
				$errmsg = vmText::_ ('COM_VIRTUEMART_ASKQU_CS_MIN');

			} else {
				if ($commentSize > $max) {
					$errmsg = vmText::_ ('COM_VIRTUEMART_ASKQU_CS_MAX');

				} else {
					if (!$validMail) {
						$errmsg = vmText::_ ('COM_VIRTUEMART_ASKQU_INV_MAIL');

					}
				}
			}
			vmWarn($errmsg);
			$this->setRedirect (JRoute::_ ('index.php?option=com_virtuemart&tmpl=component&view=productdetails&task=askquestion&virtuemart_product_id=' . vRequest::getInt ('virtuemart_product_id', 0)) );
			return;
		}
		
		$msg = shopFunctionsF::checkCaptcha('ask_captcha');
		$session = JFactory::getSession(); 
		if ($msg !== TRUE) {
				$askquestionform = array('name' => vRequest::getVar ('name'), 'email' => vRequest::getVar ('email'), 'comment' => vRequest::getString ('comment'));
				$session->set('askquestion', $askquestionform, 'vm');
				vmWarn($msg);
				$this->setRedirect (JRoute::_ ('index.php?option=com_virtuemart&tmpl=component&view=productdetails&task=askquestion&virtuemart_product_id=' . vRequest::getInt ('virtuemart_product_id', 0)) );
				return;
		}
		else {
			$session->set('askquestion', array());
		}
		

		$user = JFactory::getUser ();
		if (empty($user->id)) {
			$fromMail = vRequest::getVar ('email'); //is sanitized then
			$fromName = vRequest::getVar ('name', ''); //is sanitized then
			$fromMail = str_replace (array('\'', '"', ',', '%', '*', '/', '\\', '?', '^', '`', '{', '}', '|', '~'), array(''), $fromMail);
			$fromName = str_replace (array('\'', '"', ',', '%', '*', '/', '\\', '?', '^', '`', '{', '}', '|', '~'), array(''), $fromName);
		} else {
			$fromMail = $user->email;
			$fromName = $user->name;
		}
		$vars['user'] = array('name' => $fromName, 'email' => $fromMail);

		$virtuemart_product_id = vRequest::getInt ('virtuemart_product_id', 0);
		$productModel = VmModel::getModel ('product');

		$vars['product'] = $productModel->getProduct ($virtuemart_product_id);

		$vendorModel = VmModel::getModel ('vendor');
		$VendorEmail = $vendorModel->getVendorEmail ($vars['product']->virtuemart_vendor_id);

		JPluginHelper::importPlugin ('system');
		vDispatcher::importVMPlugins('userfield');
		vDispatcher::trigger ('plgVmOnAskQuestion', array(&$VendorEmail, &$vars, &$view));

		$vars['vendor'] = array('vendor_store_name' => $fromName);

		if (shopFunctionsF::renderMail ('askquestion', $VendorEmail, $vars, 'productdetails',true)) {
			$string = 'COM_VIRTUEMART_MAIL_SEND_SUCCESSFULLY';
		} else {
			$string = 'COM_VIRTUEMART_MAIL_NOT_SEND_SUCCESSFULLY';
		}
		$app->enqueueMessage (vmText::_ ($string));


		$view->setLayout ('mail_confirmed');
		$view->display ();
	}

	/**
	 * Send the Recommend to a friend email.
	 *
	 * @author Kohl Patrick
	 * @author Max Milbers
	 */
	public function mailRecommend () {

		JSession::checkToken () or jexit ('Invalid Token');

		$app = JFactory::getApplication ();
		if(!VmConfig::get('show_emailfriend',false)){
			vmWarn('Function disabled');
			$app->redirect (JRoute::_ ('index.php?option=com_virtuemart&tmpl=component&view=productdetails&task=askquestion&virtuemart_product_id=' . vRequest::getInt ('virtuemart_product_id', 0)) );
			return; 
		}
		
		$msg = shopFunctionsF::checkCaptcha('ask_captcha');
		$session = JFactory::getSession(); 
		if ($msg !== TRUE) {
				$mailrecommend = array('email' => vRequest::getVar ('email'), 'comment' => vRequest::getString ('comment'));
				$session->set('mailrecommend', $mailrecommend, 'vm');
				vmWarn($msg);
				$this->setRedirect (JRoute::_ ('index.php?option=com_virtuemart&tmpl=component&view=productdetails&task=recommend&virtuemart_product_id=' . vRequest::getInt ('virtuemart_product_id', 0)) );
				return;
		}
		else {
			$session->set('mailrecommend', 0, 'vm');
		}
		


		$vars = array();

		$toMail = vRequest::getVar ('email'); //is sanitized then
		$toMail = str_replace (array('\'', '"', ',', '%', '*', '/', '\\', '?', '^', '`', '{', '}', '|', '~'), array(''), $toMail);

		if (shopFunctionsF::renderMail ('recommend', $toMail, $vars, 'productdetails', TRUE)) {
			vmInfo('COM_VIRTUEMART_MAIL_SEND_SUCCESSFULLY');
		} else {
			vmError('COM_VIRTUEMART_MAIL_NOT_SEND_SUCCESSFULLY');
		}

		$view = $this->getView ('recommend', 'html');

		$view->setLayout ('mail_confirmed');
		$view->display ();
	}

	/**
	 *  Ask Question form
	 * Recommend form for Mail
	 */
	public function MailForm () {

		if (vRequest::getCmd ('task') == 'recommend') {
			$view = $this->getView ('recommend', 'html');
		} else {
			$view = $this->getView ('askquestion', 'html');
		}

		// Set the layout
		$view->setLayout ('form');

		// Display it all
		$view->display ();
	}

	/**
	 * Add or edit a review
	 */
	public function review () {

		$model = VmModel::getModel ('ratings');
		$virtuemart_product_id = vRequest::getInt('virtuemart_product_id',0);

		$allowReview = $model->allowReview($virtuemart_product_id);
		$allowRating = $model->allowRating($virtuemart_product_id);
		if($allowReview || $allowRating){
			$return = $model->saveRating ();
			if ($return !== FALSE) {
				vmInfo(vmText::sprintf ('COM_VIRTUEMART_STRING_SAVED', vmText::_ ('COM_VIRTUEMART_REVIEW')));

				$data = vRequest::getPost();
				shopFunctionsF::sendRatingEmailToVendor($data);
			}
		}
		$virtuemart_category_id = vRequest::getInt('virtuemart_category_id',0);
		$this->setRedirect (JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $virtuemart_product_id.'&virtuemart_category_id='.$virtuemart_category_id, FALSE) );

	}

	/**
	 * Json task for recalculation of prices
	 *
	 * @author Max Milbers
	 * @author Patrick Kohl
	 */
/*	public function recalculate () {

		$virtuemart_product_idArray = vRequest::getInt ('virtuemart_product_id', array()); //is sanitized then
		if(is_array($virtuemart_product_idArray) and !empty($virtuemart_product_idArray[0])){
			$virtuemart_product_id = $virtuemart_product_idArray[0];
		} else {
			$virtuemart_product_id = $virtuemart_product_idArray;
		}

		$quantity = 0;
		$quantityArray = vRequest::getInt ('quantity', array()); //is sanitized then
		if(is_array($quantityArray)){
			if(!empty($quantityArray[0])){
				$quantity = $quantityArray[0];
			}
		} else {
			$quantity = (int)$quantityArray;
		}

		if (empty($quantity)) {
			$quantity = 1;
		}

		$product_model = VmModel::getModel ('product');

		if(!empty($virtuemart_product_id)){
			$prices = $product_model->getPrice ($virtuemart_product_id, $quantity);
		} else {
			jexit ();
		}
		$priceFormated = array();

		$currency = CurrencyDisplay::getInstance ();

		foreach (CurrencyDisplay::$priceNames as $name) {
			if(isset($prices[$name])){
				$priceFormated[$name] = $currency->createPriceDiv ($name, '', $prices, TRUE);
			}
		}

		$document = JFactory::getDocument ();
		// stAn: setName works in JDocumentHTML and not JDocumentRAW
		if (method_exists($document, 'setName')){
			$document->setName ('recalculate');
		}

		// Also return all messages (in HTML format!):
		// Since we are in a JSON document, we have to temporarily switch the type to HTML
		// to make sure the html renderer is actually used
		$previoustype = $document->getType();
		$document->setType('html');
		$msgrenderer = $document->loadRenderer('message');
		$priceFormated['messages'] = $msgrenderer->render('Message');
		$document->setType($previoustype);

		$app = JFactory::getApplication();
		$app->setHeader ('Cache-Control', 'no-cache, must-revalidate');
		$app->setHeader ('Expires', 'Mon, 6 Jul 2000 10:00:00 GMT');
		// Set the MIME type for JSON output.
		$document->setMimeEncoding ('application/json');
		//JResponse::setHeader ('Content-Disposition', 'attachment;filename="recalculate.json"', TRUE);
		$app->sendHeaders ();
		echo json_encode ($priceFormated);
		jexit ();
	}
*/
	public function getJsonChild () {

		$view = $this->getView ('productdetails', 'json');
		$view->display (NULL);
	}

	/**
	 * Notify customer
	 *
	 * @author Seyi Awofadeju
	 */
	public function notifycustomer () {

		$data = vRequest::getPost();
		
		$msg = shopFunctionsF::checkCaptcha('notify_captcha');
		if ($msg !== TRUE) {
			vmWarn($msg);
			$this->setRedirect (JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&layout=notify&virtuemart_product_id=' . $data['virtuemart_product_id'], FALSE) );
			return;
		}
		
		$model = VmModel::getModel ('waitinglist');
		if (!$model->adduser ($data)) {
			$msg = 'Notify Customer; Could not add user to waiting list';
			vmWarn($msg);
			$this->setRedirect (JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&layout=notify&virtuemart_product_id=' . $data['virtuemart_product_id'], FALSE) );
		} else {
			$msg = vmText::sprintf ('COM_VIRTUEMART_STRING_SAVED', vmText::_ ('COM_VIRTUEMART_CART_NOTIFY'));
			vmInfo($msg);
			$this->setRedirect (JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $data['virtuemart_product_id'], FALSE) );
		}

	}

	/**
	 * Send an email to all shoppers who bought a product
	 */
	public function sentProductEmailToShoppers () {

		$model = VmModel::getModel ('product');
	    $model->sentProductEmailToShoppers ();
	}

	/**
	 * View email layout on browser
	 */
	function viewRecommendMail(){

		$view = $this->getView('recommend', 'html');
		$viewLayout = vRequest::getCmd('layout', 'mail_html');
		$view->setLayout($viewLayout);
		$view->display();
	}

	function viewAskQuestionMail(){

		$view = $this->getView('askquestion', 'html');
		$viewLayout = vRequest::getCmd('layout', 'mail_confirmed');
		$view->setLayout($viewLayout);
		$view->display();
	}

}
// pure php no closing tag
