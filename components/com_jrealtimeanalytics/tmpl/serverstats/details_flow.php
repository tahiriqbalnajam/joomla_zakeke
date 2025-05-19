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
use Joomla\CMS\Uri\Uri;
?>
<div class="jes">
	<div class="row tablestats no-margin">
		<div class="card card-default accordion-group">
			<div class="card-header accordion-heading">
				<div class="accordion-toggle accordion_lightblue noaccordion">
					<h3><?php echo Text::sprintf('COM_JREALTIME_SERVERSTATS_USER_NAVIGATION_FLOW', '<span class="badge badge-info">' . @$this->detailData[0]->customer_name) . '</span>'; ?></h3>
				</div>
			</div>
			<div class="card-body card-block accordion-body accordion-inner fancybox noheight">
					<?php 
						$totalTime = 0;
						$totalAverageTime = 0;
						$counter = 0;
						$flowModelData = new \stdClass();
						$flowModelData->nodeDataArray = array();
						$flowModelData->linkDataArray = array();
						$this->detailData = array_reverse($this->detailData);
						$numPages = count($this->detailData);
						foreach ($this->detailData as $index=>$userDetail):
							$category = $index == 0 ? 'Source' : ($index == ($numPages - 1) ? 'Drain' : '');
						$flowModelData->nodeDataArray [] = array (
								'key' => $index,
								'category' => $category,
								'text' => $userDetail->visitedpage,
								'time' => Text::_('COM_JREALTIME_SERVERSTATS_VISIT_LIFE') . ': ' . gmdate ( 'H:i:s', $userDetail->impulse * $this->daemonRefresh ) 
						);
						$flowModelData->linkDataArray [] = array (
								'from' => $index,
								'to' => ($index+1));
							
							$counter++;
							$totalTime += $userDetail->impulse * $this->daemonRefresh;
							$totalAverageTime = (int)($totalTime / $counter);
						endforeach;
						
						// Put data in the javascript domain
						$this->document->getWebAssetManager()->addInlineScript("var jRealtimeModelFlowData = '" . json_encode($flowModelData) . "'");
					?>
					<div id="myDiagram" style="background-color: Snow; border: solid 1px gray; width: 100%; min-height:640px; max-height: auto; height: <?php echo 80 * ($counter+1)?>px"></div>
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
</div>