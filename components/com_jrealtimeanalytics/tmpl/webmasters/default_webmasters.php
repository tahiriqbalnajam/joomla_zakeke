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
			<?php if($this->canExport):?>
				<div class="btn-wrapper pull-left" id="toolbar-download">
					<button onclick="jQuery.submitbutton('webmasters.displayxls')" class="btn btn-primary btn-xs">
						<span class="glyphicon glyphicon-download-alt"></span> <?php echo Text::_('COM_JREALTIME_EXPORTXLS');?>
					</button>
				</div>
			<?php endif;?>
			<div class="btn-wrapper pull-left" id="toolbar-download">
				<button onclick="jQuery.submitbutton('webmasters.deleteEntity')" class="btn btn-primary btn-xs">
					<span class="glyphicon-lock"></span>
					<?php echo Text::_('COM_JREALTIME_GOOGLE_LOGOUT');?>
				</button>
			</div>
		</div>
	<?php endif; ?>
	
	<span class='badge badge-info label-large'><?php echo $this->statsDomain; ?></span> 
	
	<table class="headerlist">
		<tr>
			<td>
				<div class="input-prepend active">
					<span class="add-on"><span class="icon-calendar" aria-hidden="true"></span> <?php echo Text::_('COM_JREALTIME_FILTER_BY_DATE_FROM' ); ?>:</span>
					<input type="text" name="fromperiod" id="fromPeriod" autocomplete="off" data-role="calendar" autocomplete="off" value="<?php echo $this->dates['start'];?>" class="text_area"/>
				</div>
				
				<div class="input-prepend active">
					<span class="add-on"><span class="icon-calendar" aria-hidden="true"></span> <?php echo Text::_('COM_JREALTIME_FILTER_BY_DATE_TO' ); ?>:</span>
					<input type="text" name="toperiod" id="toPeriod" autocomplete="off" data-role="calendar" autocomplete="off" value="<?php echo $this->dates['to'];?>" class="text_area"/>
				</div>
				<button class="btn btn-primary btn-xs" onclick="this.form.submit();"><?php echo Text::_('COM_JREALTIME_GO' ); ?></button>
			</td>
		</tr>
	</table>
	
	<!-- GOOGLE SEARCH CONSOLE STATS PAGES -->
	<div class="accordion" id="jrealtime_googleconsole_query_accordion">
		<div class="row tablestats no-margin">
			<div class="card card-default accordion-group col-md-12 span12">
				<div class="card-header accordion-heading">
					<div class="accordion-toggle accordion_lightblue">
						<h3><span class="glyphicon glyphicon-file"></span> <?php echo Text::_ ('COM_JREALTIME_GOOGLE_WEBMASTERS_STATS_KEYWORDS_BY_PAGES' ); ?></h3>
					</div>
				</div>
				<div id="jrealtime_google_pages" class="card-body card-block accordion-body accordion-inner collapse" >
					<table id="jrealtime_table_webmasters_pages_stats" class="adminlist table table-striped table-hover table-webmasters">
						<thead>
							<tr>
								<th>
									<span class="badge badge-info"><?php echo Text::_('COM_JREALTIME_GOOGLE_WEBMASTERS_PAGES' ); ?></span>
								</th>
								<th class="title">
									<span class="badge badge-info"><?php echo Text::_('COM_JREALTIME_GOOGLE_WEBMASTERS_CLICKS' ); ?></span>
								</th>
								<th class="title">
									<span class="badge badge-info"><?php echo Text::_('COM_JREALTIME_GOOGLE_WEBMASTERS_IMPRESSION' ); ?></span>
								</th>
								<th class="title">
									<span class="badge badge-info"><?php echo Text::_('COM_JREALTIME_GOOGLE_WEBMASTERS_CTR' ); ?></span>
								</th>
								<th class="title">
									<span class="badge badge-info"><?php echo Text::_('COM_JREALTIME_GOOGLE_WEBMASTERS_POSITION' ); ?></span>
								</th>
							</tr>
						</thead>
						
						<tbody>
							<?php // Render errors count
								if(!empty($this->googleData['results_page'])){
									foreach ($this->googleData['results_page'] as $dataGroupedByPage) { ?>
										<tr>
											<td>
												<span class="label-italic">
													<?php $dataGroupedKeys = $dataGroupedByPage->getKeys();?>
													<a href="<?php echo $dataGroupedKeys[0];?>" target="_blank">
														<?php echo $dataGroupedKeys[0];?> <span class="icon-out" aria-hidden="true"></span>
													</a>
												</span>
											</td>
											<td>
												<?php echo $dataGroupedByPage->getClicks();?>
											</td>
											<td>
												<?php echo $dataGroupedByPage->getImpressions();?>
											</td>
											<td>
												<?php echo round(($dataGroupedByPage->getCtr() * 100), 2) . '%';?>
											</td>
											<td>
												<?php 
													$serpPosition = (int)$dataGroupedByPage->getPosition();
													$classLabel = $serpPosition > 30 ? 'badge-danger' : 'badge-success';
												?>
												<span class="label <?php echo $classLabel;?>">
													<?php echo $serpPosition;?>
												</span>
											</td>
										</tr>
								<?php }
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	
	<!-- GOOGLE SEARCH CONSOLE STATS KEYWORDS -->
	<div class="accordion" id="jrealtime_googleconsole_accordion">
		<div class="row tablestats no-margin">
			<div class="card card-default accordion-group col-md-12 span12">
				<div class="card-header accordion-heading">
					<div class="accordion-toggle accordion_lightblue">
						<h3><span class="glyphicon glyphicon-list"></span> <?php echo Text::_ ('COM_JREALTIME_GOOGLE_WEBMASTERS_STATS_KEYWORDS_BY_QUERY' ); ?></h3>
					</div>
				</div>
				<div id="jrealtime_google_query" class="card-body card-block accordion-body accordion-inner collapse" >
					<table id="jrealtime_table_webmasters_keywords_stats" class="adminlist table table-striped table-hover table-webmasters">
						<thead>
							<tr>
								<th>
									<span class="badge badge-info"><?php echo Text::_('COM_JREALTIME_GOOGLE_WEBMASTERS_KEYS' ); ?></span>
								</th>
								<th class="title">
									<span class="badge badge-info"><?php echo Text::_('COM_JREALTIME_GOOGLE_WEBMASTERS_CLICKS' ); ?></span>
								</th>
								<th class="title">
									<span class="badge badge-info"><?php echo Text::_('COM_JREALTIME_GOOGLE_WEBMASTERS_IMPRESSION' ); ?></span>
								</th>
								<th class="title">
									<span class="badge badge-info"><?php echo Text::_('COM_JREALTIME_GOOGLE_WEBMASTERS_CTR' ); ?></span>
								</th>
								<th class="title">
									<span class="badge badge-info"><?php echo Text::_('COM_JREALTIME_GOOGLE_WEBMASTERS_POSITION' ); ?></span>
								</th>
							</tr>
						</thead>
						
						<tbody>
							<?php // Render errors count
								if(!empty($this->googleData['results_query'])){
									foreach ($this->googleData['results_query'] as $dataGroupedByQuery) { ?>
										<tr>
											<td>
												<span class="badge badge-default badge-secondary label-large">
													<?php $dataGroupedQuery = $dataGroupedByQuery->getKeys();?>
													<?php echo $dataGroupedQuery[0];?>
												</span>
											</td>
											<td>
												<?php echo $dataGroupedByQuery->getClicks();?>
											</td>
											<td>
												<?php echo $dataGroupedByQuery->getImpressions();?>
											</td>
											<td>
												<?php echo round(($dataGroupedByQuery->getCtr() * 100), 2) . '%';?>
											</td>
											<td>
												<?php 
													$serpPosition = (int)$dataGroupedByQuery->getPosition();
													$classLabel = $serpPosition > 30 ? 'badge-danger' : 'badge-success';
												?>
												<span class="label <?php echo $classLabel;?>">
													<?php echo $serpPosition;?>
												</span>
											</td>
										</tr>
								<?php }
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="webmasters.display" />
</form>