<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = \Joomla\CMS\Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('keepalive');

?>

<form class="mod-login-logout form-vertical<?php echo $module->position == 'login' ? ' d-flex align-items-center' : ''; ?>" action="<?php echo Route::_('index.php', true); ?>" method="post" id="login-form-<?php echo $module->id; ?>">
	<?php if ($params->get('greeting', 1)) : ?>
		<div class="mod-login-logout__login-greeting login-greeting d-none d-md-block">
			<?php if (!$params->get('name', 0)) : ?>
				<?php echo Text::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('name'), ENT_COMPAT, 'UTF-8')); ?>
			<?php else : ?>
				<?php echo Text::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('username'), ENT_COMPAT, 'UTF-8')); ?>
			<?php endif; ?>&nbsp;
		</div>
	<?php endif; ?>
	<?php if ($params->get('profilelink', 0)) : ?>
		<ul class="mod-login-logout__options list-unstyled">
			<li>
				<a href="<?php echo Route::_('index.php?option=com_users&view=profile'); ?>">
				<?php echo Text::_('MOD_LOGIN_PROFILE'); ?></a>
			</li>
		</ul>
	<?php endif; ?>

	<div class="mod-login-logout__button logout-button">
		<button type="submit" name="Submit" class="btn btn-link btn-sm p-0">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-left me-0" viewBox="0 0 16 16">
			<path fill-rule="evenodd" d="M6 12.5a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5h-8a.5.5 0 0 0-.5.5v2a.5.5 0 0 1-1 0v-2A1.5 1.5 0 0 1 6.5 2h8A1.5 1.5 0 0 1 16 3.5v9a1.5 1.5 0 0 1-1.5 1.5h-8A1.5 1.5 0 0 1 5 12.5v-2a.5.5 0 0 1 1 0z"/>
			<path fill-rule="evenodd" d="M.146 8.354a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L1.707 7.5H10.5a.5.5 0 0 1 0 1H1.707l2.147 2.146a.5.5 0 0 1-.708.708z"/>
			</svg>
			<?php echo Text::_('JLOGOUT'); ?>
		</button>

		<input type="hidden" name="option" value="com_users">
		<input type="hidden" name="task" value="user.logout">
		<input type="hidden" name="return" value="<?php echo $return; ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>