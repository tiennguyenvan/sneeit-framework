<?php
/* process submit data 
 * -------------------
 */
$user_name = '';
$license_key = '';

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
$form_submit =	isset( $_POST['sneeit-nonce'] ) && 
				wp_verify_nonce( $_POST['sneeit-nonce'], SNEEIT_SNEEIT_THEME_ACTIVATION );
if ( $form_submit && isset($_POST['sneeit-username']) && $_POST['sneeit-username']) {
	$user_name = $_POST['sneeit-username'];
} else {
	$user_name = get_option(SNEEIT_SNEEIT_OPT_USER_NAME.'-'.$theme_slug, '');
}

if ( $form_submit && isset( $_POST['sneeit-key'] ) && $_POST['sneeit-key'] ) {

	$license_key = $_POST['sneeit-key'];		
} else {
	$license_key = get_option(SNEEIT_SNEEIT_OPT_LICENSE_KEY.'-'.$theme_slug, '');
}

// validate data
if ( $user_name && $license_key ) {
	require_once 'sneeit-theme-api.php';
	
	$theme_update = sneeit_sneeit_theme_api($user_name, $license_key, $theme_slug);
	if (is_string($theme_update)) {
		add_settings_error(SNEEIT_SNEEIT_THEME_ACTIVATION, 'update_result', $theme_update, 'error');
		settings_errors(SNEEIT_SNEEIT_THEME_ACTIVATION);
	} else if ($form_submit) {
		update_option(SNEEIT_SNEEIT_OPT_USER_NAME . '-' . $theme_slug, $user_name);
		update_option(SNEEIT_SNEEIT_OPT_LICENSE_KEY . '-' .$theme_slug, $license_key);
	}
}

/* Form and HTML output
* --------------------
*/
?>

<form method="post" action="" novalidate="novalidate">    
<table class="form-table">
	<tbody>			
		<tr>
			<th scope="row">
				<label for="sneeit-username">
					<?php esc_html_e('Sneeit User Name', 'sneeit'); ?>
					<a href="<?php echo esc_url('sneeit.com/wp-login.php');?>" target="_blank">
						(login)
					</a>
				</label>
			</th>
			<td>
				<input name="sneeit-username" type="text" id="sneeit-username" value="<?php echo esc_attr($user_name); ?>" class="regular-text"/>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="sneeit-key">
					<?php esc_html_e('Sneeit License Key', 'sneeit'); ?>
					<a href="<?php echo esc_url(SNEEIT_PLUGIN_URL_IMAGES .'sneeit-sneeit-generate-license-key.jpg');?>" target="_blank">
						(how?)
					</a>
				</label>
			</th>
			<td>
				<input name="sneeit-key" type="text" id="sneeit-key" value="<?php echo esc_attr($license_key); ?>" class="regular-text">
			</td>
		</tr>

	</tbody>
</table>
<p class="submit">
	<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Validate Now', 'sneeit'); ?>"/>
	<?php
		wp_nonce_field(SNEEIT_SNEEIT_THEME_ACTIVATION, 'sneeit-nonce');
	?>
</p>
</form>