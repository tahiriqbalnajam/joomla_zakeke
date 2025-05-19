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

$style       = (int) $this->params->get('style', 1);
$button_text = in_array($style, array(3, 4)) ? JText::_('COM_VIRTUEMART_YOUR_ACCOUNT_REG') : JText::_('COM_VIRTUEMART_REGISTER_AND_CHECKOUT');
?>
<?php if (!empty($this->regFields['fields'])) : ?>
    <form id="UserRegistration" name="userForm" autocomplete="off">
        <?php foreach ($this->regFields['fields'] as $name => $field) : ?>
                <?php $toolTip = !empty($field['tooltip']) ? ' class="hover-tootip" title="' . htmlspecialchars(JText::_($field['tooltip']), ENT_COMPAT, 'UTF-8') . '"' : ''; ?>
            <div class="proopc-group">
                <div class="proopc-input-group-level">
                    <?php if ($field['name'] == 'jcore_privacyconsent') : ?>
                        <?php if (!version_compare(JVERSION, '4.0.0', 'ge')) {
                            JHtml::_('behavior.modal');
                        } ?>
                        <?php
                        $label = '<span' . $toolTip;
                        $label .= '>' . $this->getPrivacyArticleLink($field['title']) . '</span>';
                        ?>
                        <label class="<?php echo $field['name'] ?> full-input required" for="<?php echo $field['name'] ?>_field">
                            <?php echo $label; ?>
                            <?php echo (strpos($field['formcode'], ' required') || $field['required'])  ? ' <span class="asterisk">*</span>' : ''; ?>
                        </label>
                    <?php else : ?>
                        <label class="<?php echo $field['name'] ?> full-input" for="<?php echo $field['name'] ?>_field">
                            <span<?php echo $toolTip ?>><?php echo vmText::_($field['title']) ?></span>
                            <?php echo (strpos($field['formcode'], ' required') || $field['required'])  ? ' <span class="asterisk">*</span>' : ''; ?>
                        </label>
                    <?php endif; ?>
                </div>
                <div class="proopc-input proopc-input-append"<?php echo $field['required'] ? ' data-required="true"' : ''; ?>>
                    <?php if ($field['name'] == 'password') : ?>
                        <?php echo str_replace(array('vm-chzn-select', '<input '), array('', '<input autocomplete="new-password" '), $field['formcode']); ?>
                    <?php else : ?>
                        <?php echo str_replace(array('vm-chzn-select', '<input '), array('', '<input autocomplete="off" '), $field['formcode']); ?>
                    <?php endif; ?>
                    <i class="status hover-tootip"></i>
                    <?php if ($field['name'] == 'password' && $this->params->get('live_validation', 1)) : ?>
                        <div class="password-stregth">
                            <?php echo JText::_('PLG_VPONEPAGECHECKOUT_PASSWORD_STRENGTH') ?>
                            <span id="password-stregth"></span>
                        </div>
                        <div class="strength-meter"><div id="meter-status"></div></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="proops-login-inputs">
            <div class="proopc-group">
                <div class="proopc-input proopc-input-prepend">
                    <button type="submit" id="proopc-task-registercheckout" class="proopc-btn <?php echo $this->btn_class_2 ?>" disabled>
                        <i id="proopc-register-process" class="proopc-button-process"></i><?php echo $button_text ?>
                    </button>
                </div>
            </div>
            <?php echo JHTML::_('form.token'); ?>
        </div>
    </form>
<?php endif; ?>