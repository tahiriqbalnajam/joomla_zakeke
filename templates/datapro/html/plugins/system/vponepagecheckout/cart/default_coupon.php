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
    <div class="proopc-input-append proopc-row">
        <input type="text" id="proopc-coupon-code" name="coupon_code" size="20" maxlength="50" value="<?php echo $this->coupon_text; ?>" onblur="if(this.value=='') this.value='<?php echo $this->coupon_text; ?>';" onfocus="if(this.value=='<?php echo $this->coupon_text; ?>') this.value='';" data-default="<?php echo $this->coupon_text; ?>" />
        <button type="button" id="proopc-task-savecoupon" class="proopc-btn <?php echo $this->btn_class_1 ?>" disabled><?php echo JText::_('COM_VIRTUEMART_SAVE'); ?></button>
    </div>
</div>