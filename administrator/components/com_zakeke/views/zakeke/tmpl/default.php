<?php
defined('_JEXEC') or die;
?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
    <p><?php echo JText::_('COM_ZAKEKE_DEFAULT_VIEW_MESSAGE'); ?></p>
    <p><?php echo JText::sprintf('COM_ZAKEKE_CONFIGURE_INSTRUCTIONS', JText::_('JTOOLBAR_OPTIONS')); ?></p>
</div>
