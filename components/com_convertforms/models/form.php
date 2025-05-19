<?php

/**
 * @package         Convert Forms
 * @version         4.4.7 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;

class ConvertFormsModelForm extends AdminModel
{
	/**
	 * Get Submission Data
	 *
	 * @param  object $pk	The submission's primary key
	 *
	 * @return object
	 */
	public function getItem($pk = null)
	{
		$form_id = Factory::getApplication()->getParams()->get('form_id');

		$form = ConvertForms\Helper::renderFormById($form_id);

		return $form;
	}

    /**
     * Method to get the record form.
     *
     * @param       array   $data           Data for the form.
     * @param       boolean $loadData       True if the form is to load its own data (default case), false if not.
     * @return      mixed   A JForm object on success, false on failure
     * @since       2.5
     */
    public function getForm($data = array(), $loadData = true)
    {

    }
}