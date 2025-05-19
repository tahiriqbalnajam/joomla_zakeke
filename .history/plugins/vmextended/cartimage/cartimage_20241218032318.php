<?php
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

class PlgVMExtendedCartimage extends vmCustomPlugin
{
    protected $autoloadLanguage = true;

    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        if (!class_exists('VmConfig')) {
            require_once JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php';
        }
        VmConfig::loadConfig();
    }

    public function plgVmOnViewCart($product, &$productrow, &$html)
    {
        //die('dadsfasdf');
        // Check if product has custom data
        $product->product_thumb_image = 'https://cdn.r2.zakeke.com/files/images/design/previews/2024/12/16/421896508eb144cea3adc07f852560d1.png';

        if (isset($product->customProductData)) {
            foreach ($product->customProductData as $field) {
                if (isset($field['designid'])) {
                    // Replace image URL with custom preview URL
                   echo  $newImageUrl = $this->getCustomImageUrl($field['designid']);
                    if ($newImageUrl) {
                        $product->product_thumb_image = $newImageUrl;
                        break;
                    }
                }
            }
        }
        return true;
    }
    function plgVmOnViewCartModule( $product, $row,&$html) {
        $previewUrl = "https://cdn.r2.zakeke.com/files/images/design/previews/2024/12/16/421896508eb144cea3adc07f852560d1.png";
        $product->images[0]->file_url_thumb = $previewUrl;
        $product->product_name = 'test';
    }

    public function plgVmOnAddToCartFilter(&$product, &$customfield, &$customProductData, &$customFiltered) {
        echo $product->product_thumb_image;
        echo $product->file_name_thumb;
        echo $product->file_url;
        echo $product->product_thumb_image;
        $previewUrl = "https://cdn.r2.zakeke.com/files/images/design/previews/2024/12/16/421896508eb144cea3adc07f852560d1.png";
        $image = new stdClass();
        $image->file_url = $previewUrl;
        $image->file_url_thumb = $previewUrl;
        $image->file_name = 'zakeke_preview.png';
        $image->file_name_thumb = 'zakeke_preview.png';
        $image->file_title = $product->product_name;
        
        // Replace product images array
        $product->images = array($image);

        // $product->product_thumb_image = 'https://cdn.r2.zakeke.com/files/images/design/previews/2024/12/16/421896508eb144cea3adc07f852560d1.png';
        // $product->file_name_thumb = 'https://cdn.r2.zakeke.com/files/images/design/previews/2024/12/16/421896508eb144cea3adc07f852560d1.png';
        // $product->file_url = 'https://cdn.r2.zakeke.com/files/images/design/previews/2024/12/16/421896508eb144cea3adc07f852560d1.png';
        
    }

    public function plgVmOnAddToCart(&$cart){
        // echo sizeof($cart->products);
        // foreach($cart->products as $product_id => $product) {
        //     print_r($product->images);
        //     //echo $product->product_name;
        //     $product->quantity = 10;
        // }
        // foreach ($cart->cartProductsData as $cart_key=>$row) {
        //     print_r($row);
        //     if (!isset($cart->products[$cart_key])) return 0;
		// 		//if (isset($cart->products[$cart_key])) {
		// 			$cart->products[$cart_key]->quantity = 10; 
		// 		//}
		// 		unset($cart->cartProductsData[$cart_key]);
		// }
		
		//self::syncCart($cart); 

    }
    public function plgVmOnViewCartVM3(&$product, &$productCustom, &$html)
    {
        
        // $previewUrl = "https://cdn.r2.zakeke.com/files/images/design/previews/2024/12/16/421896508eb144cea3adc07f852560d1.png";
        // $image = new stdClass();
        //         $image->file_url = $previewUrl;
        //         $image->file_url_thumb = $previewUrl;
        //         $image->file_name = 'zakeke_preview.png';
        //         $image->file_name_thumb = 'zakeke_preview.png';
        //         $image->file_title = $product->product_name;
                
        //         // Replace product images array
        //         $product->images = array($image);
                
        //         // Force virtuemart to use our image
        //         if(isset($product->virtuemart_media_id)) {
        //             unset($product->virtuemart_media_id);
        //         }
        // //$product->images[0]->file_url = 'adfasdf';
        // //$product->images[0]->file_url_thumb = $previewUrl;
        // //$product->images[0]->file_url = 'https://cdn.r2.zakeke.com/files/images/design/previews/2024/12/16/421896508eb144cea3adc07f852560d1.png';
        // echo '<pre>';
        // print_r($product->images[0]);
        // echo '</pre>';
        // $product->images[0] = '';
    }

    private function getCustomImageUrl($designId)
    {
        // Replace with your logic to get image URL based on design ID
        // Example:
        return 'https://your-domain.com/designs/' . $designId . '/preview.jpg';
    }
}