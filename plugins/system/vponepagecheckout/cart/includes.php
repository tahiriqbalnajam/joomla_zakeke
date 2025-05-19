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

// phpcs:disable PSR1.Files.SideEffects
// Register required classes to JLoader
JLoader::register('VmConfig', JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php');
JLoader::register('vmDefines', JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/vmdefines.php');
JLoader::register('vmVersion', JPATH_ADMINISTRATOR . '/components/com_virtuemart/version.php');
JLoader::register('vmPSPlugin', JPATH_ADMINISTRATOR . '/components/com_virtuemart/plugins/vmpsplugin.php');
JLoader::register('CurrencyDisplay', JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/currencydisplay.php');
JLoader::register('VirtueMartModelUserfields', JPATH_ADMINISTRATOR . '/components/com_virtuemart/models/userfields.php');
JLoader::register('ShopFunctions', JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/shopfunctions.php');
JLoader::register('VmView', JPATH_SITE . '/components/com_virtuemart/helpers/vmview.php');
JLoader::register('CouponHelper', JPATH_SITE . '/components/com_virtuemart/helpers/coupon.php');
JLoader::register('VirtueMartCart', JPATH_SITE . '/components/com_virtuemart/helpers/cart.php');
JLoader::register('shopFunctionsF', JPATH_SITE . '/components/com_virtuemart/helpers/shopfunctionsf.php');

if (JPluginHelper::isEnabled('system', 'bonus')) {
    JLoader::discover('VmbonusHelperFront', JPATH_SITE . '/components/com_vm_bonus/helpers');
}

if (!function_exists('vpdump')) {
    /**
    * Method to print any data with debug information.
    * Used in debuggin purpose. Similar to vardump but more advanced.
    *
    * @param mixed $data Any type of data but form xml object
    *
    * @return void
    */
    function vpdump($data, $debug = true)
    {
        ob_start();
        print_r($data);
        $str = ob_get_contents();
        ob_end_clean();

        echo '<pre class="vpdump" dir="ltr" style="font-size: 14px;">';
        echo '<small>' . gettype($data) . '</small> ';
        echo '<font color="#cc0000">' . $str . '</font>';
        echo '<i>{Length: ' . strlen($str) . '}</i>';

        if ($debug) {
            $backTraces = function_exists('debug_backtrace') ? debug_backtrace() : array();

            echo '<ul style="margin:15px 0; padding:15px; list-style-position:inside; background: #FAFAFA; border: 1px solid #DDD; line-height: 1.4;">';

            foreach ($backTraces as $backTrace) {
                echo '<li>';
                echo '<b>File:</b> ' . $backTrace['file'] . '  <b>Line:</b> ' . $backTrace['line'] . (isset($backTrace['class']) ? '  <b>Class:</b> ' . $backTrace['class'] : '') . (isset($backTrace['class']) ? '  <b>Function:</b> ' . $backTrace['function'] : '');
                echo '</li>';
            }

            echo '<li><b>Current Memory Usage:</b> ' . vpCalculateSize(memory_get_usage()) . '</li>';
            echo '<li><b>Peak Memory Usage:</b> ' . vpCalculateSize(memory_get_peak_usage()) . '</li>';

            echo '</ul>';
        }

        echo '<br/>';
        echo '</pre>';
    }
}

if (!function_exists('vpCalculateSize')) {
    function vpCalculateSize($mem_usage)
    {
        if ($mem_usage < 1024) {
            return $mem_usage . ' bytes';
        } elseif ($mem_usage < 1048576) {
            return round($mem_usage / 1024, 2) . ' KB';
        } else {
            return round($mem_usage / 1048576, 2) . ' MB';
        }
    }
}
