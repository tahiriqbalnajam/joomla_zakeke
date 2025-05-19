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
<?php if ($this->found_payment_method) : ?>
    <div class="inner-wrap">
        <form id="proopc-payment-form"<?php echo $this->section_class_suffix ? ' class="' . trim($this->section_class_suffix) . '"' : ''; ?>>
            <fieldset>
                <?php foreach ($this->paymentplugins_payments as $paymentplugin_payments) {
                    if (is_array($paymentplugin_payments)) {
                        foreach ($paymentplugin_payments as $paymentplugin_payment) {
                            echo $paymentplugin_payment;
                            echo '<div class="clear proopc-method-end"></div>';
                        }
                    }
                } ?>
            </fieldset>
        </form>
    </div>
<?php else : ?>
    <div class="proopc-alert-error payment"><?php echo $this->payment_not_found_text ?></div>  
<?php endif; ?>
