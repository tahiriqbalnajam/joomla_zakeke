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
        foreach ($cart->products as $product) {
            $custom_value = $this->customfieldsModel->CustomsFieldCartDisplay($prow);
            $image = $prow->images[0]->displayMediaThumb('class="img-responsive"', false);;
            if ($custom_value) {
                $custom_fields = explode("<br />", $custom_value);
                foreach ($custom_fields as $field) {
                    $clean_field = trim(strip_tags($field));
                    $parts = preg_split('/:|\s+/', $clean_field, 2);
                    
                    if (count($parts) == 2) {
                        $key = trim($parts[0]);
                        $value = trim($parts[1]);
                        
                        // Usage in existing code
                        if ($key === 'zakek_designid' && !empty($value)) {
                            $token = $this->getToken();
                            $curl = curl_init();
                            $orderData = [
                                'orderCode' => $orderId, // Unique identifier of the order on the e-commerce platform
                                'orderDate' => date('Y-m-d\TH:i:s\Z'),    // Current date and time in ISO 8601 format
                                'sessionID' => uniqid('session_', true), // Generate a unique session identifier
                                'total' => $cart->cartPrices['billTotal'], // Total order amount
                                'details' => [
                                    [
                                        'orderDetailCode' => $product->virtuemart_product_id, // ID for the line item in your system
                                        'sku' => $product->product_sku,       // Unique product identifier in e-commerce
                                        'designID' => $value,      // Unique design identifier provided by Zakeke
                                        'modelUnitPrice' => $product->prices['salesPrice'],     // Product unit price without the design price
                                        'designUnitPrice' => $product->prices['salesPrice'], // Use the product's sales price
                                        'quantity' => 1,                // Quantity of products ordered
                                    ]
                                ]
                            ];
                            $curl = curl_init();
        
                            // Set cURL options
                            curl_setopt_array($curl, [
                                CURLOPT_URL => 'https://api.zakeke.com/v2/order', // Zakeke API endpoint for registering orders
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => '',
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 30, // Timeout set to 30 seconds
                                CURLOPT_FOLLOWLOCATION => true,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => 'POST', // HTTP POST method
                                CURLOPT_POSTFIELDS => json_encode($orderData), // JSON-encoded order data
                                CURLOPT_HTTPHEADER => [
                                    'Authorization: Bearer ' . $token, // Bearer token for authentication
                                    'Content-Type: application/json',     // Content type set to JSON
                                    'Accept: application/json'            // Expect JSON response
                                ],
                            ]);
        
                            $response = curl_exec($curl);
        
                        
                            if (curl_errno($curl)) {
                                // Output cURL error
                                echo 'cURL Error: ' . curl_error($curl);
                                
                            } else {
                                // Decode and display the JSON response
                                $decodedResponse = json_decode($response, true);
                                print_r($decodedResponse); // Print the response as an associative array
                            
                            }
        
                            // Close the cURL session
                            curl_close($curl);
                            break;
                        }
                    }
                }
            }
        }
        if (!empty($designId)) {
            




           // $token = $this->getToken();  // Function to retrieve the authentication token (ensure it's correct)
            $curl = curl_init();
            
            // Set cURL options
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.zakeke.com/v2/order/112', // Use the correct endpoint and orderCode (107 is a placeholder)
                CURLOPT_RETURNTRANSFER => true,    // Ensure we get the response as a string
                CURLOPT_ENCODING => '',            // Allow all encodings
                CURLOPT_MAXREDIRS => 10,           // Max number of redirects
                CURLOPT_TIMEOUT => 30,             // Set timeout to 30 seconds
                CURLOPT_FOLLOWLOCATION => true,    // Allow redirects if needed
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,  // Use HTTP 1.1 version
                CURLOPT_CUSTOMREQUEST => 'GET',    // HTTP GET method
                CURLOPT_HTTPHEADER => [           // Set the necessary headers
                    'Authorization: Bearer ' . $token,  // Use your Bearer token for authorization
                    'Content-Type: application/json',   // Specify the content type
                    'Accept: application/json'          // Expect JSON in the response
                ],
            ]);
            
            // Execute the cURL request
            $response = curl_exec($curl);
            
            // Handle errors
            if (curl_errno($curl)) {
                echo 'cURL Error: ' . curl_error($curl);
            } else {
                // Get the HTTP response code
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                echo "HTTP Code: " . $httpCode . "\n";  // Display HTTP status code
                
                // Decode and print the response
                $decodedResponse = json_decode($response, true);
                print_r($decodedResponse);
            }
            
            // Close cURL session
            curl_close($curl);
            

            die(); 




            /*
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
            */       
        }
    }

}