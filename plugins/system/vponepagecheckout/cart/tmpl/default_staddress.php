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

if ($this->juser->guest) {
    unset($this->stFields['fields']['address_type_name']);
}
?>
<div id="proopc-st-address">
    <div class="inner-wrap">
        <label for="STsameAsBT" class="st-same-checkbox">
            <input type="checkbox" name="STsameAsBT" id="STsameAsBT" <?php echo $this->cart->STsameAsBT == 1 ? 'checked="checked"' : '' ; ?> onclick="return ProOPC.setst(this);" />
            <?php echo JText::_('COM_VIRTUEMART_USER_FORM_ST_SAME_AS_BT') ?>
        </label>
        <div class="edit-address<?php echo ($this->cart->STsameAsBT == 1) ? '' : ' soft-show'; ?>">
            <?php if (!empty($this->stFields['fields'])) : ?>
                <form id="EditSTAddres" autocomplete="off">
                    <?php
                    if ($this->selectSTName && !$this->juser->guest) {
                        echo '<div class="proopc-select-st-group">';
                        echo '<div class="inner">';
                        echo '<label class="proopc-select-st_field" for="proopc-select-st">' . JText::_('PLG_VPONEPAGECHECKOUT_SELECT_ADDRESS') . '</label>';
                        echo $this->selectSTName;
                        echo '</div>';
                        echo '</div>';
                    }

                    foreach ($this->stFields['fields'] as $field) {
                        $toolTip = !empty($field['tooltip']) ? ' class="hover-tootip" title="' . htmlspecialchars($field['tooltip']) . '"' : '';

                        echo '<div class="' . $field['name'] . '-group">';
                        echo '<div class="inner">';

                        if ($field['type'] == 'delimiter') {
                            echo '<h5 id="' . $field['name'] . '_field_delimiter" class="proopc-delimiter">';
                            echo '<span' . $toolTip . '>' . JText::_($field['title']) . '</span>';
                            echo '</h5>';
                        } elseif ($field['type'] == 'pluginmailchimp' || $field['type'] == 'pluginprivacy') {
                            echo $field['formcode'];
                        } elseif ($field['type'] == 'checkbox') {
                            echo '<label class="' . $field['name'] . '_field_lbl checkbox proopc-row" for="' . $field['name'] . '_field">';
                            echo $field['formcode'];
                            echo '<span' . $toolTip . '>' . JText::_($field['title']) . '</span>';
                            echo (strpos($field['formcode'], ' required') || $field['required']) ? ' <span class="asterisk">*</span>' : '';
                            echo '</label>';
                        } else {
                            echo '<label class="' . $field['name'] . '_field_lbl" for="' . $field['name'] . '_field">';
                            echo '<span' . $toolTip . '>' . JText::_($field['title']) . '</span>';
                            echo (strpos($field['formcode'], ' required') || $field['required']) ? ' <span class="asterisk">*</span>' : '';
                            echo '</label>';

                            if (strpos($field['formcode'], 'vm-chzn-select') !== false) {
                                echo str_replace('vm-chzn-select', '', $field['formcode']);
                            } else {
                                echo $field['formcode'];
                            }
                        }

                        echo '</div>';
                        echo '</div>';
                    } ?>
                    <input type="hidden" name="shipto_virtuemart_userinfo_id" id="shipto_virtuemart_userinfo_id" value="<?php echo $this->cart->selected_shipto ?>" />
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>