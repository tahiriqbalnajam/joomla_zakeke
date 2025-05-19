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

defined('_JEXEC') && defined('VPOPC_FOUND') or die;

// Creating shortcuts for the variables.
$btState_id = !empty($this->cart->BT['virtuemart_state_id']) ? $this->cart->BT['virtuemart_state_id'] : 0;
$stState_id = !empty($this->cart->ST['virtuemart_state_id']) ? $this->cart->ST['virtuemart_state_id'] : 0;
$document   = JFactory::getDocument();

$document->addStyleDeclaration('
.proopc-hide-prices .vpopc-price, 
.proopc-hide-prices .col-price *,
.proopc-hide-prices .col-tax,
.proopc-hide-prices .col-discount,
.proopc-hide-prices .col-total,
.proopc-hide-prices .grand-total,
.proopc-hide-prices .vmshipment_cost,
.proopc-hide-prices .vmpayment_cost {
    display: none !important;
}
@media (max-width: 767px) {
    .proopc-hide-prices .col-price {
        display: none !important;
    }
}
');
?>
<div class="payments-signin-button"></div>
<?php if (empty($this->productsCount)) : ?>
    <div id="ProOPC" class="cart-view emptyCart-view proopc-row<?php echo $this->page_class_suffix ?>">
        <?php echo $this->loadTemplate('module'); ?>
        <h1 class="cart-page-title"><?php echo JText::_('COM_VIRTUEMART_EMPTY_CART') ?></h1>
        <?php if (!empty($this->continue_link)) : ?>
            <div class="proopc-empty-continue-link">
                <a href="<?php echo $this->continue_link ?>" class="proopc-btn <?php echo $this->btn_class_1 ?>"><?php echo JText::_('COM_VIRTUEMART_CONTINUE_SHOPPING') ?></a>
            </div>
        <?php endif; ?>
    </div>
<?php elseif ($this->finalStage) : ?>
    <div id="ProOPC" class="cart-view proopc-row<?php echo $this->page_class_suffix ?>">
        <?php echo $this->loadTemplate('module'); ?>
        <?php echo $this->loadTemplate('shopperform'); ?>
        <?php echo $this->loadTemplate('vendors'); ?>
        <?php echo $this->loadTemplate('checkout'); ?>
        <div id="formToken"><?php echo JHTML::_('form.token') ?></div>
        <input type="hidden" id="BTStateID" name="BTStateID" value="<?php echo (int) $btState_id ?>" />
        <input type="hidden" id="STStateID" name="STStateID" value="<?php echo (int) $stState_id ?>" />
    </div>
<?php else : ?>
    <div id="ProOPC" class="cart-view proopc-row<?php echo $this->page_class_suffix ?>">
        <?php echo $this->loadTemplate('module'); ?>
        <?php echo $this->loadTemplate('shopperform'); ?>
        <?php echo $this->loadTemplate('vendors'); ?>
        <?php echo $this->loadTemplate('header'); ?>
        <div class="proopc-row">
            <div class="proopc-toolbar-right">
                <?php if ($this->params->get('show_clear_cart', 1)) : ?>
                    <div class="proopc-clear-cart-wrapper">
                        <a href="#" class="proopc-clear-cart" data-clearcart="true"><?php echo JText::_('PLG_VPONEPAGECHECKOUT_CLEAR_CART') ?></a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($this->continue_link)) : ?>
                    <div class="proopc-continue-link">
                        <a href="<?php echo $this->continue_link ?>" class="proopc-continue-shopping"><?php echo JText::_('COM_VIRTUEMART_CONTINUE_SHOPPING') ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <input type="hidden" id="proopc-cart-summery" name="proopc-cart-summery" value="1" />
        <form id="proopc-carttable-form">
            <div id="proopc-pricelist" class="first-page">
                <?php echo $this->loadTemplate('pricelist') ?>
            </div>
            <input type="hidden" name="ctask" value="updateproduct" />
        </form>
        <?php // This is our system message container. Need to keep it empty. ?>
        <div id="proopc-system-message"></div>
        <?php echo $this->loadTemplate('entry') ?>
    </div>
<?php endif; ?>
<?php // Dummy hidden form which used to refresh the page with an empty post. This avoids system cache issues. ?>
<form method="post" action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart', $this->useXHTML, $this->useSSL); ?>" id="proopc-reload-form" class="hide">
    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="view" value="cart" />
    <input type="hidden" name="task" value="display" />
</form>

<?php
vmJsApi::addJScript('vmprices', false, false);
vmJsApi::addJScript('vmsite', false, false);
vmJsApi::vmVariables();

echo vmJsApi::writeJS();
?>