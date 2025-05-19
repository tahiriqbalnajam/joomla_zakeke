<?php
/**
 * @package      VP One Page Checkout - Joomla! System Plugin
 * @subpackage   For VirtueMart 3+ and VirtueMart 4+
 *
 * @copyright    Copyright (C) 2012-2024 Virtueplanet Services LLP. All rights reserved.
 * @license      GNU General Public License version 2 or later; see LICENSE.txt
 * @authors      Abhishek Das <info@virtueplanet.com>
 * @link         https://www.virtueplanet.com
 */

defined('_JEXEC') or die;

echo '<div class="vm-wrap vm-order-done">';

if($this->display_title)
{
	echo '<h3>' . vmText::_('COM_VIRTUEMART_CART_ORDERDONE_THANK_YOU') . '</h3>';
}

// Everything here is displayed by payment method plugin.
// It is exactly same as standard VirtueMart order done layout. We just need to print it as it is.
echo $this->html;

if($this->display_loginform && !JFactory::getUser()->guest && class_exists('shopFunctionsF'))
{
	echo shopFunctionsF::getLoginForm();
}

echo '</div>';