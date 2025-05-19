<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die();

use Joomla\CMS\Language\Text;

/** @var $this \Akeeba\Component\AdminTools\Site\View\Block\HtmlView */

/**
 * Feel free to customize this file using a standard template override. For more
 * information on template overrides, please consult Joomla!'s documentation wiki:
 * http://docs.joomla.org/How_to_override_the_output_from_the_Joomla!_core
 */

/**
 * Inline CSS for displaying the error page.
 *
 * The default is a full page, centered message. The page background is red, the message background is white or gray
 * color depending on the browser color scheme (light or dark). The default CSS is both responsive and dark mode aware.
 */
$css = <<< CSS
section.blocked-wrapper {
	width: 100vw;
	height: 100vh;
	display: flex;
	justify-content: center;
	align-items: center;
	align-content: center;
	background-color: #E2363C;
}

#blocked {
	width: 50vw;
	background-color: #EFEFEF;
	padding: 1em 2em;
	border: 4px solid black;
	border-radius: 1em;
}

#blocked h1 {
	color: #cc0000;
}

@media screen and (max-width: 900px) {
	#blocked {
		width: 80vw;
	}
}

@media screen and (prefers-color-scheme: dark) {
	body {
		color: #EFEFEF;
	}

	section.blocked-wrapper {
		background-color: #4d0000;
	}
	
	#blocked {
		background-color: #333233;
		border-color: #514F50;
		
	}
	
	#blocked h1 {
		color: #E2363C;
	}
}
CSS;

$this->document->getWebAssetManager()->addInlineStyle($css);
?>
<section class="blocked-wrapper">
	<div id="blocked">
		<h1><?php echo Text::_('JGLOBAL_AUTH_ACCESS_DENIED'); ?></h1>

		<p><?php echo $this->message; ?></p>
	</div>
</section>
