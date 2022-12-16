<?php
/**
 * Fired when the plugin is uninstalled.
 * @author      Tien Nguyen
 */

// If uninstall, not called from WordPress, then exit
if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// TODO: Define uninstall functionality here