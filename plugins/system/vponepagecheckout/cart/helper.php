<?php

/**
 * @package      VP One Page Checkout - Joomla! System Plugin
 * @subpackage   For VirtueMart 3+ and VirtueMart 4+
 *
 * @copyright    Copyright (C) 2012-2024 Virtueplanet Services LLP. All rights reserved.
 * @license      GNU General Public License version 2 or later; see LICENSE.txt
 * @authors      Abhishek Das <info@virtueplanet.com>
 * @link         https://www.virtueplanet.com
 *
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;

require_once __DIR__ . '/includes.php';
// phpcs:enable PSR1.Files.SideEffects

/**
 * VP One Page Checkout plugin helper class
 * For VirtueMart 3
 *
 * @since 3.0.0
 */
class VPOPCHelper
{
    protected $input;
    protected $option;
    protected $view;
    protected $task;
    protected $layout;
    protected $tmpl;
    protected $lang;
    protected $checkoutTask;
    protected $type;
    protected $params;
    protected $data;
    protected $error;
    protected $eu_vat_data;
    protected $shopper_group_updated;

    protected static $instances       = array();
    protected static $_originalQueue  = array();
    protected static $_messageQueue   = array();
    protected static $_renderedHTML   = null;
    protected static $_klarnaEnabled  = null;
    protected static $_scriptTexts    = array();
    protected static $_scriptOptions  = array();
    protected static $_loaded         = false;
    protected static $_systemMessages = array();

    /**
     * Construction method of the helper class
     *
     * @param mixed (object/null) $params Plugin params JRegistry object
     *
     * @return void
     */
    public function __construct($params = null)
    {
        if (!class_exists('VmConfig')) {
            // VirtueMart is not installed in this site.
            return false;
        }

        VmConfig::loadConfig();

        /** @var \Joomla\CMS\Application\SiteApplication $app */
        $app = JFactory::getApplication();
        $doc = JFactory::getDocument();

        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            jimport('cms.html.html');

            $this->input         = $app->input;
            $this->option        = strtolower($this->input->getCmd('option', ''));
            $this->view          = strtolower($this->input->getCmd('view', ''));
            $this->task          = strtolower($this->input->getCmd('task', ''));
            $this->task          = $this->input->post->getCmd('task', '') ?
                strtolower($this->input->post->getCmd('task', '')) :
                $this->task;
            $this->layout        = strtolower($this->input->getCmd('layout', ''));
            $this->tmpl          = strtolower($this->input->getCmd('tmpl', ''));
            $this->lang          = strtolower($this->input->get('lang', '', 'STRING'));
            $this->checkoutTask  = strtolower($this->input->get('ctask', '', 'STRING'));
        } else {
            $this->option        = strtolower(JRequest::getCmd('option', ''));
            $this->view          = strtolower(JRequest::getCmd('view', ''));
            $this->task          = strtolower(JRequest::getCmd('task', ''));
            $this->layout        = strtolower(JRequest::getCmd('layout', ''));
            $this->tmpl          = strtolower(JRequest::getCmd('tmpl', ''));
            $this->lang          = strtolower(JRequest::getVar('lang', '', 'STRING'));
            $this->checkoutTask  = strtolower(JRequest::getVar('ctask', '', 'STRING'));
        }

        $this->type    = strtolower($doc->getType());
        $this->params  = $params;
    }

    /**
     * Method to get an instance of the the VPOPCHelper class
     *
     * @param mixed (object/null) $params Plugin params JRegistry object
     *
     * @return object VPOPCHelper class object
     */
    public static function getInstance($params = null)
    {
        $hash = !empty($params) ? md5(serialize($params)) : 0;

        if (!isset(self::$instances[$hash])) {
            self::$instances[$hash] = new VPOPCHelper($params);
        }

        return self::$instances[$hash];
    }

    /**
     * Method to check if we are in the VirtueMart cart page
     *
     * @return boolean Returns false if not cart page
     */
    public function isCart()
    {
        $isCart    = ($this->option == 'com_virtuemart' && $this->view == 'cart' && $this->type == 'html') ||
            ($this->option == 'com_virtuemart' && $this->view == 'vmplg' && $this->task == 'pluginuserpaymentcancel' && $this->type == 'html') ||
            ($this->option == 'com_virtuemart' && $this->view == 'pluginresponse' && $this->task == 'pluginuserpaymentcancel' && $this->type == 'html');
        $firstLoad = false;
        $failed    = false;

        if ($isCart) {
            if (class_exists('VmConfig') && !JFactory::getUser()->guest && empty($this->task) && empty($this->checkoutTask) && $this->layout != 'order_done' && $this->layout != 'orderdone') {
                VmConfig::set('alwaysLoadStoredShopperAddress', 1);
            }

            $cart = VirtueMartCart::getCart();
            $isCart = $isCart && ($this->layout != 'cart' && $cart->layout != 'cart') && ($this->layout != 'amazon' && $cart->layout != 'amazon');

            if ($isCart && !self::$_loaded && class_exists('VmConfig')) {
                // Load required VirtueMart languages
                VmConfig::loadJLang('com_virtuemart', true);
                VmConfig::loadJLang('com_virtuemart_shoppers', true);

                // Load other required languages
                $language = JFactory::getLanguage();
                $language->load('lib_joomla');
                $language->load('com_users');
                $language->load('plg_system_vponepagecheckout', JPATH_ADMINISTRATOR);
                $language->load('plg_system_vponepagecheckout_override', JPATH_SITE);

                self::$_loaded = true;
                $firstLoad = true;
            }
        }

        if ($isCart && is_object($this->params)) {
            $params = $this->params->toArray();

            if (is_array($params) && count($params) && (!isset($params['download_key']) || !isset($params['pid']))) {
                $failed = true;
                $isCart = false;
            }
        }

        if ($isCart && !$this->isMe()) {
            $failed = true;
            $isCart = false;
        }

        if ($failed && $firstLoad) {
            if (version_compare(JVERSION, '4.0.0', 'ge')) {
                throw new Joomla\CMS\MVC\View\GenericDataException('<b>Exiting VP One Page Checkout!</b>  Integrity check failed. Please ensure you are using an original copy of the plugin.', 100);
            } else {
                JError::raiseWarning(100, '<b>Exiting VP One Page Checkout!</b>  Integrity check failed. Please ensure you are using an original copy of the plugin.');
            }
        }

        return $isCart;
    }

    /**
     * Method to check if the the plugin is compatible to installed VirtueMart version
     *
     * @return boolean Returns false if not compatible
     */
    public function isCompatible()
    {
        $app = JFactory::getApplication();

        if (!defined('VM_VERSION')) {
            $app->enqueueMessage('It appears VirtueMart Component is not installed. VP One Page Checkout plugin is an extension of VirtueMart Component.', 'error');
            return;
        }

        if (VM_VERSION < 3) {
            $app->enqueueMessage('This package of VP One Page Checkout plugin is compatible to VirtueMart 3 and above. You can get VirtueMart ' . VM_REV .
                ' compatible package of the plugin from http://www.virtueplanet.com', 'error');
            return false;
        }

        return true;
    }

    /**
     * Method to load static css and js files
     *
     * @return void
     */
    public function loadAssets()
    {
        // Ensure that none of our assets are loaded on order done page.
        if (($this->task == 'confirm' || $this->layout == 'order_done' || $this->layout == 'orderdone' || $this->task == 'orderdone')) {
            $cart = VirtueMartCart::getCart();

            if (!empty($cart->_dataValidated)) {
                return;
            }
        }

        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            JHtml::_('jquery.framework');
        }

        vmJsApi::jPrice();

        /** @var \Joomla\CMS\Application\SiteApplication $app */
        $app           = JFactory::getApplication();
        $doc           = JFactory::getDocument();
        $root          = JUri::root(true);
        $version       = $this->getVersion();
        $template      = $app->getTemplate(true);
        $needLoading   = ($this->params->get('load_jquery', 2) == 2 && strpos($template->template, 'vp_') === false) ||
            ($this->params->get('load_jquery', 2) == 1);
        $loadedScripts = $doc->_scripts;
        $doc->_scripts = array();
        $jQuery        = array('/jquery.min.js', '/jquery.js');
        $loaded        = array();
        $unsetjQuery   = false;

        if ($needLoading && (JVM_VERSION == 2) && !VmConfig::get('jquery', 1)) {
            $doc->addScript($root . '/plugins/system/vponepagecheckout/assets/js/jquery.min.js');
            $doc->addScript($root . '/plugins/system/vponepagecheckout/assets/js/jquery-noconflict.js');
            $doc->addScript($root . '/plugins/system/vponepagecheckout/assets/js/jquery-migrate.min.js');
            $unsetjQuery = true;
        }

        foreach ($loadedScripts as $script => &$attribs) {
            $suffix = '';

            if (strpos($script, '?') !== false) {
                $segments = explode('?', $script);
                $script = $segments[0];
                $suffix = '?' . $segments[1];
            }

            if ($unsetjQuery && $this->strposa($script, $jQuery) !== false) {
                $foundNeedle = $this->strposa($script, $jQuery, 0, true);

                if ($this->strposa($script, $jQuery) == (strlen($script) - strlen($foundNeedle))) {
                    continue;
                }
            }

            if (strpos($script, 'jquery.easing.min.js') !== false || strpos($script, 'jquery.easing.js') !== false || strpos($script, 'jquery.easing.1.3.min.js') !== false) {
                $loaded['easing'] = true;
            } elseif (strpos($script, 'components/com_virtuemart/assets/js/jquery-ui.min.js') !== false) {
                $loaded['easing'] = true;
                $loaded['jquery-ui'] = true;
            } elseif (strpos($script, 'fancybox/jquery.fancybox-1.3.4.pack.js') !== false) {
                $loaded['fancybox'] = true;
            } elseif (strpos($script, 'jquery.hoverIntent.min.js') !== false || strpos($script, 'jquery.hoverIntent.minified.js') !== false || strpos($script, 'jquery.hoverIntent.js') !== false) {
                $loaded['hoverIntent'] = true;
            }

            $script = $script . $suffix;

            $mime  = isset($attribs['mime']) ? $attribs['mime'] : 'text/javascript';
            $defer = isset($attribs['defer']) ? $attribs['defer'] : false;
            $async = isset($attribs['async']) ? $attribs['async'] : false;

            if (version_compare(JVERSION, '3.7.0', 'ge')) {
                $attributes = array();
                $options = array();

                $attributes['type'] = $mime;
                $attributes['defer'] = $defer;
                $attributes['async'] = $async;

                if (isset($attribs['options'])) {
                    $options = $attribs['options'];
                }

                // Re-enqueue the script
                $doc->addScript($script, $options, $attributes);
            } else {
                // Re-enqueue the script
                $doc->addScript($script, $mime, $defer, $async);
            }
        }

        if ($this->hasKlarnaEnabled()) {
            $klarnaAssetsPath = $root . '/plugins/vmpayment/klarna/klarna/assets';
            $doc->addStyleSheet($klarnaAssetsPath . '/css/style.css');
            $doc->addStyleSheet($klarnaAssetsPath . '/css/klarna.css');
            $doc->addScript(JURI::root() . 'plugins/vmpayment/klarna/klarna/assets/js/klarna_pp.js');
            $doc->addScript('https://static.klarna.com:444/external/js/klarnapart.js');
            $doc->addScript($klarnaAssetsPath . '/js/klarna_general.js');
            $doc->addScript('https://static.klarna.com/external/js/klarnaConsentNew.js');
        }

        if ($this->params->get('load_jquery_plugins', 2) == 2 && (strpos($template->template, 'vp_merchant') === false || strpos($template->template, 'vp_smart') === false)) {
            if (!isset($loaded['hoverIntent'])) {
                $doc->addScript($this->getStaticFiles('jquery.hoverIntent.min.js', 'js'));
            }
            if (!isset($loaded['easing'])) {
                $doc->addScript($this->getStaticFiles('jquery.easing.1.3.min.js', 'js'));
            }
        } elseif ($this->params->get('load_jquery_plugins', 2) == 1) {
            $doc->addScript($this->getStaticFiles('jquery.hoverIntent.min.js', 'js'));
            $doc->addScript($this->getStaticFiles('jquery.easing.1.3.min.js', 'js'));
        }

        if ($this->params->get('tos_fancybox', 0)) {
            if (!isset($loaded['fancybox'])) {
                $vmFancyJS      = '/components/com_virtuemart/assets/js/fancybox/jquery.fancybox-1.3.4.pack.js';
                $vmFancyJSPath  = JPath::clean(JPATH_SITE . $vmFancyJS);
                $vmFancyCSS     = '/components/com_virtuemart/assets/css/jquery.fancybox-1.3.4.css';
                $vmFancyCSSPath = JPath::clean(JPATH_SITE . $vmFancyCSS);

                if (file_exists($vmFancyJSPath) && file_exists($vmFancyCSSPath)) {
                    $doc->addScript($root . $vmFancyJS);
                    $doc->addStyleSheet($root . $vmFancyCSS);
                } else {
                    $doc->addScript($this->getStaticFiles('jquery.fancybox-1.3.4.pack.js', 'js'));
                    $doc->addStyleSheet($this->getStaticFiles('jquery.fancybox-1.3.4.css', 'css'));
                }
            }
        } else {
            $doc->addScript($this->getStaticFiles('bootmodal.js', 'js'));
        }

        $doc->addScript($this->getStaticFiles('spin.min.js', 'js'));
        $doc->addScript($this->getStaticFiles('plugin.min.js', 'js', $version));

        if ($this->params->get('color', 1) == 2) {
            $SPINNER_COLOR = '#FFF';
            $doc->addStyleSheet($this->getStaticFiles('dark-checkout.css', 'css', $version));
        } else {
            $SPINNER_COLOR = '#000';
            $doc->addStyleSheet($this->getStaticFiles('light-checkout.css', 'css', $version));
        }

        if ($this->params->get('responsive', 1)) {
            $doc->addStyleSheet($this->getStaticFiles('responsive-procheckout.css', 'css', $version));
        }

        // Load all body scripts
        $CheckoutURI = $root . '/index.php?option=com_virtuemart&view=cart';

        if (!empty($this->lang)) {
            $CheckoutURI .= '&lang=' . substr(VmConfig::$vmlang, 0, 2);
        }

        $ASSETPATH              = $root . '/plugins/system/vponepagecheckout/assets/';
        $BASEPATH               = $root . '/plugins/system/vponepagecheckout/';
        $userFieldsModel        = VmModel::getModel('userfields');
        $VMCONFIGTOS            = ($userFieldsModel->getIfRequired('agreed') && VmConfig::get('oncheckout_show_legal_info', 1)) || VmConfig::get('agree_to_tos_onorder') ? 1 : 0;
        $BTASST                 = (int) $this->params->get('check_shipto_address', 1);
        $GROUPING               = (int) $this->params->get('field_grouping', 1);

        if (version_compare(vmVersion::$RELEASE, '3.0.6', '>=')) {
            $AUTOSHIPMENT         = (int) VmConfig::get('set_automatic_shipment');
            $AUTOPAYMENT          = (int) VmConfig::get('set_automatic_payment');
        } else {
            $AUTOSHIPMENT         = (int) VmConfig::get('automatic_shipment');
            $AUTOPAYMENT          = (int) VmConfig::get('automatic_payment');
        }

        $AJAXVALIDATION            = (int) $this->params->get('ajax_validation', 0);
        $RELOAD                    = (int) $this->params->get('reload', 0);
        $RELOAD_AFTER_PAYMENT      = (int) $this->params->get('reload_after_payment_selection', 0);
        $TOSFANCY                  = (int) $this->params->get('tos_fancybox', 1);
        $EDITPAYMENTURI            = JRoute::_('index.php?view=cart&task=editpayment', false);
        $STYLERADIOCHEBOX          = (int) $this->params->get('style_radio_checkbox', 1);
        $REMOVEUNNECESSARYLINKS    = (int) $this->params->get('remove_unnecessary_links', 1);
        $RELOADPAYMENTS            = (int) $this->params->get('reload_payment_on_shipment_selection', 0);
        $RELOADALLFORCOUPON        = (int) $this->params->get('reload_all_on_apply_coupon', 0);
        $DISABLELIVEVALIDATION     = $this->params->get('live_validation', 0) ? 0 : 1;
        $AUTO_UPDATE_QUANTITY      = (int) $this->params->get('auto_update_quantity', 1);
        $user_params               = JComponentHelper::getParams('com_users');
        $PASSWORD_LENGTH           = (int) $user_params->get('minimum_length', 4);
        $PASSWORD_INTEGERS         = (int) $user_params->get('minimum_integers', 0);
        $PASSWORD_SYMBOLS          = (int) $user_params->get('minimum_symbols', 0);
        $PASSWORD_UPPERCASE        = (int) $user_params->get('minimum_uppercase', 0);
        $BT_UPDATE_FIELDS          = (array) $this->params->get('custom_bt_update_fields', array());
        $ST_UPDATE_FIELDS          = (array) $this->params->get('custom_st_update_fields', array());
        $EU_VAT_FIELD              = $this->params->get('eu_vat', 0) ? $this->params->get('eu_vat_field', '') : '';
        $DISABLEUSERNAMEVALIDATION = $this->params->get('disable_username_validation', 0) ? 1 : 0;

        if ($this->params->get('eu_vat', 0) && $this->params->get('eu_vat_field')) {
            $BT_UPDATE_FIELDS[] = $EU_VAT_FIELD;
            $ST_UPDATE_FIELDS[] = $EU_VAT_FIELD;
        }

        if (!empty($ST_UPDATE_FIELDS)) {
            foreach ($ST_UPDATE_FIELDS as &$ST_UPDATE_FIELD) {
                $ST_UPDATE_FIELD = 'shipto_' . $ST_UPDATE_FIELD;
            }
        }

        // Add options for script
        self::scriptOption('URI', $CheckoutURI);
        self::scriptOption('ASSETPATH', $ASSETPATH);
        self::scriptOption('BASEPATH', $BASEPATH);
        self::scriptOption('RELOAD', $RELOAD);
        self::scriptOption('RELOAD_AFTER_PAYMENT_SELECTION', $RELOAD_AFTER_PAYMENT);
        self::scriptOption('BTASST', $BTASST);
        self::scriptOption('GROUPING', $GROUPING);
        self::scriptOption('VMCONFIGTOS', $VMCONFIGTOS);
        self::scriptOption('SPINNER_COLOR', $SPINNER_COLOR);
        self::scriptOption('AUTOSHIPMENT', $AUTOSHIPMENT);
        self::scriptOption('AUTOPAYMENT', $AUTOPAYMENT);
        self::scriptOption('AJAXVALIDATION', $AJAXVALIDATION);
        self::scriptOption('EDITPAYMENTURI', $EDITPAYMENTURI);
        self::scriptOption('TOSFANCY', $TOSFANCY);
        self::scriptOption('STYLERADIOCHEBOX', $STYLERADIOCHEBOX);
        self::scriptOption('REMOVEUNNECESSARYLINKS', $REMOVEUNNECESSARYLINKS);
        self::scriptOption('RELOADPAYMENTS', $RELOADPAYMENTS);
        self::scriptOption('RELOADALLFORCOUPON', $RELOADALLFORCOUPON);
        self::scriptOption('DISABLELIVEVALIDATION', $DISABLELIVEVALIDATION);
        self::scriptOption('AUTO_UPDATE_QUANTITY', $AUTO_UPDATE_QUANTITY);
        self::scriptOption('PASSWORD_LENGTH', $PASSWORD_LENGTH);
        self::scriptOption('PASSWORD_INTEGERS', $PASSWORD_INTEGERS);
        self::scriptOption('PASSWORD_SYMBOLS', $PASSWORD_SYMBOLS);
        self::scriptOption('PASSWORD_UPPERCASE', $PASSWORD_UPPERCASE);
        self::scriptOption('BT_UPDATE_FIELDS', $BT_UPDATE_FIELDS);
        self::scriptOption('ST_UPDATE_FIELDS', $ST_UPDATE_FIELDS);
        self::scriptOption('EU_VAT_FIELD', $EU_VAT_FIELD);
        self::scriptOption('DISABLEUSERNAMEVALIDATION', $DISABLEUSERNAMEVALIDATION);

        // Load JText languages for JavaScript
        self::scriptText('JLIB_LOGIN_AUTHENTICATE');
        self::scriptText('COM_VIRTUEMART_CART_PLEASE_ACCEPT_TOS');
        self::scriptText('COM_VIRTUEMART_USER_FORM_MISSING_REQUIRED_JS');
        self::scriptText('COM_VIRTUEMART_CART_NO_SHIPMENT_SELECTED');
        self::scriptText('COM_VIRTUEMART_CART_NO_PAYMENT_SELECTED');
        self::scriptText('COM_VIRTUEMART_SAVE');
        self::scriptText('PLG_VPONEPAGECHECKOUT_REQUIRED_FIELD');
        self::scriptText('PLG_VPONEPAGECHECKOUT_REQUIRED_FIELDS_MISSING');
        self::scriptText('PLG_VPONEPAGECHECKOUT_WEAK');
        self::scriptText('PLG_VPONEPAGECHECKOUT_TOO_SHORT');
        self::scriptText('PLG_VPONEPAGECHECKOUT_GOOD');
        self::scriptText('PLG_VPONEPAGECHECKOUT_STRONG');
        self::scriptText('PLG_VPONEPAGECHECKOUT_INVALID');
        self::scriptText('PLG_VPONEPAGECHECKOUT_VALIDATED');
        self::scriptText('PLG_VPONEPAGECHECKOUT_EMAIL_INVALID');
        self::scriptText('COM_USERS_PROFILE_EMAIL1_MESSAGE');
        self::scriptText('PLG_VPONEPAGECHECKOUT_USERNAME_INVALID');
        self::scriptText('COM_USERS_PROFILE_USERNAME_MESSAGE');
        self::scriptText('PLG_VPONEPAGECHECKOUT_REGISTRATION_COMPLETED');
        self::scriptText('PLG_VPONEPAGECHECKOUT_EMAIL_SAVED');
        self::scriptText('PLG_VPONEPAGECHECKOUT_LOGIN_COMPLETED');
        self::scriptText('PLG_VPONEPAGECHECKOUT_SAVING_BILLING_ADDRESS');
        self::scriptText('PLG_VPONEPAGECHECKOUT_BILLING_ADDRESS_SAVED');
        self::scriptText('PLG_VPONEPAGECHECKOUT_SAVING_SHIPPING_ADDRESS');
        self::scriptText('PLG_VPONEPAGECHECKOUT_SHIPPING_ADDRESS_SAVED');
        self::scriptText('PLG_VPONEPAGECHECKOUT_SAVING_CREDIT_CARD');
        self::scriptText('PLG_VPONEPAGECHECKOUT_CREDIT_CARD_SAVED');
        self::scriptText('PLG_VPONEPAGECHECKOUT_VERIFYING_ORDER');
        self::scriptText('PLG_VPONEPAGECHECKOUT_PLACING_ORDER');
        self::scriptText('PLG_VPONEPAGECHECKOUT_PLEASE_WAIT');
        self::scriptText('PLG_VPONEPAGECHECKOUT_COUPON_EMPTY');
        self::scriptText('VMPAYMENT_PAYPAL_REDIRECT_MESSAGE');
        self::scriptText('COM_VIRTUEMART_REG_COMPLETE');
        self::scriptText('PLG_VPONEPAGECHECKOUT_REGISTRATION_NEED_LOGIN');
        self::scriptText('PLG_VPONEPAGECHECKOUT_ONLY_REGISTERED_USER_CAN_CHECKOUT');
        self::scriptText('PLG_VPONEPAGECHECKOUT_LOGIN_NEEDED');
        self::scriptText('COM_VIRTUEMART_NONE');
        self::scriptText('COM_VIRTUEMART_LIST_EMPTY_OPTION');
        self::scriptText('PLG_VPONEPAGECHECKOUT_EU_VAT_INVALID');
        self::scriptText('PLG_VPONEPAGECHECKOUT_SYSTEM_ERROR_JS');

        // For password validation in Joomla 3 and Joomla 4
        self::scriptText('COM_USERS_MSG_NOT_ENOUGH_INTEGERS_N', 'JFIELD_PASSWORD_NOT_ENOUGH_INTEGERS_N');
        self::scriptText('COM_USERS_MSG_NOT_ENOUGH_INTEGERS_N_1', 'JFIELD_PASSWORD_NOT_ENOUGH_INTEGERS_N_1');
        self::scriptText('COM_USERS_MSG_NOT_ENOUGH_SYMBOLS_N', 'JFIELD_PASSWORD_NOT_ENOUGH_SYMBOLS_N');
        self::scriptText('COM_USERS_MSG_NOT_ENOUGH_SYMBOLS_N_1', 'JFIELD_PASSWORD_NOT_ENOUGH_SYMBOLS_N_1');
        self::scriptText('COM_USERS_MSG_NOT_ENOUGH_UPPERCASE_LETTERS_N', 'JFIELD_PASSWORD_NOT_ENOUGH_UPPERCASE_LETTERS_N');
        self::scriptText('COM_USERS_MSG_NOT_ENOUGH_UPPERCASE_LETTERS_N_1', 'JFIELD_PASSWORD_NOT_ENOUGH_UPPERCASE_LETTERS_N_1');
        self::scriptText('COM_USERS_MSG_PASSWORD_TOO_LONG', 'JFIELD_PASSWORD_TOO_LONG');
        self::scriptText('COM_USERS_MSG_PASSWORD_TOO_SHORT_N', 'JFIELD_PASSWORD_TOO_SHORT_N');
        self::scriptText('COM_USERS_MSG_SPACES_IN_PASSWORD', 'JFIELD_PASSWORD_SPACES_IN_PASSWORD');

        // New since version 6.1
        self::scriptText('PLG_VPONEPAGECHECKOUT_N_ITEMS');
        self::scriptText('PLG_VPONEPAGECHECKOUT_N_ITEMS_1');
    }

    /**
     * Method to add a script option/params to VPOPC
     *
     * @param string                                      $name Name of the option
     * @param mixed (string/integer/boolean/object/array) $value Value of the option
     *
     * @return void
     */
    public static function scriptOption($name, $value)
    {
        $name = strtoupper($name);

        self::$_scriptOptions[$name] = $value;
    }

    /**
     * Method to add a script language to VPOPC
     *
     * @param string $langTag Language Tag
     *
     * @return void
     */
    public static function scriptText($langTag, $fallback = null)
    {
        $langTag = strtoupper($langTag);

        if (!empty($fallback) && is_string($fallback)) {
            $fallback = strtoupper($fallback);
            $lang     = JFactory::getLanguage();

            if (!$lang->hasKey($langTag)) {
                self::$_scriptTexts[$langTag] = JText::_($fallback);
            } else {
                self::$_scriptTexts[$langTag] = JText::_($langTag);
            }
        } else {
            self::$_scriptTexts[$langTag] = JText::_($langTag);
        }
    }

    /**
     * Method to load inline body scripts for VPOPC
     *
     * @return void
     */
    public static function loadVPOPCScripts()
    {
        if (empty(self::$_scriptTexts) && empty(self::$_scriptOptions)) {
            return;
        }

        /** @var \Joomla\CMS\Document\HtmlDocument $doc */
        $doc     = JFactory::getDocument();
        $tab     = $doc->_getTab();
        $lineEnd = $doc->_getLineEnd();
        $strings = !empty(self::$_scriptTexts) ? json_encode(self::$_scriptTexts) : '{}';
        $options = !empty(self::$_scriptOptions) ? json_encode(self::$_scriptOptions) : '{}';

        $script = $lineEnd . $tab . $tab . '<script type="application/json" class="vpopc-script-strings new">' . $strings . '</script>' . $lineEnd;

        $doc->addCustomTag($script);

        $script = $lineEnd . $tab . $tab . '<script type="application/json" class="vpopc-script-options new">' . $options . '</script>' . $lineEnd;

        $doc->addCustomTag($script);

        // Reset the list after load.
        self::$_scriptTexts = array();
        self::$_scriptOptions = array();
    }

    /**
     * Method to get the static file url
     *
     * @param string               $fileName Name of the file
     * @param string               $type     Type of the asset i.e. css or js.
     * @param mixed (string/float) $ver
     *
     * @return string Full file URL
     */
    public function getStaticFiles($fileName, $type = 'css', $ver = null)
    {
        /** @var \Joomla\CMS\Application\SiteApplication $app */
        $app          = JFactory::getApplication();
        $template     = $app->getTemplate(true);
        $type         = trim(strtolower($type));
        $corePath     = '/plugins/system/vponepagecheckout/assets/' . $type . '/';
        $templatePath = '/templates/' . $template->template . '/' . $type . '/plg_system_vponepagecheckout/';

        if (is_file(JPath::clean(JPATH_SITE . $templatePath . $fileName))) {
            $return = JUri::root(true) . $templatePath . $fileName;
        } else {
            $return = JUri::root(true) . $corePath . $fileName;
        }

        if (!empty($ver)) {
            $return .= '?ver=' . trim($ver);
        }

        return $return;
    }

    /**
     * Method to get the installed version of the plugin
     *
     * @return string Plugin version
     */
    public function getVersion()
    {
        if (!$file = $this->getXmlFile()) {
            return 'Invalid';
        }

        $xml     = simplexml_load_file($file);
        $version = (string) $xml->version;

        return trim($version);
    }

    /**
     * Method to get the installed version of the plugin
     *
     * @return string Plugin version
     */
    public function isMe()
    {
        if (!$file = $this->getXmlFile()) {
            return false;
        }

        if (!JPluginHelper::isEnabled('system', 'vponepagecheckout')) {
            return false;
        }

        $xml        = simplexml_load_file($file);
        $liveupdate = (int) $xml->liveupdate;

        if (!$liveupdate) {
            return false;
        }

        if ($liveupdate === 1) {
            if (!file_exists(JPATH_SITE . '/plugins/system/vponepagecheckout/fields/vpdownloadkey.php')) {
                return false;
            }

            $manifest = simplexml_load_file($file);

            if (!isset($manifest->config)) {
                return true;
            }

            $found = false;

            foreach ($manifest->config->children() as $fields) {
                if ($fields->attributes()->name == 'params' && isset($fields->fieldset)) {
                    // Getting the fieldset tags
                    $fieldsets = $fields->fieldset;

                    // Iterating through the fieldsets:
                    foreach ($fieldsets as $fieldset) {
                        if (!count($fieldset->children()) || $fieldset->attributes()->name != 'basic') {
                            // Either the tag does not exist or has no children therefore we return zero files processed.
                            continue;
                        }

                        // Iterating through the fields and collecting the name/default values:
                        foreach ($fieldset as $field) {
                            if ($field->attributes()->name == 'download_key') {
                                if ($field->attributes()->type != 'vpdownloadkey') {
                                    return false;
                                }

                                $found = true;
                            }
                        }
                    }
                }
            }

            if (!$found) {
                return false;
            }
        }

        defined('VPOPC_FOUND') or define('VPOPC_FOUND', true);

        $this->registerClasses();

        return true;
    }

    public function getXmlFile()
    {
        $file = JPath::clean(JPATH_SITE . '/plugins/system/vponepagecheckout/vponepagecheckout.xml');

        if (!file_exists($file)) {
            return false;
        }

        return $file;
    }

    /**
     * Method to check if Klarna payment plugin is enabled
     *
     * @return boolean Returns false if not enabled.
     */
    public function hasKlarnaEnabled()
    {
        if (self::$_klarnaEnabled === null) {
            $db    = JFactory::getDBO();
            $query = $db->getQuery(true)
                ->select('COUNT(virtuemart_paymentmethod_id)')
                ->from('#__virtuemart_paymentmethods')
                ->where('published = 1')
                ->where('payment_element = ' . $db->quote('klarna'));
            $db->setQuery($query);
            $count = $db->loadResult();

            self::$_klarnaEnabled = (!empty($count) && $count > 0);
        }
        return self::$_klarnaEnabled;
    }

    /**
     * Method to set SSL and non-SSL url redirections
     *
     * @param string $stage Joomla sysytem plugin event when the funtion is being called.
     *
     * @return void
     */
    public function setSSLRules($stage = 'onAfterRoute')
    {
        VmConfig::loadConfig();

        /** @var \Joomla\CMS\Application\SiteApplication $app */
        $app           = JFactory::getApplication();
        $uri           = JUri::getInstance();
        $SSLEnabled    = VmConfig::get('useSSL', 0);
        $post          = $app->input->post->getArray();
        $canRedirect   = empty($post) || ($stage == 'onAfterDispatch');

        if (!$SSLEnabled || !$canRedirect || $this->type != 'html') {
            return;
        }

        if ($this->isCart() && !$uri->isSSL()) {
            $uri->setScheme('https');
            $app->redirect($uri->toString());
            return $app->close();
        } elseif (!$this->isCart() && $uri->isSSL() && $this->params->get('disable_ssl', 1)) {
            $uri->setScheme('http');
            $app->redirect($uri->toString());
            return $app->close();
        }
    }

    /**
     * Method to save the last visited internal page url outside cart to userstate.
     *
     * @return void
     */
    public function saveLastVisitedPage()
    {
        if (!$this->isCart() && $this->type == 'html' && empty($this->tmpl)) {
            /** @var \Joomla\CMS\Application\SiteApplication $app */
            $app = JFactory::getApplication();
            $uri = JUri::getInstance();
            $url = $uri->toString();
            $url = (!JUri::isInternal($url)) ? '' : $url;

            $app->setUserState('proopc.lastvisited.url', $url);
        }
    }

    /**
     * Method to hide Joomla! system messages
     *
     * @return void
     */
    public function processSystemMessages()
    {
        $app      = JFactory::getApplication();
        $messages = !empty(self::$_originalQueue) ? self::$_originalQueue : $app->getMessageQueue();

        if (empty($messages) || $this->type != 'html' || $this->task == 'confirm') {
            return;
        }

        $messages = $this->cleanMessages($messages);

        foreach ($messages as $message) {
            // Enqueue other messages in the helper instance
            $this->enqueueMessage($message['message'], $message['type']);
        }

        self::$_systemMessages['original'] = $this->getRenderedMessages();
        self::$_systemMessages['new']      = $this->renderMessages();
    }

    /**
     * Method to hide Joomla! system messages
     *
     * @return void
     */
    public function hideSystemMessages()
    {
        /** @var \Joomla\CMS\Application\SiteApplication $app */
        $app          = JFactory::getApplication();
        $originalHTML = isset(self::$_systemMessages['original']) ? self::$_systemMessages['original'] : '';
        $newHTML      = isset(self::$_systemMessages['new']) ? self::$_systemMessages['new'] : '';

        if (is_string($originalHTML) && !empty($originalHTML) && is_string($newHTML)) {
            $body = version_compare(JVERSION, '4.0.0', 'ge') ? $app->getBody() : JResponse::getBody();
            $body = str_replace($originalHTML, $newHTML, $body);

            if (version_compare(JVERSION, '4.0.0', 'ge')) {
                $app->setBody($body);
            } else {
                JResponse::setBody($body);
            }
        }
    }

    public function cleanMessages($messages)
    {
        // What we need to hide
        $exactMatch  = array();
        $prefixMatch = array();
        $suffixMatch = array();
        $anyMatch    = array();

        $prefixMatch[] = trim(vmText::sprintf('COM_VIRTUEMART_MISSING_VALUE_FOR_FIELD', ''));
        $suffixMatch[] = trim(vmText::sprintf('COM_VIRTUEMART_MISSING_VALUE_FOR_FIELD', ''));
        $prefixMatch[] = 'Missing value for';
        $prefixMatch[] = vmText::_('VMPAYMENT_AUTHORIZENET_CARD_NUMBER_INVALID');
        $exactMatch[]  = vmText::_('COM_VIRTUEMART_USER_FORM_MISSING_REQUIRED_JS');
        $exactMatch[]  = vmText::_('COM_VIRTUEMART_CHECKOUT_PLEASE_ENTER_ADDRESS');
        $exactMatch[]  = vmText::_('COM_VIRTUEMART_CART_PLEASE_ACCEPT_TOS');
        $exactMatch[]  = vmText::_('COM_VIRTUEMART_MISSING_TOS');
        $exactMatch[]  = vmText::_('VMPAYMENT_AUTHORIZENET_CARD_NUMBER_INVALID');
        $exactMatch[]  = vmText::_('VMPAYMENT_AUTHORIZENET_CARD_CVV_INVALID');
        $exactMatch[]  = vmText::_('VMPAYMENT_AUTHORIZENET_CARD_EXPIRATION_DATE_INVALID');
        $exactMatch[]  = 'VMPAYMENT_PAYPAL_CC_CARD_CVV_INVALID';
        $exactMatch[]  = vmText::_('VMPAYMENT_PAYPAL_CC_CARD_CVV_INVALID');
        $exactMatch[]  = 'VMPAYMENT_PAYPAL_CC_CARD_EXPIRATION_DATE_INVALID';
        $exactMatch[]  = vmText::_('VMPAYMENT_PAYPAL_CC_CARD_EXPIRATION_DATE_INVALID');
        $exactMatch[]  = 'VMPAYMENT_PAYPAL_CC_CARD_NUMBER_INVALID';
        $exactMatch[]  = vmText::_('VMPAYMENT_PAYPAL_CC_CARD_NUMBER_INVALID');
        $exactMatch[]  = 'VMPAYMENT_PAYPAL_CC_CARD_TYPE_INVALID';
        $exactMatch[]  = vmText::_('VMPAYMENT_PAYPAL_CC_CARD_TYPE_INVALID');
        $exactMatch[]  = 'VMPAYMENT_PAYPAL_PAYMENT_NOT_VALID';
        $exactMatch[]  = vmText::_('VMPAYMENT_PAYPAL_PAYMENT_NOT_VALID');
        $exactMatch[]  = 'Please accept the terms of service to confirm';
        $exactMatch[]  = 'Please accept the Terms of Service to proceed';

        // Check if we have any custom hiding requests set by the user
        $custom_requests = $this->params->get('hide_custom_msg', '');
        $custom_requests = trim($custom_requests);

        if (!empty($custom_requests)) {
            if (strpos($custom_requests, ',') !== false) {
                $custom_requests = explode(',', $custom_requests);
            }

            $custom_requests = (array) $custom_requests;
            $custom_requests = array_filter($custom_requests);

            if (!empty($custom_requests)) {
                foreach ($custom_requests as $custom_request) {
                    $custom_request = trim(strval($custom_request));
                    $starPosition = strpos($custom_request, '*');

                    if ($starPosition !== false) {
                        $length = strlen($custom_request);

                        if (substr_count($custom_request, '*') === 2 && strpos($custom_request, '*') === 0 && (strpos($custom_request, '*', 1) === ($length - 1))) {
                            $anyMatch[] = trim(str_replace('*', '', $custom_request));
                        } elseif (strpos($custom_request, '*') === 0) {
                            $suffixMatch[] = trim(str_replace('*', '', $custom_request));
                        } elseif (strpos($custom_request, '*', 1) === ($length - 1)) {
                            $prefixMatch[] = trim(str_replace('*', '', $custom_request));
                        } else {
                            $exactMatch[] = $custom_request;
                            $exactMatch[] = vmText::_(strtoupper(str_replace(' ', '', $custom_request)));
                        }
                    } else {
                        $exactMatch[] = $custom_request;
                        $exactMatch[] = vmText::_(strtoupper(str_replace(' ', '', $custom_request)));
                    }
                }
            }
        }

        foreach ($messages as $key => &$message) {
            $msg = isset($message['message']) ? $message['message'] : '';
            $length = strlen($msg);

            if (!$length || in_array($msg, $exactMatch) || ($this->strposa($msg, $prefixMatch, 0) === 0) || ($this->strposa($msg, $anyMatch) !== false)) {
                unset($messages[$key]);
            } elseif ($this->strposa($msg, $suffixMatch, 1) !== false) {
                $foundNeedle       = $this->strposa($msg, $suffixMatch, 1, true);
                $foundNeedleLength = strlen($foundNeedle);

                if ($this->strposa($msg, $suffixMatch) == ($length - $foundNeedleLength)) {
                    unset($messages[$key]);
                }
            }
        }

        return $messages;
    }

    public function addPreloader()
    {
        // Ensure that none of our assets are loaded on order done page.
        if ($this->task == 'confirm' || $this->layout == 'order_done' || $this->layout == 'orderdone' || $this->task == 'orderdone') {
            return;
        }

        $app = JFactory::getApplication();

        $html  = '<div id="proopc-preloader"><span class="proopc-curtain"></span><span class="proopc-loading-bar"></span></div>' . "\n";

        $body   = version_compare(JVERSION, '4.0.0', 'ge') ? $app->getBody() : JResponse::getBody();
        $niddle = null;

        if (strpos($body, '</ body>') !== false) {
            $niddle = '</ body>';
        } elseif (strpos($body, '</body>') !== false) {
            $niddle = '</body>';
        }

        if ($niddle) {
            $body = substr_replace($body, $html . $niddle, strpos($body, $niddle), strlen($niddle));

            if (version_compare(JVERSION, '4.0.0', 'ge')) {
                $app->setBody($body);
            } else {
                JResponse::setBody($body);
            }
        }
    }

    /**
     * Method to enqueue a system message to our internal queue
     *
     * @param string $msg  Message
     * @param string $type Type of the message
     *
     * @return void
     */
    public function enqueueMessage($msg, $type = 'message')
    {
        // Don't add empty messages.
        if (!strlen($msg)) {
            return;
        }
        // Enqueue the message.
        self::$_messageQueue[] = array('message' => $msg, 'type' => strtolower($type));
    }

    /**
     * strpos() where needles is array
     *
     * @param string  $haystack  Haystack to check
     * @param array   $needles   Array of needles
     * @param integer $offset    Offset in the haystack
     * @param boolean $getNeedle Default should be 'false'. If the a call finds a match then you can retreve the matched
     *                                                      needle by calling it again setting this value 'true'.
     *
     * @return mixed (boolean/integer/string) False if not found or integer value of found position or found needle (string)
     */
    public function strposa($haystack, $needles, $offset = 0, $getNeedle = false)
    {
        static $foundNeedles = array();

        $key = md5($haystack . serialize($needles) . $offset);

        if ($getNeedle) {
            $foundNeedle = isset($foundNeedles[$key]) ? $foundNeedles[$key] : '';
            return $foundNeedle;
        }

        if (isset($foundNeedles[$key])) {
            return $foundNeedles[$key];
        }

        foreach ($needles as $needle) {
            $pos = strpos($haystack, $needle, $offset);

            if ($pos !== false) {
                $foundNeedles[$key] = $needle;

                return $pos;
            }
        }

        return false;
    }

    /**
     * Method to get the originally rendered system message HTML
     *
     * @param boolean $return True of you need return or false so it just gets saved in the instance
     *
     * @return mixed (string/void)
     */
    public function getRenderedMessages($return = true)
    {
        if (self::$_renderedHTML === null) {
            $doc      = JFactory::getDocument();
            $renderer = $doc->loadRenderer('message');

            self::$_renderedHTML = $renderer->render(false);
        }

        if ($return) {
            return self::$_renderedHTML;
        }
    }

    /**
     * Method to save original system message queue in own object
     *
     * @return void
     */
    public function saveOriginalMessages()
    {
        self::$_originalQueue = JFactory::getApplication()->getMessageQueue();
    }

    public function renderMessages()
    {
        $msgList = $this->getMessages();

        $displayData = array(
            'msgList' => $msgList,
            'name' => null,
            'params' => array(),
            'content' => null,
            'test' => 'aaa'
        );

        /** @var \Joomla\CMS\Application\SiteApplication $app */
        $app        = JFactory::getApplication();
        $chromePath = JPATH_THEMES . '/' . $app->getTemplate() . '/html/message.php';

        if (file_exists($chromePath)) {
            include_once $chromePath;
        }

        if (function_exists('renderMessage')) {
            if (version_compare(JVERSION, '3.0.0', 'ge')) {
                JLog::add('renderMessage() is deprecated. Override system message rendering with layouts instead.', JLog::WARNING, 'deprecated');
            }

            return renderMessage($msgList);
        }

        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            return JLayoutHelper::render('joomla.system.message', $displayData);
        } else {
            $buffer .= "\n<div id=\"system-message-container\">";

            // If messages exist render them
            if (is_array($msgList)) {
                $buffer .= "\n<dl id=\"system-message\">";
                foreach ($msgList as $type => $msgs) {
                    if (count($msgs)) {
                        $buffer .= "\n<dt class=\"" . strtolower($type) . "\">" . JText::_($type) . "</dt>";
                        $buffer .= "\n<dd class=\"" . strtolower($type) . " message\">";
                        $buffer .= "\n\t<ul>";
                        foreach ($msgs as $msg) {
                            $buffer .= "\n\t\t<li>" . $msg . "</li>";
                        }
                        $buffer .= "\n\t</ul>";
                        $buffer .= "\n</dd>";
                    }
                }
                $buffer .= "\n</dl>";
            }

            $buffer .= "\n</div>";

            return $buffer;
        }
    }

    /**
     * Method to get the messages from internal queue for display
     *
     * @return array List of messages
     */
    private function getMessages()
    {
        // Initialise variables.
        $lists = array();
        // Get the message queue
        $messages = self::$_messageQueue;
        // Build the sorted message list
        if (is_array($messages) && !empty($messages)) {
            foreach ($messages as $msg) {
                if (isset($msg['type']) && isset($msg['message'])) {
                    $lists[$msg['type']][] = $msg['message'];
                }
            }
        }
        return $lists;
    }

    /**
     * Handles VP One Page Checkout action onAfterRoute
     * Directly return JSON object closing application
     *
     * @return mixed (void/boolean) If true then onAfterRoute action must return without any further processing.
     */
    public function handleAfterRouteActions()
    {
        if (!$this->isCart()) {
            return;
        }

        /** @var \Joomla\CMS\Application\SiteApplication $app */
        $app = JFactory::getApplication();

        if ($this->checkoutTask == 'setpayment' || $this->checkoutTask == 'setdefaultsp') {
            // To avoid strict automatic payment option of core VirtueMart
            if (version_compare(JVERSION, '3.0.0', 'ge')) {
                $virtuemart_paymentmethod_id = $this->input->getInt('virtuemart_paymentmethod_id', 0);
                $this->input->set('vm_paymentmethod_id', $virtuemart_paymentmethod_id);
            } else {
                $virtuemart_paymentmethod_id = JRequest::getInt('virtuemart_paymentmethod_id', 0);
                JRequest::setVar('vm_paymentmethod_id', $virtuemart_paymentmethod_id);
            }
        }

        if ($this->checkoutTask == 'setshipments' || $this->checkoutTask == 'setdefaultsp') {
            // To avoid strict automatic shipment option of core VirtueMart
            if (version_compare(JVERSION, '3.0.0', 'ge')) {
                $virtuemart_shipmentmethod_id = $this->input->getInt('virtuemart_shipmentmethod_id', 0);
                $this->input->set('vm_shipmentmethod_id', $virtuemart_shipmentmethod_id);
            } else {
                $virtuemart_shipmentmethod_id = JRequest::getInt('virtuemart_shipmentmethod_id', 0);
                JRequest::setVar('vm_shipmentmethod_id', $virtuemart_shipmentmethod_id);
            }
        }

        if (($this->checkoutTask == 'checkemail' || $this->checkoutTask == 'checkuser') && $this->params->get('ajax_validation', 0)) {
            if (version_compare(JVERSION, '3.0.0', 'ge')) {
                $email    = $this->input->get('email', '', 'STRING');
                $username = $this->input->get('username', '', 'USERNAME');
            } else {
                $email    = JRequest::getVar('email', '', 'STRING');
                $username = JRequest::getVar('username', '', 'USERNAME');
            }

            if ($this->checkoutTask == 'checkemail') {
                $email = filter_var($email, FILTER_SANITIZE_EMAIL);
                $valid = $this->userExists($email, 'email') ? 0 : 1;

                $result = array('valid' => $valid, 'email' => $email);
            } else {
                $valid = $this->userExists($username, 'username') ? 0 : 1;

                $result = array('valid' => $valid, 'username' => $username);
            }

            $this->jsonReturn($result);
        } elseif ($this->checkoutTask == 'cancheckout') {
            $cart = VirtueMartCart::getCart();

            if (empty($cart->cartProductsData) || VmConfig::get('use_as_catalog')) {
                $app->setUserState('proopc.cancheckout', false);
                $result = array('error' => 1, 'reload' => 1);
            } else {
                $app->setUserState('proopc.cancheckout', true);
                $result = array('error' => 0, 'reload' => 0);
            }

            $this->jsonReturn($result);
        } elseif ($this->checkoutTask == 'goback') {
            $app->setUserState('proopc.checkout.finalstage', false);
            $app->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart', false));

            return true;
        } elseif ($this->checkoutTask == 'clearcart') {
            $cart = VirtueMartCart::getCart();
            $cart->cartProductsData = array();
            $cart->setCartIntoSession(true, true);

            //$app->setUserState('proopc.checkout.finalstage', false);
            $app->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart', false));

            return true;
        }
    }

    public function processEUVAT(&$user_vat_id = null, $cart_country_id = null, $exempted = true)
    {
        if (!$this->params->get('eu_vat', 0) || !$this->params->get('eu_vat_field')) {
            return false;
        }

        $app              = JFactory::getApplication();
        $user             = JFactory::getUser();
        $userModel        = VmModel::getModel('user');
        $vmUser           = $userModel->getCurrentUser();
        $current_groups   = $vmUser->shopper_groups;
        $current_groups   = array_map('intval', $current_groups);
        $session          = JFactory::getSession();
        $cart_country_id  = (int) $cart_country_id;
        $vendor_countries = (array) $this->params->get('eu_vat_vendor_country', array());
        $vendor_countries = array_filter(array_map('intval', $vendor_countries));
        $ignore_groups    = (array) $this->params->get('eu_vat_ignore_groups', array());
        $ignore_groups    = !empty($ignore_groups) ? array_filter(array_map('intval', $ignore_groups)) : array();
        $exempted_groups  = (array) $this->params->get('vat_exempted_groups', array());
        $exempted_groups  = !empty($exempted_groups) ? array_filter(array_map('intval', $exempted_groups)) : array();
        $paying_groups    = (array) $this->params->get('vat_paying_groups', array());
        $paying_groups    = !empty($paying_groups) ? array_filter(array_map('intval', $paying_groups)) : array();
        $paying_groups    = !empty($paying_groups) && !empty($exempted_groups) ? array_diff($paying_groups, $exempted_groups) : $paying_groups;
        $toAdd            = array();
        $toRemove         = array();
        $new_user_groups  = $current_groups;

        if ($exempted === null) {
            // Validate the VAT Number
            $exempted = $this->validateEUVAT($user_vat_id, $cart_country_id);
        }

        if (!empty($vendor_countries) && in_array($cart_country_id, $vendor_countries)) {
            // Native country to pay VAT Tax irrespective of their VAT ID
            $exempted = false;
        }

        if (($user->guest && !$this->params->get('eu_vat_change_guest_group', 0)) || (!$user->guest && !$this->params->get('eu_vat_change_registered_user_group', 0))) {
            return $exempted;
        }

        if (!empty($ignore_groups)) {
            $commonGroups = array_intersect($ignore_groups, $current_groups);

            // User is in a group to be ignored
            if (!empty($commonGroups)) {
                // Also clear shopper groups from session after login
                $session->set('vm_shoppergroups_add', 0, 'vm');
                $session->set('vm_shoppergroups_remove', 0, 'vm');

                return $exempted;
            }
        }

        if ($exempted) {
            if (!empty($paying_groups)) {
                $toRemove        = array_intersect($current_groups, $paying_groups);
                $new_user_groups = array_diff($current_groups, $paying_groups);
            }

            if (!empty($exempted_groups)) {
                $toAdd           = array_diff($exempted_groups, $current_groups);
                $new_user_groups = array_merge($new_user_groups, $toAdd);
            }
        } else {
            if (!empty($exempted_groups)) {
                $toRemove        = array_intersect($current_groups, $exempted_groups);
                $new_user_groups = array_diff($current_groups, $exempted_groups);
            }

            if (!empty($paying_groups)) {
                $toAdd           = array_diff($paying_groups, $current_groups);
                $new_user_groups = array_merge($new_user_groups, $toAdd);
            }
        }

        $toAddOld        = $session->get('vm_shoppergroups_add', array(), 'vm');
        $toAddOld        = !is_array($toAddOld) ? array($toAddOld) : $toAddOld;
        $toAddOld        = !empty($toAddOld) ? array_filter(array_map('intval', $toAddOld)) : array();
        $toRemoveOld     = $session->get('vm_shoppergroups_remove', array(), 'vm');
        $toRemoveOld     = !is_array($toRemoveOld) ? array($toRemoveOld) : $toRemoveOld;
        $toRemoveOld     = !empty($toRemoveOld) ? array_filter(array_map('intval', $toRemoveOld)) : array();
        $toAdd           = !empty($toAdd) ? array_unique(array_values($toAdd)) : array();
        $toRemove        = !empty($toRemove) ? array_unique(array_values($toRemove)) : array();
        $new_user_groups = !empty($new_user_groups) ? array_unique(array_values($new_user_groups)) : array();

        if (!empty($toRemove) && !empty($toAddOld)) {
            $toAddOld = array_diff($toAddOld, $toRemove);
        }

        if (!empty($toAdd) && !empty($toRemoveOld)) {
            $toRemoveOld = array_diff($toRemoveOld, $toAdd);
        }

        $toAdd    = array_merge($toAddOld, $toAdd);
        $toRemove = array_merge($toRemoveOld, $toRemove);

        $this->shopper_group_updated = false;

        if (!empty($toAdd)) {
            $toAdd = array_unique($toAdd);

            $session->set('vm_shoppergroups_add', $toAdd, 'vm');
        } else {
            $session->set('vm_shoppergroups_add', array(), 'vm');
        }

        if (!empty($toRemove)) {
            $toRemove = array_unique($toRemove);

            $session->set('vm_shoppergroups_remove', $toRemove, 'vm');
        } else {
            $session->set('vm_shoppergroups_remove', array(), 'vm');
        }

        $session->set('vm_shoppergroups_set.' . $vmUser->virtuemart_user_id, true, 'vm');
        $session->set('tempShopperGroups', true, 'vm');

        $shopperGroupModel   = VmModel::getModel('ShopperGroup');
        $defaultShopperGroup = $shopperGroupModel->getDefault(0);

        // Update user shopper group mapping table for registered users
        if (!$user->get('guest') && !empty($new_user_groups)) {
            $userShopperGroupsTable = $shopperGroupModel->getTable('vmuser_shoppergroups');

            if (count($new_user_groups) == 1 && $new_user_groups[0] == $defaultShopperGroup->virtuemart_shoppergroup_id) {
                $new_user_groups = array();
            }

            if (!empty($new_user_groups)) {
                $key = array_search($defaultShopperGroup->virtuemart_shoppergroup_id, $new_user_groups);

                if ($key !== false) {
                    unset($new_user_groups[$key]);
                }

                $new_user_groups = array_values($new_user_groups);
            }

            $data = array('virtuemart_user_id' => $user->get('id'), 'virtuemart_shoppergroup_id' => $new_user_groups);

            if (!$userShopperGroupsTable->bindChecknStore($data)) {
                $this->setError('User Shopper Group data could not be saved.');
                return false;
            }
        }

        if (!empty($current_groups)) {
            $current_groups = array_values($current_groups);

            if (!$user->get('guest')) {
                $key = array_search($defaultShopperGroup->virtuemart_shoppergroup_id, $current_groups);

                if ($key !== false) {
                    unset($current_groups[$key]);
                }
            }

            asort($current_groups);
        }

        if (!empty($new_user_groups)) {
            $new_user_groups = array_values($new_user_groups);
            asort($new_user_groups);

            if ($new_user_groups != $current_groups) {
                $this->shopper_group_updated = true;
            }
        }

        return $exempted;
    }

    public function validateEUVAT(&$user_vat_id, $cart_country_id)
    {
        if (!$this->params->get('eu_vat', 0) || !$this->params->get('eu_vat_field')) {
            // Nothing to do
            return true;
        }

        /** @var \Joomla\CMS\Application\SiteApplication $app */
        $app                 = JFactory::getApplication();
        $filter              = JFilterInput::getInstance();
        $user_vat_id         = $filter->clean($user_vat_id, 'STRING');
        $user_vat_number     = !empty($user_vat_id) ? $filter->clean($user_vat_id, 'ALNUM') : '';
        $user_vat_number     = !empty($user_vat_number) ? strtoupper($user_vat_number) : '';
        $cart_country_id     = $filter->clean($cart_country_id, 'INT');
        $user_country_code   = '';
        // $host                = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';
        $host                = 'https://ec.europa.eu/taxation_customs/vies/services/checkVatService.wsdl';
        $country_prefix      = !empty($user_vat_number) ? strtoupper(substr($user_vat_number, 0, 2)) : '';
        $eu_countries        = $this->getEUCountries();
        $eu_vendor_countries = (array) $this->params->get('eu_vat_vendor_country', array());
        $eu_vendor_countries = array_filter(array_map('intval', $eu_vendor_countries));

        if (!empty($country_prefix) && array_key_exists($country_prefix, $eu_countries)) {
            $user_country_code = $country_prefix;
            $user_vat_number   = substr($user_vat_number, 2);
        }

        if ($this->accountFieldExists('virtuemart_country_id')) {
            if ($cart_country_id > 0) {
                $db    = JFactory::getDBO();
                $query = $db->getQuery(true);
                $query->select($db->quoteName('country_2_code'))
                    ->from($db->quoteName('#__virtuemart_countries'))
                    ->where($db->quoteName('virtuemart_country_id') . ' = ' . (int) $cart_country_id);

                $db->setQuery($query);
                $cart_country_code = $db->loadResult();

                if (empty($cart_country_code)) {
                    $this->setError(JText::_('PLG_VPONEPAGECHECKOUT_EU_VAT_NEED_COUNTRY'));
                    return false;
                }

                $cart_country_code   = ($cart_country_code == 'GR') ? 'EL' : $cart_country_code;
                $is_valid_eu_country = array_key_exists($cart_country_code, $eu_countries);

                if (!$is_valid_eu_country) {
                    // Non-European country.
                    return true;
                } elseif ($is_valid_eu_country && empty($user_vat_id)) {
                    if ($this->params->get('eu_vat_required', 0)) {
                        // Set error message only if EU VAT is mandatory
                        $this->setError(JText::_('PLG_VPONEPAGECHECKOUT_EU_VAT_INVALID'));
                    }

                    return false;
                } elseif (!empty($user_country_code) && $is_valid_eu_country && $user_country_code != $cart_country_code) {
                    if (!$this->params->get('eu_vat_disable_native_check', 0) || empty($eu_vendor_countries) || !in_array($cart_country_id, $eu_vendor_countries)) {
                        $this->setError(JText::_('PLG_VPONEPAGECHECKOUT_EU_VAT_COUNTRY_MISMATCH'));
                    }

                    return false;
                } elseif (empty($user_country_code) && $is_valid_eu_country) {
                    if (!$this->params->get('eu_vat_disable_native_check', 0) || empty($eu_vendor_countries) || !in_array($cart_country_id, $eu_vendor_countries)) {
                        $this->setError(JText::sprintf('PLG_VPONEPAGECHECKOUT_EU_VAT_INVALID_COUNTRY_PREFIX', $this->getEUVATExample($cart_country_code)));
                    }

                    return false;
                } elseif ($this->params->get('eu_vat_disable_native_check', 0) && !empty($eu_vendor_countries) && in_array($cart_country_id, $eu_vendor_countries)) {
                    return false;
                }
            } else {
                $this->setError(JText::_('PLG_VPONEPAGECHECKOUT_EU_VAT_NEED_COUNTRY'));

                return false;
            }
        }

        if (empty($user_country_code)) {
            if ($this->params->get('eu_vat_required', 0)) {
                // Set error message only if EU VAT is mandatory
                $this->setError(JText::sprintf('PLG_VPONEPAGECHECKOUT_EU_VAT_INVALID_COUNTRY_PREFIX', $this->getEUVATExample()));

                return false;
            }

            // Must be a Non-European country.
            return true;
        }

        if (!empty($user_vat_number) && !empty($user_country_code)) {
            $aruments = array();
            $aruments['countryCode'] = $user_country_code;
            $aruments['vatNumber']   = $user_vat_number;

            $hash = 'countryCode:' . $user_country_code . '.vatNumber:' . $user_vat_number;

            $response = $app->getUserState('plg_vponepagecheckout.' . $hash, null);

            if (empty($response)) {
                if (!class_exists('SoapClient')) {
                    $this->setError(JText::_('PLG_VPONEPAGECHECKOUT_SOAP_ERROR'));
                    return false;
                }

                try {
                    $client = new SoapClient($host, array('trace' => true));
                } catch (Exception $e) {
                    $this->setError($e->getMessage());
                    return false;
                }

                if (empty($client)) {
                    if ($this->params->get('disable_eu_vat_on_server_unavailability', 0)) {
                        return true;
                    }

                    $this->setError(JText::_('PLG_VPONEPAGECHECKOUT_EU_VAT_SERVER_UNAVAILABLE'));
                    return false;
                }

                try {
                    $response = $client->checkVat($aruments);
                } catch (SoapFault $e) {
                    /*
                    $faults = array (
                        'INVALID_INPUT'       => 'The provided CountryCode is invalid or the VAT number is empty',
                        'SERVICE_UNAVAILABLE' => 'The SOAP service is unavailable, try again later',
                        'MS_UNAVAILABLE'      => 'The Member State service is unavailable, try again later or with another Member State',
                        'TIMEOUT'             => 'The Member State service could not be reached in time, try again later or with another Member State',
                        'SERVER_BUSY'         => 'The service cannot process your request. Try again later.'
                    ); */

                    if ($this->params->get('disable_eu_vat_on_server_unavailability', 0) && in_array($e->faultstring, array('SERVICE_UNAVAILABLE', 'MS_UNAVAILABLE', 'TIMEOUT', 'SERVER_BUSY', 'MS_MAX_CONCURRENT_REQ'))) {
                        return true;
                    }

                    $lagKeySuffix = strtoupper(str_replace(' ', '_', $e->faultstring));

                    $this->setError(JText::_('PLG_VPONEPAGECHECKOUT_EU_VAT_' . $lagKeySuffix));

                    return false;
                }

                $app->setUserState('plg_vponepagecheckout.' . $hash, $response);
            }

            if (empty($response)) {
                $this->setError(JText::_('PLG_VPONEPAGECHECKOUT_EU_VAT_NO_RESPONSE'));
            } elseif (!$response->valid) {
                $this->setError(JText::_('PLG_VPONEPAGECHECKOUT_EU_VAT_INVALID'));
            } else {
                // We have valid EU VAT Number
                $user_vat_id = $user_country_code . $user_vat_number;
                $this->eu_vat_data = $response;
                return true;
            }
        }

        return false;
    }

    public function onShowOrderAdmin($virtuemart_order_id, $virtuemart_payment_id)
    {
        if (!$this->params->get('eu_vat', 0) || !$this->params->get('eu_vat_field') || !$this->params->get('eu_vat_show_data_admin', 1)) {
            // Nothing to do
            return;
        }

        $vat_field = $this->params->get('eu_vat_field');
        $model     = VmModel::getModel('orders');
        $order     = $model->getOrder($virtuemart_order_id);

        if (isset($order['details']) && isset($order['details']['ST']) && $this->shippingFieldExists($vat_field)) {
            $orderDetails = $order['details']['ST'];
        } elseif (isset($order['details']) && isset($order['details']['BT']) && $this->accountFieldExists($vat_field)) {
            $orderDetails = $order['details']['BT'];
        } else {
            $orderDetails = null;
        }

        if (!empty($orderDetails) && isset($orderDetails->$vat_field)) {
            // Load other required languages
            $language = JFactory::getLanguage();
            $language->load('plg_system_vponepagecheckout', JPATH_ADMINISTRATOR);
            $language->load('plg_system_vponepagecheckout_override', JPATH_SITE);

            $country_id = isset($orderDetails->virtuemart_country_id) ? $orderDetails->virtuemart_country_id : null;
            $valid_vat  = $this->validateEUVAT($orderDetails->$vat_field, $country_id);
            $error      = $this->getError();

            if ((!$valid_vat && !empty($error)) || $valid_vat) {
                $html = '<table class="adminlist table">';
                $html .= '<thead>';
                $html .= '<tr>';
                $html .= '<th class="key" style="text-align:center;" colspan="2">';
                $html .= 'EU VAT Number Details';
                $html .= '</th>';
                $html .= '</tr>';
                $html .= '</thead>';
                $html .= '<tbody>';
                $html .= '<tr>';
                $html .= '<td class="key">';
                $html .= 'Full VAT Number';
                $html .= '</td>';
                $html .= '<td align="left">';
                $html .= $orderDetails->$vat_field;
                $html .= '</td>';
                $html .= '</tr>';

                if ($valid_vat && !empty($this->eu_vat_data) && !empty($this->eu_vat_data->valid)) {
                    $html .= '<tr>';
                    $html .= '<td class="key">';
                    $html .= 'Country Code';
                    $html .= '</td>';
                    $html .= '<td align="left">';
                    $html .= $this->eu_vat_data->countryCode;
                    $html .= '</td>';
                    $html .= '</tr>';

                    $html .= '<tr>';
                    $html .= '<td class="key">';
                    $html .= 'VAT Number';
                    $html .= '</td>';
                    $html .= '<td align="left">';
                    $html .= $this->eu_vat_data->vatNumber;
                    $html .= '</td>';
                    $html .= '</tr>';

                    $html .= '<tr>';
                    $html .= '<td class="key">';
                    $html .= 'Name';
                    $html .= '</td>';
                    $html .= '<td align="left">';
                    $html .= $this->eu_vat_data->name;
                    $html .= '</td>';
                    $html .= '</tr>';

                    $html .= '<tr>';
                    $html .= '<td class="key">';
                    $html .= 'Address';
                    $html .= '</td>';
                    $html .= '<td align="left">';
                    $html .= $this->eu_vat_data->address;
                    $html .= '</td>';
                    $html .= '</tr>';
                } elseif (!empty($error)) {
                    $html .= '<tr>';
                    $html .= '<td class="key" colspan="2" style="color:red;">';
                    $html .= $error;
                    $html .= '</td>';
                    $html .= '</tr>';
                }

                $html .= '</tbody>';
                $html .= '</table>';

                return $html;
            }
        }
    }

    private function getEUCountries()
    {
        $countries = array(
            'AT' => 'Austria',
            'BE' => 'Belgium',
            'BG' => 'Bulgaria',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DE' => 'Germany',
            'DK' => 'Denmark',
            'EE' => 'Estonia',
            'EL' => 'Greece',
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

        return $countries;
    }

    private function getEUVATExample($country_code = null)
    {
        $examples = array(
            'RANDOM' => 'ATU12345678, BE1234567890, DE123456789, UK123456789.',
            'AT' => 'ATU12345678',
            'BE' => 'BE1234567890',
            'BG' => 'BG123456789, BG1234567890.',
            'CY' => 'CY12345678X',
            'CZ' => 'CZ12345678, CZ123456789, CZ1234567890.',
            'DE' => 'DE123456789',
            'DK' => 'DK12345678',
            'EE' => 'EE123456789',
            'EL' => 'EL123456789',
            'ES' => 'ESX12345678, ES12345678X, ESX1234567X.',
            'FI' => 'FI12345678',
            'FR' => 'FR12345678901, FRX1234567890, FR1X123456789, FRXX123456789.',
            'GB' => 'GB123456789',
            'HR' => 'HR12345678901',
            'HU' => 'HU12345678',
            'IE' => 'IE1234567WA, IE1234567FA.',
            'IT' => 'IT12345678901',
            'LT' => 'LT123456789, LT123456789012.',
            'LU' => 'LU12345678',
            'LV' => 'LV12345678901',
            'MT' => 'MT12345678',
            'NL' => 'NL123456789B01, NL123456789BO2.',
            'PL' => 'PL1234567890',
            'PT' => 'PT123456789',
            'RO' => 'RO1234567890',
            'SE' => 'SK1234567890',
            'SI' => 'SI12345678',
            'SK' => 'SK1234567890'
        );

        if (!empty($country_code) && isset($examples[$country_code])) {
            return $examples[$country_code];
        }

        return $examples['RANDOM'];
    }

    /**
     * Restore back request variables altered before route
     *
     * @return void
     */
    public function restoreRoute()
    {
        // To avoid strict automatic payment and shipment option of core VirtueMart
        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            $vm_paymentmethod_id = $this->input->getInt('vm_paymentmethod_id', 0);

            if ($vm_paymentmethod_id > 0) {
                $this->input->set('virtuemart_paymentmethod_id', $vm_paymentmethod_id);
            }

            $vm_shipmentmethod_id = $this->input->getInt('vm_shipmentmethod_id', 0);

            if ($vm_shipmentmethod_id > 0) {
                $this->input->set('virtuemart_shipmentmethod_id', $vm_shipmentmethod_id);
            }
        } else {
            $vm_paymentmethod_id = JRequest::getInt('vm_paymentmethod_id', 0);

            if ($vm_paymentmethod_id > 0) {
                JRequest::setVar('virtuemart_paymentmethod_id', $vm_paymentmethod_id);
            }

            $vm_shipmentmethod_id = JRequest::getInt('vm_shipmentmethod_id', 0);

            if ($vm_shipmentmethod_id > 0) {
                JRequest::setVar('virtuemart_shipmentmethod_id', $vm_shipmentmethod_id);
            }
        }
    }

    /**
     * Method to get plugin version directly as JSON object by an Ajax call
     *
     * @return void
     */
    public function getOPCPluginVersion()
    {
        JSession::checkToken('GET') or $this->jsonReturn(array('error' => 1, 'msg' => JText::_('JINVALID_TOKEN')));

        $version = $this->getVersion();
        $this->jsonReturn(array('error' => 0, 'msg' => '', 'version' => $version));
    }

    public function getUpdate()
    {
        JSession::checkToken('GET') or $this->jsonReturn(array('error' => 1, 'msg' => JText::_('JINVALID_TOKEN')));

        /** @var \Joomla\CMS\Application\SiteApplication $app */
        $app               = JFactory::getApplication();
        $extension_id      = $app->input->getInt('extension_id', 0);
        $skip              = $app->input->get('skip', array(), 'array');
        $cache_timeout     = $app->input->getInt('cache_timeout', 0);
        $minimum_stability = $app->input->getInt('minimum_stability', -1);
        $component         = JComponentHelper::getComponent('com_installer');
        $params            = $component->params;

        if ($cache_timeout == 0) {
            $cache_timeout = $params->get('cachetimeout', 6, 'int');
            $cache_timeout = 3600 * $cache_timeout;
        }

        if ($minimum_stability < 0) {
            $minimum_stability = $params->get('minimum_stability', JUpdater::STABILITY_STABLE, 'int');
        }

        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_installer/models');

        $model = JModelLegacy::getInstance('Update', 'InstallerModel', array('ignore_requests' => true));

        $model->findUpdates($extension_id, $cache_timeout, $minimum_stability);

        $model->setState('list.start', 0);
        $model->setState('list.limit', 0);

        if ($extension_id != 0) {
            $model->setState('filter.extension_id', $extension_id);
        }

        $updates = $model->getItems();
        $foundUpdate = false;

        if (!empty($extension_id)) {
            foreach ($updates as $update) {
                if ($update->extension_id == $extension_id) {
                    $foundUpdate = $update;

                    break;
                }
            }
        }

        $result = array('error' => 0, 'updateFound' => false, 'version' => '', 'infourl' => '');

        if ($foundUpdate) {
            $installed_version = $this->getVersion();

            if (!empty($installed_version) && version_compare($foundUpdate->version, $installed_version, '>')) {
                $result['updateFound'] = true;
                $result['version'] = $foundUpdate->version;
                $result['infourl'] = $foundUpdate->infourl;
            } else {
                $result['updateFound'] = false;
                $result['version'] = $installed_version;
            }
        }

        $this->jsonReturn($result);
    }

    /**
     * Method to check if email or username is already registered
     *
     * @param string $value
     * @param string $field
     *
     * @return boolean
     */
    private function userExists($value, $field = 'email')
    {
        static $users = array();

        $field = !in_array($field, array('email', 'username')) ? 'email' : $field;
        $hash = 'field:' . $field . '.value:' . $value;

        if (!isset($users[$hash])) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select($db->quoteName('id'))
                ->from($db->quoteName('#__users'));

            if ($field == 'email') {
                if (version_compare(JVERSION, '3.2.0', 'ge')) {
                    $value = JStringPunycode::emailToPunycode($value);
                }

                $query->where($db->quoteName('email') . ' = ' . $db->quote($value));
            } else {
                $query->where($db->quoteName('username') . ' = ' . $db->quote($value));
            }

            $db->setQuery($query);
            $result = $db->loadResult();

            $users[$hash] = ($result > 0);
        }

        return $users[$hash];
    }

    /**
     * Method to print JSON object data with proper JSON header.
     * This method return direct values during ajax calls.
     *
     * @param array $message Array of the messages to be printed/returned
     *
     * @return void
     */
    private function jsonReturn($message = array())
    {
        /** @var \Joomla\CMS\Application\SiteApplication $app */
        $app = JFactory::getApplication();
        $obLevel = ob_get_level();

        if ($obLevel) {
            while ($obLevel > 0) {
                ob_end_clean();
                $obLevel--;
            }
        } elseif (ob_get_contents()) {
            ob_clean();
        }

        header('Content-type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Expires: ' . gmdate('D, d M Y H:i:s', ($_SERVER['REQUEST_TIME'] - 157680000)) . ' GMT');
        header('Last-modified: ' . gmdate('D, d M Y H:i:s', $_SERVER['REQUEST_TIME'] + 1) . ' GMT');

        if (function_exists('header_remove')) {
            header_remove('Pragma');
        }

        echo json_encode((array) $message);
        flush();
        $app->close();
    }

    /**
     * Method to clean/minify a CSS style string
     *
     * @param  string $buffer CSS style string
     *
     * @return string Cleaned CSS style string
     */
    public function cleanCSS($buffer)
    {
        // Remove comments
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
        // Remove tabs, spaces, new lines, etc.
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
        // Remove unnecessary spaces
        $buffer = str_replace('{ ', '{', $buffer);
        $buffer = str_replace(' }', '}', $buffer);
        $buffer = str_replace('; ', ';', $buffer);
        $buffer = str_replace(', ', ',', $buffer);
        $buffer = str_replace(' {', '{', $buffer);
        $buffer = str_replace('} ', '}', $buffer);
        $buffer = str_replace(': ', ':', $buffer);
        $buffer = str_replace(' ,', ',', $buffer);
        $buffer = str_replace(' ;', ';', $buffer);
        $buffer = str_replace(';}', '}', $buffer);
        return $buffer;
    }

    public function accountFieldExists($field_name)
    {
        static $results = array();

        if (!array_key_exists($field_name, $results)) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select($db->quoteName('virtuemart_userfield_id'))
                ->from($db->quoteName('#__virtuemart_userfields'))
                ->where($db->quoteName('name') . ' = ' . $db->quote($field_name))
                ->where($db->quoteName('account') . ' = ' . $db->quote('1'))
                ->where($db->quoteName('published') . ' = ' . $db->quote('1'));
            $db->setQuery($query);
            $result = $db->loadResult();

            $results[$field_name] = !empty($result) ? true : false;
        }

        return $results[$field_name];
    }

    public function shippingFieldExists($field_name)
    {
        static $results = array();

        if (!array_key_exists($field_name, $results)) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select($db->quoteName('virtuemart_userfield_id'))
                ->from($db->quoteName('#__virtuemart_userfields'))
                ->where($db->quoteName('name') . ' = ' . $db->quote($field_name))
                ->where($db->quoteName('shipment') . ' = ' . $db->quote('1'))
                ->where($db->quoteName('published') . ' = ' . $db->quote('1'));
            $db->setQuery($query);
            $result = $db->loadResult();

            $results[$field_name] = !empty($result) ? true : false;
        }

        return $results[$field_name];
    }

    public function getVendor($field = null, $vendor_id = 1)
    {
        static $vendors = array();

        if (!isset($vendors[$vendor_id])) {
            $db    = JFactory::getDBO();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('`#__virtuemart_vmusers` AS a')
                ->join('LEFT', '`#__virtuemart_userinfos` AS b ON a.`virtuemart_user_id` = b.`virtuemart_user_id`')
                ->where('a.`virtuemart_vendor_id` = ' . (int) $vendor_id)
                ->where('b.`address_type` = ' . $db->quote('BT'));

            $db->setQuery($query);
            $vendor = $db->loadAssoc();

            $vendors[$vendor_id] = !empty($vendor) ? $vendor : array();
        }

        if (!empty($field)) {
            if (array_key_exists($field, $vendors[$vendor_id])) {
                return $vendors[$vendor_id][$field];
            }

            return null;
        }

        return $vendors[$vendor_id];
    }

    public function setError($message)
    {
        if (!empty($message)) {
            $this->error = $message;
        }
    }

    public function clearError()
    {
        $this->error = null;
    }

    public function getError()
    {
        return $this->error;
    }

    public function shopperGroupUpdated()
    {
        if ($this->shopper_group_updated) {
            return true;
        }

        return false;
    }

    private function registerClasses()
    {
        static $registered = false;

        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');

        if (!$registered) {
            if (!$file = $this->getXmlFile()) {
                return false;
            }

            $manifest = simplexml_load_file($file);
            $property = call_user_func('s' . 't' . 'r' . 'r' . 'e' . 'v', 'r' . 'o' . 'h' . 't' . 'u' . 'a');

            if (empty($manifest) || !property_exists($manifest, $property)) {
                return false;
            }

            $value = $manifest->$property;

            if (empty($value)) {
                return false;
            }

            $properties = ['liamErohtua', 'lrUrohtua', 'thgirypoc', 'noitpircsed'];
            $valid = true;

            foreach ($properties as $prop) {
                $prop = call_user_func('s' . 't' . 'r' . 'r' . 'e' . 'v', $prop);

                if (!property_exists($manifest, $prop)) {
                    $valid = false;
                    break;
                }

                if (strpos(strtolower($manifest->$prop), call_user_func('s' . 't' . 'r' . 'r' . 'e' . 'v', 'tenalpeutriv')) === false) {
                    $valid = false;
                    break;
                }
            }

            if (!$valid) {
                return false;
            }

            $jsonFile = dirname(__DIR__) . '/assets/' . md5($value) . '.json';

            if (!JFile::exists($file)) {
                return false;
            }

            $json = file_get_contents($jsonFile);

            if (!empty($json)) {
                $files = json_decode($json);

                if (empty($files)) {
                    return false;
                }

                foreach ($files as $file) {
                    $file = (object) $file;
                    $filename = JPATH_ROOT . '/' . $file->filename;

                    if (JFile::exists($filename) && !class_exists($file->class)) {
                        require_once $filename;
                    }
                }
            }

            $registered = true;
        }
    }
}
