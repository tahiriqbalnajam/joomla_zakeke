<?php 
/** 
 * @package JREALTIME::OVERVIEW::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage overview
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
// title
if ( $this->cparams->get ( 'show_page_heading', 0 )) {
	$title = $this->cparams->get ( 'page_heading', $this->menuTitle);
	echo '<h1>' . $title . '</h1>';
}
$cssClass = $this->cparams->get ( 'pageclass_sfx', null);
?>
<form action="<?php echo \JRealtimeRoute::_('index.php?option=com_jrealtimeanalytics&view=webmasters');?>" method="post" class="jes jesform <?php echo $cssClass;?>" name="adminForm" id="adminForm">
	<?php if($this->isLoggedIn):?>
		<div class="btn-toolbar well" id="toolbar">
			<div class="btn-wrapper pull-left" id="toolbar-download">
				<button onclick="jQuery.submitbutton('google.deleteEntity')" class="btn btn-primary btn-xs">
					<span class="glyphicon-lock"></span>
					<?php echo Text::_('COM_JREALTIME_GOOGLE_LOGOUT');?>
				</button>
			</div>
		</div>
	<?php endif; ?>
	
	<?php echo $this->googleData;?>
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="webmasters.display" />
</form>