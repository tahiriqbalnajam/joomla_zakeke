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
<div class="inner-wrap">
    <div class="edit-address">
        <?php if (!empty($this->btFields['fields'])) : ?>
            <form id="EditBTAddres" autocomplete="off">
                <?php
                foreach ($this->btFields['fields'] as $name => $field) {
                    $toolTip = !empty($field['tooltip']) ? ' class="hover-tootip" title="' . htmlspecialchars($field['tooltip']) . '"' : '';

                    if ($field['name'] == 'email') {
                        echo '<div class="bt_' . $field['name'] . '-group">';
                    } else {
                        echo '<div class="' . $field['name'] . '-group">';
                    }

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
                        if ($field['name'] == 'email') {
                            $field['name'] = 'bt_' . $field['name'];
                            $field['formcode'] = str_replace('id="email_field"', 'id="bt_email_field"', $field['formcode']);
                        }

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
            </form>
        <?php endif; ?>
    </div>
</div>