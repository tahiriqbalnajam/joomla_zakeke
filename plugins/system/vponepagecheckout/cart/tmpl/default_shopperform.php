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
<?php if ($this->isAdminUser && VmConfig::get('oncheckout_change_shopper', true)) : ?>
    <?php
    $users  = $this->getUserList();
    /** @var \Joomla\CMS\Application\SiteApplication $app */
    $app    = JFactory::getApplication();
    $search = $app->getUserStateFromRequest('proopc.changeshoper.usersearch', 'usersearch', '', 'STRING');
    ?>
    <div class="proopc-change-shopper-cont">
        <h3 class="proopc-change-shopper-title"><?php echo vmText::_('COM_VIRTUEMART_CART_CHANGE_SHOPPER'); ?></h3>
        <div class="proopc-change-shopper-inner">
            <?php if (!empty($this->vmAdminID) && $this->juser->id != $this->vmAdminID) : ?>
                <div class="proopc-active-shopper">
                    <span><?php echo vmText::_('COM_VIRTUEMART_CART_ACTIVE_ADMIN') . ' ' . $this->adminUser->name; ?></span>
                </div>
            <?php endif; ?>
            <form action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart', false); ?>" method="post" id="form-usersearch" class="proopc-form-inline">
                <div class="proopc-field-group">
                    <input type="text" name="usersearch" id="usersearch" value="<?php echo $search ?>" onchange="this.form.submit();" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>"/>
                    <button type="submit" class="proopc-btn" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                    <button type="button" class="proopc-btn proopc-clear-filter" onclick="return ProOPC.resetForm(this.form, '#usersearch');" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
                </div>
                <input type="hidden" name="view" value="cart"/>
                <?php echo JHtml::_('form.token'); ?>
            </form>
            <div class="clear"></div>
            <form action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart', false); ?>" method="post" class="proopc-form-inline">
                <div class="proopc-field-group">
                    <?php echo JHtml::_('select.genericlist', $users, 'userID', 'class=""', 'id', 'displayedName', $this->cart->user->virtuemart_user_id, 'shopper_id'); ?>
                    <button type="submit" class="proopc-btn" title="<?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?>"><?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?></button>
                </div>
                <input type="hidden" name="view" value="cart"/>
                <input type="hidden" name="task" value="changeShopper"/>
                <?php echo JHtml::_('form.token'); ?>
            </form>
        </div>
        
        <div class="clear"></div>
        
        <?php
        $shopperGroupList = $this->getShopperGroupList();
        ?>
        <?php if ($shopperGroupList) : ?>
            <h3 class="proopc-change-shopper-title"><?php echo vmText::_('COM_VIRTUEMART_CART_CHANGE_SHOPPERGROUP'); ?></h3>
            <div class="proopc-change-shopper-inner">
                <form action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart', false); ?>" method="post" class="proopc-form-inline">
                    <div class="proopc-field-group">
                        <?php echo $shopperGroupList; ?>
                        <button type="submit" class="proopc-btn" title="<?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?>"><?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?></button>
                        <?php if (JFactory::getSession()->get('tempShopperGroups', false, 'vm')) : ?>
                            <button type="button" class="proopc-btn proopc-clear-filter" onclick="window.location.href='<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart&task=resetShopperGroup'); ?>'" title="<?php echo JText::_('COM_VIRTUEMART_RESET'); ?>"><?php echo JText::_('COM_VIRTUEMART_RESET'); ?></button>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="view" value="cart" />
                    <input type="hidden" name="task" value="changeShopper" />
                    <?php echo JHtml::_('form.token'); ?>
                </form>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>