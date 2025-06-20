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

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Content categories view.
 *
 * @since  1.5
 */
class ConvertFormsViewSubmissions extends HtmlView
{
	/**
	 * Display the forms' submissions
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->params = Factory::getApplication()->getParams();
        $this->state = $this->get('State');
		$this->submissions = $this->get('Items');
		$this->total_submissions = $this->get('Total');
		$this->searchbar = $this->loadTemplate('searchbar');
		$this->pagination = $this->params->get('show_pagination', true) ? $this->get('Pagination') : null;

		$this->_prepareDocument();

		if ($this->params->get('load_css', true))
		{
			HTMLHelper::stylesheet('com_convertforms/submissions.css', ['relative' => true, 'version' => 'auto']);
		}
		
		// Layout checks
		if ($this->params->get('layout_type', 'file') == 'custom')
		{
			$layout_container = $this->params->get('layout_container');
			$layout_row = $this->params->get('layout_row');
			
			if (!empty($layout_container) && !empty($layout_row))
			{
				$st = new \NRFramework\SmartTags();

				// Register CF Front End Submission Smart Tags
				$st->register(
					'\ConvertForms\SmartTags',
					JPATH_SITE . '/administrator/components/com_convertforms/ConvertForms/SmartTags',
					[
						'front_end_submission' => [
							'submissions' => $this->submissions,
							'searchbar' => $this->searchbar,
							'layout_row' => $layout_row,
							'total' => $this->get('Total'),
							'pagination' => $this->pagination
						]
					]
				);

				$html = $st->replace($layout_container);
				$html = \Joomla\CMS\HTML\HTMLHelper::_('content.prepare', $html);
				echo $html;
				return;
			}
		}

		// Display the view
		$this->setLayout($this->params->get('submissions_layout'));
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 */
	protected function _prepareDocument()
	{
		$doc = Factory::getDocument();
		$app = Factory::getApplication();
		$activeMenuItem = $app->getMenu()->getActive();
		$params = $activeMenuItem->getParams();

		if ($robots_value = $params->get('robots'))
		{
			$robots = $doc->getMetaData('robots');
			$robots = empty($robots) ? $robots_value : $robots . ', ' . $robots_value;
	
			$doc->setMetaData('robots', $robots);
		}

		if ($params->get('menu-meta_keywords'))
		{
			$doc->setMetadata('keywords', $params->get('menu-meta_keywords'));
		}

		if ($params->get('menu-meta_description'))
		{
			$doc->setDescription($params->get('menu-meta_description'));
		}
	}
}