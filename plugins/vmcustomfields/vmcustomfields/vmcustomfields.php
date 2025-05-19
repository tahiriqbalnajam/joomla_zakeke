<?php
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;

class PlgVmcustomfieldsVmcustomfields extends CMSPlugin
{
    public function __construct(&$subject, $config)
    {
        die('test');
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    public function plgVmOnDisplayProductFE($product, &$data)
    {
        die('test');
        // Add your custom field to the product edit form
        $html = '<div class="control-group">';
        $html .= '<div class="control-label">';
        $html .= '<label for="custom_field">' . JText::_('Tahirssssss') . '</label>';
        $html .= '</div>';
        $html .= '<div class="controls">';
        $html .= '<input type="text" name="custom_field" id="custom_field" value="' . (isset($product->custom_field) ? $product->custom_field : '') . '" />';
        $html .= '</div>';
        $html .= '</div>';

        echo $html;
    }

    public function plgVmOnStoreProduct($data, &$product)
    {
        // Save the custom field value
        if (isset($data['custom_field'])) {
            $product->custom_field = $data['custom_field'];
        }
    }
}

?>