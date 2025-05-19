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

$checkout_step = 1;
?>
<div id="proopc-system-message"></div>
<div class="proopc-finalpage<?php echo $this->params->get('reload', 0) ? ' proopc-reload' : ''; ?>">
    <?php echo $this->loadTemplate('header'); ?>
    <div class="proopc-row">
        <div class="proopc-login-message-cont">
            <?php if ($this->juser->guest && !$this->params->get('only_guest', 0)) : ?>
                <a href="#goback" data-vpopc="redirect" data-vphref="<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart&ctask=goback', $this->useXHTML, $this->useSSL) ?>" class="proopc-goback-link"><?php echo JText::_('JLOGIN') . '/' . JText::_('JREGISTER') ?></a>
            <?php elseif (!$this->juser->guest) : ?>
                <?php echo $this->loadTemplate('logout'); ?>
            <?php endif; ?>
        </div>
        <div class="proopc-toolbar-right">
            <?php if ($this->params->get('show_clear_cart', 1)) : ?>
                <div class="proopc-clear-cart-wrapper">
                    <a href="#" class="proopc-clear-cart" data-clearcart="true"><?php echo JText::_('PLG_VPONEPAGECHECKOUT_CLEAR_CART') ?></a>
                </div>
            <?php endif; ?>
            <?php if (!empty($this->continue_link)) : ?>
                <div class="proopc-continue-link">
                    <a href="<?php echo $this->continue_link ?>"><?php echo vmText::_('COM_VIRTUEMART_CONTINUE_SHOPPING') ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="proopc-column3">
        <div class="proopc-bt-address">
            <h3 class="proopc-process-title">
                <?php echo $this->params->get('oncheckout_show_steps', 1) ? '<div class="proopc-step">' . ($checkout_step++) . '</div>' : ''; ?><?php echo JText::_('COM_VIRTUEMART_USER_FORM_BILLTO_LBL') ?>
            </h3>
            <?php echo $this->loadTemplate('btaddress'); ?>
        </div>
    </div>
    <div class="proopc-column3">
        <?php
        $shipto_class_suffix = '';
        $shipment_class_suffix = '';
        $payment_class_suffix = '';

        if ($this->params->get('hide_shipto', 0) && !empty($this->cart->STsameAsBT)) {
            $shipto_class_suffix = ' hide';
            $shipment_class_suffix = ' proopc-no-margin-top';
            $checkout_step--;
        }
        ?>
        <div class="proopc-st-address<?php echo $shipto_class_suffix; ?>">
            <h3 class="proopc-process-title">
                <?php echo $this->params->get('oncheckout_show_steps', 1) ? '<div class="proopc-step">' . ($checkout_step++) . '</div>' : '' ?><?php echo JText::_('COM_VIRTUEMART_USER_FORM_SHIPTO_LBL') ?>
            </h3>
            <?php echo $this->loadTemplate('staddress'); ?>
        </div>
        <?php
        if ($this->params->get('hide_shipment', 0)) {
            $shipment_class_suffix .= ' hide';

            if ($this->params->get('hide_shipto', 0)) {
                $payment_class_suffix = ' proopc-no-margin-top';
            }

            $checkout_step--;
        }
        ?>
        <div class="proopc-shipments<?php echo $shipment_class_suffix; ?>">
            <h3 class="proopc-process-title">
                <?php echo $this->params->get('oncheckout_show_steps', 1) ? '<div class="proopc-step">' . ($checkout_step++) . '</div>' : ''; ?><?php echo JText::_('COM_VIRTUEMART_CART_SHIPPING')?>
            </h3>
            <div id="proopc-shipments">
                <?php echo $this->loadTemplate('shipment'); ?>
            </div>
        </div>
        <div class="proopc-payments<?php echo $payment_class_suffix; ?>">
            <h3 class="proopc-process-title">
                <?php echo $this->params->get('oncheckout_show_steps', 1) ? '<div class="proopc-step">' . ($checkout_step++) . '</div>' : ''; ?><?php echo JText::_('COM_VIRTUEMART_CART_PAYMENT')?>
            </h3>
            <div id="proopc-payments">
                <?php echo $this->loadTemplate('payment'); ?>
            </div>
        </div>
        <?php if (VmConfig::get('coupons_enable')) : ?>
            <div class="proopc-coupon">
                <h3 class="proopc-process-title">
                    <?php echo $this->params->get('oncheckout_show_steps', 1) ? '<div class="proopc-step">' . ($checkout_step++) . '</div>' : ''; ?><?php echo JText::_('COM_VIRTUEMART_COUPON_DISCOUNT')?>
                </h3>
                <div id="proopc-coupon">
                    <?php echo $this->loadTemplate('coupon'); ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($this->params->get('handlerbund_compliant', 0)) : ?>
            <div class="proopc-additional-info">
                <h3 class="proopc-process-title">
                    <?php echo $this->params->get('oncheckout_show_steps', 1) ? '<div class="proopc-step">' . ($checkout_step++) . '</div>' : '' ?><?php echo JText::_('PLG_VPONEPAGECHECKOUT_ADDITIONAL_INFO')?>
                </h3>
                <div id="proopc-additional-info">
                    <?php echo $this->loadTemplate('cartform'); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="proopc-column3 last">
        <div class="proopc-cartlist">
            <h3 class="proopc-process-title"><?php echo $this->params->get('oncheckout_show_steps', 1) ? '<div class="proopc-step">' . ($checkout_step++) . '</div>' : ''; ?><?php echo JText::_('COM_VIRTUEMART_CART_OVERVIEW')?></h3>
            <form id="proopc-carttable-form">
                <div id="proopc-pricelist">
                    <?php echo $this->loadTemplate('cartlist'); ?>
                </div>
                <input type="hidden" name="ctask" value="updateproduct" />
            </form>
        </div>
        <div class="proopc-confirm-order">
            <h3 class="proopc-process-title"><?php echo $this->params->get('oncheckout_show_steps', 1) ? '<div class="proopc-step">' . ($checkout_step++) . '</div>' : ''; ?><?php echo JText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU')?></h3>
            <div id="proopc-confirm-order">
                <?php echo $this->loadTemplate('confirm'); ?>
            </div>
            <?php echo $this->loadTemplate('advertisement'); ?>
        </div>
    </div>
</div>