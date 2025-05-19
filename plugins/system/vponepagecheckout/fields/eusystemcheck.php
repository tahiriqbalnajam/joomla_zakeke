<?php

/**
 * @package      VP One Page Checkout - Joomla! System Plugin
 * @subpackage   For VirtueMart 3+ and VirtueMart 4+
 *
 * @copyright    Copyright (C) 2012-2024 Virtueplanet Services LLP. All rights reserved.
 * @license      GNU General Public License version 2 or later; see LICENSE.txt
 * @author       Abhishek Das <info@virtueplanet.com>
 * @link         https://www.virtueplanet.com
 *
  * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class JFormFieldEusystemcheck extends JFormField
{
    protected $type = 'Eusystemcheck';

    public function getInput()
    {
        return '';
    }

    public function getLabel()
    {
        $errors = array();
        $html   = '';

        if (!class_exists('SoapClient')) {
            $errors[] = 'SoapClient is missing. You need to have Soap library installed and enabled in your server to run EU VAT check. Refer to the PHP <a href="http://php.net/manual/en/class.soapclient.php" target="_blank">documentation</a> to learn more.';
        }

        if (!empty($errors)) {
            $html .= '<div id="' . $this->id . '" style="background-color:#f2dede;border: 1px solid #ebccd1;color:#a94442;margin-bottom:15px;padding:8px 14px;">';

            foreach ($errors as $error) {
                $html .= '<p>' . $error . '</p>';
            }

            $html .= '</div>';

            $html .= '<script>';
            $html .= 'jQuery(document).ready(function($) {$("#' . $this->id . '").closest(".control-label").css("width", "auto");})';
            $html .= '</script>';
        } else {
            $html .= '<div id="' . $this->id . '"></div>';

            $html .= '<script>';
            $html .= 'jQuery(document).ready(function($) {$("#' . $this->id . '").closest(".control-group").hide();})';
            $html .= '</script>';
        }

        return $html;
    }
}
