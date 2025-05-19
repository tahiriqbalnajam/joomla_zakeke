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

use Joomla\Component\Modules\Administrator\Service\HTML\Modules;

class JFormFieldModPositions extends JFormField
{
    protected $type = 'ModPositions';

    public function getInput()
    {
        if (version_compare(JVERSION, '4.0.0', 'ge')) {
            JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_modules/src/Service/HTML/Modules.php');

            // Load language files
            $language = JFactory::getLanguage();
            // Loads the current language-tag
            $language_tag = $language->getTag();
            $language->load('com_modules', JPATH_ADMINISTRATOR, $language_tag, true);

            $clientId          = 0;
            $state             = 1;
            $selectedPosition  = $this->value;
            $modules           = new Modules();
            $positions         = $modules->positions($clientId, $state, $selectedPosition);

            // Add custom position to options
            $customGroupText = JText::_('COM_MODULES_CUSTOM_POSITION');

            JFactory::getDocument()->getWebAssetManager()
                ->usePreset('choicesjs')
                ->useScript('webcomponent.field-fancy-select');

            // Build field
            $attr = array(
                'id'             => $this->id,
                'list.select'    => $this->value,
                'list.attr'      => 'class="chzn-custom-value" '
                                    . 'data-custom_group_text="' . $customGroupText . '" '
                                    . 'data-no_results_text="' . JText::_('COM_MODULES_ADD_CUSTOM_POSITION') . '" '
                                    . 'data-placeholder="' . JText::_('COM_MODULES_TYPE_OR_SELECT_POSITION') . '" '
            );

            $html  = '<joomla-field-fancy-select class="" allow-custom placeholder="' . JText::_('COM_MODULES_TYPE_OR_SELECT_POSITION') . '" search-placeholder="' . JText::_('COM_MODULES_TYPE_OR_SELECT_POSITION') . '" >';
            $html .= JHtml::_('select.groupedlist', $positions, $this->name, $attr);
            $html .= '</joomla-field-fancy-select>';

            return $html;
        } elseif (version_compare(JVERSION, '3.0.0', 'ge')) {
            require_once JPATH_ADMINISTRATOR . '/components/com_templates/helpers/templates.php';

            JLoader::register('ModulesHelper', JPATH_ADMINISTRATOR . '/components/com_modules/helpers/modules.php');

            // Load language files
            $language = JFactory::getLanguage();
            // Loads the current language-tag
            $language_tag = $language->getTag();
            $language->load('com_modules', JPATH_ADMINISTRATOR, $language_tag, true);

            JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_modules/helpers/html');

            $clientId          = 0;
            $state             = 1;
            $selectedPosition  = $this->value;
            $positions         = JHtml::_('modules.positions', $clientId, $state, $selectedPosition);

            // Add custom position to options
            $customGroupText = JText::_('COM_MODULES_CUSTOM_POSITION');

            // Build field
            $attr = array(
                'id'             => $this->id,
                'list.select'    => $this->value,
                'list.attr'      => 'class="chzn-custom-value" '
                                    . 'data-custom_group_text="' . $customGroupText . '" '
                                    . 'data-no_results_text="' . JText::_('COM_MODULES_ADD_CUSTOM_POSITION') . '" '
                                    . 'data-placeholder="' . JText::_('COM_MODULES_TYPE_OR_SELECT_POSITION') . '" '
            );

            return JHtml::_('select.groupedlist', $positions, $this->name, $attr);
        } else {
            // For Joomla! 2.5
            return '<input type="text" name="' . $this->name . '" id="' . $this->id . '" class="readonly" value="' . $this->value . '" readonly="readonly"/>';
        }
    }
}
