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

$return           = JRoute::_('index.php?option=com_virtuemart&view=cart', false);
$twofactormethods = array();
$extraButtons     = array();

if (version_compare(JVERSION, '3.0.0', 'ge')) {
    require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';
    $twofactormethods = UsersHelper::getTwoFactorMethods();
}

if (version_compare(JVERSION, '4.0.0', 'ge')) {
    $extraButtons = \Joomla\CMS\Helper\AuthenticationHelper::getLoginButtons('#UserLogin');
}

$email_as_username = (int) $this->params->get('email_as_username', 2);
$style             = (int) $this->params->get('style', 1);
$button_text       = in_array($style, array(3, 4)) ? JText::_('COM_VIRTUEMART_LOGIN') : JText::_('PLG_VPONEPAGECHECKOUT_LOGIN_AND_CHECKOUT');
?>
<?php if ($this->juser->guest) : ?>
    <?php if (!empty($this->social_login)) : ?>
        <div class="proopc-social-login">
            <?php echo $this->social_login ?>
        </div>
    <?php endif ?>
    <h4 class="proopc-subtitle"><?php echo JText::_('PLG_VPONEPAGECHECKOUT_ASK_FOR_LOGIN'); ?></h4>
    <form name="proopc-login" id="UserLogin" autocomplete="off">
        <div class="proopc-group">
            <div class="proopc-input-group-level">
                <?php if ($email_as_username == 1) : ?>
                    <label class="full-input" for="proopc-username"><?php echo vmText::_('JGLOBAL_EMAIL'); ?></label>
                <?php elseif ($email_as_username == 2) : ?>
                    <label class="full-input" for="proopc-username"><?php echo vmText::_('JGLOBAL_USERNAME'); ?> / <?php echo vmText::_('JGLOBAL_EMAIL'); ?></label>
                <?php else : ?>
                    <label class="full-input" for="proopc-username"><?php echo vmText::_('JGLOBAL_USERNAME'); ?></label>
                <?php endif; ?>
            </div>
            <div class="proopc-input proopc-input-append">
                <?php if ($this->params->get('email_as_username') == 1) : ?>
                    <input type="email" id="proopc-username" name="username" size="18" required />
                <?php else : ?>
                    <input type="text" id="proopc-username" name="username" size="18" required />
                <?php endif ?>
                <i class="status hover-tootip"></i>
            </div>
        </div>
        <div class="proopc-group">
            <div class="proopc-input-group-level">
                <label class="full-input" for="proopc-passwd"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></label>
            </div>
            <div class="proopc-input proopc-input-append">
                <input id="proopc-passwd" type="password" name="password" size="18" required />
                <i class="status hover-tootip"></i>
            </div>
        </div>
        <?php if (count($twofactormethods) > 1) : ?>
            <div id="form-login-secretkey" class="proopc-group">
                <div class="proopc-input-group-level">
                    <label class="full-input" for="proopc-secretkey"><?php echo JText::_('JGLOBAL_SECRETKEY') ?></label>
                </div>
                <div class="proopc-input proopc-input-append">
                    <input id="proopc-secretkey" autocomplete="off" type="text" name="secretkey" size="18" />
                </div>
            </div>
        <?php endif; ?>
        <?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
            <div class="proopc-group">
                <div class="proopc-input proopc-input-append">
                    <label for="proopc-remember" class="proopc-checkbox inline">
                        <input type="checkbox" id="proopc-remember" name="remember" value="yes" alt="<?php echo JText::_('JGLOBAL_REMEMBER_ME') ?>" />
                        <?php echo JText::_('JGLOBAL_REMEMBER_ME') ?>
                    </label>
                </div>
            </div>
        <?php endif; ?>
        <?php foreach ($extraButtons as $button) :
            $dataAttributeKeys = array_filter(array_keys($button), function ($key) {
                return substr($key, 0, 5) == 'data-';
            });
            ?>
            <div class="proops-login-inputs">
                <div class="proopc-group">
                    <div class="proopc-input">
                        <button type="button"
                                class="proopc-btn <?php echo $this->btn_class_1 ?> <?php echo $button['class'] ?? '' ?>"
                                <?php foreach ($dataAttributeKeys as $key) : ?>
                                    <?php echo $key ?>="<?php echo $button[$key] ?>"
                                <?php endforeach; ?>
                                <?php if ($button['onclick']) : ?>
                                onclick="<?php echo $button['onclick'] ?>"
                                <?php endif; ?>
                                title="<?php echo JText::_($button['label']) ?>"
                                id="<?php echo $button['id'] ?>"
                                >
                            <?php if (!empty($button['icon'])) : ?>
                                <span class="<?php echo $button['icon'] ?>"></span>
                            <?php elseif (!empty($button['image'])) : ?>
                                <?php echo $button['image']; ?>
                            <?php elseif (!empty($button['svg'])) : ?>
                                <?php echo $button['svg']; ?>
                            <?php endif; ?>
                            <?php echo JText::_($button['label']) ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="proops-login-inputs">
            <div class="proopc-group">
                <div class="proopc-input proopc-input-prepend">
                    <button type="submit" id="proopc-task-loginajax" class="proopc-btn <?php echo $this->btn_class_2 ?>" disabled>
                        <i id="proopc-login-process" class="proopc-button-process"></i>
                        <?php echo $button_text ?>
                    </button>
                </div>
            </div>
            <input type="hidden" name="ctask" value=""/>
            <input type="hidden" name="return" id="proopc-return" value="<?php echo base64_encode($return); ?>" />
            <?php echo JHtml::_('form.token');?>
        </div>
        <div class="proops-login-inputs">
            <div class="proopc-group">
                <div class="proopc-input">
                    <ul class="proopc-ul">
                        <?php if ($this->params->get('email_as_username') != 1) : ?>
                            <li>
                                <a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
                                    <?php echo JText::_('COM_VIRTUEMART_ORDER_FORGOT_YOUR_USERNAME'); ?>
                                </a>
                            </li>
                        <?php endif ?>
                        <li>
                            <a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
                                <?php echo JText::_('COM_VIRTUEMART_ORDER_FORGOT_YOUR_PASSWORD'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
<?php endif; ?>