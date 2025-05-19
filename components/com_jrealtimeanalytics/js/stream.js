/**
 * STREAM main JS APP class
 * 
 * @package JREALTIMEANALYTICS::STREAM::components::com_jrealtimeanalytics
 * @subpackage js
 * @author Joomla! Extensions Store
 * @copyright (C)2014 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
// 'use strict';
(function($) {
	var Stream = function() {
		/**
		 * Save bind context to this object instance
		 * 
		 * @access private
		 * @var Object
		 */
		var bind = this;

		/**
		 * Heatmap class object reference
		 * 
		 * @access private
		 * @var Object
		 */
		var heatmapInstance = null;

		/**
		 * Enable debug for JS App
		 * 
		 * @access private
		 * @var Boolean
		 */
		var debugEnabled = false;
		
		/**
		 * Daemon Refresh
		 * 
		 * @access private
		 * @var Int
		 */
		var daemonRefresh = 2000;

		/**
		 * Daemon Refresh in seconds
		 * 
		 * @access private
		 * @var Int
		 */
		var daemonRefreshSeconds = 2;
		
		/**
		 * Number of current refreshes
		 * 
		 * @access private
		 * @var Int
		 */
		var daemonNumberRefreshes = 0;
		
		/**
		 * Daemon timeout
		 * 
		 * @access private
		 * @var Int
		 */
		var daemonTimeout = 0;
		
		/**
		 * Daemon max number of refresh admitted
		 * 
		 * @access private
		 * @var Int
		 */
		var maxDaemonRefresh = null;

		/**
		 * OS detected via Client Hints, mandatory to detect Windows 11
		 * 
		 * @access private
		 * @var Int
		 */
		var clientHintsOperatingSystem = null;

		/**
		 * Main app dispatch method
		 * 
		 * @param String
		 *            coreFile
		 * @param String
		 *            errorDetails
		 * @access public
		 * @return Void
		 */
		this.showDebugMsgs = function(coreFile, errorDetails) {
			/**
			 * .delay(2500).fadeOut(500, function() { $(this).remove(); })
			 */
			if (debugEnabled) {
				// Gestione messaggio flottante e reset form
				if(!$('div#jrealtime_msg').length) {
					$('<div/>').attr('id', 'jrealtime_msg').prependTo('body').append(
							'<div id="jrealtime_msgtitle">' + coreFile + '</div>').append(
									'<div id="jrealtime_msgtext">' + errorDetails + '</div>').css(
											'margin-top', 0).animate({
												'margin-top' : '-150px'
											}, 300, "linear");
				}
			}
		}

		/**
		 * Main app dispatch method
		 * 
		 * @param Boolean
		 *            init
		 * @access public
		 * @return Void
		 */
		this.dispatch = function(init) {
			var jrealtimeLivesite = jrealtimeBaseURI + "index.php?option=com_jrealtimeanalytics&format=json";
			var postData = {};

			postData.task = "stream.display";
			postData.nowpage = $(location).attr("href");
			postData.initialize = init;
			postData.module_available = parseInt($('#jes_mod').length);

			// Add device width/height informations
			if(init) {
				postData.device_width = window.outerWidth;
				postData.device_height = window.outerHeight;
				
				// Check if client hints entropy values are available to detect Windows 11
				if (navigator.userAgentData && typeof navigator.userAgentData === 'object') {
					navigator.userAgentData.getHighEntropyValues(["platformVersion"]).then(ua => {
						if (navigator.userAgentData.platform === "Windows") {
							const majorPlatformVersion = parseInt(ua.platformVersion.split('.')[0]);
							if (majorPlatformVersion >= 13) {
								clientHintsOperatingSystem = 'Windows 11';
							}
						}
					});
				}
			} else {
				if(clientHintsOperatingSystem) {
					postData.windowsver = clientHintsOperatingSystem;
				}
			}

			// Daemon dispatch HTTP Request
			$.ajax({
				url : jrealtimeLivesite,
				data : postData,
				type : "post",
				cache : false,
				dataType : "json",
				success : function(response, textStatus, jqXHR) {
					// Increment the number of refreshes
					daemonNumberRefreshes++;
					
					if (response) {
						// Params from server response
						if (response.configparams) {
							daemonRefreshSeconds = response.configparams.daemonrefresh;
							daemonRefresh = response.configparams.daemonrefresh * 1000;
							debugEnabled = !! parseInt(response.configparams.enable_debug);
							if(typeof(response.configparams.daemontimeout) !== 'undefined') {
								daemonTimeout = response.configparams.daemontimeout * 60;
								maxDaemonRefresh = parseInt(daemonTimeout / daemonRefreshSeconds);
							}
						}

						// Manage storing models exceptions
						if (response.storing && response.storing.length) {
							$.each(response.storing, function(k, elem) {
								bind.showDebugMsgs(elem.corefile, elem.details);
							});
						} else {
							var refreshDaemon = true;
							
							// Check if there is a max number of recursions
							if(daemonTimeout) {
								if(daemonNumberRefreshes > maxDaemonRefresh) {
									refreshDaemon = false;
								}
							}
							
							// All went well, start daemon recursion, callback cycle
							if (refreshDaemon) {
								setTimeout(function() {
									bind.dispatch()
								}, daemonRefresh);
							}
						}
						
						// Manage loading models exceptions and realtime data retrieved
						if (response.loading && response.loading.length) {
							$.each(response.loading, function(k, elem) {
								bind.showDebugMsgs(elem.corefile, elem.details);
							});
						} else {
							if(response['data-bind']) {
								// Cycle on data-bind array of data retrieved, find the module bound elems and populate
								$.each(response['data-bind'], function(bindSelector, bindValue){
									$('#jes_mod span.badge[data-bind=' + bindSelector + ']').text(bindValue);
								});
							}
						}
						
						// After the promise is resolved, instantiate and start the heatmap class with decorator pattern
						if(typeof(JRealtimeHeatmap) !== 'undefined' && !heatmapInstance) {
							heatmapInstance = new JRealtimeHeatmap(response.configparams, bind);
							heatmapInstance.startListening();
						}
					}
				},
				error : function(jqXHR, textStatus, error) {
					text = COM_JREALTIME_NETWORK_ERROR + error;
					// Format alert bootstrap message
					bind.showDebugMsgs('Client side stream', text);
				}
			});
		}
	}

	// Make it global to instantiate as plugin
	window.JRealtimeStream = Stream;

	// Instantiate on DOM ready and start daemon dispatch// On DOM Ready
	$(function() {
		var streamInstance = new JRealtimeStream();
		streamInstance.dispatch(true);
	});
})(jQuery);