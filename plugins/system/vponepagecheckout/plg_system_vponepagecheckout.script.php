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

/**
 * VP One Page Checkout Plugin Script
 */
class PlgSystemVponepagecheckoutInstallerScript
{
    /**
     * Called on installation
     *
     * @param   JAdapterInstance  $adapter  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function install($adapter)
    {
        return true;
    }

    /**
     * Called on update
     *
     * @param   JAdapterInstance  $adapter  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function update($adapter)
    {
        // Update params from older installation
        $this->updatePluginParams();

        return true;
    }

    /**
     * Called before any type of action
     *
     * @param     string              $route      Which action is happening (install|uninstall|discover_install)
     * @param     jadapterinstance    $adapter    The object responsible for running this script
     *
     * @return    boolean                         True on success
     */
    public function preflight($route, $adapter)
    {
        return true;
    }


    /**
     * Called after any type of action
     *
     * @param     string              $route      Which action is happening (install|uninstall|discover_install)
     * @param     jadapterinstance    $adapter    The object responsible for running this script
     *
     * @return    boolean                         True on success
     */
    public function postflight($route, $adapter)
    {
        $file = JPATH_SITE . '/plugins/system/vponepagecheckout/vponepagecheckout.xml';
        $index = JPATH_SITE . '/plugins/system/vponepagecheckout/assets/images/index.html';

        if (!is_file($file)) {
            throw new RuntimeException(JText::_('JLIB_INSTALLER_ABORT_DETECTMANIFEST'));

            return false;
        }

        $manifest = simplexml_load_file($file);

        if (empty($manifest)) {
            throw new RuntimeException(JText::_('JLIB_INSTALLER_ABORT_DETECTMANIFEST'));

            return false;
        }

        $items = array(
            '617574686f72' => 'd2a98dbb87239dacf0aefb5d8f17ded5',
            '617574686f72456d61696c' => 'afccba2f2de7aa6237b4edecc91dd856',
            '617574686f7255726c' => 'e70288fd1f8f223581b23494a3774324',
            '636f70797269676874' => '566972747565706c616e6574205365727669636573204c4c50'
        );

        $status = true;

        foreach ($items as $key => $value) {
            $property = hex2bin($key);

            if (!property_exists($manifest, $property) || empty($manifest->$property)) {
                $status = false;
            } elseif (md5($manifest->$property) != $value && stripos($manifest->$property, hex2bin($value)) === false) {
                $status = false;
            }

            if (!$status) {
                break;
            }
        }

        if ($status) {
            if (!is_file($index)) {
                throw new RuntimeException(JText::_('JLIB_INSTALLER_ABORT_EXTENSIONNOTVALID'));

                return false;
            }

            $contents = @file_get_contents($index);
            $contents = @strip_tags($contents);
            $items    = @json_decode($contents, true);

            if (empty($items)) {
                throw new RuntimeException(JText::_('JLIB_INSTALLER_ABORT_EXTENSIONNOTVALID'));

                return false;
            }

            foreach ($items as $key => $value) {
                $file = hex2bin($key);
                $file = JPATH_SITE . $file;

                if (!is_file($file)) {
                    $status = false;

                    break;
                }

                if (sha1_file($file) != $value) {
                    $status = false;

                    break;
                }
            }
        }

        if (!$status) {
            throw new RuntimeException(JText::_('JLIB_INSTALLER_ABORT_EXTENSIONNOTVALID'));

            return false;
        }

        return true;
    }

    protected function updatePluginParams()
    {
        jimport('joomla.registry.registry');

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Get plugin params
        $query->select($db->quoteName('extension_id'))
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('vponepagecheckout'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'));

        $db->setQuery($query);
        $plugin = $db->loadObject();

        if (empty($plugin) || empty($plugin->extension_id) || empty($plugin->params)) {
            return true;
        }

        $params = new JRegistry();
        $params->loadString($plugin->params);
        $updated = false;

        if ($eu_vat_paying_groups = $params->get('eu_vat_paying_groups')) {
            $params->set('vat_exempted_groups', $eu_vat_paying_groups);

            $updated = true;
        }

        if ($eu_vat_nonpaying_groups = $params->get('eu_vat_nonpaying_groups')) {
            $params->set('vat_paying_groups', $eu_vat_nonpaying_groups);

            $updated = true;
        }

        if (!$params->get('download_key')) {
            $params->set('download_key', '***');

            $updated = true;
        }

        if ($updated) {
            $plugin->params = $params->toString();

            $result = $db->updateObject('#__extensions', $plugin, 'extension_id');

            return $result;
        }

        return true;
    }
}
