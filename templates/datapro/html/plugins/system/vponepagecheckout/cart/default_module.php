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

$modules      = $this->getCartModules();
$loginOptions = $this->getPaymentLoginOptions();
$count        = count($modules);
$i            = 0;
?>
<?php if ($count > 0) : ?>
    <div class="proopc-cart-modules">
        <?php foreach ($modules as $module) : ?>
            <?php if (!empty($module->moduleHtml)) : ?>
                <?php $i++; ?>
                <div class="proopc-row">
                    <div class="cart-promo-mod<?php echo ($i == $count) ? ' last' : ''; ?>">
                        <?php if ($module->showtitle) : ?>
                            <h3><?php echo $module->title ?></h3>
                        <?php endif; ?>
                        <div class="proopc-cart-module">
                            <?php echo $module->moduleHtml; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php if (!empty($loginOptions)) : ?>
    <div class="proopc-payment-logins">
        <?php echo $loginOptions; ?>
    </div>
<?php endif; ?>