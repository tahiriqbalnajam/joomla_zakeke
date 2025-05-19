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

use Joomla\Registry\Registry;

class JFormRuleVPSystem extends JFormRule
{
    protected $pluginPath = null;

    protected $download_key = null;

    /**
     * Method to test if two fields have a value in order to use only one field.
     * To use this rule, the form
     * XML needs a validate attribute of loginuniquefield and a field attribute
     * that is equal to the field to test against.
     *
     * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed             $value    The form field value to validate.
     * @param   string            $group    The field name group control value. This acts as an array container for the field.
     *                                      For example if the field has name="foo" and the group value is set to "bar" then the
     *                                      full field name would end up being "bar[foo]".
     * @param   Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
     * @param   JForm             $form     The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid, false otherwise.
     */
    public function test(SimpleXMLElement $element, $value, $group = null, $input = null, $form = null)
    {
        if ($form === null) {
            throw new InvalidArgumentException(sprintf('The value for $form must not be null in %s', get_class($this)));
        }

        if ($input === null) {
            throw new InvalidArgumentException(sprintf('The value for $input must not be null in %s', get_class($this)));
        }

        $this->pluginPath = JPath::clean(JPATH_SITE . '/plugins/system/vponepagecheckout');
        $download_key = '';
        $dlk = '';
        $pid = isset($input['params']->pid) ? $input['params']->pid : 0;

        if (!empty($input['params']->download_key)) {
            $data = self::decodeData($input['params']->download_key);

            if (!empty($data) && !empty($data['dlk'])) {
                $download_key = $input['params']->download_key;
                $dlk = $data['dlk'];
            }
        }

        if (empty($dlk) && $this->updateEnabled() && $input['params']->download_key != '***') {
            $this->reset($download_key, $pid, $input);
        }

        return true;
    }

    public function reset($download_key, $pid, $input)
    {
        /** @var \Joomla\CMS\Application\SiteApplication $app */
        $app          = JFactory::getApplication();
        $db           = JFactory::getDbo();
        $extension_id = $app->input->getInt('extension_id', 0);
        $enabled      = (int) $input->get('enabled', 0);

        if ($extension_id > 0) {
            $params = new Registry();
            $params->set('download_key', $download_key);
            $params->set('pid', $pid);

            $query = $db->getQuery(true);
            $query->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('params') . ' = ' . $db->quote($params->toString()))
                ->set($db->quoteName('enabled') . ' = ' . $enabled)
                ->where($db->quoteName('extension_id') . ' = ' . (int) $extension_id);

            $db->setQuery($query);

            try {
                $db->execute();
            } catch (Exception $e) {
                return false;
            }

            if ($enabled) {
                $app->enqueueMessage(hex2bin('506c656173652061646420796f757220446f776e6c6f6164204b657920746f206368616e6765207468652064656661756c742073657474696e67732e'), 'error');
            }

            $app->redirect(JRoute::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . $extension_id, false));
        }
    }

    protected function updateEnabled()
    {
        $file = $this->pluginPath . '/vponepagecheckout.xml';

        if (!file_exists($file)) {
            return true;
        }

        $xml        = simplexml_load_file($file);
        $liveupdate = (int) $xml->liveupdate;

        if ($liveupdate > 1) {
            return false;
        }

        return true;
    }

    protected static function decodeData($string, $renewHost = false)
    {
        $data = array('host' => '', 'dlk' => '', 'uname' => '', 'access' => 0, 'last_checked' => '');

        if (empty($string) || !is_string($string)) {
            return $data;
        }

        $string = @base64_decode($string);

        if (empty($string) || !is_string($string)) {
            return $data;
        }

        $parts  = !empty($string) && strpos($string, '|*|') !== false ? explode('|*|', $string) : array();
        $newHost = self::getHost();

        if ($renewHost) {
            $data['host']         = $newHost;
            $data['dlk']          = isset($parts[1]) ? self::cleanDlk($parts[1]) : '';
            $data['uname']        = isset($parts[2]) ? $parts[2] : '';
            $data['access']       = isset($parts[3]) ? intval($parts[3]) : 0;
            $data['last_checked'] = isset($parts[4]) ? $parts[4] : null;
        } else {
            $host = '';

            if (!empty($parts[0])) {
                $host = @base64_decode($parts[0]);

                if ($host === false) {
                    $host = $parts[0];
                }
            }

            $prefix = 'www.';

            if (!empty($host) && substr($host, 0, strlen($prefix)) == $prefix) {
                $host = substr($host, strlen($prefix));
            }

            if (!empty($newHost) && substr($newHost, 0, strlen($prefix)) == $prefix) {
                $newHost = substr($newHost, strlen($prefix));
            }

            if (!empty($newHost) && $host == $newHost) {
                $data['host']         = $newHost;
                $data['dlk']          = isset($parts[1]) ? self::cleanDlk($parts[1]) : '';
                $data['uname']        = isset($parts[2]) ? $parts[2] : '';
                $data['access']       = isset($parts[3]) ? intval($parts[3]) : 0;
                $data['last_checked'] = isset($parts[4]) ? $parts[4] : null;
            }
        }

        return $data;
    }

    protected static function getHost($host = null)
    {
        $host = $host ? $host : JUri::root();

        if (empty($host)) {
            return '';
        }

        $parts = parse_url($host);

        $result = '';

        if (!empty($parts['host'])) {
            $result .= $parts['host'];
        }

        if (!empty($parts['path'])) {
            $result .= $parts['path'];
        }

        return $result;
    }

    protected static function cleanDlk($dlk)
    {
        if (empty($dlk)) {
            return '';
        }

        $dlk = trim($dlk);

        if (empty($dlk)) {
            return '';
        }

        // Is the Download Key too short?
        if (strlen($dlk) < 32) {
            return '';
        }

        if (strlen($dlk) > 32) {
            $dlk = substr($dlk, 0, 32);
        }

        $dlk = preg_replace('/[^a-zA-Z0-9]+/', '', $dlk);

        if (strlen($dlk) != 32) {
            return '';
        }

        $numbers = preg_replace('/\D/', '', $dlk);
        $letters = preg_replace('/[^a-zA-Z]/', '', $dlk);

        if (strlen($dlk) == strlen($numbers) || strlen($dlk) == strlen($letters)) {
            return '';
        }

        return $dlk;
    }

    public static function setReady()
    {
        VPOPCHelper::scriptOption('READY', defined('VPOPC_FOUND'));
    }
}

if (defined('VPOPC_FOUND')) {
    define('VPOPC_SYSTEMRULE', true);
}
