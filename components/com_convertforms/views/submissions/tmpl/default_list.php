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

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<table>
    <thead>
        <tr>
            <th><?php echo Text::_('COM_CONVERTFORMS_ID') ?></th>
            <th><?php echo Text::_('COM_CONVERTFORMS_CREATED') ?></th>
            <th><?php echo Text::_('JSTATUS') ?></th>
            <th width="70px"></th>
        </tr>
    </thead>
    <?php foreach ($this->submissions as $submission) { ?>
        <tr>
            <td><a href="<?php echo $submission->link ?>"><?php echo $submission->id ?></a></td>
            <td><?php echo $submission->created ?></td>
            <td>
                <?php 
                    $badge = 'bg-' . ($submission->state == '1' ? 'success' : 'danger');

                    if (!defined('nrJ4'))
                    {
                        $badge = 'badge-' . ($submission->state == '1' ? 'success' : 'important');
                    }
                ?>

                <span class="badge <?php echo $badge ?>">
					<?php echo Text::_(($submission->state == '1' ? 'COM_CONVERTFORMS_SUBMISSION_CONFIRMED' : 'COM_CONVERTFORMS_SUBMISSION_UNCONFIRMED')) ?>
				</span>
            </td>
            <td><a class="btn btn-secondary btn-small" href="<?php echo $submission->link ?>">View</a></td>  
        </tr>
    <?php } ?> 
</table>

<?php if ($this->pagination && $pagination = $this->pagination->getPagesLinks()) {  ?>
    <div class="pagination">
        <?php echo $pagination; ?>
        <div class="pagecounter">
            <?php echo $this->pagination->getPagesCounter(); ?>
        </div>
    </div>
<?php } ?>
