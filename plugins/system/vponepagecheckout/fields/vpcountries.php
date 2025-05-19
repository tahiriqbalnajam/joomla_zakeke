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

class JFormFieldVPCountries extends JFormFieldList
{
    protected $type = 'VPCountries';

    protected static $vmcountries = null;

    protected static $vendor_country = null;

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
        VmConfig::loadJLang('com_virtuemart_countries', true);

        $vendorasdefault = !empty($this->element['vendorasdefault']) && $this->element['vendorasdefault'] == 'true' ? true : false;
        $euonly          = !empty($this->element['euonly']) && $this->element['euonly'] == 'true' ? true : false;
        $db              = JFactory::getDBO();

        if (self::$vmcountries === null) {
            $query = $db->getQuery(true)
                ->select('`virtuemart_country_id` AS value, `country_name` AS text, `country_2_code`')
                ->from('`#__virtuemart_countries`')
                ->where('published = 1');

            if (version_compare(JVERSION, '3.0.0', 'ge')) {
                $query->clear('limit');
            }

            $db->setQuery($query);
            self::$vmcountries = $db->loadObjectList();
        }

        if ($vendorasdefault && empty($this->value)) {
            if (self::$vendor_country === null) {
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('`#__virtuemart_vmusers` AS a')
                    ->join('LEFT', '`#__virtuemart_userinfos` AS b ON a.`virtuemart_user_id` = b.`virtuemart_user_id`')
                    ->where('a.virtuemart_vendor_id = 1')
                    ->where('b.`address_type` = ' . $db->quote('BT'));
                $db->setQuery($query);
                $vendor = $db->loadAssoc();

                self::$vendor_country = !empty($vendor['virtuemart_country_id']) ? $vendor['virtuemart_country_id'] : 0;
            }

            $this->value = self::$vendor_country;
        }

        $eu_countries = array(
            'AT' => 'Austria',
            'BE' => 'Belgium',
            'BG' => 'Bulgaria',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DE' => 'Germany',
            'DK' => 'Denmark',
            'EE' => 'Estonia',
            'EL' => 'Greece',
            'GR' => 'Greece', // Greece has different country code in VirtueMart
            'ES' => 'Spain',
            'FI' => 'Finland',
            'FR' => 'France ',
            // 'GB' => 'United Kingdom', /* Not in EU */
            'HR' => 'Croatia',
            'HU' => 'Hungary',
            'IE' => 'Ireland',
            'IT' => 'Italy',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'LV' => 'Latvia',
            'MT' => 'Malta',
            'NL' => 'The Netherlands',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'RO' => 'Romania',
            'SE' => 'Sweden',
            'SI' => 'Slovenia',
            'SK' => 'Slovakia'
        );

        $options = array();

        foreach (self::$vmcountries as $country) {
            if (!$euonly || ($euonly && array_key_exists($country->country_2_code, $eu_countries))) {
                $options[] = JHtml::_('select.option', (int) $country->value, JText::_($country->text));
            }
        }

        return array_merge(parent::getOptions(), $options);
    }
}
