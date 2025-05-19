<?php 
/** 
 * @package JREALTIMEANALYTICS::SERVERSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage serverstats
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
?>
<div class="jes">
	<div class="row tablestats no-margin">
		<div class="card card-default accordion-group">
			<div class="card-header accordion-heading">
				<div class="accordion-toggle accordion_lightblue noaccordion">
					<h3><?php echo Text::sprintf('COM_JREALTIME_SERVERSTATS_IP_DETAILS', '<span class="badge badge-info">' . $this->app->getInput()->get('identifier')) . '</span>'; ?></h3>
				</div>
			</div>
			<div class="card-body card-block accordion-body accordion-inner fancybox">
				<table id="jrealtime_table_serverstats_details_ip" class="adminlist table table-striped table-hover">
					<thead>
						<tr>
							<th><span class="badge badge-info"><?php echo Text::_('COM_JREALTIME_SERVERSTATS_NAME');?></span></th>
							<th><span class="badge badge-info"><?php echo Text::_('COM_JREALTIME_SERVERSTATS_USERS_DETAILS_VISITEDPAGE');?></span></th>
							<th><span class="badge badge-info"><?php echo Text::_('COM_JREALTIME_SERVERSTATS_VISIT_LIFE');?></span></th>
							<th><span class="badge badge-info"><?php echo Text::_('COM_JREALTIME_SERVERSTATS_USERS_DETAILS_LASTVISIT');?></span></th>
							<th><span class="badge badge-info"><?php echo Text::_('COM_JREALTIME_SERVERSTATS_GEOLOCATION_STATS');?></span></th>
							<th><span class="badge badge-info"><?php echo Text::_('COM_JREALTIME_SERVERSTATS_BROWSERNAME');?></span></th>
							<th><span class="badge badge-info"><?php echo Text::_('COM_JREALTIME_SERVERSTATS_OS_TITLE');?></span></th>
							<th><span class="badge badge-info label-minwidth"><?php echo Text::_('COM_JREALTIME_SERVERSTATS_DEVICE');?></span></th>
						</tr>
					</thead>
					<tbody>
						<?php 
							$totalTime = 0;
							$totalAverageTime = 0;
							$counter = 0;
							foreach ($this->detailData as $index=>$userDetail):
						?>
							<tr>
								<td><?php echo $userDetail->customer_name;?></td>
								<td><?php echo $userDetail->visitedpage;?></td>
								<td><?php echo gmdate('H:i:s', $userDetail->impulse * $this->daemonRefresh);?></td>
								<td><?php echo date('Y-m-d H:i:s',  $userDetail->visit_timestamp);?></td>
								<td><?php echo $userDetail->geolocation;?> <img onerror="this.style.display='none'" src="<?php echo $this->livesite;?>/administrator/components/com_jrealtimeanalytics/images/flags/<?php echo strtolower($userDetail->geolocation);?>.png"/></td>
								<td><?php echo $userDetail->browser;?> <img class="jr-browsericon" onerror="this.style.display='none'" src="<?php echo $this->livesite;?>administrator/components/com_jrealtimeanalytics/images/browsers/<?php echo str_replace(array(' ', '/'), '', strtolower($userDetail->browser));?>_black.svg"/></td>
								<td><?php echo $userDetail->os;?></td>
								<td><?php if ($userDetail->device):?> 
										<?php echo $userDetail->device;?> <img onerror="this.style.display='none'" src="<?php echo $this->livesite;?>administrator/components/com_jrealtimeanalytics/images/devices/<?php echo strtolower($userDetail->device);?>_black.svg"/>
									<?php else:
										echo Text::_('COM_JREALTIME_NA');
									 endif;?>
								</td>
							</tr>
						<?php 
							$counter++;
							$totalTime += $userDetail->impulse * $this->daemonRefresh;
							$totalAverageTime = (int)($totalTime / $counter);
							endforeach;
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	
	<div class="headstats texttitle">
	<span class="badge badge-info">
		<?php echo Text::_('COM_JREALTIME_SERVERSTATS_PAGES_DETAILS_TOTALDURATION');?>
		<span class="badge badge-inverse-info"><?php echo gmdate('H:i:s', $totalTime);?></span>
	</span>
	</div>
	<div class="headstats average texttitle">
		<span class="badge badge-info">
			<?php echo Text::_('COM_JREALTIME_SERVERSTATS_PAGES_DETAILS_AVERAGEPAGE_DURATION')?>
			<span class="badge badge-inverse-info"><?php echo gmdate('H:i:s', $totalAverageTime);?>
		</span>
	</div>
	<a class="headstats btn btn-primary btn-xs csv" download href="<?php echo \JRealtimeRoute::_('index.php?option=com_jrealtimeanalytics&amp;task=serverstats.showEntitycsv&amp;tmpl=component&amp;details=ip&amp;identifier=' . rawurlencode($this->app->getInput()->get('identifier')));?>">
		<span class="icon-chart" aria-hidden="true"></span>
		<?php echo Text::_('COM_JREALTIME_EXPORTCSV' ); ?>
	</a>
	<a class="headstats btn btn-primary btn-xs xls" download href="<?php echo \JRealtimeRoute::_('index.php?option=com_jrealtimeanalytics&amp;task=serverstats.showEntityxls&amp;tmpl=component&amp;details=ip&amp;identifier=' . rawurlencode($this->app->getInput()->get('identifier')));?>">
		<span class="icon-chart" aria-hidden="true"></span>
		<?php echo Text::_('COM_JREALTIME_EXPORTXLS' ); ?>
	</a>
	<a class="headstats btn btn-primary btn-xs pdf" download href="<?php echo \JRealtimeRoute::_('index.php?option=com_jrealtimeanalytics&task=serverstats.showEntitypdf&tmpl=component&details=ip&identifier=' . rawurlencode($this->app->getInput()->get('identifier')));?>">
		<span class="icon-chart" aria-hidden="true"></span>
		<?php echo Text::_('COM_JREALTIME_EXPORTPDF' ); ?>
	</a>
</div>