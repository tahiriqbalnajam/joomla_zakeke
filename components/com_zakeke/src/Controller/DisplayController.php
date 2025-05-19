<?php

namespace Zakeke\Component\Tasks\Site\Controller;
use Joomla\CMS\Factory;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController {
    
    public function display($cachable = false, $urlparams = array())
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

        $productModel = new VirtueMartModelProduct();

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
                $thumbnail = JURI::root() . $product->images[0]->file_url;
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
}