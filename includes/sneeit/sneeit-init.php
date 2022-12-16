<?php
if (is_admin()) :
	define('SNEEIT_THEME_ACTIVATION_PAGE_SLUG', 'sneeit-theme-activation');
	include_once 'sneeit-defines.php';
	include_once 'sneeit-theme-activation.php';
	include_once 'sneeit-theme-auto-update.php';
endif;