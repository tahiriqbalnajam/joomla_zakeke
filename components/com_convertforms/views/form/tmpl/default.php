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

?>

<?php if ($this->params->get('show_page_heading')) { ?>
    <h1>
        <?php echo $this->escape($this->params->get('page_heading', $this->params->get('page_title'))); ?>
    </h1>
<?php } ?>

<?php echo $this->item; ?>