<?php
namespace Zakeke\Component\Tasks\Site\Controller;

defined('_JEXEC') or die;

use JFactory;
use Joomla\CMS\Factory;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\MVC\Controller\BaseController;

class AjaxController extends BaseController
{
    public function products()
    {
        $app = Factory::getApplication();
        $input = $app->input;

        // Get the page and search parameters from the input
        $page = $input->getInt('page', 0);
        $search = $input->getString('search', '');

        // Load the VirtueMart component
        if (!class_exists('VmConfig')) {
            require(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php');
        }
        \VmConfig::loadConfig();

        // Load the VirtueMart product model
        if (!class_exists('VirtueMartModelProduct')) {
            require(JPATH_ADMINISTRATOR . '/components/com_virtuemart/models/product.php');
        }

        $productModel = new \VirtueMartModelProduct();

        // Set the search filter
        $productModel->_searchplugin = $search;

        // Fetch the products
        $products = $productModel->getProductsInCategory(0, $page * 2, 2); // Adjust the parameters as needed

        $productModel->addImages($products, 1);

        // Extract specific fields
        $productData = [];
        foreach ($products as $product) {
            // Get the product thumbnail
            $thumbnail = '';
            if (!empty($product->images)) {
                $thumbnail = \JURI::root() . $product->images[0]->file_url;
            }

            $productData[] = [
                'code' => $product->virtuemart_product_id,
                'name' => $product->product_name,
                'thumbnail' => $thumbnail,
            ];
        }

        // Set the response header to JSON
        $app->setHeader('Content-Type', 'application/json', true);

        // Output the JSON response
        echo json_encode($productData);

        // Close the application to prevent Joomla from rendering the default view
        $app->close();
    }


    
    public function addproduct()
    {
        $app = Factory::getApplication();
        $input = $app->input;
        
        // Get and validate input
        $productId = $input->getInt('product_id', 0);
        $designid = $input->getString('designid', 1);
    
        if (!$productId) {
            echo new JsonResponse(['success' => false, 'message' => 'Invalid product ID'], 400);
            $app->close();
            return;
        }
    
        try {
            // Load VirtueMart config
            if (!class_exists('\VmConfig')) {
                require_once JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php';
            }
            \VmConfig::loadConfig();
    
            // Load cart
            if (!class_exists('\VirtueMartCart')) {
                require_once JPATH_SITE . '/components/com_virtuemart/helpers/cart.php';
            }
    
            // Debug log
            //error_log('Adding product: ' . $productId . ' quantity: ' . $quantity);
    
            $cart = \VirtueMartCart::getCart();
    
            $app = \JFactory::getApplication();
            $app->input->set('virtuemart_product_id', $productId);
            
            if(!$cart) {
                throw new \Exception('Failed to get cart instance');
            }

            // Add to cart with debug
            $post = array(
                    'quantity' => array($productId => $quantity),
                    'customProductData' => array(
                        $productId => array(
                            22 => array(
                                191 => array(
                                            'comment' => $designid,
                                        )
                                )
                        ),
                    ),
                );
            //echo new JsonResponse($post);
            $added = $cart->add($productId, $post);
            if ($added) {
                $session = Factory::getSession();
                $cart->setCartIntoSession();
                //$cart->prepareCartData();
                
                echo new JsonResponse(
                    ['url' => \JURI::root().'index.php?option=com_virtuemart&view=cart']);
            } else {
                throw new \Exception('Failed to add product to cart. Product ID: ' . $productId);
            }
    
        } catch (\Exception $e) {
            //error_log('Cart error: ' . $e->getMessage());
            echo new JsonResponse([
                'success' => false, 
                'message' => $e->getMessage(),
                'debug' => [
                    'product_id' => $productId,
                    'quantity' => $quantity
                ]
            ], $e->getMessage(), 500);
        }
    
        $app->close();
    }

    public function divide()
    {
        
    }
    
    private function _divide($a, $b)  
    {
        if ($b == 0)
        {
            throw new \Exception('Division by zero!');
        }
        return $a/$b;
    }
}