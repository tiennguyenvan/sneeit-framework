<?php

add_filter('sneeit_articles_pagination_fields', 'sneeit_articles_pagination_fields');
function sneeit_articles_pagination_fields($args = array()) {
	return array(
		'pagination' => array(
			'label' => esc_html__('Pagination', 'sneeit'), 		
			'description' => esc_html__('Depending on your post queries, the pagination may be not displayed if your query have no any paged permalink', 'sneeit'), 		
			'type' => 'select', 
			'choices' => array(
				'' => esc_html__('None', 'sneeit'), 
				'number-ajax' => esc_html__('Number Ajax Load', 'sneeit'), 
				'number-reload' => esc_html__('Number Full Reload', 'sneeit'), 
				'loadmore' => esc_html__('Loadmore', 'sneeit'), 
				'infinite' => esc_html__('Infinite Scroll', 'sneeit'), 
				'nextprev-ajax' => esc_html__('Next / Previous Ajax Load', 'sneeit'),
				'nextprev-reload' => esc_html__('Next / Previous Full Reload', 'sneeit')
			),
			'default' => '',
			'heading' => esc_html__('Block Pagination', 'sneeit')
		),
	);
}

add_action('sneeit_articles_pagination', 'sneeit_articles_pagination');
function sneeit_articles_pagination($site_args) {
	if (empty($site_args['ajax_handler']) ||
		empty($site_args['pagination_container']) ||
		empty($site_args['content_container'])) {
		return ;
	}
	
	global $Sneeit_Articles_Pagination_Script;
	$Sneeit_Articles_Pagination_Script = array(
		'site_args' => $site_args,
		'block_args' => array(),
		'ajax_url' => admin_url( 'admin-ajax.php' )
	);
	
	add_filter('sneeit_articles_query_args', 'sneeit_articles_pagination_block_args');
	add_action('wp_enqueue_scripts', 'sneeit_articles_pagination_register');
	add_action('wp_footer', 'sneeit_articles_pagination_enqueue', 1);
	
	if (is_admin()) :
		add_action( 'wp_ajax_nopriv_sneeit_articles_pagination', 'sneeit_articles_pagination_callback' );
		add_action( 'wp_ajax_sneeit_articles_pagination', 'sneeit_articles_pagination_callback' );
		
		add_action( 'wp_ajax_nopriv_sneeit_articles_pagination_redirect', 'sneeit_articles_pagination_redirect_callback' );
		add_action( 'wp_ajax_sneeit_articles_pagination_redirect', 'sneeit_articles_pagination_redirect_callback' );
	endif;// is_admin for ajax
}

function sneeit_articles_pagination_callback() {	
	$callback = sneeit_get_server_request('callback');
	
	if (function_exists($callback)) {
		$args = sneeit_get_server_request('args');
		$args = json_decode( trim( wp_unslash( $args ) ), true );
		call_user_func($callback, $args);
	}
	die();
}
function sneeit_articles_pagination_redirect_callback() {	
	$args = sneeit_get_server_request('args');
	$args = json_decode( trim( wp_unslash( $args ) ), true );		
	echo sneeit_articles_archive_link($args);
	die();
}


function sneeit_articles_pagination_block_args($block_args) {
	if (!is_array($block_args) ||
		!is_array($block_args['args']) ||
		empty($block_args['args']) || 
		empty($block_args['args']['pagination']) ||
		empty($block_args['args']['block_id']) ||
		empty($block_args['args']['count']) || 
		empty($block_args['found_posts']) ||
		$block_args['args']['count'] > $block_args['found_posts']) {
		return '';
	}
	
	// process block args if full reload
	if ($block_args['args']['pagination'] == 'number-reload' ||
		$block_args['args']['pagination'] == 'nextprev-reload') {
		$block_args['args']['count'] = get_option('posts_per_page', $block_args['args']['count']);
	}
	
	global $Sneeit_Articles_Pagination_Script;	
		
	$block_id = $block_args['args']['block_id'];		
	$Sneeit_Articles_Pagination_Script['block_args'][$block_id] = $block_args;
	
	if (!empty($block_args['args']['menu_item_id'])) {		
		foreach ( (array) $block_args as $key => $value ) {
			if ( !is_scalar($value) )
				continue;

			$block_args[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
		}

		return '<script type="text/javascript">Sneeit_Articles_Pagination["block_args"]["'.$block_id.'"] = ' . wp_json_encode( $block_args ) . ';</script>';
	}
		
	return '';
}


function sneeit_articles_pagination_register() {	
	wp_register_script('sneeit-articles-pagination', sneeit_front_enqueue_url('front-articles-pagination.js'), array('jquery'), SNEEIT_PLUGIN_VERSION, true);
}
function sneeit_articles_pagination_enqueue() {
	global $Sneeit_Articles_Pagination_Script;		
	
	if (empty($Sneeit_Articles_Pagination_Script['block_args'])) {
		unset($Sneeit_Articles_Pagination_Script['block_args']);
	}
	wp_enqueue_script('sneeit-articles-pagination');
	
	wp_localize_script( 'sneeit-articles-pagination', 'Sneeit_Articles_Pagination', $Sneeit_Articles_Pagination_Script);
	
}