<?php
namespace Zakeke\Component\Tasks\Administrator\View\Config;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    protected $form;

    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        return parent::display($tpl);
    }
}