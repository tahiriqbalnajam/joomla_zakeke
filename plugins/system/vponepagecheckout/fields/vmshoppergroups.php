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

JFormHelper::loadFieldClass('list');

JLoader::register('VmConfig', JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php');
// phpcs:enable PSR1.Files.SideEffects

class JFormFieldVmshoppergroups extends JFormFieldList
{
    protected $type = 'Vmshoppergroups';

    protected static $shopper_groups = null;

    public function getInput()
    {
        if (version_compare(JVERSION, '4.0.0', 'ge')) {
            JFactory::getDocument()->getWebAssetManager()
                ->usePreset('choicesjs')
                ->useScript('webcomponent.field-fancy-select');

            $html  = '<joomla-field-fancy-select class="" allow-custom placeholder="Type or select some options" search-placeholder="Type or select some options">';
            $html .= parent::getInput();
            $html .= '</joomla-field-fancy-select>';
        } else {
            $html = parent::getInput();
        }

        return $html;
    }

    protected function getOptions()
    {
        if (!class_exists('VmConfig')) {
            JFactory::getApplication()->enqueueMessage('VirtueMart 3 Component not found in your site.', 'error');
            return array();
        }

        VmConfig::loadConfig();
        VmConfig::loadJLang('com_virtuemart', true);
        VmConfig::loadJLang('com_virtuemart_shoppers', true);

        if (self::$shopper_groups === null) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('`virtuemart_shoppergroup_id` AS value, `shopper_group_name` AS text, `default`')
                ->from('`#__virtuemart_shoppergroups`')
                ->where('`published` = 1');

            $db->setQuery($query);
            self::$shopper_groups = $db->loadObjectList();
        }

        $ignore_defaults = !empty($this->element['ignore_defaults']) && $this->element['ignore_defaults'] == 'true' ? true : false;
        $options = array();

        if (!empty(self::$shopper_groups)) {
            foreach (self::$shopper_groups as $group) {
                if ($ignore_defaults && $group->default > 0) {
                    continue;
                }

                $options[] = JHtml::_('select.option', (int) $group->value, JText::_($group->text));
            }
        }

        return array_merge(parent::getOptions(), $options);
    }
}
