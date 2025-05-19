<?php

/**
 * @package      VP One Page Checkout - Joomla! System Plugin
 * @subpackage   For VirtueMart 3+ and VirtueMart 4+
 *
 * @copyright    Copyright (C) 2012-2024 Virtueplanet Services LLP. All rights reserved.
 * @license      GNU General Public License version 2 or later; see LICENSE.txt
 * @author       Abhishek Das <info@virtueplanet.com>
 * @link         https://www.virtueplanet.com
 */

defined('_JEXEC') or die;

$menuId = $this->getOrderDoneMenuId();

if (!empty($menuId)) {
    $url = 'index.php?option=com_virtuemart&view=cart&task=orderdone';
} else {
    if (class_exists('vmVersion') && isset(vmVersion::$RELEASE) && version_compare(vmVersion::$RELEASE, '3.2.6', 'ge')) {
        $url = 'index.php?option=com_virtuemart&view=cart&task=orderdone';
    } else {
        $url = 'index.php?option=com_virtuemart&view=cart&layout=order_done';
    }
}

if (VmConfig::get('multix', 'none') != 'none' && VmConfig::get('multixcart', 0) == 'byproduct' && !empty($this->cart->vendorId)) {
    $url .= '&virtuemart_vendor_id=' . $this->cart->vendorId;
}
?>
<div class="inner-wrap">
    <form method="post" id="checkoutForm" name="checkoutForm" action="<?php echo JRoute::_($url, $this->useXHTML, $this->useSSL); ?>">
        <?php if (!$this->params->get('handlerbund_compliant', 0)) : ?>
            <?php echo $this->loadTemplate('cartfields'); ?>
        <?php endif; ?>
        <?php if (!VmConfig::get('use_as_catalog')) : ?>
            <div class="proopc-row proopc-confirm-button-wrapper proopc-checkout-box<?php echo $this->params->get('handlerbund_compliant', 0) ? ' proopc-checkout-box-splitted' : ''; ?>">
                <button type="button" id="proopc-order-submit" class="proopc-btn proopc-btn-lg <?php echo $this->btn_class_3 ?>" disabled>
                    <?php echo JText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU'); ?>
                </button>

                <div class="proopc-order-confirmation-notice proopc-alert proopc-info-msg hide" aria-live="polite"></div>
            </div>
        <?php endif; ?>
    </form>
</div>
<?php
// We have intentionally kept important hidden input fields outside the checkout form.
// They will be moved within the form by JavaScript when the cart is verified.
?>
<div id="proopc-hidden-confirm">
    <input type="hidden" name="STsameAsBT" value="<?php echo $this->cart->STsameAsBT ?>" />
    <input type="hidden" name="shipto" value="<?php echo $this->cart->selected_shipto ?>" />
    <input type="hidden" name="order_language" value="<?php echo $this->order_language; ?>" />
    <input type="hidden" name="task" value="confirm" />
    <input type="hidden" name="confirm" value="1" />
    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="view" value="cart" />
</div>

<button type="button" id="checkoutFormSubmit" name="confirm" class="soft-hide dummy-button invisible" data-dvalue="<?php echo JText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU'); ?>"></button>
