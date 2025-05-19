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

$style = $this->params->get('style', 1);

if($style == 1 || $style == 3)
{
	// For style 1 and style 3 layout we need to have a different type of price list layout
	echo $this->loadTemplate('pricelistnarrow');
}
else
{
	// For style 2 and style 4 layout we use the same price list sublayout as first stage.
	// default_pricelist.php layout will always display full cart table when we are in final stage.
	echo $this->loadTemplate('pricelist');
}