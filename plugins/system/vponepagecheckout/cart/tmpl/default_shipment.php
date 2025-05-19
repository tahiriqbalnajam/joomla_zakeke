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
<?php if ($this->found_shipment_method) : ?>
    <form id="proopc-shipment-form"<?php echo $this->section_class_suffix ? ' class="' . trim($this->section_class_suffix) . '"' : ''; ?>>
        <div class="inner-wrap">
            <fieldset>
                <?php foreach ($this->shipments_shipment_rates as $shipment_shipment_rates) {
                    if (is_array($shipment_shipment_rates)) {
                        foreach ($shipment_shipment_rates as $shipment_shipment_rate) {
                            echo $shipment_shipment_rate;
                            echo '<div class="clear"></div>';
                        }
                    }
                } ?>
                
                <input type="hidden" name="proopc-savedShipment" id="proopc-savedShipment" value="<?php echo $this->cart->virtuemart_shipmentmethod_id ?>" />
            </fieldset>
        </div>
    </form>
<?php else : ?>
    <div class="proopc-alert-error"><?php echo $this->shipment_not_found_text ?></div>
<?php endif; ?>

