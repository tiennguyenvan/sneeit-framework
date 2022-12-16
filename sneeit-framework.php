<?php
/*
Plugin Name: Sneeit Framework
Plugin URI:  
Description: This plugin will help theme developers finish their theme faster
Version:     8.3
Author:      Tien Nguyen
Author URI:  
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: sneeit
*/

define('SNEEIT_PLUGIN_VERSION', '8.3');
/******************************************/

/*DEFINES*/
function sneeit_framework() {}
function sneeit_framework_plugin_path($path = '') {
	return untrailingslashit( plugin_dir_path( __FILE__ ) ) . $path;
}

/*common*/
define('SNEEIT_PLUGIN_URL', plugin_dir_url( __FILE__ ));


/*URL parts*/
define('SNEEIT_HOME_URL',					get_home_url());
define('SNEEIT_PLUGIN_URL_CSS',				SNEEIT_PLUGIN_URL . 'css/');
define('SNEEIT_PLUGIN_URL_DEMO',			SNEEIT_PLUGIN_URL . 'demo/');
define('SNEEIT_PLUGIN_URL_FONTS',			SNEEIT_PLUGIN_URL . 'fonts/');
define('SNEEIT_PLUGIN_URL_IMAGES',			SNEEIT_PLUGIN_URL . 'images/');
define('SNEEIT_PLUGIN_URL_JS',				SNEEIT_PLUGIN_URL . 'js/');
define('SNEEIT_PLUGIN_URL_JS_PLUGINS',		SNEEIT_PLUGIN_URL_JS . 'plugins/');
/*
define('SNEEIT_PLUGIN_URL_FONT_AWESOME',	SNEEIT_PLUGIN_URL_FONTS . 'font-awesome/css/font-awesome.min.css');
define('SNEEIT_PLUGIN_URL_FONT_AWESOME_RTL',SNEEIT_PLUGIN_URL_FONTS . 'font-awesome/css/font-awesome-rtl.min.css');
*/
define('SNEEIT_PLUGIN_URL_FONT_AWESOME',	SNEEIT_PLUGIN_URL_FONTS . 'font-awesome-5x/css/all.min.css');
define('SNEEIT_PLUGIN_URL_FONT_AWESOME_RTL',SNEEIT_PLUGIN_URL_FONTS . 'font-awesome-5x/css/all.min.css');
define('SNEEIT_PLUGIN_URL_FONT_AWESOME_SHIMS',SNEEIT_PLUGIN_URL_FONTS . 'font-awesome-5x/css/v4-shims.min.css');


/*modules*/
require_once 'includes/defines/defines-init.php';
require_once 'includes/lib/lib-init.php';
require_once 'includes/utilities/utilities-init.php';
require_once 'includes/articles/articles-init.php';
require_once 'includes/customizer/customizer-init.php';
require_once 'includes/theme-options/theme-options-init.php';
require_once 'includes/menus/menus-init.php';
require_once 'includes/widgets/widgets-init.php';
require_once 'includes/post-meta-box/post-meta-box-init.php';
require_once 'includes/user-meta-box/user-meta-box-init.php';
require_once 'includes/shortcodes/shortcodes-init.php';
require_once 'includes/page-builder/page-builder-init.php';
require_once 'includes/seo/seo-init.php';
require_once 'includes/demo-installer/demo-installer-init.php';
require_once 'includes/sneeit/sneeit-init.php';
require_once 'includes/envato/envato-init.php';
require_once 'includes/social/social-init.php';
require_once 'includes/rating/rating-init.php';
require_once 'includes/controls/controls-init.php';