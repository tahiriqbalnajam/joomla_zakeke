<?php
/**
 *
 * Show the product details page
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers, Eugen Stranz, Max Galt
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default.php 10801 2023-03-20 10:15:00Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/* Let's see if we found the product */
if (empty($this->product)) {
	echo vmText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND');
	echo '<br /><br />  ' . $this->continue_link_html;
	return;
}
?>
<?php
if(isset($_GET['customize'])) :

	

	// Get OAuth token from Zakeke API

	// Get database connection
	$db = JFactory::getDbo();

	// Check if we have a stored token
	// $db->setQuery('CREATE TABLE IF NOT EXISTS `#__zakeke_tokens` (
	// 	`id` int(11) NOT NULL AUTO_INCREMENT,
	// 	`access_token` text NOT NULL,
	// 	`expires_in` int(11) NOT NULL,
	// 	`created_at` int(11) NOT NULL,
	// 	PRIMARY KEY (`id`)
	// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
	// $db->execute();
	$query = $db->getQuery(true)
		->select('*')
		->from($db->quoteName('#__zakeke_tokens'))
		->where($db->quoteName('id') . ' = 1');

		$db->execute();

	$db->setQuery($query);
	$stored = $db->loadObject();
	$now = time();

	// If no stored token or token expired, save new one
	if (!$stored || ($stored->created_at + $stored->expires_in < $now)) {
		
		$fields = 'grant_type=client_credentials&access_type=S2S';

		$user = JFactory::getUser();
		if ($user->id) {
			$fields .= '&customercode=' . $user->id;
		} else {
			$visitor_code = isset($_COOKIE['visitor_code']) ? $_COOKIE['visitor_code'] : uniqid();
			setcookie('visitor_code', $visitor_code, time() + (86400 * 30), "/"); // 30 days
			$fields .= '&visitorcode=' . $visitor_code;
		}
		echo $fields;

		$curl = curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://api.zakeke.com/token',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => $fields,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/x-www-form-urlencoded',
			'Authorization: Basic ' . base64_encode("283732:ycHalo4JiLuk_pfCoeqRyZoUCBudPGnhGdDvOBwuOhc.")
		),
		));

		$response = curl_exec($curl);
		if (curl_errno($curl)) {
			echo "CURL Error: " . curl_error($curl);
		}
		curl_close($curl);

		$token = json_decode($response);
		//print_r($response);
		$access_token = $token->access_token;
		$expires_in = $token->expires_in;

		$object = new stdClass();
		$object->id = 1;
		$object->access_token = $access_token;
		$object->expires_in = $expires_in;
		$object->created_at = $now;
		//die('not stores');
		// Insert or update
		try {
			if (!$stored) {
				$db->insertObject('#__zakeke_tokens', $object);
			} else {
				$db->updateObject('#__zakeke_tokens', $object, 'id');
			}
		} catch (Exception $e) {
			echo "Error saving token: " . $e->getMessage();
		}
	} else {
		// Use stored token
		$access_token = $stored->access_token;
	}
	
	// Check if this product has a Zakeke customizer
	$zakekeEnabled = true; // Replace with your logic to check if Zakeke is enabled for this product

	if ($zakekeEnabled) {
		$productId = $this->product->virtuemart_product_id;
		$productSku = $this->product->product_sku;
		$productName = $this->product->product_name;
	?>
		<style>
			#zakeke-container {
				padding: 100px 0 0 0;
			}
			#zakeke-container iframe{
				width: 100%;
				border: 0;
				overflow: hidden;
			}
			@media (min-width: 768px) {
				#zakeke-container iframe {
					min-height: 719px;
					/* height: 80vh; */
				}
			}
		</style>
		<div class="container-inner">
			<div class="row">
				<div class="col-md-12">
					<div class="sp-column">
						<div id="zakeke-container"></div>
					</div>
				</div>
			</div>
		</div>
		<script src="https://portal.zakeke.com/scripts/integration/apiV2/customizer.js"></script>
		<script>
			var customizer = new ZakekeDesigner();
			//Create config object
			var config = {
				tokenOauth: "<?php echo $access_token;?>", // Replace with your actual OAuth token from Zakeke portal
				// Product configuration
				productId: "<?php echo $productId; ?>",
				productName: "<?php echo $productName; ?>",
				getProductInfo: () => {
					return data = {
						price: <?php echo $this->product->prices['salesPrice']; ?>,
						isOutOfStock: false
					};
				},
				getProductPrice: () => {
					return data = {
						price: <?php echo $this->product->prices['salesPrice']; ?>,
						isOutOfStock: false
					};
				},
				getProductAttribute: () => {
					return productData = {
								attributes: [
									// Attribute I
									{
										code: "1",
										label: "Size",
										values: [
											{ code: "m", label: "M" },
											{ code: "s", label: "S" }
										]
									},
									// Attribute II
									{
										code: "2",
										label: "Color",
										values: [
											{   code: "blue", label: "Blue" },
											{ code: "green", label: "Green" }
										]
									}
								],
								variants: [
									// Variant I
									[
										{ code: "1", value: { code: "m" } },
										{ code: "2", value: { code: "blue" } }
									],
									// Variant II
									[
										{ code: "1", value: { code: "s" } },
										{ code: "2", value: { code: "green" } }
									]
								]
							};
				},
				
				// Add to cart
				cartButtonText: "Add To Cart",
				addToCart: (data) =>{

					console.log(data);
					const myHeaders = new Headers();
					myHeaders.append("Content-Type", "application/x-www-form-urlencoded");

					const urlencoded = new URLSearchParams();
					urlencoded.append("product_id", <?php echo $productId; ?>);
					urlencoded.append("designid",data.designid);

					const requestOptions = {
					method: "POST",
					headers: myHeaders,
					body: urlencoded,
					redirect: "follow"
					};

					fetch("<?php echo  \JRoute::_('index.php?option=com_zakeke&format=json&task=ajax.addproduct', false)?>", requestOptions)
					.then((response) => response.json())
					.then((result) => {
						if(result.success) {
							console.log(result.data.url);

							//window.location.href = result.data.url;
						} else {
							console.error('Error adding product to cart');
						}
					})
					.catch((error) => console.error(error));
				},
				editAddToCart: () => { console.log('adfdsf')  },
				// Name and numbers (add to cart and edit product)
				isNamesAndNumbers: false,
				addToCartNameAndNumber: () => {console.log("Add to cart name and number");},
				editAddToCartNameAndNumber: () => {console.log("Edit add to cart name and number");},
				isMultipleVariations: true,
				addToCartBulk: () => {console.log("Add to cart bulk");},
				editAddToCartBulk: () => {console.log("Edit add to cart bulk");},
				isBulkIndividualFile: true,
				isSharedDesignUrl: true,
				getSharedDesign: () => {console.log("Get shared design");},
				ShareUrlPrefix: "https://j5.laserprint.shop/",
				// Save design
				isSaveDesign: true,
				getSaveDesign: () => {console.log("Save design");},
				onBackClicked: () => {console.log("Back clicked");},
				designId: "",
				hideVariants: true,
				inventoryManagement: false,
				priceTaxIncluded: false,
				isClientPreviewsEnabled: true,
				mobileVersion: false,
				culture: "it-IT",
				currency: "EUR",
				loadTemplateId: "",
				labelTax: "hidden",
				quantity: 1,
				selectedAttributes: {},
				//sides: ["codeSide1","codeSiden"],
				additionalData: {}
			};
			//Create Iframe
			customizer.createIframe(config);
		</script>
	<?php
	}
	?>
<?php else: ?>
	<?php
	$customFieldModel = VmModel::getModel('customfields');
	$customFields = $customFieldModel->getCustomEmbeddedProductCustomFields([$this->product->virtuemart_product_id]);

	$hasZakekeDesignId = false;
	foreach ($customFields as $customField) {
		if ($customField->custom_title == 'zakek_designid') {
			$hasZakekeDesignId = true;
			break;
		}
	}

	echo $hasZakekeDesignId ? 'yes' : 'no';
	echo shopFunctionsF::renderVmSubLayout('askrecomjs',array('product'=>$this->product));

	//vmdebug('My product',$this->product->loadFieldValues());
	
	if(vRequest::getInt('print',false)){ ?>
	<body onload="javascript:print();">
	<?php } ?>
	
	<div class="product-container productdetails-view productdetails">
	
		<?php
		// Product Navigation
		if (VmConfig::get('product_navigation', 1)) {
		?>
			<div class="product-neighbours">
			<?php
			if (!empty($this->product->neighbours ['previous'][0])) {
			$prev_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->neighbours ['previous'][0] ['virtuemart_product_id'] . '&virtuemart_category_id=' . $this->product->virtuemart_category_id, FALSE);
			echo JHtml::_('link', $prev_link, $this->product->neighbours ['previous'][0]
				['product_name'], array('rel'=>'prev', 'class' => 'previous-page','data-dynamic-update' => '1'));
			}
			if (!empty($this->product->neighbours ['next'][0])) {
			$next_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->neighbours ['next'][0] ['virtuemart_product_id'] . '&virtuemart_category_id=' . $this->product->virtuemart_category_id, FALSE);
			echo JHtml::_('link', $next_link, $this->product->neighbours ['next'][0] ['product_name'], array('rel'=>'next','class' => 'next-page','data-dynamic-update' => '1'));
			}
			?>
			<div class="clear"></div>
			</div>
		<?php } // Product Navigation END
		?>
	
		<?php // Back To Category Button
		if ($this->product->virtuemart_category_id) {
			$catURL =  JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$this->product->virtuemart_category_id, FALSE);
			$categoryName = vmText::_($this->product->category_name) ;
		} else {
			$catURL =  JRoute::_('index.php?option=com_virtuemart');
			$categoryName = vmText::_('COM_VIRTUEMART_SHOP_HOME') ;
		}
		?>
		<div class="back-to-category">
			<a href="<?php echo $catURL ?>" class="product-details" title="<?php echo $categoryName ?>"><?php echo vmText::sprintf('COM_VIRTUEMART_CATEGORY_BACK_TO',$categoryName) ?></a>
		</div>
	
		<?php // Product Title   ?>
		<h1><?php echo $this->product->product_name ?></h1>
		<?php // Product Title END   ?>
	
		<?php // afterDisplayTitle Event
		echo $this->product->event->afterDisplayTitle ?>
			<?php
		// Product Edit Link
		echo $this->edit_link;
		// Product Edit Link END
		?>
	
		<?php
		// PDF - Print - Email Icon
		if (VmConfig::get('show_emailfriend') || VmConfig::get('show_printicon') || VmConfig::get('pdf_icon')) {
		?>
			<div class="icons">
			<?php
	
			$link = 'index.php?tmpl=component&option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->virtuemart_product_id;
	
			echo $this->linkIcon($link . '&format=pdf', 'COM_VIRTUEMART_PDF', 'pdf_button', 'pdf_icon', false);
			//echo $this->linkIcon($link . '&print=1', 'COM_VIRTUEMART_PRINT', 'printButton', 'show_printicon');
			echo $this->linkIcon($link . '&print=1', 'COM_VIRTUEMART_PRINT', 'printButton', 'show_printicon',false,true,false,'class="printModal"');
			$MailLink = 'index.php?option=com_virtuemart&view=productdetails&task=recommend&virtuemart_product_id=' . $this->product->virtuemart_product_id . '&virtuemart_category_id=' . $this->product->virtuemart_category_id . '&tmpl=component';
			echo $this->linkIcon($MailLink, 'COM_VIRTUEMART_EMAIL', 'emailButton', 'show_emailfriend', false,true,false,'class="recommened-to-friend"');
			?>
			<div class="clear"></div>
			</div>
		<?php } // PDF - Print - Email Icon END
		?>
	
		<?php
		// Product Short Description
		if (!empty($this->product->product_s_desc)) {
		?>
			<div class="product-short-description">
			<?php
			/** @todo Test if content plugins modify the product description */
			echo nl2br($this->product->product_s_desc);
			?>
			</div>
		<?php
		} // Product Short Description END
	
		echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'ontop'));
		?>
	
		<div class="vm-product-container">
		<div class="vm-product-media-container">
	<?php
	echo $this->loadTemplate('images');
	?>
		</div>
	
		<div class="vm-product-details-container">
			<div class="spacer-buy-area">
	
			<?php
			// TODO in Multi-Vendor not needed at the moment and just would lead to confusion
			/* $link = JRoute::_('index2.php?option=com_virtuemart&view=virtuemart&task=vendorinfo&virtuemart_vendor_id='.$this->product->virtuemart_vendor_id);
			  $text = vmText::_('COM_VIRTUEMART_VENDOR_FORM_INFO_LBL');
			  echo '<span class="bold">'. vmText::_('COM_VIRTUEMART_PRODUCT_DETAILS_VENDOR_LBL'). '</span>'; ?><a class="modal" href="<?php echo $link ?>"><?php echo $text ?></a><br />
			 */
			?>
	
			<?php
			echo shopFunctionsF::renderVmSubLayout('rating', array('showRating' => $this->showRating, 'product' => $this->product));
	
			foreach ($this->productDisplayTypes as $type=>$productDisplayType) {
	
				foreach ($productDisplayType as $productDisplay) {
	
					foreach ($productDisplay as $virtuemart_method_id =>$productDisplayHtml) {
						?>
						<div class="<?php echo substr($type, 0, -1) ?> <?php echo substr($type, 0, -1).'-'.$virtuemart_method_id ?>">
							<?php
							echo $productDisplayHtml;
							?>
						</div>
						<?php
					}
				}
			}
	
			//In case you are not happy using everywhere the same price display fromat, just create your own layout
			//in override /html/fields and use as first parameter the name of your file
			echo shopFunctionsF::renderVmSubLayout('prices',array('product'=>$this->product,'currency'=>$this->currency));
			?> <div class="clear"></div><?php
			echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$this->product));
			// Check if the product is customizable
			$customFieldModel = VmModel::getModel('customfields');
			$customFields = $customFieldModel->getCustomEmbeddedProductCustomFields([$this->product->virtuemart_product_id]);

			$isCustomizable = false;
			foreach ($customFields as $customField) {
				if ($customField->custom_title == 'Is Customizable' && $customField->customfield_value == '1') {
					$isCustomizable = true;
					break;
				}
			}

			if ($isCustomizable) {
				$base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'];
 				$url = $base_url . $_SERVER["REQUEST_URI"];
				echo '
				<div class="customize_btn">
					<button class="c_btn" onclick="window.location.href=\'' . $url. '?customize=1\'">Design erstellen</button>
				</div>';
			}
			//echo '<button class="btn btn-primary" onclick="window.location.href=\'' . JUri::current() . '?customize=1\'">Customize</button>';
			echo shopFunctionsF::renderVmSubLayout('stockhandle',array('product'=>$this->product));
	
			// Ask a question about this product
			if (VmConfig::get('ask_question', 0) == 1) {
				$askquestion_url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&task=askquestion&virtuemart_product_id=' . $this->product->virtuemart_product_id . '&virtuemart_category_id=' . $this->product->virtuemart_category_id . '&tmpl=component', FALSE);
				?>
				<div class="ask-a-question">
					<a class="ask-a-question" href="<?php echo $askquestion_url ?>" rel="nofollow" ><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_ENQUIRY_LBL') ?></a>
				</div>
			<?php
			}
			?>
	
			<?php
			// Manufacturer of the Product
			if (VmConfig::get('show_manufacturers', 1) && !empty($this->product->virtuemart_manufacturer_id)) {
				echo $this->loadTemplate('manufacturer');
			}
			?>
	
			</div>
		</div>
		<div class="clear"></div>
	
	
		</div>
	<?php
		$count_images = count ($this->product->images);
		if ($count_images > 1) {
			echo $this->loadTemplate('images_additional');
		}
	
		// event onContentBeforeDisplay
		echo $this->product->event->beforeDisplayContent; ?>
	
		<?php
		//echo ($this->product->product_in_stock - $this->product->product_ordered);
		// Product Description
		if (!empty($this->product->product_desc)) {
			?>
			<div class="product-description" >
			<!-- <span class="title"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_DESC_TITLE') ?></span> -->
		<?php echo $this->product->product_desc; ?>
			</div>
		<?php
		} // Product Description END
	
		echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'normal'));
	
		// Product Packaging
		$product_packaging = '';
		if ($this->product->product_box) {
		?>
			<div class="product-box">
			<?php
				echo vmText::_('COM_VIRTUEMART_PRODUCT_UNITS_IN_BOX') .$this->product->product_box;
			?>
			</div>
		<?php } // Product Packaging END ?>
	
		<?php
		echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'onbot'));
	
		echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'related_products','class'=> 'product-related-products','customTitle' => true ));
	
		echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'related_categories','class'=> 'product-related-categories'));
	
		?>
	
	<?php // onContentAfterDisplay event
	echo $this->product->event->afterDisplayContent;
	
	echo $this->loadTemplate('reviews');
	
	// Show child categories
	if ($this->cat_productdetails)  {
		echo $this->loadTemplate('showcategory');
	}
	
	$j = 'jQuery(document).ready(function($) {
		$("form.js-recalculate").each(function(){
			if ($(this).find(".product-fields").length && !$(this).find(".no-vm-bind").length) {
				var id= $(this).find(\'input[name="virtuemart_product_id[]"]\').val();
				Virtuemart.setproducttype($(this),id);
	
			}
		});
	});';
	//vmJsApi::addJScript('recalcReady',$j);
	
	if(VmConfig::get ('jdynupdate', TRUE)){
	
		/** GALT
		 * Notice for Template Developers!
		 * Templates must set a Virtuemart.container variable as it takes part in
		 * dynamic content update.
		 * This variable points to a topmost element that holds other content.
		 */
	/*	$j = "Virtuemart.container = jQuery('.productdetails-view');
	Virtuemart.containerSelector = '.productdetails-view';
	//Virtuemart.recalculate = true;	//Activate this line to recalculate your product after ajax
	";
	
		vmJsApi::addJScript('ajaxContent',$j);*/
	
		$j = "jQuery(document).ready(function($) {
		Virtuemart.stopVmLoading();
		var msg = '';
		$('a[data-dynamic-update=\"1\"]').off('click', Virtuemart.startVmLoading).on('click', {msg:msg}, Virtuemart.startVmLoading);
		$('[data-dynamic-update=\"1\"]').off('change', Virtuemart.startVmLoading).on('change', {msg:msg}, Virtuemart.startVmLoading);
	});";
	
		vmJsApi::addJScript('vmPreloader',$j);
	}
	
	echo vmJsApi::writeJS();
	
	if ($this->product->prices['salesPrice'] > 0) {
		echo shopFunctionsF::renderVmSubLayout('snippets',array('product'=>$this->product, 'currency'=>$this->currency, 'showRating'=>$this->showRating));
	}
	
	?>
	</div>
<?php endif; ?>

