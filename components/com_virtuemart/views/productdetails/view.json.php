<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author
* @link https://virtuemart.net
* @copyright Copyright (c) 2024 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: view.json.php 9572 2017-06-07 15:03:30Z kkmediaproduction $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author
 */
class VirtuemartViewProductdetails extends VmView {

	var $json = array();

	
	function display($tpl = null) {

		/**
		 * Json task for recalculation of prices
		 *
		 * @author Max Milbers
		 * @author Patrick Kohl
		 */
	//	public function recalculate () {

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
			echo vmJsApi::safe_json_encode ($priceFormated);
			jExit ();

	}

}
// pure php no closing tag
