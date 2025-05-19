<?php
defined('_JEXEC') or die;

class ZakekeControllerZakeke extends JControllerLegacy
{
	/**
	 * Display view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy  A JControllerLegacy object to support chaining.
	 *
	 * @since   1.6
	 */
	public function display($cachable = false, $urlparams = array())
	{
		// Set the default view name and layout.
		$this->default_view = 'zakeke';
		parent::display($cachable, $urlparams);

		return $this;
	}
}
