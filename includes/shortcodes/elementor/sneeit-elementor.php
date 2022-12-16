<?php
/**
 * https://github.com/elementor/elementor-hello-world/blob/master/elementor-hello-world.php
 * https://stackoverflow.com/questions/45522015/select-an-setting-item-of-elementor-plugin-using-jquery
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Main Sneeit Elementor Class
 *
 * The init class that runs the Hello World plugin.
 * Intended To make sure that the plugin's minimum requirements are met.
 *
 * You should only modify the constants to match your plugin's needs.
 *
 * Any custom code should go inside Plugin Class in the sneeit-elementor-plugin.php file.
 * @since 1.2.0
 */
final class Sneeit_Elementor {	
	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct($Sneeit_ShortCodes) {
		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {			
			return;
		}
		
		require_once( 'sneeit-elementor-plugin.php' );
		
		// Instantiate Plugin Class
		new \SneeitElementor\Plugin($Sneeit_ShortCodes);
	}
	
	
}

