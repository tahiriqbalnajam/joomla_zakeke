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

use Joomla\CMS\MVC\Controller\FormController;
use ConvertForms\Helper;
use ConvertForms\SmartTags;
use NRFramework\URLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

/**
 * Convert Forms form submit controller
 *
 * @since  2.4.0
 */
class ConvertFormsControllerSubmit extends FormController
{
    /**
     * Joomla Application Object
     *
     * @var object
     */
    protected $app;

    /**
     * The secret key configured in the configuration page
     *
     * @var string
     */
    protected $params;

    /**
     * Form data to be stored in the database
     *
     * @var array
     */
    protected $form_data;

    /**
     * Represents the form ID to store the data to.
     *
     * @var integer
     */
    protected $form_id;

	/**
     *  Class Constructor
     *
     *  @param  string  $key  User API Key
     */
    public function __construct()
    {
        $this->app    = Factory::getApplication();
        $this->params = Helper::getComponentParams();

        $input = $this->app->input;
        $data = $input->getArray();

        // Ensure nothing is stripped by Joomla filters by getting the raw object.
        $data['cf'] = $input->post->get('cf', '', 'RAW'); 

        $this->form_data = $data;

        // Detect form ID
        $form_id = (isset($data['cf']) && isset($data['cf']['form_id'])) ? (int) $data['cf']['form_id'] : null;
        $this->form_id = $form_id;

        // Load component language file
        NRFramework\Functions::loadLanguage('com_convertforms');
        
		parent::__construct();
    }

    /**
     * The main submit method
     *
     * @return void
     */
    public function submit()
    {  
        // Prevent AJAX response pollution by disabling PHP reporting notices.
        if (!$this->params->get('debug', false))
        {
            error_reporting(E_ALL & ~E_NOTICE);
        }

        // Check for a CSRF Token only if the respective option is enabled.
        if ($this->params->get('csrf_check', false))
        {
            $this->checkCSRFTokenOrDie();
        }

        $response = new stdClass();

        // The purpose of this property is to help script parse JSON
        $response->convertforms = 'submit';
        
        try
        {
            $submission = $this->createSubmission();
            
            $response->success = true;
            $response->task = $submission->form->onsuccess;
    
            switch ($response->task)
            {
                case 'msg':
                    $response->value     = URLHelper::relativePathsToAbsoluteURLs($submission->form->successmsg);
                    $response->hideform  = $submission->form->hideform;
                    $response->resetform = $submission->form->resetform;
                    break;

                case 'url':
                    $response->value     = $submission->form->successurl;
                    $response->passdata  = $submission->form->passdata;
                    break;
                    
                case 'menu':
                    $response->task      = 'url';
                    $response->value     = $this->resolveURL($submission->form->redirectmenu);
                    $response->passdata  = $submission->form->passdata;
            }
        }
        catch (Exception $e)
        {
            $this->triggerError($e->getMessage());
            $response->error = $e->getMessage();
        }

        echo json_encode($response);

        // Stop execution
        jexit();
    }

    /**
     * Store the submitted to database
     *
     * @return Object
     */
    protected function createSubmission()
    {
        $componentPath = JPATH_ADMINISTRATOR . '/components/com_convertforms/';
        BaseDatabaseModel::addIncludePath($componentPath . 'models');
        Table::addIncludePath($componentPath . 'tables');

        $model = BaseDatabaseModel::getInstance('Conversion', 'ConvertFormsModel', ['ignore_request' => true]);
        $submission = $model->createConversion($this->form_data);

        // Prepare with Smart Tags
        if ($submission->form->onsuccess == 'msg')
        {
            // Prevent Smart Tags replacements in conditional content by parsing IF shortcode before replacing Smart Tags.
            Helper::parseIfShortcode($submission->form->successmsg, $submission);

            $submission->form->successmsg = SmartTags::replace($submission->form->successmsg, $submission);
        }

        if ($submission->form->onsuccess == 'url')
        {
            $submission->form->successurl = SmartTags::replace($submission->form->successurl, $submission);
        }

        return $submission;
    }

    /**
     * Reject request if no valid CSRF token is found.
     *
     * @return void
     */
    protected function checkCSRFTokenOrDie()
    {
        if (!Session::checkToken('request'))
        {
            $this->triggerError(Text::_('JINVALID_TOKEN'));
            jexit(Text::_('JINVALID_TOKEN'));
        }
    }

    /**
     * Trigger a submission-based error
     *
     * @param  string $message  The error message
     *
     * @return void
     */
    protected function triggerError($message)
    {
        Helper::triggerError($message, ucfirst($this->input->get('task')), $this->form_id, $this->form_data);
    }

    private function resolveURL($menu_item_id)
    {
        $item = Factory::getApplication()->getMenu()->getItem($menu_item_id);

        $flink = $item->link;

        switch ($item->type)
        {
            case 'alias':
                $flink = 'index.php?Itemid=' . $item->getParams()->get('aliasoptions');
                break;
            
            case 'url':
                if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false))
                {
                    // If this is an internal Joomla link, ensure the Itemid is set.
                    $flink .= '&Itemid=' . $item->id;
                }
                break;

            default:
                $flink = 'index.php?Itemid=' . $item->id;
                break;
        }

        return Route::_($flink);
    }
}