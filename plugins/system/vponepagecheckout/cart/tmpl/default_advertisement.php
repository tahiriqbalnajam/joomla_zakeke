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
<?php if (!empty($this->checkoutAdvertise) && $this->params->get('checkout_advertisement', 1)) : ?>
    <div id="proopc-advertise-box">
        <?php foreach ($this->checkoutAdvertise as $checkoutAdvertise) : ?>
            <div class="checkout-advertise">
                <?php echo $checkoutAdvertise; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>