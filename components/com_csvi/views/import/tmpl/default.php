<?php
/**
 * Import file
 *
 * @author 		RolandD Cyber Produksi
 * @link 		https://rolandd.com
 * @copyright 	Copyright (C) 2006 - 2024 RolandD Cyber Produksi. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version 	$Id: default.php 2389 2013-03-21 09:03:25Z RolandD $
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if ($this->runId) :
	?>
	<div class="row-fluid">
		<form method="post" action="index.php?option=com_csvi&view=imports" id="adminForm" name="adminForm">
			<h3><?php echo Text::sprintf('COM_CSVI_PROCESS_TEMPLATE_NAME', $this->template->getName()); ?></h3>
			<div id="import-div">
				<div class="span2">
					<span class="badge badge-info"><?php echo Text::_('COM_CSVI_RECORDS_PROCESSED'); ?></span>
					<div id="processed"></div>
				</div>
				<div class="span2">
					<span class="badge badge-info"><?php echo Text::_('COM_CSVI_LAST_SERVER_RESPONSE'); ?></span>
					<div class="uncontrolled-interval"><span></span></div>
				</div>
			</div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="csvi_process_id" value="<?php echo $this->runId; ?>" />
		</form>
	</div>

	<script type="text/javascript">
        let intervalId;
        window.addEventListener('DOMContentLoaded', () => {
            startTime()
			doImport()
		});

		function startTime() {
            intervalId = setInterval(
                () => {
                    const timerValue = document.querySelector('.uncontrolled-interval span').innerHTML;
                    const oldTime = parseInt(timerValue.length > 0 ? timerValue : 0)
                    let newValue = oldTime + 1

                    if (<?php echo ini_get('max_execution_time'); ?> > 0 && oldTime > <?php echo ini_get('max_execution_time'); ?>)
                    {
                        newValue = '<?php echo addslashes(Text::_('COM_CSVI_MAX_IMPORT_TIME_PASSED')); ?>'
                    }

                    document.querySelector('.uncontrolled-interval span').innerHTML = newValue
                },
                1000
            );
		}

        function stopTime() {
            clearInterval(intervalId)
            document.querySelector('.uncontrolled-interval span').innerHTML = '';
        }

		function submitbutton(task)
		{
			if (task == 'doimport')
			{
				doImport();

				return true;
			}
			else
			{
                stopTime()
				submitform(task);
			}
		}

		function doImport()
		{
            fetch('<?php echo JUri::root(); ?>administrator/components/com_csvi/rantai/rantai.php?task=import&runId=<?php echo $this->runId; ?>', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                },
            })
            .then(function(response) {
                return response.json()
            })
            .then(function(data) {
                stopTime()

                if (data) {
                    if (data.process == true)
                    {
                        document.getElementById('processed').innerHTML = data.records;
                        startTime();
                        doImport();
                    }
                    else if (data.error == true)
                    {
                        document.getElementById('import-div').innerHTML = '<?php echo Text::sprintf('COM_CSVI_ERROR_DURING_PROCESS', 'import'); ?><p><span class="error">' + data.message + '</span></p>';

                    }
                    else
                    {
                        document.getElementById('import-div').innerHTML = '<?php echo Text::_('COM_CSVI_IMPORT_HAS_FINISHED'); ?>'
                    }
                }
            })
            .catch(function(error) {
                document.getElementById('import-div').innerHTML = '<?php echo Text::sprintf('COM_CSVI_ERROR_DURING_PROCESS', 'import'); ?><p>Status error: ' + error;
            })
		}
	</script>
	<?php
else :
	throw new InvalidArgumentException(Text::_('COM_CSVI_NO_RUNID_FOUND'));
endif;
