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
            $token = $this->getToken();
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
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $token,
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $data = json_decode($response, true);
            if (isset($data['tempPreviewImageUrl'])) {
                $tempPreviewImageUrl = $data['tempPreviewImageUrl'];
                //$html .= "";
            }
        }

        $html;
       
    }

    public function getToken() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
		->select('*')
		->from($db->quoteName('#__zakeke_tokens'))
		->where($db->quoteName('id') . ' = 1');

		$db->execute();

        $db->setQuery($query);
        $stored = $db->loadObject();
        return $stored->access_token;
    }

    public function plgVmConfirmedOrder($cart, $order)
    {
        $orderId = $order['details']['BT']->virtuemart_order_id;
        $designId = $cart->products[0]->customProductData['22']['191']['comment']; // Adjust the indices as needed

        if (!empty($designId)) {
            $token = $this->getToken();
            $curl = curl_init();

            $data = [
                'orderId' => '12345', // Example order ID
                'amount' => 100.00,   // Example amount
                'currency' => 'USD',  // Example currency
            ];


            curl_setopt_array($curl, array(
                //CURLOPT_URL => 'https://api.zakeke.com/v3/orders/' . $orderId . '/designs/' . $designId,
                CURLOPT_URL => 'https://cdn.r2.zakeke.com/v2/order',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data), // Attach the JSON payload
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json'
                ),
                CURLOPT_POSTFIELDS => json_encode(array(
                    'orderId' => $orderId,
                    'designId' => $designId
                ))
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $data = json_decode($response, true);

            if (isset($data['success']) && $data['success'] == true) {
                // Handle successful response
            } else {
                // Handle error response
            }
        }
    }

    private function getCustomImageUrl($designId)
    {
    }
}