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
?>
<?php if ($this->juser->guest) : ?>
    <div class="proopc-register-login">
        <div class="proopc-register">
            <?php if (!VmConfig::get('oncheckout_show_register') && !VmConfig::get('oncheckout_only_registered')) : ?>
                <h3><?php echo vmText::_('COM_VIRTUEMART_CHECKOUT_AS_GUEST')?></h3>
            <?php elseif (VmConfig::get('oncheckout_only_registered')) : ?>
                <h3><?php echo vmText::_('COM_VIRTUEMART_CART_ONLY_REGISTERED')?></h3>
            <?php else : ?>
                <h3><?php echo JText::_('PLG_VPONEPAGECHECKOUT_CHECKOUT_AS_GUEST_REGISTER')?></h3>
            <?php endif; ?>
            <div class="proopc-inner">
                <?php if (!VmConfig::get('oncheckout_show_register') && !VmConfig::get('oncheckout_only_registered')) : ?>
                    <h4 class="proopc-subtitle"><?php echo vmText::_('COM_VIRTUEMART_ENTER_A_VALID_EMAIL_ADDRESS')?></h4>
                    <div class="proopc-guest-form">
                        <div class="proopc-inner">
                            <?php echo $this->loadTemplate('guest'); ?>
                        </div>
                    </div>
                <?php elseif (VmConfig::get('oncheckout_only_registered')) : ?>
                    <h4 class="proopc-subtitle"><?php echo JText::_('PLG_VPONEPAGECHECKOUT_REGISTER_CONVINIENCE')?></h4>
                    <div class="proopc-reg-form show">
                        <div class="proopc-inner">
                            <?php echo $this->loadTemplate('register'); ?>
                        </div>
                    </div>
                <?php else : ?>
                    <h4 class="proopc-subtitle"><?php echo JText::_('PLG_VPONEPAGECHECKOUT_REGISTER_CONVINIENCE')?></h4>
                    <label class="proopc-switch">
                        <input type="radio" name="proopc-method" value="guest"<?php echo $this->params->get('registration_by_default', 0) ? '' : ' checked'; ?> autocomplete="off" /> 
                        <?php echo vmText::_('COM_VIRTUEMART_CHECKOUT_AS_GUEST') ?>
                    </label>
                    <div class="proopc-guest-form<?php echo $this->params->get('registration_by_default', 0) ? ' soft-hide' : ''; ?>">
                        <div class="proopc-inner with-switch">
                            <?php echo $this->loadTemplate('guest'); ?>
                        </div>
                    </div>
                    <label class="proopc-switch">
                        <input type="radio" name="proopc-method" value="register"<?php echo $this->params->get('registration_by_default', 0) ? ' checked' : ''; ?> autocomplete="off" />
                        <?php echo vmText::_('COM_VIRTUEMART_REGISTER') ?>
                    </label>
                    <div class="proopc-reg-form<?php echo $this->params->get('registration_by_default', 0) ? '' : ' soft-hide'; ?>">
                        <div class="proopc-inner with-switch">
                            <?php echo $this->loadTemplate('register'); ?>
                        </div>
                    </div>
                    <div class="proopc-reg-advantages<?php echo $this->params->get('registration_by_default', 0) ? ' soft-hide' : ''; ?>">
                        <?php $registration_message = trim($this->params->get('registration_message', '')); ?>
                        <?php if (empty($registration_message)) : ?>
                            <?php echo JText::_('PLG_VPONEPAGECHECKOUT_DEFAULT_REGISTRATION_ADVANTAGE_MSG'); ?>
                        <?php  else : ?>
                            <?php echo $registration_message; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="proopc-login">
            <h3><?php echo JText::_('PLG_VPONEPAGECHECKOUT_LOGIN_AND_CHECKOUT') ?></h3>
            <div class="proopc-inner">
                <?php echo $this->loadTemplate('login'); ?>
            </div>
        </div>
    </div>
<?php endif; ?>