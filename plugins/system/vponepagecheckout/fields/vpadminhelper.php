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

jimport('joomla.form.formfield');
// phpcs:enable PSR1.Files.SideEffects

class JFormFieldVPAdminHelper extends JFormField
{
    public $type = 'VPAdminHelper';

    protected function getInput()
    {
        /** @var \Joomla\CMS\Application\SiteApplication $app */
        $app = JFactory::getApplication();

        JLoader::register('VmConfig', JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php');

        if (class_exists('VmConfig')) {
            VmConfig::loadConfig();
        }

        if (!defined('VM_VERSION') || !class_exists('VmConfig')) {
            $app->enqueueMessage('It appears VirtueMart Component is not installed. VP One Page Checkout plugin is an extension of VirtueMart Component.', 'error');
            return;
        }

        if (VM_VERSION < 3) {
            $app->enqueueMessage('This package of VP One Page Checkout plugin is compatible to VirtueMart 3 and above. You can get VirtueMart ' . VM_VERSION .
                                 ' compatible package of the plugin from http://www.virtueplanet.com', 'error');
        }

        // Check plugin status
        $xml = $this->form->getXml();

        if ($xml instanceof SimpleXMLElement && !empty($xml->config)) {
            $state = 1;

            foreach ($xml->config->children() as $fields) {
                if ($fields->attributes()->name == 'params' && isset($fields->fieldset)) {
                    // Getting the fieldset tags
                    $fieldsets = $fields->fieldset;

                    // Iterating through the fieldsets:
                    foreach ($fieldsets as $fieldset) {
                        if (!count($fieldset->children())) {
                            // Either the tag does not exist or has no children therefore we return zero files processed.
                            continue;
                        }

                        // Iterating through the fields and collecting the name/default values:
                        foreach ($fieldset as $field) {
                            $default = (string) $field->attributes()->default;

                            if ($field->attributes()->name == 'download_key') {
                                if ($field->attributes()->type != 'vpdownloadkey' || ($this->updateEnabled() && $field->attributes()->required != 'true') || !empty($default)) {
                                    $state--;
                                }
                            } elseif ($field->attributes()->name == 'adminhelper') {
                                if ($field->attributes()->validate != 'vpsystem' || !empty($default)) {
                                    $state--;
                                }
                            }
                        }
                    }
                }
            }

            if ($state < 1) {
                $app->enqueueMessage(strrev('.ti llatsnier dna nigulp fo ypoc hserf a daolnwoD .etis ruoy ni ylreporp dellatsni ton era selif nigulp ehT') . ' <a href="https://www.virtueplanet.com/downloads/one-page-checkout/" target="_blank">' . strrev('ereh morf daolnwoD') . '</a>.', 'error');
                $app->redirect(JRoute::_('index.php?option=com_plugins&view=plugins', false));

                return false;
            }
        }

        $doc  = JFactory::getDocument();
        $app  = JFactory::getApplication();
        $root = JUri::root(true);
        $extension_id = $app->input->getInt('extension_id', 0);

        if (version_compare(JVERSION, '4.0.0', 'ge')) {
            $staticJS  = 'plugins/system/vponepagecheckout/assets/admin/js/j4/admin.js';
            $staticCSS = 'plugins/system/vponepagecheckout/assets/admin/css/j4/admin.css';
        } elseif (version_compare(JVERSION, '3.0.0', 'ge')) {
            $staticJS  = 'plugins/system/vponepagecheckout/assets/admin/js/j3/admin.js';
            $staticCSS = 'plugins/system/vponepagecheckout/assets/admin/css/j3/admin.css';
        } else {
            $staticJS = array();
            $staticJS[] = 'plugins/system/vponepagecheckout/assets/admin/js/jquery.min.js';
            $staticJS[] = 'plugins/system/vponepagecheckout/assets/admin/js/jquery-noconflict.js';
            $staticJS[] = 'plugins/system/vponepagecheckout/assets/admin/js/jquery-migrate.min.js';
            $staticJS[] = 'plugins/system/vponepagecheckout/assets/admin/js/j2.5/admin.js';

            $staticCSS = 'plugins/system/vponepagecheckout/assets/admin/css/j2.5/admin.css';
        }

        $scripts = !empty($this->element['scripts']) ? $this->element['scripts'] : $staticJS;
        $styleSheets = !empty($this->element['styleSheets']) ? $this->element['styleSheets'] : $staticCSS;

        if (!empty($scripts)) {
            if (is_string($scripts)) {
                if (strpos($scripts, ',') !== false) {
                    $scripts = explode(',', $scripts);
                } else {
                    $scripts = (array) $scripts;
                }
            }

            foreach ($scripts as $script) {
                $path = JPath::clean(JPATH_ROOT . '/' . $script);

                if (file_exists($path)) {
                    $version = strpos($script, 'j2.5') || strpos($script, 'j3') ? '?ver=7.7' : '';
                    $doc->addScript($root . '/' . trim($script) . $version);
                }
            }
        }

        if (!empty($styleSheets)) {
            if (is_string($styleSheets)) {
                if (strpos($styleSheets, ',') !== false) {
                    $styleSheets = explode(',', $styleSheets);
                } else {
                    $styleSheets = (array) $styleSheets;
                }
            }

            foreach ($styleSheets as $styleSheet) {
                $path = JPath::clean(JPATH_ROOT . '/' . $styleSheet);

                if (file_exists($path)) {
                    $version = strpos($styleSheet, 'j2.5') || strpos($styleSheet, 'j3') ? '?ver=7.7' : '';
                    $doc->addStyleSheet($root . '/' . trim($styleSheet) . $version);
                }
            }
        }

        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            $js = "
            jQuery(document).ready(function($) {
                var elementGroup = $('#general input#jform_element:text').parents('.control-group');
                var base = '" . JUri::base(true) . "';
                if (elementGroup.length) {
                    var versionGroup = elementGroup.clone();
                    versionGroup.find('label').attr('id', 'jform_version-lbl').attr('for', 'jform_version').text('Plugin Version').removeAttr('data-original-title').attr('title', '<strong>Plugin Version</strong><br/>VP One Page Checkout plugin version installed in your site.');
                    versionGroup.find('input:text').attr('id', 'jform_version').attr('name', 'jform[version]').attr('disabled', true).val('fetching...');
                    elementGroup.after(versionGroup);
                    var form = elementGroup.parents('form');
                    versionField = $('#jform_version');
                    versionGroup = versionField.parents('.control-group');
                    $('.hasTooltip', versionGroup).tooltip({'html': true,'container': 'body'});
                    $.ajax({
                        type: 'GET',
                        url: base + '/index.php?option=com_ajax&plugin=vponepagecheckout&format=json',
                        data: {'method' : 'getversion', '" . JSession::getFormToken() . "' : 1},
                        cache: false,
                        success: function(e) {
                            if (($.type(e) === 'string' && e.indexOf('<\/head>') != -1) || ($.type(e) === 'object' && typeof e.version === typeof undefined)) {
                                versionGroup.addClass('warning');
                                versionField.val('Enable the plugin to fetch version');
                            }
                            else if (e.error == 1) {
                                versionGroup.remove();
                            } else {
                                versionGroup.addClass('success');
                                versionField.val(e.version);
                                $('.vp-extension-description .extension-version').text(e.version).addClass('label label-info');
                            }
                        }
                    });
                    if ($('#system-message-container').length) {
                        $.ajax({
                            type: 'GET',
                            url: base + '/index.php?option=com_ajax&plugin=vponepagecheckout&format=json',
                            data: {'method': 'getupdate', 'extension_id': " . $extension_id . ", '" . JSession::getFormToken() . "' : 1},
                            cache: false,
                            success: function(e) {
                                if (($.type(e) === 'string' && e.indexOf('<\/head>') != -1) || ($.type(e) === 'object' && typeof e.updateFound === typeof undefined)) {
                                    console.log(e); // Invalid data received. 
                                }
                                else if (e.error != 1 && e.updateFound) {
                                    var alert = $('<div></div>').addClass('alert alert-success alert-block'),
                                        close = $('<button></button>').html('&times;').addClass('close').attr('type', 'button').attr('data-dismiss', 'alert').data('dismiss', 'alert').appendTo(alert),
                                        title = $('<h4></h4>').text('Plugin Update Found').appendTo(alert),
                                        infourl = $('<a></a>').text(e.infourl).attr('href', e.infourl).attr('target', '_blank'),
                                        message = $('<p></p>').html('A newer version of VP One Page Checkout plugin is available. Latest version: <b>' + e.version + '</b>. Learn more: ').append(infourl).appendTo(alert),
                                        update = $('<button></button>').text('Update Now').addClass('btn btn-success').attr('type', 'button').attr('onclick', 'window.location.href = \"index.php?option=com_installer&view=update\";return false;').css({'margin-top': '10px'}).appendTo(alert);
                                    
                                    alert.appendTo($('#system-message-container'));
                                }
                            }
                        });
                    }
                }
            });
            ";
        } else {
            $js = "
            jQuery(document).ready(function($) {
                var elementGroup = $('.adminformlist input#jform_extension_id:text').parent('li');
                if (elementGroup.length) {
                    var versionGroup = elementGroup.clone();
                    versionGroup.find('label').attr('id', 'jform_version-lbl').attr('for', 'jform_version').text('Plugin Version').removeAttr('data-original-title').attr('title', 'Plugin Version::VP One Page Checkout plugin version installed in your site.');
                    versionGroup.find('input:text').attr('id', 'jform_version').attr('name', 'jform[version]').attr('disabled', true).val('fetching...');
                    elementGroup.after(versionGroup);
                    var form = elementGroup.parents('form');
                    versionField = $('#jform_version');
                    versionGroup = versionField.parents('li');
                    $$('.hasTip').each(function(el) {
                        var title = el.get('title');
                        if (title) {
                            var parts = title.split('::', 2);
                            el.store('tip:title', parts[0]);
                            el.store('tip:text', parts[1]);
                        }
                    });
                    var JTooltips = new Tips($$('.hasTip'), {maxTitleChars: 50, fixed: false});
                    $.ajax({
                        type: 'GET',
                        url: form.attr('action'),
                        data: {'ctask' : 'getplgversion', '" . JSession::getFormToken() . "' : 1},
                        success: function(e) {
                            if ($.type(e) === 'string' && e.indexOf('<\/head>') != -1) {
                                versionField.val('Enable to plugin first').addClass('warning');
                            }
                            else if (e.error == 1) {
                                versionGroup.remove();
                            } else {
                                versionField.addClass('success').val(e.version);
                                $('.vp-extension-description .extension-version').text(e.version).addClass('label label-info');
                            }
                        },
                        error: function() {
                            versionField.val('Enable the plugin to fetch version');
                        }
                    });
                }
            });
            ";
        }

        $doc->addScriptDeclaration($js);
        return null;
    }

    public function getLabel()
    {
        return null;
    }

    protected function updateEnabled()
    {
        $file = JPATH_ROOT . '/plugins/system/vponepagecheckout/vponepagecheckout.xml';

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
}

if (defined('VPOPC_FOUND')) {
    define('VPOPC_ADMINHELPER', true);
}
