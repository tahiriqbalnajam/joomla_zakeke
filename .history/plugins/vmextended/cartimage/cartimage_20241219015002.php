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

    }
    function plgVmOnViewCartModule( $product, $row,&$html) {

    }

    public function plgVmOnAddToCartFilter(&$product, &$customfield, &$customProductData, &$customFiltered) {

    }

    public function plgVmOnAddToCart(&$cart){


    }
    public function plgVmOnViewCartVM3(&$product, &$productCustom, &$html)
    {
        $designId = $product->customProductData['22']['191']['comment'];
        if ($productCustom->custom_title == 'zakek_designid' && !empty($designId)) {
            $this->getToken();
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.zakeke.com/v3/designs/' . $designId . '/2',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            print_r($response);
            $data = json_decode($response, true);
            print_r($data);
            if (isset($data['tempPreviewImageUrl'])) {
                $tempPreviewImageUrl = $data['tempPreviewImageUrl'];
                $html .= "<script>
                            jQuery(document).ready(function() {
                                jQuery('.cart-images img').attr('src', '$tempPreviewImageUrl');
                            });
                            </script>";
            }
        }
       
    }

    public function getToken() {
        $query = $db->getQuery(true)
		->select('*')
		->from($db->quoteName('#__zakeke_tokens'))
		->where($db->quoteName('id') . ' = 1');

		$db->execute();

        $db->setQuery($query);
        $stored = $db->loadObject();
        return $stored->access_token;
    }

    private function getCustomImageUrl($designId)
    {
    }
}