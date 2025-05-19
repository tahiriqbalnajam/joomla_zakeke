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

JFormHelper::loadFieldClass('text');
// phpcs:enable PSR1.Files.SideEffects

class JFormFieldTexttotag extends JFormFieldText
{
    protected $type = 'Texttotag';

    public function getInput()
    {
        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            JHtml::_('jquery.framework');

            if (version_compare(JVERSION, '4.0.0', 'lt')) {
                JHtml::_('script', 'system/html5fallback.js', array('relative' => true, 'version' => 'auto'));
            }

            $doc = JFactory::getDocument();
            $root = JUri::root(true);
            $doc->addScript($root . '/plugins/system/vponepagecheckout/assets/admin/js/angular.min.js');

            JHtml::_('bootstrap.framework');

            $doc->addScript($root . '/plugins/system/vponepagecheckout/assets/admin/js/bootstrap-tagsinput.js');
            $doc->addScript($root . '/plugins/system/vponepagecheckout/assets/admin/js/bootstrap-tagsinput-angular.js');
            $doc->addStyleSheet($root . '/plugins/system/vponepagecheckout/assets/admin/css/bootstrap-tagsinput.css');

            if (version_compare(JVERSION, '4.0.0', 'ge')) {
                $doc->addScriptDeclaration("
                jQuery(function($) {
                    $('#{$this->id}').tagsinput({
                            trimValue: true,
                            allowDuplicates: false,
                            tagClass: 'badge bg-secondary'
                    });
                    
                    setTimeout(function() {
                        $('#{$this->id}').siblings('.bootstrap-tagsinput').width('100%');
                    }, 200);
                });
                ");
            } else {
                $doc->addScriptDeclaration("
                jQuery(function($) {
                    $('#{$this->id}').tagsinput({
                            trimValue: true,
                            allowDuplicates: false,
                            tagClass: 'label'
                    });
                });
                ");
            }
        }

        $html  = parent::getInput();
        $html .= '<div class="clearfix"></div>';
        $html .= '<div class="muted small">Examples: Email Missing, Missing* etc.</div>';
        return $html;
    }
}
