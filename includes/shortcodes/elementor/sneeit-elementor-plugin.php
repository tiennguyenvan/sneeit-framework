<?php
namespace SneeitElementor;

use SneeitElementor\PageSettings\Page_Settings;

/**
 * Class Plugin
 *
 * Main Plugin class
 * @since 1.2.0
 */
class Plugin {
	public $shortcode_declaration = null;
	
	/**
	 * Instance
	 *
	 * @since 1.2.0
	 * @access private
	 * @static
	 *
	 * @var Plugin The single instance of the class.
	 */
	private static $_instance = null;	

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @return Plugin An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * widget_scripts
	 *
	 * Load required plugin core files.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	
	public function widget_scripts() {
		wp_register_script( 'sneeit-elementor', plugins_url( '/assets/js/hello-world.js', __FILE__ ), [ 'jquery' ], false, true );
	}	

	/**
	 * Editor scripts
	 *
	 * Enqueue plugin javascripts integrations for Elementor editor.
	 *
	 * @since 1.2.1
	 * @access public
	 */
	public function editor_scripts() {
		add_filter( 'script_loader_tag', [ $this, 'editor_scripts_as_a_module' ], 10, 2 );
		wp_enqueue_script('jquery');
		wp_enqueue_script(
			'sneeit-elementor-editor',
			plugins_url( '/js/editor.js', __FILE__ ),
			[
				'jquery',
			],
			'1.0.1',
			true
		);
	}

	/**
	 * Force load editor script as a module
	 *
	 * @since 1.2.1
	 *
	 * @param string $tag
	 * @param string $handle
	 *
	 * @return string
	 */
	public function editor_scripts_as_a_module( $tag, $handle ) {
		if ( 'sneeit-elementor-editor' === $handle ) {
			$tag = str_replace( '<script', '<script type="module"', $tag );
		}

		return $tag;
	}
	
	/**
	 * //////////////////////
	 * CONTROLS REGISTER AREA
	 */
	public function register_controls() {
		// Its is now safe to include Widgets files		
		require_once( 'sneeit-elementor-controls.php' );
		$controls_manager = \Elementor\Plugin::$instance->controls_manager;
		
		$controls_manager->register_control(
			Controls\Visual::type, new Controls\Visual() 
		);		
	}
	
	/**
	 * END of CONTROLS REGISTER AREA
	 * ////////////////////////////*/

	
	/**
	 * ///////////////////
	 * WIDGETS REGISTER AREA
	 */	

	/**
	 * Register Widgets
	 *
	 * Register new Elementor widgets.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function register_widgets() {		
		// Its is now safe to include Widgets files		
		require_once( 'sneeit-elementor-widgets.php' );
		
		
		// Register Widgets
		foreach ($this->shortcode_declaration['declarations'] as $shortcode_id => $shortcode_declaration) {
			
			if (empty($shortcode_declaration['display_callback']) ||
				'column' == $shortcode_id ||
				isset($shortcode_declaration['nested'])
			) {				
				continue;
			}
			
			$shortcode = new Widgets\Shortcodes();
			$shortcode->id = $shortcode_id;
			$shortcode->declaration = $shortcode_declaration;
			$shortcode->script = $this->shortcode_declaration['script'];
			
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( $shortcode );
		}
	}
	
	/**
	 * END of WIDGETS REGISTER AREA
	 * ////////////////////////////*/
	
	
	
	
	/**
	 * 
	 * @param type $elements_manager
	 */	
	public function register_category( $elements_manager ) {

		$elements_manager->add_category(
			$this->shortcode_declaration['script'],
			[
				'title' => $this->shortcode_declaration['title'],
				'icon' => 'fa fa-plug',
			]
		);
	}
	

	/**
	 * Add page settings controls
	 *
	 * Register new settings for a document page settings.
	 *
	 * @since 1.2.1
	 * @access private
	 */
	private function add_page_settings_controls() {
		require_once( 'sneeit-elementor-manager.php' );
		new Page_Settings();
	}

	

	/**
	 *  Plugin class constructor
	 *
	 * Register plugin action hooks and filters
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function __construct($Sneeit_ShortCodes) {
		$this->shortcode_declaration = $Sneeit_ShortCodes;
		// Register widget scripts
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'widget_scripts' ] );
		
		// Register controls
		add_action( 'elementor/controls/controls_registered', [ $this, 'register_controls' ] );
		

		// Register widgets
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );

		// Register editor scripts		
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'editor_scripts' ] );
		
		// Register specific category
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ]  );		

		$this->add_page_settings_controls();
	}
}
