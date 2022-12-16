<?php
/* process submit data 
 * -------------------
 */
$error_message = '';
$user_name = '';
$api_key = '';

// only validate with current theme
$current_theme = wp_get_theme();
if (is_object($current_theme->parent())) {
	$current_theme = $current_theme->parent();
}
if (!isset($current_theme->stylesheet)) {
	$current_theme->stylesheet = 'global';
}
$theme_slug = $current_theme->stylesheet;

// get data from submit / option
if ( isset($_POST['envato-nonce']) 
		&& wp_verify_nonce( $_POST['envato-nonce'], SNEEIT_ENVATO_THEME_ACTIVATION) 
		&& isset($_POST['envato-username']) && $_POST['envato-username']) {
	$user_name = $_POST['envato-username'];
} else {
	$user_name = get_option(SNEEIT_ENVATO_OPT_USER_NAME.'-'.$theme_slug, '');
}


if ( isset( $_POST['envato-nonce'] ) 
		&& wp_verify_nonce( $_POST['envato-nonce'], SNEEIT_ENVATO_THEME_ACTIVATION ) 
		&& isset( $_POST['envato-key'] ) && $_POST['envato-key'] ) {

	$api_key = $_POST['envato-key'];		
} else {
	$api_key = get_option(SNEEIT_ENVATO_OPT_API_KEY.'-'.$theme_slug, '');
}

// validate data
if ( $user_name && $api_key ) {
	require_once 'envato-class-protected-api.php';	
	$envato_api = new Envato_Protected_API( $user_name, $api_key );

	if ($envato_api) {		
		$list_themes = $envato_api->private_user_data( 'wp-list-themes' );				
		
		// raise error
		if ( ! $list_themes || !is_array($list_themes) || ! empty( $list_themes['api_error'] ) ) {
			if ( empty( $list_themes['api_error'] ) ) {
				$error_message = __('Operation timed out with 0 bytes received.', 'sneeit');
			} else {
				$error_message = $list_themes['api_error'];
			}
			
			add_settings_error(SNEEIT_ENVATO_THEME_ACTIVATION, 'update_result', $error_message, 'error');			
		} 
		elseif ( isset( $_POST['envato-nonce'] ) && 
				wp_verify_nonce( $_POST['envato-nonce'], SNEEIT_ENVATO_THEME_ACTIVATION ) ) {

			// check if this theme is in purchase list and the 
			$purchased_theme = false;
//			var_dump($current_theme);
//			var_dump($current_theme->parent() );
//			$p_theme = $current_theme->parent();
//			var_dump($p_theme->get('Name'));
			
			foreach ($list_themes as $theme) {
				
				if (is_object($theme) && 
					property_exists($theme, 'theme_name') && ( 
						strtolower($theme->theme_name) == strtolower($theme_slug) ||
						strtolower($theme->theme_name) == strtolower($current_theme->get( 'Name' )) ||
						strtolower($theme->theme_name) == strtolower($current_theme->get('TextDomain'))
					)
				) {
					$purchased_theme = true;
					break;
				}
			}

			if ( $purchased_theme ) {
				// save data if new submit and validated
				update_option(SNEEIT_ENVATO_OPT_USER_NAME . '-' . $theme_slug, $user_name);
				update_option(SNEEIT_ENVATO_OPT_API_KEY . '-' .$theme_slug, $api_key);

				$error_message = __('Theme Activated.', 'sneeit');
				add_settings_error(SNEEIT_ENVATO_THEME_ACTIVATION, 'update_result', $error_message, 'updated');
			} else {
				if (!empty($list_themes['http_code']) && $list_themes['http_code'] == 403) {
					$error_message = __('Forbidden Username or API Key', 'sneeit');
				} else {
					$error_message = __('You did not purchase this theme', 'sneeit');
				}				
				add_settings_error(SNEEIT_ENVATO_THEME_ACTIVATION, 'update_result', $error_message, 'error');				
			}
		}
	} /*check envato API*/
}

/* Form and HTML output
* --------------------
*/
if ($error_message) {
settings_errors(SNEEIT_ENVATO_THEME_ACTIVATION);
}

?>

<form method="post" action="" novalidate="novalidate">    
<table class="form-table">
	<tbody>			
		<tr>
			<th scope="row">
				<label for="envato-username"><?php esc_html_e('Envato / ThemeForest User Name', 'sneeit'); ?></label>
			</th>
			<td>
				<input name="envato-username" type="text" id="envato-username" value="<?php echo esc_attr($user_name); ?>" class="regular-text"/>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="envato-key"><?php esc_html_e('Envato / ThemeForest API Key', 'sneeit'); ?></label>
			</th>
			<td>
				<input name="envato-key" type="text" id="envato-key" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
				<p class="description" id="envato-key-description">
					<a href="<?php echo esc_url(SNEEIT_PLUGIN_URL_IMAGES .'sneeit-themeforest-generate-api-key.png');?>" target="_blank">
						<?php esc_html_e('How to get Envato / Themeforest API Key?', 'sneeit'); ?>
					</a>
				</p>
			</td>
		</tr>

	</tbody>
</table>
<p class="submit">
	<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Validate Now', 'sneeit'); ?>"/>
	<?php
		wp_nonce_field(SNEEIT_ENVATO_THEME_ACTIVATION, 'envato-nonce');
	?>
</p>
</form>