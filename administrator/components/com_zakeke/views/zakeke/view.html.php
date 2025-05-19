<?php
defined('_JEXEC') or die;

class ZakekeViewZakeke extends JViewLegacy
{
	public function display($tpl = null)
	{
		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$canDo = JHelperContent::getActions('com_zakeke');

		JToolbarHelper::title(JText::_('COM_ZAKEKE_MANAGER_ZAKEKE'), 'cog');

		if ($canDo->get('core.options'))
		{
			JToolbarHelper::preferences('com_zakeke');
		}
        ZakekeHelper::addSubmenu('zakeke');
	}
}
