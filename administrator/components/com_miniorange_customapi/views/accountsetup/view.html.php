<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_miniorange_customapi
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Account Setup View
 *
 * @since  0.0.1
 */
class miniorangecustomapiViewAccountSetup extends JViewLegacy
{
    function display($tpl = null)
    {
        // Get data from the model
        $this->lists = $this->get('List');
        //$this->pagination	= $this->get('Pagination');

        // Check for errors.
        $errors = $this->get('Errors');

        if(!empty($errors)){
            if (count($errors)) {
                JFactory::getApplication()->enqueueMessage(500, implode('<br />', $errors));

                return false;
            }
        }

        $this->setLayout('accountsetup');
        // Set the toolbar
        $this->addToolBar();

        // Display the template
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function addToolBar()
    {
        JToolBarHelper::title(JText::_('COM_MINIORANGE_CUSTOMAPI_PLUGIN_TITLE'), 'mo_page_logo mo_page_icon');
    }

}