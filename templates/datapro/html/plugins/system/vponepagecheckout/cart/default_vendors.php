<?php

/**
 * @package      VP One Page Checkout - Joomla! System Plugin
 * @subpackage   For VirtueMart 3+ and VirtueMart 4+
 *
 * @copyright    Copyright (C) 2012-2024 Virtueplanet Services LLP. All rights reserved.
 * @license      GNU General Public License version 2 or later; see LICENSE.txt
 * @authors      Abhishek Das <info@virtueplanet.com>
 * @link         https://www.virtueplanet.com
 */

defined('_JEXEC') or die;
?>
<?php if (property_exists($this->cart, 'vendorId') && method_exists('shopFunctions', 'renderVendorFullVendorList') && VmConfig::get('multixcart') == 'byselection') : ?>
    <div id="proopc-vendor-form">
        <form method="post" name="checkoutForm" action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart', $this->useXHTML, $this->useSSL); ?>" class="proopc-form-inline">
            <div class="proopc-field-group">
                <?php echo shopFunctions::renderVendorFullVendorList($this->cart->vendorId); ?>
                <button class="proopc-btn" type="submit"><?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?></button>
            </div>
            <input type="hidden" name="order_language" value="<?php echo $this->order_language; ?>" />
            <input type="hidden" name="task" value="updatecart" />
            <input type="hidden" name="option" value="com_virtuemart" />
            <input type="hidden" name="view" value="cart" />
        </form>
    </div>
<?php endif; ?>