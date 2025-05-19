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

class JFormFieldVMUserfields extends JFormFieldList
{
    protected $type = 'VMUserfields';

    protected static $userfields = array();

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

        if (!class_exists('VirtueMartModelUserfields')) {
            require(VMPATH_ADMIN . '/models/userfields.php');
        }

        $types = !empty($this->element['field_types']) ? (string) $this->element['field_types'] : array('BT');

        if (!empty($types) && is_string($types)) {
            if (strpos($types, ',') !== false) {
                $types = array_map('trim', explode(',', $types));
            } else {
                $types = array($types);
            }
        }

        $sf_types = !empty($this->element['sf_types']) ? (string) $this->element['sf_types'] : array();

        if (!empty($sf_types) && is_string($sf_types)) {
            if (strpos($sf_types, ',') !== false) {
                $sf_types = array_map('trim', explode(',', $sf_types));
            } else {
                $sf_types = array($sf_types);
            }
        }

        $skips = !empty($this->element['skips']) ? (string) $this->element['skips'] : array();

        if (!empty($skips) && is_string($skips)) {
            if (strpos($skips, ',') !== false) {
                $skips = array_map('trim', explode(',', $skips));
            } else {
                $skips = array($skips);
            }
        }

        $userFieldsModel = VmModel::getModel('Userfields');
        $fields = array();
        $options = array();
        $added = array();

        foreach ($types as $type) {
            if (!isset(self::$userfields[$type])) {
                self::$userfields[$type] = $userFieldsModel->getUserFieldsFor('cart', $type);
            }

            $fields = array_merge($fields, self::$userfields[$type]);
        }

        foreach ($fields as $field) {
            if (!in_array($field->name, $skips) && $field->type != 'delimiter' && !in_array($field->name, $added) && (empty($sf_types) || in_array($field->type, $sf_types))) {
                $options[] = JHtml::_('select.option', (string) $field->name, JText::_($field->title));
                $added[] = $field->name;
            }
        }

        $options = array_merge(parent::getOptions(), $options);
        return $options;
    }
}
