<?php
function sneeit_utilities_contact_wp_enqueue_scripts() {
	$rtl = '';
	if (is_rtl()) {
		$rtl = '-rtl';
	}
	
	wp_register_script('sneeit-utilities-contact-form', SNEEIT_PLUGIN_URL_JS . 'utilities-contact-form'.$rtl.SNEEIT_MIN_ENQUEUE.'.js', array('jquery'), SNEEIT_PLUGIN_VERSION, true);
}
add_action('wp_enqueue_scripts', 'sneeit_utilities_contact_wp_enqueue_scripts');


/**
 * Output a complete contact form for use within a theme.
 * @param array       $args {
 *     Optional. Default arguments and form fields to override.
 *
 *     @type mixed  $target_email  The email will be receive contact entries. Leave blank and admin will receive
 *     @type bool   $enable_name   Allow to show name field in contact form. Default true
 *     @type bool   $enable_url    Allow to show url field in contact form. Default false
 *     @type string $label_name    The translatable 'Name' field label. Default __('Name:', 'sneeit')
 *     @type string $label_email   The translatable 'Email' field label. Default __('Email:', 'sneeit')
 *     @type string $label_url     The translatable 'URL' field label. Default __('Website:', 'sneeit')
 *     @type string $label_content The translatable 'Content' field label. Default  __('Content:', 'sneeit')
 *     @type string $label_submit  The translatable 'Submit' button label. Default  __('Send Content', 'sneeit')
 *     @type string $id_form              The contact form element id attribute. Default 'sneeit_contact_form'.
 *     @type string $id_submit            The contact submit element id attribute. Default 'sneeit_contact_submit'.
 *     @type string $class_submit         The contact submit element class attribute. Default 'sneeit_contact_submit'.
 *     @type string $name_submit          The contact submit element name attribute. Default 'sneeit_contact_submit'.
 *     @type string $message_successful           The translatable 'Successful' message. 
 *										          Default  __('We received your contact. Thank you!', 'sneeit')
 *     @type string $message_required_email       The translatable 'required email' message. 
 *                                                Default  __('The email is required', 'sneeit')
 *     @type string $message_required_content     The translatable 'required content' message. 
 *                                                Default  __('The content is required', 'sneeit')
 *     @type string $message_short_content        The translatable for too short content. 
 *                                                Default  __('Your content is too short to submit', 'sneeit')
 *     @type int    $min_content_length           Minimum Content Length to allow submit. Default 20
 * }
 * @param int|WP_Post $post_id Post ID or WP_Post object to generate the form for. Default current post.
 */

function sneeit_utilities_contact_form ($args = array()) {
	extract(wp_parse_args($args, array(
		'target_email' => '',
		'enable_name' => true,
		'enable_url' => false,
		'label_name' => __('Name:', 'sneeit'),
		'label_email' => __('Email:  <span class="required">*</span>', 'sneeit'),
		'label_url' => __('Website:', 'sneeit'),
		'label_content' => __('Content:', 'sneeit'),
		'label_submit' => __('Send Content', 'sneeit'),
		'id_form' => 'sneeit-contact-form',
		'id_submit' => 'sneeit-contact-submit',
		'class_submit' => 'sneeit-contact-submit',
		'message_successful' => __('We received your contact. Thank you!', 'sneeit'),
		'message_required_email' => __('The email is required', 'sneeit'),
		'message_required_content' => __('The content is required', 'sneeit'),
		'message_short_content' => __('Your content is too short to submit', 'sneeit'),
		'min_content_length' => 20
	)));
	
	// validate arguments
	if (!$id_form) {
		$id_form = 'sneeit-contact-form';
	}
	$id_form = sneeit_title_to_slug($id_form);

	if (!$id_submit) {
		$id_submit = $id_form.'-submit';
	}
	$id_submit = sneeit_title_to_slug($id_submit);
	
	if (!$class_submit) {
		$class_submit = $id_form.'-submit';
	}	
	
	if ( !$target_email) {		
		$target_email = get_option('admin_email');
	}	

	// check cases and validate submit data
	$submitting = false;
	$email_error = '';
	$content_error = '';
	$cached = false;
	$sender_ip = sneeit_get_client_ip();
	$sender_name = '';
	$sender_url = '';
	$sender_email = '';
	$sender_content = '';
	
	if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'sneeit-contact-form')) {
		$submitting = true;
	}
	
	if (!$sender_ip) {
		$sender_ip = 'UNKNOWN';
	}
	if (get_transient($id_form.'-'.$sender_ip)) {
		$cached = true;
	}
	
	if ($submitting && !$cached) {
		// validate email
		if (!isset($_POST[$id_form.'-email'])) {
			$email_error = $message_required_email;
		} else {
			$sender_email = sanitize_email($_POST[$id_form.'-email']);
			if (!$sender_email || strpos($sender_email, '@') === false || strpos($sender_email, '@') < 1 || strpos($sender_email, '.') === false || strpos($sender_email, '.') < 3) {
				$email_error = $message_required_email;
			}
		}
		
		// validate content
		if (!isset($_POST[$id_form.'-content'])) {
			$content_error = $message_required_content;
		} else {
			$sender_content = sanitize_textarea_field($_POST[$id_form.'-content']);
			if (!$sender_content || strlen($sender_content) < $min_content_length) {
				$content_error = $message_short_content;
			}
		}

		// validate name
		if (!isset($_POST[$id_form.'-name'])) {
			$sender_name = 'Sneeit Contact Form';
		} else {
			$sender_name = sanitize_text_field($_POST[$id_form.'-name']);
		}

		// validate url
		if (isset($_POST[$id_form.'-url'])) {
			$sender_url = esc_url_raw($_POST[$id_form.'-url']);
		}		
	}
	if ($submitting && !$email_error && !$content_error || $cached)  {
		if (!$cached) {
			// send contact
			$subject = sprintf(__('[%1$s] contact message sent from: %2$s','sneeit'), get_bloginfo('name'), $sender_name);
			$eol="\n";
			$mime_boundary=md5(time());
			$headers = "From: ".$sender_email." <".$sender_email.">".$eol;
			//$headers .= "Reply-To: ".$email."<".$email.">".$eol;
			$headers .= "Message-ID: <".time()."-".$sender_email.">".$eol;
			$headers .= "X-Mailer: PHP v".phpversion().$eol;
			$headers .= 'MIME-Version: 1.0'.$eol;
			$headers .= "Content-Type: text/html; charset=UTF-8; boundary=\"".$mime_boundary."\"".$eol.$eol;

			$content = 
			'<div style="padding-bottom: 100px">'.
				$label_name . ' ' . $sender_name .
				$label_email . ' ' . $sender_email .
				(!empty($sender_url) ?
					$label_url . ' ' . $sender_url : ''
				).
			'</div>'.
			$label_content . ' ' . $sender_content;
			wp_mail( $target_email, $subject, $content, $headers);

			// and then processing cache here			
			set_transient($id_form.'-'.$sender_ip, time(), 60*5);
		}
		
		// successful message		
		return '<div class="'.$id_form.'-successful">'.$message_successful.'</div>';
	} else {
		return 
	'<form id="'.$id_form.'" class="'.$id_form.'" action="" method="post">'.		
		($enable_name ?
			'<p class="'.$id_form.'-name name">'.
				'<label for="'.$id_form.'-name">'.$label_name.' </label> '.
				'<input id="'.$id_form.'-name" name="'.$id_form.'-name" type="name" value="'.$sender_name.'" size="30">'.
			'</p>'
			:''
		).
		'<p class="'.$id_form.'-email">'.
			'<label for="'.$id_form.'-email email">'.$label_email.'</label> '.
			($email_error ? '<span class="'.$id_form.'-error '.$id_form.'-email-error">'.$email_error.'</span>' : '').
			'<input id="'.$id_form.'-email" name="'.$id_form.'-email" type="email" value="'.$sender_email.'" size="30" aria-describedby="email-notes" aria-required="true" required="required"/>'.
		'</p>'.
		($enable_url ?
			'<p class="'.$id_form.'-url">'.
				'<label for="'.$id_form.'-url url">'.$label_url.' </label> '.
				'<input id="'.$id_form.'-url" name="'.$id_form.'-url" type="url" value=""'.$sender_url.' size="30"/>'.
			'</p>'
			:''
		).	
		'<p class="'.$id_form.'-content content">'.
			'<label for="'.$id_form.'-content">'.$label_content.'</label> '.
			($content_error ? '<span class="'.$id_form.'-error sneeit-content-form-content-error">'.$content_error.'</span>' : '').
			'<textarea id="'.$id_form.'-content" name="'.$id_form.'-content" cols="45" rows="8" aria-required="true" required="required"></textarea>'.
		'</p>'.
		wp_nonce_field('sneeit-contact-form', '_wpnonce', true, false) .
		'<p class="'.$id_form.'-submit submit">'.
			'<input type="submit" id="'.$id_submit.'" name="'.$id_submit.'" class="'.$class_submit.' submit" value="'.$label_submit.'"/> '.		
		'</p>'.		
	'</form>';
	}
}
add_filter('sneeit_contact_form', 'sneeit_utilities_contact_form' , 1, 1);
