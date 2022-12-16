<?php
global $Sneeit_Setup_Compact_Menu_Args;

add_action('sneeit_setup_compact_menu', 'sneeit_setup_compact_menu_action', 2);
function sneeit_setup_compact_menu_action($args = array()) {
	global $Sneeit_Setup_Compact_Menu_Args;	
	$Sneeit_Setup_Compact_Menu_Args = array();
	
	$enable_mega = false;
	
	// fill menu declarations
	foreach ($args as $theme_location => $settings) {
		if (!$enable_mega && !empty($settings['mega_block_display_callback'])) {
			$enable_mega = true;
		}
		$Sneeit_Setup_Compact_Menu_Args[$theme_location] = wp_parse_args($settings, array(
			/* GENERAL DESIGN */
			/* the extend class of container tag */
			'container_class' => '', 
			/* the extended id of container tag, 
			 * if missing, use the theme location id */		
			'container_id' => 'sneeit-compact-menu-'.$theme_location,
			
			/**/
			'main_level_icon_down'  => 'fa-angle-down',
			'sub_level_icon_down' => 'fa-angle-right',

			/* MEGA MENU CONTENT */
			/* function to display mega content when ajax sent*/
			'mega_block_display_callback' => '',		

			/* sticky menu */
			/* disable, up (show when scroll up), down (show when scroll down), always */
			'sticky_enable' => 'disable',
			/* the logo of sticky menu, stay at beginning of menu*/
			'sticky_logo' => '',
			/* the logo of sitkcy menu for retina screens*/
			'sticky_logo_retina' => '',
			
			/* ! IF HOLDER OR SCROLLER IS CSS DISPLAY NONE, STICKY WILL STOP THEN */
			/* the elements that will be clone to hold the position when floating
			 * sneeit will base on it's height to make a fake div to hold position */
			'sticky_holder' => '',
			/* the elements that will be float*/
			'sticky_scroller' => '',
			
			/* MOBILE MENU */	
			/* enable or disable mobile menu clone */
			'mobile_enable' => true,
			/* the container where the mobile menu will be append to */
			'mobile_container' => '.fn-mob-menu-box',
		));
		$Sneeit_Setup_Compact_Menu_Args[$theme_location]['container_class'] .= (' sneeit-compact-menu sneeit-compact-menu-'.$theme_location) ;
		if ($Sneeit_Setup_Compact_Menu_Args[$theme_location]['sticky_enable'] != 'disable') {
			if (!$Sneeit_Setup_Compact_Menu_Args[$theme_location]['sticky_holder']) {
				$Sneeit_Setup_Compact_Menu_Args[$theme_location]['sticky_holder'] = 
					'#'.$Sneeit_Setup_Compact_Menu_Args[$theme_location]['container_id'];
			}
			if (!$Sneeit_Setup_Compact_Menu_Args[$theme_location]['sticky_scroller']) {
				$Sneeit_Setup_Compact_Menu_Args[$theme_location]['sticky_scroller'] = 
					'#'.$Sneeit_Setup_Compact_Menu_Args[$theme_location]['container_id'];
			}
		}	

	}
	
	/* set up meta fields for menu item (back-end)
	 */	
	$sneeit_compact_menu_fields = sneeit_validate_menu_fields_declaration(array(
		'enable_mega' => array(
			'label' => esc_html__('Enable Mega Menu', 'sneeit'),
			'description' => esc_html__('If this menu item is a category, posts will show when you hover it and its sub menu items (if they are categories also). If this item is not a category, sub menu items will show as group links', 'sneeit'),
			'type' => 'checkbox',
			'default' => false,
			'depth' => 0 /*display specific for depth level 0 */
		),
		'show_hide_for_users' => array(
			'label' => esc_html__('Show / Hide Menu for Users', 'sneeit'),
			'description' => esc_html__('Usually use to create login / logout or register links', 'sneeit'),
			'type' => 'select',
			'default' => '',
			'choices' => array(
				'' => esc_html__('Show for All Users', 'sneeit'),
				'logged-in' => esc_html__('Show for Logged in Users only', 'sneeit'),
				'logged-out' => esc_html__('Show for Logged out Users only', 'sneeit'),
			),
		),
		'color' => array(
			'label' => esc_html__('Menu Item Text Color', 'sneeit'),
			'type' => 'color'
		),
		'bg_color' => array(
			'label' => esc_html__('Menu Item Background Color', 'sneeit'),
			'type' => 'color'
		),
		'icon_before' => array(
			'label' => esc_html__('Icon Code Before Text', 'sneeit'),
			'description' => wp_kses(
				sprintf(__('Example: fa-home. <a href="%s" target="_blank">Check Full List of Icon Codes Here</a>', 'sneeit'), esc_url('https://fortawesome.github.io/Font-Awesome/icons/')),
				array(
					'a' => array(
						'href' => array(),
						'target' => array()
					)
				)
			)
		),
		'icon_after' => array(
			'label' => esc_html__('Icon Code After Text', 'sneeit'),
			'description' => wp_kses(
				sprintf(__('Example: fa-angle-down. <a href="%s" target="_blank">Check Full List of Icon Codes Here</a>', 'sneeit'), esc_url('https://fortawesome.github.io/Font-Awesome/icons/')),
				array(
					'a' => array(
						'href' => array(),
						'target' => array()
					)
				)
			)
		),
		'badge_text' => array(
			'label' => esc_html__('Badge Text', 'sneeit'),			
		),
		'badge_color' => array(
			'label' => esc_html__('Badge Text Color', 'sneeit'),			
			'type' => 'color'
		),
		'badge_bg' => array(
			'label' => esc_html__('Badge Background Color', 'sneeit'),			
			'type' => 'color'
		),
	));
		
	if (!$enable_mega) {
		unset($sneeit_compact_menu_fields['enable_mega']);		
	}
	sneeit_menus_init_setup_menu_fields($sneeit_compact_menu_fields);
	
	add_action( 'wp_enqueue_scripts', 'sneeit_compact_menu_enqueue', 1 );
	
	if (is_admin()) :
		add_action( 'wp_ajax_nopriv_sneeit_compact_menu_mega_content', 'sneeit_compact_menu_mega_content_callback' );
		add_action( 'wp_ajax_sneeit_compact_menu_mega_content', 'sneeit_compact_menu_mega_content_callback' );
	endif;// is_admin for ajax	
}

function sneeit_compact_menu_enqueue() {
	global $Sneeit_Setup_Compact_Menu_Args;
	
	wp_enqueue_style( 'sneeit-compact-menu', 
		sneeit_front_enqueue_url('front-menus-compact.css'), 
		array(), 
		SNEEIT_PLUGIN_VERSION 
	);
	wp_enqueue_script( 'sneeit-compact-menu', 
		sneeit_front_enqueue_url('front-menus-compact.js'), 
		array( 'jquery'), 
		SNEEIT_PLUGIN_VERSION, 
		true 
	);
	$Sneeit_Setup_Compact_Menu_Args['ajax_url'] = admin_url('admin-ajax.php');	
	
	wp_localize_script('sneeit-compact-menu', 'Sneeit_Compact_Menu', $Sneeit_Setup_Compact_Menu_Args);		
}

class Sneeit_Compact_Menu_Walker extends Walker_Nav_Menu {
	var $main_level_icon_down = '';
	var $sub_level_icon_down = '';
	
	function __construct($args = array()) {
		$this->main_level_icon_down = $args['main_level_icon_down'];
		$this->sub_level_icon_down = $args['sub_level_icon_down'];
	}
	
	/**
	 * Starts the list before the elements are added.
	 *
	 * @since 3.0.0
	 *
	 * @see Walker::start_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of wp_nav_menu() arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "\n$indent<ul class=\"sub-menu\">\n";
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @since 3.0.0
	 *
	 * @see Walker::end_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of wp_nav_menu() arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	/**
	 * Starts the element output.
	 *
	 * @since 3.0.0
	 * @since 4.4.0 The {@see 'nav_menu_item_args'} filter was added.
	 *
	 * @see Walker::start_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An Object of wp_nav_menu() arguments.
	 * @param int    $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;		
		
		
		/* Sneeit Compact Menu Classes Start Here */		
		if (get_post_meta($item->ID, 'enable_mega', true) && $depth == 0) {
			array_push($classes, 'menu-item-mega');
			if ($item->object == 'category') {				
				array_push($classes, 'menu-item-mega-category');
			} else {
				array_push($classes, 'menu-item-mega-link');
			}
		}
		$show_hide_for_users  = get_post_meta($item->ID, 'show_hide_for_users', true);
		if ('logged-in' == $show_hide_for_users) {
			array_push($classes, 'menu-item-show-when-logged-in');
		}
		if ('logged-out' == $show_hide_for_users) {
			array_push($classes, 'menu-item-show-when-logged-out');
		}
		/* Sneeit Compact Menu Classes End Here */
		
		


		/**
		 * Filters the arguments for a single nav menu item.
		 *
		 * @since 4.4.0
		 *
		 * @param array  $args  An array of arguments.
		 * @param object $item  Menu item data object.
		 * @param int    $depth Depth of menu item. Used for padding.
		 */
		$args = apply_filters( 'nav_menu_item_args', $args, $item, $depth );
				
		/**
		 * Filters the CSS class(es) applied to a menu item's list item element.
		 *
		 * @since 3.0.0
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param array  $classes The CSS classes that are applied to the menu item's `<li>` element.
		 * @param object $item    The current menu item.
		 * @param array  $args    An array of wp_nav_menu() arguments.
		 * @param int    $depth   Depth of menu item. Used for padding.
		 */
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		/**
		 * Filters the ID applied to a menu item's list item element.
		 *
		 * @since 3.0.1
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param string $menu_id The ID that is applied to the menu item's `<li>` element.
		 * @param object $item    The current menu item.
		 * @param array  $args    An Object of wp_nav_menu() arguments.
		 * @param int    $depth   Depth of menu item. Used for padding.
		 */
		$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args, $depth );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li' . $id . $class_names .'>';

		$atts = array();
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target )     ? $item->target     : '';
		$atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
		$atts['href']   = ! empty( $item->url )        ? $item->url        : '';
		
		/* Sneeit Compact Menu Attribute Start Here */
		if (get_post_meta($item->ID, 'enable_mega', true) && $item->object == 'category' && $depth == 0) {
			$atts['data-cat'] = esc_attr($item->object_id);
		}
		if ($item->object == 'category') {
			$atts['data-id'] = esc_attr($item->ID);
		}
		
		if (get_post_meta($item->ID, 'color', true)) {
			if (!isset($atts['style'])) {
				$atts['style'] = '';
			}
			$atts['style'] .= esc_attr('color:'.get_post_meta($item->ID, 'color', true).';');
		}
		if (get_post_meta($item->ID, 'bg_color', true)) {
			if (!isset($atts['style'])) {
				$atts['style'] = '';
			}
			$atts['style'] .= esc_attr('background:'.get_post_meta($item->ID, 'bg_color', true).';');
		}
		// add data id into category items
		if ($item->object == 'category') {
			$atts['data-cat'] = esc_attr($item->object_id);
		}
		
		/* Sneeit Compact Menu Attribute End Here */
		
		/* Sneeit Compact Menu Extra Texts Start Here */
		$sneeit_compact_link_before = '';
		$sneeit_compact_link_after = '';
		if (get_post_meta($item->ID, 'icon_before', true)) {
			$sneeit_compact_link_before .= '<span class="icon-before">'.sneeit_font_awesome_tag(get_post_meta($item->ID, 'icon_before', true)) . '</span> ';
		}
		
		if (get_post_meta($item->ID, 'badge_text', true)) {
			$sneeit_compact_link_after .= ' <span class="badge"';
			$badge_style = '';
			if (get_post_meta($item->ID, 'badge_color', true)) {
				$badge_style .= 'color:'.get_post_meta($item->ID, 'badge_color', true).';';
			}
			if (get_post_meta($item->ID, 'badge_bg', true)) {
				$badge_style .= 'background:'.get_post_meta($item->ID, 'badge_bg', true).';';
			}
			if ($badge_style) {
				$sneeit_compact_link_after .= ' style="'.$badge_style.'"';
			}
			$sneeit_compact_link_after .= '>' .get_post_meta($item->ID, 'badge_text', true). '</span>';
		}		
		
		$icon_after = get_post_meta($item->ID, 'icon_after', true);
		
		if ($this->has_children && !$icon_after) {
			if ($depth) {
				$icon_after = $this->sub_level_icon_down;
			} else {
				$icon_after = $this->main_level_icon_down;
			}
		}
		
		if ($icon_after) {
			$sneeit_compact_link_after .= ' <span class="icon-after">'.sneeit_font_awesome_tag($icon_after) . '</span>';
		}
		
						
		/* Sneeit Compact Menu Extra Texts End Here */
		

		/**
		 * Filters the HTML attributes applied to a menu item's anchor element.
		 *
		 * @since 3.6.0
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param array $atts {
		 *     The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
		 *
		 *     @type string $title  Title attribute.
		 *     @type string $target Target attribute.
		 *     @type string $rel    The rel attribute.
		 *     @type string $href   The href attribute.
		 * }
		 * @param object $item  The current menu item.
		 * @param array  $args  An array of wp_nav_menu() arguments.
		 * @param int    $depth Depth of menu item. Used for padding.
		 */
		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		/** This filter is documented in wp-includes/post-template.php */
		$title = apply_filters( 'the_title', $item->title, $item->ID );

		/**
		 * Filters a menu item's title.
		 *
		 * @since 4.4.0
		 *
		 * @param string $title The menu item's title.
		 * @param object $item  The current menu item.
		 * @param array  $args  An array of wp_nav_menu() arguments.
		 * @param int    $depth Depth of menu item. Used for padding.
		 */
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );
		$args = (object) $args;
		$item_output = $args->before;
		$item_output .= '<a'. $attributes .'>';
		$item_output .= $sneeit_compact_link_before;
		$item_output .= $args->link_before . $title . $args->link_after;
		$item_output .= $sneeit_compact_link_after;
		$item_output .= '</a>';
		$item_output .= $args->after;
		$item_output .= '<div class="menu-item-inner">';
				
		
		/**
		 * Filters a menu item's starting output.
		 *
		 * The menu item's starting output only includes `$args->before`, the opening `<a>`,
		 * the menu item's title, the closing `</a>`, and `$args->after`. Currently, there is
		 * no filter for modifying the opening and closing `<li>` for a menu item.
		 *
		 * @since 3.0.0
		 *
		 * @param string $item_output The menu item's starting HTML output.
		 * @param object $item        Menu item data object.
		 * @param int    $depth       Depth of menu item. Used for padding.
		 * @param array  $args        An array of wp_nav_menu() arguments.
		 */
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @since 3.0.0
	 *
	 * @see Walker::end_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Page data object. Not used.
	 * @param int    $depth  Depth of page. Not Used.
	 * @param array  $args   An array of wp_nav_menu() arguments.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = array() ) {		
		if (get_post_meta($item->ID, 'enable_mega', true) && $item->object == 'category' && $depth == 0) {
			$output .= '<div class="menu-mega-block"><div class="menu-mega-block-content"><div class="menu-mega-block-content-inner"></div><span class="menu-mega-block-loading"><i class="fa fa-spin fa-spinner"></i></span></div></div><div class="menu-mega-block-bg"></div>';// .menu-mega-block
		}
		$output .= '<div class="clear"></div></div></li>'; // .menu-item-inner
	}
}

add_action('sneeit_display_compact_menu', 'sneeit_display_compact_menu_action');
function sneeit_display_compact_menu_action($theme_location) {
	if (!has_nav_menu($theme_location)) {
		return;
	}
	global $Sneeit_Setup_Compact_Menu_Args;
	if (empty($Sneeit_Setup_Compact_Menu_Args[$theme_location])) {
		return;
	}
	
	// validate args
	////////////////
	$args = $Sneeit_Setup_Compact_Menu_Args[$theme_location];
		
	// output menu	
	echo '<nav id="'.$args['container_id'].'" class="'.$args['container_class'].'">';
	
	if ($args['sticky_enable'] != 'disable' && $args['sticky_logo']) :		
		?><a href="<?php echo esc_url(home_url());?>" class="sneeit-compact-menu-sticky-logo <?php echo $theme_location . '-sticky-menu-logo' ; ?>">
			<img alt="<?php echo esc_attr(bloginfo('name')); ?>" src="<?php echo esc_attr($args['sticky_logo']); ?>"<?php
			if (empty($args['sticky_logo_retina'])) : 
				?> data-retina="<?php echo esc_attr($args['sticky_logo_retina']); ?>"<?php 
			endif; ?>/>
		</a><?php
	endif;
		
	wp_nav_menu(array(
		'theme_location' => $theme_location,
		'container' => '',		
		'walker' => new Sneeit_Compact_Menu_Walker($args)
	));
	echo '</nav>';
}

function sneeit_compact_menu_mega_content_callback() {		
	$callback = sneeit_get_server_request('callback');
	
	if (function_exists($callback)) {
		$args = sneeit_get_server_request('args');
		$args = json_decode( trim( wp_unslash( $args ) ), true );
		$args['block_id'] = 'sneeit-menu-mega-content-'.$args['item_id'];	
		echo call_user_func($callback, $args);
	}
	die();
}
