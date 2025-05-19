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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\Document\HtmlDocument $this */

$app   = Factory::getApplication();
$input = $app->getInput();
$wa    = $this->getWebAssetManager();

// Set responsive meta
$this->setMetaData('viewport', 'width=device-width, initial-scale=1');

// Load Bootstrap offcanvas
\Joomla\CMS\HTML\HTMLHelper::_('bootstrap.offcanvas');

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

if ($this->params->get('inlinecustomcss')) {
	$wa->addInlineStyle($this->params->get('inlinecustomcss'));
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

// Calculate main element bootstrap width
$leftCol = $this->countModules('sidebar-left', true) && $view != 'productdetails' ? 3 : 0;
$rightCol = $this->countModules('sidebar-right', true) && $view != 'productdetails' ? 3 : 0;
$mainCol = (12 - $leftCol - $rightCol);

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

<body class="site <?php echo $option
	. ' view-' . $view
	. ($layout ? ' layout-' . $layout : ' no-layout')
	. ($task ? ' task-' . $task : ' no-task')
	. ($itemid ? ' itemid-' . $itemid : '')
	. ($pageclass ? ' ' . $pageclass : '')
	. ($this->direction == 'rtl' ? ' rtl' : '');
?>">
	<header class="header mb-0 mb-xl-3">
		<div class="toolbar p-0 py-md-2">
			<div class="container-xxl">
				<div class="row align-items-center">
					<?php if ($this->countModules('topbarleft', true)) : ?>
						<div class="col-md-auto col-xl-4 py-1 py-md-0 text-center text-md-start<?php echo $this->countModules('topbar', true) ? '' : ' bg-alt'; ?>">
							<jdoc:include type="modules" name="topbarleft" style="none" />
						</div>
					<?php endif; ?>

					<?php if ($this->countModules('topbar', true)) : ?>
						<div class="col-md-auto col-xl-4 py-1 py-md-0 top-bar<?php echo $this->countModules('topbarleft', true) ? ' text-center' : ' text-center text-md-start'; ?>">
							<jdoc:include type="modules" name="topbar" style="none" />
						</div>
					<?php endif; ?>

					<div class="col-md-auto ms-md-auto d-flex justify-content-between justify-content-md-end align-items-center py-1 py-md-0">
						<?php if ($this->countModules('login', true)) : ?>
							<div>
								<jdoc:include type="modules" name="login" style="none" />
							</div>
						<?php endif; ?>
						<?php if ($this->countModules('currencies', true)) : ?>
							<div class="ms-2 ms-lg-4">
								<jdoc:include type="modules" name="currencies" style="none" />
							</div>
						<?php endif; ?>
						<?php if ($this->countModules('languages', true)) : ?>
							<div class="ms-2 ms-lg-4">
								<jdoc:include type="modules" name="languages" style="none" />
							</div>
						<?php endif; ?>
					</div>
				 </div>
			 </div>
		</div>

		<div class="container-xxl header-inner py-3 py-lg-4">
			<div class="row gy-3 align-items-center">
				<div class="col-6 col-lg-4 logo order-1">
					<a class="logo" href="<?php echo $this->baseurl; ?>/">
						<?php if ($this->params->get('logoFile')) : ?>
							<?php echo HTMLHelper::_('image', Uri::root(false) . htmlspecialchars($this->params->get('logoFile'), ENT_QUOTES), $sitename, ['loading' => 'eager', 'decoding' => 'async'], false, 0); ?>
						<?php else : ?>
							<span class="h3"><?php echo $sitename; ?></span>
						<?php endif; ?>
					</a>
				</div>
				<div class="col-lg-4 main-search order-3 order-lg-2">
					<?php if ($this->countModules('search', true)) : ?>
						<div class="row">
							<div class="col">
								<jdoc:include type="modules" name="search" style="none" />
							</div>
						</div>
					<?php endif; ?>
				</div>
				<div class="col-6 col-lg-4 d-flex justify-content-end cart-module order-2 order-lg-3">
					<?php if ($this->countModules('cart', true)) : ?>
						<div class="row">
							<div class="col d-flex justify-content-end">
								<jdoc:include type="modules" name="cart" style="none" />
							</div>
						</div>
					<?php endif; ?>

					<?php if ($this->countModules('search', true)) : ?>
						<button class="btn btn-sm btn-link text-dark p-0 d-lg-none ms-3" id="search-toggle" type="button" title="Search">
							<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
								<path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
							</svg>
						</button>
					<?php endif; ?>

					<button class="btn btn-sm btn-link text-dark p-0 d-xl-none ms-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas" aria-controls="offcanvas" title="Menu">
						<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
							<path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
						</svg>
					</button>
				</div>
			</div>
		</div>

		<div class="main-menu d-none d-xl-block">
 			 <div class="container-xxl">
				 <?php if ($this->countModules('menu', true)) : ?>
					<div class="row">
						<div class="col">
							<jdoc:include type="modules" name="menu" style="none" />
						</div>
					</div>
				<?php endif; ?>
			 </div>
		</div>
	</header>

	<div class="page">
		<?php if ($this->countModules('breadcrumbs', true)) : ?>
			<div class="container-xxl mt-lg-2 breadcrumbs">
				<div class="row">
					<jdoc:include type="modules" name="breadcrumbs" style="none" />
				</div>
			</div>
		<?php endif; ?>

		<?php if ($this->countModules('banner-1', true) || $this->countModules('banner-2', true) || $this->countModules('banner-3', true)) : ?>
			<div class="container-xxl banner-section">
				<div class="row g-3">
					<div class="<?php echo $this->countModules('banner-2', true) || $this->countModules('banner-3', true) ? 'col-lg-8' : 'col-12'; ?>">
						<?php if ($this->countModules('banner-1', true)) : ?>
						<div class="row">
							<jdoc:include type="modules" name="banner-1" style="none" />
						</div>
						<?php endif; ?>
					</div>

					<?php if ($this->countModules('banner-2', true) || $this->countModules('banner-3', true)) : ?>
					<div class="col-lg-4">
						<div class="row gy-3">
							<div class="col-12 col-md-6 col-lg-12">
								<jdoc:include type="modules" name="banner-2" style="none" />
							</div>
							<div class="col-12 col-md-6 col-lg-12">
								<jdoc:include type="modules" name="banner-3" style="none" />
							</div>
						</div>
					</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ($this->countModules('top-1', true)) : ?>
			<div class="container-xxl py-4 top-1">
				<div class="row">
					<jdoc:include type="modules" name="top-1" style="html5" />
				</div>
			</div>
		<?php endif; ?>

		<?php if ($this->countModules('top-2', true)) : ?>
			<div class="container-xxl py-4 top-2">
				<div class="row">
					<jdoc:include type="modules" name="top-2" style="html5" />
				</div>
			</div>
		<?php endif; ?>

		<?php if ($this->countModules('top-3', true)) : ?>
			<div class="container-xxl py-4 top-3">
				<div class="row">
					<jdoc:include type="modules" name="top-3" style="html5" />
				</div>
			</div>
		<?php endif; ?>

		<?php if ($this->countModules('top-4', true)) : ?>
			<div class="container-xxl py-4 top-4">
				<div class="row">
					<jdoc:include type="modules" name="top-4" style="html5" />
				</div>
			</div>
		<?php endif; ?>

		<div class="container-xxl">
			<jdoc:include type="message" />
		</div>

		<div class="container-xxl py-4">
			<div class="row">
				<main class="col-12 col-lg-<?php echo $mainCol; ?> order-1 order-lg-2">
					<jdoc:include type="component" />
				</main>

				<?php if ($this->countModules('sidebar-left', true) && $view != 'productdetails') : ?>
					<aside class="col-12 col-lg-3 order-2 order-lg-1 mt-5 mt-lg-0 sidebar-left">
						<div class="row row-cols-1 row-cols-md-2 row-cols-lg-1 gy-5">
							<jdoc:include type="modules" name="sidebar-left" style="html5" />
						</div>
					</aside>
				<?php endif; ?>

				<?php if ($this->countModules('sidebar-right', true) && $view != 'productdetails') : ?>
					<aside class="col-12 col-lg-3 order-3 mt-5 mt-lg-0 sidebar-right">
						<div class="row row-cols-1 row-cols-md-2 row-cols-lg-1 gy-5">
							<jdoc:include type="modules" name="sidebar-right" style="html5" />
						</div>
					</aside>
				<?php endif; ?>
			</div>
		</div>

		<?php if ($this->countModules('bottom-1', true)) : ?>
			<div class="container-xxl py-4 bottom-1">
				<div class="row">
					<jdoc:include type="modules" name="bottom-1" style="html5" />
				</div>
			</div>
		<?php endif; ?>

		<?php if ($this->countModules('bottom-2', true)) : ?>
			<div class="container-xxl py-4 bottom-2">
				<div class="row">
					<jdoc:include type="modules" name="bottom-2" style="html5" />
				</div>
			</div>
		<?php endif; ?>

		<?php if ($this->countModules('bottom-3', true)) : ?>
			<div class="container-xxl py-4 bottom-3">
				<div class="row">
					<jdoc:include type="modules" name="bottom-3" style="html5" />
				</div>
			</div>
		<?php endif; ?>

		<?php if ($this->countModules('bottom-4', true)) : ?>
			<div class="container-xxl py-4 bottom-4">
				<div class="row">
					<jdoc:include type="modules" name="bottom-4" style="html5" />
				</div>
			</div>
		<?php endif; ?>
	</div>

	<footer>
		<?php if ($this->params->get('logoFile') && $this->params->get('footerlogo')) : ?>
			<div class="container-xxl footer-logo text-center mb-5">
				<a class="logo d-inline-block" href="<?php echo $this->baseurl; ?>/">
					<?php echo HTMLHelper::_('image', Uri::root(false) . htmlspecialchars($this->params->get('logoFile'), ENT_QUOTES), $sitename, ['loading' => 'eager', 'decoding' => 'async'], false, 0); ?>
				</a>
			</div>
		<?php endif; ?>
		<?php if ($this->countModules('footer', true)) : ?>
			<div class="container-xxl">
				<div class="row gy-4 text-center text-md-start">
					<jdoc:include type="modules" name="footer" style="html5" />
				</div>
			</div>
		<?php endif; ?>
		<?php if ($this->countModules('copyright', true)) : ?>
			<div class="container-xxl pt-3 mt-5 border-top small">
				<div class="row text-center">
					<jdoc:include type="modules" name="copyright" style="none" />
				</div>
			</div>
		<?php endif; ?>
	</footer>

	<?php if ($this->params->get('backTop', true)) : ?>
		<a href="#" class="btn btn-sm btn-primary back-to-top-link" type="button" aria-label="<?php echo Text::_('TPL_VMBASIC_BACKTOTOPFE'); ?>">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-bar-up" viewBox="0 0 16 16">
				<path fill-rule="evenodd" d="M8 10a.5.5 0 0 0 .5-.5V3.707l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L7.5 3.707V9.5a.5.5 0 0 0 .5.5m-7 2.5a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13a.5.5 0 0 1-.5-.5"/>
			</svg>
		</a>
	<?php endif; ?>

	<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvas" aria-labelledby="offcanvas">
		<div class="offcanvas-header">
			<a class="logo" href="<?php echo $this->baseurl; ?>/">
				<?php if ($this->params->get('logoFile')) : ?>
					<?php echo HTMLHelper::_('image', Uri::root(false) . htmlspecialchars($this->params->get('logoFile'), ENT_QUOTES), $sitename, ['loading' => 'eager', 'decoding' => 'async'], false, 0); ?>
				<?php else : ?>
					<span class="h3"><?php echo $sitename; ?></span>
				<?php endif; ?>
			</a>

			<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
		</div>
		<div class="offcanvas-body">
			<?php if ($this->countModules('menu', true)) : ?>
				<jdoc:include type="modules" name="menu" style="none" />
			<?php endif; ?>
		</div>
	</div>

	<jdoc:include type="modules" name="debug" style="none" />
	<?php echo $this->params->get('bodycustomjs') ?: ''; ?>
</body>
</html>