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
* VP One Page Checkout system plugin class
* For VirtueMart 3. Comaptible to Joomla! 2.5, Joomla! 3 and Joomla! 4
*
* @since 3.1
*/
class PlgSystemVPOnePageCheckout extends JPlugin
{
    protected $app;

    /**
     * Constructor
     *
     * @param   DispatcherInterface  &$subject  The object to observe
     * @param   array                $config    An optional associative array of configuration settings.
     *                                          Recognized key values include 'name', 'group', 'params', 'language'
     *                                         (this list is not meant to be comprehensive).
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);

        $this->app = empty($this->app) ? JFactory::getApplication() : $this->app;

        JLoader::register('VPOPCHelper', __DIR__ . '/cart/helper.php');
    }

    /**
    * After route events
    *
    * @return void
    */
    public function onAfterRoute()
    {
        // If admin do nothing
        if ($this->isAdmin()) {
            if ($this->app->input->getCmd('ctask') == 'getplgversion') {
                // Return installed plugin version on admin request
                return VPOPCHelper::getInstance($this->params)->getOPCPluginVersion();
            }

            if (!version_compare(JVERSION, '3.0.0', 'ge')) {
                if ($this->app->input->getCmd('plugin') == 'vponepagecheckout') {
                    // Register dlk helper class
                    JLoader::register('VPDownloadKeyHelper', __DIR__ . '/fields/vpdownloadkey/helper.php');

                    $options = array(
                        'manifest' => __DIR__ . DIRECTORY_SEPARATOR . 'vponepagecheckout.xml'
                    );

                    if ($this->app->input->getCmd('method') == 'validatedlk') {
                        // Return validation result
                        return VPDownloadKeyHelper::getInstance($options)->validate($this->params->get('pid', 9));
                    } elseif ($this->app->input->getCmd('method') == 'revalidatedlk') {
                        // Return validation result
                        return VPDownloadKeyHelper::getInstance($options)->revalidate($this->params->get('pid', 9));
                    }
                }
            }

            return;
        }

        // Create a helper instance
        $helper = VPOPCHelper::getInstance($this->params);

        // If it is VirtueMart Cart Page
        if ($helper->isCart()) {
            // If not compatible then we can not proceed
            if (!$helper->isCompatible()) {
                return false;
            }

            if (!class_exists('VirtueMartViewCart')) {
                require_once __DIR__ . '/cart/cartview.html.php';
            } else {
                $msg  = 'VP One Page Checkout plugin could not be loaded. ';
                $msg .= 'You are already using another third party VirtueMart Checkout system ';
                $msg .= 'in your site which does not allow the plugin to get loaded. ';
                $msg .= 'Please disable the same and try again.';

                $this->app->enqueueMessage($msg);
            }

            // Handle all after route actions
            return $helper->handleAfterRouteActions();

            if ($result === true) {
                return;
            }
        }

        // Handle SSL redirections
        $helper->setSSLRules('onAfterRoute');
    }

    /**
    * After dispatch events
    *
    * @return void
    */
    public function onAfterDispatch()
    {
        // If admin do nothing
        if ($this->isAdmin()) {
            return;
        }

        // Create a helper instance
        $helper = VPOPCHelper::getInstance($this->params);

        // Handle SSL redirections
        $helper->setSSLRules('onAfterDispatch');
    }


    /**
    * Before render events
    *
    * @return void
    */
    public function onBeforeRender()
    {
        // If admin do nothing
        if ($this->isAdmin()) {
            return;
        }

        // Create a helper instance
        $helper = VPOPCHelper::getInstance($this->params);

        if ($helper->isCart()) {
            // If not compatible then we can not proceed
            if (!$helper->isCompatible()) {
                return false;
            }

            // Do something if required.
        }
    }

    /**
    * Before head compile events
    *
    * @return void
    */
    public function onBeforeCompileHead()
    {
        // If admin do nothing
        if ($this->isAdmin()) {
            return;
        }
        //$this->app->getDocument()->set('scriptOptions', array());
        // Create a helper instance
        $helper = VPOPCHelper::getInstance($this->params);

        // If it is VirtueMart Cart Page
        if ($helper->isCart()) {
            // If not compatible then we can not proceed
            if (!$helper->isCompatible()) {
                return false;
            }

            if ($this->params->get('hide_system_msg', 1)) {
                // This will save the original messages and rendered html in helper instance.
                $helper->getRenderedMessages(false);
                $helper->saveOriginalMessages();

                // Clear out original system messages for Joomla 4
                if (version_compare(JVERSION, '4.0.0', 'ge')) {
                    JFactory::getDocument()->addScriptOptions('joomla.messages', null, false);
                }

                // Process system messages.
                $helper->processSystemMessages();
            }

            $helper->loadAssets();

            VPOPCHelper::loadVPOPCScripts();
        }
    }

    /**
    * After render events
    *
    * @return void
    */
    public function onAfterRender()
    {
        // If admin do nothing
        if ($this->isAdmin()) {
            return;
        }

        // Create a helper instance
        $helper = VPOPCHelper::getInstance($this->params);

        // Hide system message it cart page.
        // Only if hide system message option enabled.
        if ($helper->isCart()) {
            // If not compatible then we can not proceed
            if (!$helper->isCompatible()) {
                return false;
            }

            if ($this->params->get('hide_system_msg', 1)) {
                // Hide necessary system messages
                $helper->hideSystemMessages();
            }

            if ($this->params->get('show_preloader', 1)) {
                $helper->addPreloader();
            }
        }

        // Save the last visited page url after render to avoid error pages
        $helper->saveLastVisitedPage();
    }

    public function onContentPrepareData($context, $data)
    {
        if ($context == 'com_plugins.plugin' && is_object($data) && !empty($data->element) && $data->element == 'vponepagecheckout') {
            if (!JPluginHelper::isEnabled('system', 'vpadvanceduser') || !defined('JPATH_ADVANCEDUSER_ADMIN') || !version_compare(JVERSION, '3.5', 'ge')) {
                $data->params['vpau_registration_mail'] = 0;
            }

            if (!JPluginHelper::isEnabled('system', 'privacyconsent') || !version_compare(JVERSION, '3.9', 'ge')) {
                $data->params['jcore_privacyconsent'] = 0;
            }
        }
    }

    /**
     * On content form preparation.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onContentPrepareForm($form, $data)
    {
        if (!($form instanceof JForm)) {
            return;
        }

        $context = $form->getName();

        if ($context == 'com_plugins.plugin' && !empty($data->element) && $data->element == 'vponepagecheckout') {
            if (!JPluginHelper::isEnabled('system', 'vpadvanceduser') || !defined('JPATH_ADVANCEDUSER_ADMIN') || !version_compare(JVERSION, '3.5', 'ge')) {
                $form->setFieldAttribute('show_social_login', 'readonly', 'true', 'params');
                $form->setFieldAttribute('social_btn_size', 'readonly', 'true', 'params');
                $form->setFieldAttribute('vpau_registration_mail', 'readonly', 'true', 'params');
                $form->setFieldAttribute('vpau_registration_mail', 'default', '0', 'params');
            }

            if (!JPluginHelper::isEnabled('system', 'privacyconsent') || !version_compare(JVERSION, '3.9', 'ge')) {
                $form->setFieldAttribute('jcore_privacyconsent', 'readonly', 'true', 'params');
            }
        }
    }

    /**
    * Ajax dlk validation event
    *
    * @return json
    */
    public function onAjaxVponepagecheckout()
    {
        if ($this->isAdmin()) {
            // Register dlk helper class
            JLoader::register('VPDownloadKeyHelper', __DIR__ . '/fields/vpdownloadkey/helper.php');

            // Create a helper instance
            $helper = VPOPCHelper::getInstance($this->params);

            $method  = strtolower($this->app->input->getCmd('method'));
            $options = array(
                'manifest' => __DIR__ . DIRECTORY_SEPARATOR . 'vponepagecheckout.xml'
            );

            if ($method == 'validatedlk') {
                // Return validation result
                return VPDownloadKeyHelper::getInstance($options)->validate($this->params->get('pid', 9));
            } elseif ($method == 'revalidatedlk') {
                // Return validation result
                return VPDownloadKeyHelper::getInstance($options)->revalidate($this->params->get('pid', 9));
            } elseif ($method == 'getversion') {
                // Return installed plugin version on admin request
                return $helper->getOPCPluginVersion();
            } elseif ($method == 'getupdate') {
                // Return installed plugin version on admin request
                return $helper->getUpdate();
            }
        }
    }

    public function onInstallerBeforePackageDownload(&$url, &$headers)
    {
        // Register dlk helper class
        JLoader::register('VPDownloadKeyHelper', __DIR__ . '/fields/vpdownloadkey/helper.php');

        $options = array(
            'manifest' => __DIR__ . DIRECTORY_SEPARATOR . 'vponepagecheckout.xml'
        );

        return VPDownloadKeyHelper::getInstance($options)->addDlk($url, $headers);
    }

    public function plgVmOnShowOrderBEShipment($virtuemart_order_id, $virtuemart_payment_id)
    {
        // Create a helper instance
        return VPOPCHelper::getInstance($this->params)->onShowOrderAdmin($virtuemart_order_id, $virtuemart_payment_id);
    }

    private function isAdmin()
    {
        return version_compare(JVERSION, '3.7.0', 'ge') ? $this->app->isClient('administrator') : $this->app->isAdmin();
    }
}
