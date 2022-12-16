<?php
define( 'SNEEIT_SOCIAL_API_KEY_COLLECTOR', 'sneeit-social-api-key-collector');
define( 'SNEEIT_SOCIAL_DEBUG', false);

global $Sneeit_Social_Api_Key_Collector;

require_once 'social-common.php';
require_once 'social-facebook.php';
require_once 'social-twitter.php';
require_once 'social-google-plus.php';
require_once 'social-instagram.php';
require_once 'social-pinterest.php';
require_once 'social-behance.php';
require_once 'social-youtube.php';
require_once 'social-vimeo.php';
require_once 'social-linkedin.php';


add_action('sneeit_social_api_key_collector_action', 'sneeit_social_api_key_collector_action');
function sneeit_social_api_key_collector_action( $args = array() ) {
	
}

global $Sneeit_Social_Api_Key_Collector;

function sneeit_social_api_key_collector_admin_menu() {
	global $Sneeit_Social_Api_Key_Collector;
	
	if (!isset($Sneeit_Social_Api_Key_Collector['menu-title'])) {
		$Sneeit_Social_Api_Key_Collector['menu-title'] = esc_html__('Social Api Keys', 'sneeit');
	}
	
	if (!isset($Sneeit_Social_Api_Key_Collector['page-title'])) {
		$Sneeit_Social_Api_Key_Collector['page-title'] = esc_html__('Social Api Keys', 'sneeit');
	}
	
	add_theme_page( 
		$Sneeit_Social_Api_Key_Collector['page-title'],
		$Sneeit_Social_Api_Key_Collector['menu-title'], 
		'manage_options',
		'sneeit-social-api-key-collector', 
		'sneeit_social_api_key_collector_html'
	);
}
function sneeit_social_api_key_collector_html() {
	global $Sneeit_Social_Api_Key_Collector;
	if (!isset($Sneeit_Social_Api_Key_Collector['page-title'])) {
		$Sneeit_Social_Api_Key_Collector['page-title'] = esc_html__('Theme Options', 'sneeit');
	}
	
	echo '<div class="wrap">'.
		'<h1>'.$Sneeit_Social_Api_Key_Collector['page-title'].'</h1>';
		if (isset($Sneeit_Social_Api_Key_Collector['html-before'])) {
			echo $Sneeit_Social_Api_Key_Collector['html-before'];
		}
				
		$args = wp_parse_args( $Sneeit_Social_Api_Key_Collector['declarations'], array(
			'facebook' => false,
			'twitter' => false,
			'google-plus' => false,
			'behance' => false,
			'youtube' => false,
			'vimeo' => false,
			'linkedin' => false,
			'instagram' => false,
		) );

		$nonce = false;
		if ( isset($_POST[SNEEIT_SOCIAL_API_KEY_COLLECTOR .'-nonce']) 
				&& wp_verify_nonce( $_POST[SNEEIT_SOCIAL_API_KEY_COLLECTOR .'-nonce'], SNEEIT_SOCIAL_API_KEY_COLLECTOR) 
				) {
			$nonce = true;
		}

		foreach ($args as $key => $value) {
			if ( $value && ! is_array( $value )) {
				$args[$key] = array();
			}			
		}
		?>

		<form method="post" action="" novalidate="novalidate">    
			<table class="form-table">
				<tbody>			
					<?php					
					foreach ( $args as $key => $value) {
						if ( is_array( $value ) ) {
							do_action( 'sneeit_'.$key.'_api_key_collector', $value );
						}
					}
					?>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Save All', 'sneeit'); ?>"/>
				<?php
					wp_nonce_field(SNEEIT_SOCIAL_API_KEY_COLLECTOR, SNEEIT_SOCIAL_API_KEY_COLLECTOR .'-nonce');
				?>
			</p>
		</form>

		<?php
	
		
		if (isset($Sneeit_Social_Api_Key_Collector['html-after'])) {
			echo $Sneeit_Social_Api_Key_Collector['html-after'];
		}
	echo '</div>';
}

add_action('sneeit_social_api_key_collector', 'sneeit_social_api_key_collector',  10, 1); // end of filter
function sneeit_social_api_key_collector($args) {
	// validate args
	if (!isset($args['declarations']) || !is_admin()) {
		return;
	}

	// save it
	global $Sneeit_Social_Api_Key_Collector;	
	$Sneeit_Social_Api_Key_Collector = $args;
	
	add_action( 'admin_menu', 'sneeit_social_api_key_collector_admin_menu');
}


