<?php 
/** 
 * @package JREALTIMEANALYTICS::OVERVIEW::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage overlook
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

// title
if ( $this->cparams->get ( 'show_page_heading', 0 )) {
	$title = $this->cparams->get ( 'page_heading', $this->menuTitle);
	echo '<h1>' . $title . '</h1>';
}
$cssClass = $this->cparams->get ( 'pageclass_sfx', null);
?>
<form action="<?php echo \JRealtimeRoute::_('index.php?option=com_jrealtimeanalytics&view=overlook');?>" method="post" class="jes jesform <?php echo $cssClass;?>" id="adminForm" name="adminForm">
	<?php if($this->canExport):?>
		<div class="btn-toolbar well" id="toolbar">
			<div class="btn-wrapper pull-left" id="toolbar-download">
				<button onclick="jQuery.submitbutton('overlook.displaypdf')" class="btn btn-primary btn-xs">
					<span class="glyphicon glyphicon-download-alt"></span> <?php echo Text::_('COM_JREALTIME_EXPORTPDF');?>
				</button>
			</div>
		</div>
	<?php endif;?>
	
	<div class="headerlist well">
		<div class="input-prepend blockfield">
			<span class="add-on"><span class="icon-filter" aria-hidden="true"></span> <?php echo Text::_('COM_JREALTIME_GRAPH_TYPE' ); ?>:</span>
			<?php echo $this->lists['graphType'];?> 
		</div>
		<div class="clearfix"></div>
		<div class="input-prepend blockfield">
			<span class="add-on"><span class="icon-filter" aria-hidden="true"></span> <?php echo Text::_('COM_JREALTIME_GRAPH_THEME' ); ?>:</span>
			<?php echo $this->lists['graphTheme'];?> 
		</div>
		<div class="clearfix"></div>
		<div class="input-prepend blockfield">
			<span class="add-on"><span class="icon-filter" aria-hidden="true"></span> <?php echo Text::_('COM_JREALTIME_MONTH_PERIOD' ); ?>:</span>
			<?php echo $this->lists['statsMonth'];?> 
		</div>
		<div class="clearfix"></div>
		<div class="input-prepend blockfield">
			<span class="add-on"><span class="icon-filter" aria-hidden="true"></span> <?php echo Text::_('COM_JREALTIME_YEAR' ); ?>:</span>
			<?php echo $this->lists['statsYear'];?> 
		</div>
	</div>

	<div>
		<a data-role="overview" href="<?php echo Uri::root();?>administrator/components/com_jrealtimeanalytics/cache/<?php echo $this->userid . '_serverstats_overview.png' . $this->nocache;?>">
			<img src="<?php echo Uri::root();?>administrator/components/com_jrealtimeanalytics/cache/<?php echo $this->userid . '_serverstats_overview.png' . $this->nocache;?>" />
		</a>
	</div>
	
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="overlook.display" />
</form>