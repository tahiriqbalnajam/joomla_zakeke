<?php

/**
 * @package     Joomla.Site
 * @subpackage  Templates.vmbasic
 *
 * @copyright   (C) 2024 Spiros Petrakis
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\Document\HtmlDocument $this */

$extraButtons     = AuthenticationHelper::getLoginButtons('form-login');

$app   = Factory::getApplication();
$input = $app->getInput();
$wa    = $this->getWebAssetManager();

// Set responsive meta
$this->setMetaData('viewport', 'width=device-width, initial-scale=1');

// Load Bootstrap tooltip
\Joomla\CMS\HTML\HTMLHelper::_('bootstrap.tooltip');

// Load required css
$wa->useStyle('bootstrap5');
$wa->useStyle('vmbasic');
$wa->useStyle('fontawesome');

if ($this->params->get('customcss', true)) {
	$wa->useStyle('vmbasic.user');
}

if (!empty($this->params->get('bodyfontfamily')))
{
	$wa->addInlineStyle('
		body {
		' . $this->params->get('bodyfontfamily') . '
		}
	');
}

if (!empty($this->params->get('headingsfontfamily')))
{
	$wa->addInlineStyle('
		h1, .h1, h2, .h2, h3, .h3, h4, h5, h6 {
		' . $this->params->get('headingsfontfamily') . '
		}
	');
}


// Load required scripts
$wa->useScript('vmbasic');

// Detecting Active Variables
$option    = $input->getCmd('option', '');
$view      = $input->getCmd('view', '');
$layout    = $input->getCmd('layout', '');
$task      = $input->getCmd('task', '');
$itemid    = $input->getCmd('Itemid', '');
$sitename  = htmlspecialchars($app->get('sitename'), ENT_QUOTES, 'UTF-8');
$menu      = $app->getMenu()->getActive();
$pageclass = $menu !== null ? $menu->getParams()->get('pageclass_sfx', '') : '';

?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas" />
	<?php echo $this->params->get('gfonts'); ?>
	<jdoc:include type="styles" />
	<jdoc:include type="scripts" />
	<?php echo $this->params->get('headcustomjs') ?: ''; ?>
</head>
<body class="<?php echo $this->direction === 'rtl' ? 'rtl' : ''; ?>">
	<div class="container mt-5">
		<div class="col-md-6 offset-md-3 col-lg-4 offset-lg-4 p-4 bg-light">
			<div class="header text-center">
				<div class="mb-3">
					<?php if ($this->params->get('logoFile')) : ?>
						<?php echo HTMLHelper::_('image', Uri::root(false) . htmlspecialchars($this->params->get('logoFile'), ENT_QUOTES), $sitename, ['loading' => 'eager', 'decoding' => 'async'], false, 0); ?>
					<?php else : ?>
						<span class="h3"><?php echo $sitename; ?></span>
					<?php endif; ?>
				</div>

				<?php if ($app->get('offline_image')) : ?>
					<?php echo HTMLHelper::_('image', $app->get('offline_image'), $sitename, [], false, 0); ?>
				<?php endif; ?>

				<?php if ($app->get('display_offline_message', 1) == 1 && str_replace(' ', '', $app->get('offline_message')) != '') : ?>
					<p><?php echo $app->get('offline_message'); ?></p>
				<?php elseif ($app->get('display_offline_message', 1) == 2) : ?>
					<p><?php echo Text::_('JOFFLINE_MESSAGE'); ?></p>
				<?php endif; ?>
			</div>

			<div class="login">
				<jdoc:include type="message" />
				<form action="<?php echo Route::_('index.php', true); ?>" method="post" id="form-login">
					<fieldset>
						<div class="mb-3">
							<label class="form-label" for="username"><?php echo Text::_('JGLOBAL_USERNAME'); ?></label>
							<input name="username" class="form-control" id="username" type="text">
						</div>

						<div class="mb-3">
							<label class="form-label" for="password"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label>
							<input name="password" class="form-control" id="password" type="password">
						</div>

						<?php foreach ($extraButtons as $button) :
							$dataAttributeKeys = array_filter(array_keys($button), function ($key) {
							return substr($key, 0, 5) == 'data-';
							});
						?>
						<div class="mod-login__submit form-group mb-4">
							<button type="button" class="btn btn-secondary w-100 mt-4 <?php echo $button['class'] ?? '' ?>"
								<?php foreach ($dataAttributeKeys as $key) : ?>
								<?php echo $key ?>="<?php echo $button[$key] ?>"
								<?php endforeach; ?>
								<?php if ($button['onclick']) : ?>
								onclick="<?php echo $button['onclick'] ?>"
								<?php endif; ?>
								title="<?php echo Text::_($button['label']) ?>"
								id="<?php echo $button['id'] ?>"
								>
								<?php if (!empty($button['icon'])) : ?>
									<span class="<?php echo $button['icon'] ?>"></span>
								<?php elseif (!empty($button['image'])) : ?>
									<?php echo $button['image']; ?>
								<?php elseif (!empty($button['svg'])) : ?>
									<?php echo $button['svg']; ?>
								<?php endif; ?>

								<?php echo Text::_($button['label']) ?>
							</button>
						</div>
						<?php endforeach; ?>

						<button type="submit" name="Submit" class="btn btn-primary w-100"><?php echo Text::_('JLOGIN'); ?></button>

						<input type="hidden" name="option" value="com_users">
						<input type="hidden" name="task" value="user.login">
						<input type="hidden" name="return" value="<?php echo base64_encode(Uri::base()); ?>">
						<?php echo HTMLHelper::_('form.token'); ?>
					</fieldset>
				</form>
			</div>
		</div>
	</div>
	<?php echo $this->params->get('bodycustomjs') ?: ''; ?>
</body>
</html>
