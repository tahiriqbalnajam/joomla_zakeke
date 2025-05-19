<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Script file of miniorange_saml_system_plugin.
 *
 * The name of this class is dependent on the component being installed.
 * The class name should have the component's name, directly followed by
 * the text InstallerScript (ex:. com_helloWorldInstallerScript).
 *
 * This class will be called by Joomla!'s installer, if specified in your component's
 * manifest file, and is used for custom automation actions in its installation process.
 *
 * In order to use this automation script, you should reference it in your component's
 * manifest file as follows:
 * <scriptfile>script.php</scriptfile>
 *
 
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


class pkg_customapiInstallerScript
{
    /**
     * This method is called after a component is installed.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function install($parent) 
    {

            
    }

    /**
     * This method is called after a component is uninstalled.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function uninstall($parent) 
    {
        
    }

    /**
     * This method is called after a component is updated.
     *
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function update($parent) 
    {
    }

    /**
     * Runs just before any installation action is performed on the component.
     * Verifications and pre-requisites should run in this function.
     *
     * @param  string    $type   - Type of PreFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function preflight($type, $parent) 
    {
    }

    /**
     * Runs right after any installation action is performed on the component.
     *
     * @param  string    $type   - Type of PostFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    function postflight($type, $parent) 
    {
       if ($type == 'uninstall') {
        return true;
        }
       $this->showInstallMessage('');
    }

    protected function showInstallMessage($messages=array()) {
        ?>

        
        <style>
        
	.mo-row {
		width: 100%;
		display: block;
		margin-bottom: 2%;
	}

	.mo-row:after {
		clear: both;
		display: block;
		content: "";
	}

	.mo-column-2 {
		width: 19%;
		margin-right: 1%;
		float: left;
	}

	.mo-column-10 {
		width: 80%;
		float: left;
	}
    
    .mo_boot_btn {
    display: inline-block;
    font-weight: 400;
    text-align: center;
    vertical-align: middle;
    user-select: none;
    background-color: transparent;
    border: 1px solid transparent;
    padding: 4px 12px;
    font-size: 0.85rem;
    line-height: 1.5;
    border-radius: 0.25rem;
    transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .mo_boot_btn-media{
    color: white!important;
    background-color: #001b4c;
    border-color: #226a8b;
    }

    .mo_boot_btn-media:hover{
    color: white!important;
    background-color: #001b4c;
    }

    .mo_boot_btn-media:focus, .mo_boot_btn-media.mo_boot_focus {
    box-shadow: 0 0 0 0.2rem #163c4e;
    }

    .mo_boot_btn-meida.mo_boot_disabled, .mo_boot_btn-media:disabled {
    color: #fff;
    background-color: #163c4e;
    border-color: #163c4e;
    }

    </style>

        <p>Plugin package for Joomla Custom API</p>
        <p>Our plugin is compatible with Joomla 5, 4 as well as 3.</p>
        <h4>What this plugin does?</h4>
            This plugin will help you to create any custom API into Joomla. This plugin provides a seamless way to interact with the Joomla database, allowing you to perform CRUD operations (Create, Read, Update, Delete) with ease using the custom endpoints you have created. Also, you can connect external API to joomla site using Joomla Custom API plugin.
    	<div class="mo-row" style="margin-top:10px">
            <a class="mo_boot_btn mo_boot_btn-media" onClick="window.location.reload();" href="index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=overview">Start Using Joomla Custom API plugin</a>
            <a class="mo_boot_btn mo_boot_btn-media" href="https://plugins.miniorange.com/custom-api-for-joomla" target="_blank">Read the miniOrange documents</a>
		    <a class="mo_boot_btn mo_boot_btn-media" href="https://www.miniorange.com/contact" target="_blank">Get Support!</a>
        </div>
        <?php
    }
  
}