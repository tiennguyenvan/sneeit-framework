<?php
add_action('sneeit_setup_responsive', 'sneeit_setup_responsive');
function sneeit_setup_responsive($args = array()) {
	
	global $Sneeit_Responsive;
	
	$Sneeit_Responsive = wp_parse_args($args, array(
		/* url of your logo image */
		'logo' => '',		
		'logo_retina' => '',		
		
		/* icon for toggle buttons, you can use images */
		'left_icon' => 'fa-bars', 		
		'right_icon' => 'fa-search', 
		
		/* the action that will happen after click a toggle button
		 * of the header. Ex: slide out an box from above of header
		 * 
		 * slide-under, slide-above, slide-left, slide-right
		 * don't want slide our default boxes, you can select
		 * by inputting, effect:box-selector
		 * ex: slide:.your-selector
		 */
		'left_action' => 'slide-under', 
		'right_action' => 'slide-above', 
		
		/* allow the responsive header can sticky or not
		 * values: up, down, always, disable 
		 */
		'sticky_enable' => 'up', 
		
		/* you can use function name for this
		 * you can also use template with short tags
		 * ex: clone an element: [clone:.your-selector]
		 * ex: move a element: [move:.your-selector]
		 * others: [logo] [toggle-left] [toggle-right]
		 */
		'header_content' => '[toggle-left][logo][toggle-right]',
		
		/* content that will show after clicking toggle left button */
		'left_content' => '', 
		
		/* content that will show after clicking toggle right button */
		'right_content' => '',
		
		/* classes */
		'header_content_class' => '',
		'left_content_class' => '', 
		'right_content_class' => '', 
		'logo_class' => '', 
		'left_icon_class' => '',
		'right_icon_class' => '',
	));
	
	
	
	if (empty($Sneeit_Responsive['header_content'])) {
		return;
	}
	
	// validate classes
	$Sneeit_Responsive['header_content_class'] .= ' sneeit-mob-header';	
	$Sneeit_Responsive['left_content_class'] .= ' sneeit-mob-ctn sneeit-mob-ctn-left';	
	$Sneeit_Responsive['right_content_class'] .= ' sneeit-mob-ctn sneeit-mob-ctn-right';	
	$Sneeit_Responsive['logo_class'] .= ' sneeit-mob-logo';	
	$Sneeit_Responsive['left_icon_class'] .= ' sneeit-mob-tgl sneeit-mob-tgl-left';	
	$Sneeit_Responsive['right_icon_class'] .= ' sneeit-mob-tgl sneeit-mob-tgl-right';	
	
	add_action('wp_enqueue_scripts', 'sneeit_responsive_enqueue');
}

add_action('sneeit_display_responsive', 'sneeit_display_responsive');
function sneeit_display_responsive_smart_tags($content = '', $tag = 'clone') {
	if (strpos($content, '['.$tag.':') !== false) {
		$content = explode('['.$tag.':', $content);
		foreach ($content as $key => $value) {
			if (strpos($value, ']') !== false) {
				$value = explode(']', $value);
				$content[$key] = '<div class="sneeit-mob-ctn-'.$tag.'" data-'.$tag.'="'.esc_attr($value[0]).'"></div>'.$value[1];
			} 
			/* forgot close the square bracket*/
			else { 
				$value = '';
			}
		}
		$content = implode('', $content);
	}
	
	return $content;
}
function sneeit_display_responsive_smart_tags_replace($content) {
	$content = sneeit_display_responsive_smart_tags($content, 'clone');
	$content = sneeit_display_responsive_smart_tags($content, 'move');
	
	return $content;
}
function sneeit_display_responsive() {
	global $Sneeit_Responsive;
	
	if (empty($Sneeit_Responsive['header_content'])) {
		return;
	}
	
	extract($Sneeit_Responsive);
	
	$html = '';	
	
	// content for head template
	// - fill header if has callback
	if (function_exists($header_content)) {
		$header_content = call_user_func($header_content);
	}
	
	// - replace header clone & move elemenets
	$header_content = sneeit_display_responsive_smart_tags_replace($header_content);
	
	
	// - header logo	
	if (strpos($header_content, '[logo]') !== false) {
		$logo_html = '<a href="'.home_url().'" class="'.esc_attr($logo_class).'">';		
		if (sneeit_is_image_src($logo)) {
			$logo_html .= '<img alt="'.esc_attr(get_bloginfo('name')).'" src="'.esc_attr($logo).'"';
			if (sneeit_is_image_src($logo_retina)) {
				$logo_html .= ' data-retina="'.  esc_attr($logo_retina).'"';
			}			
			$logo_html .= '/>';
		} else {
			$logo_html .= get_bloginfo('name');
		}
		$logo_html .= '</a>';
		$header_content = str_replace('[logo]', $logo_html, $header_content);
	}
	
	// - left toggle and box content
	if (strpos($header_content, '[toggle-left]') !== false) {
		if (!empty($left_icon) && !empty($left_action) && !empty($left_content)) {
			$left = '<a href="javascript:void(0)" class="'. esc_attr($left_icon_class).'"><span class="sneeit-mob-icon">';
			if (sneeit_is_image_src($left_icon)) {
				$left .= '<img alt="tgl-btn" src="'.esc_attr($left_icon).'"/>';
			} else {
				$left .= sneeit_font_awesome_tag($left_icon);
			}
			$left .= '</span></a>';
			
			// content for left box
			// - for callback action
			if (function_exists($left_content)) {
				$left_content = call_user_func($left_content);
			}
			// - replace clone & move elemenets
			$left_content = sneeit_display_responsive_smart_tags_replace($left_content);

		} else {
			$left = '';
			$left_content = '';
		}				
		$header_content = str_replace('[toggle-left]', $left, $header_content);				
	}
	
	// - right toggle and box content
	if (strpos($header_content, '[toggle-right]') !== false) {
		if (!empty($right_icon) && !empty($right_action) && !empty($right_content)) {
			$right = '<a href="javascript:void(0)" class="'. esc_attr($right_icon_class).'"><span class="sneeit-mob-icon">';
			if (sneeit_is_image_src($right_icon)) {
				$right .= '<img alt="tgl-btn" src="'.esc_attr($right_icon).'"/>';
			} else {
				$right .= sneeit_font_awesome_tag($right_icon);
			}
			$right .= '</span></a>';
			
			// content for right box
			// - for callback action
			if (function_exists($right_content)) {
				$right_content = call_user_func($right_content);
			}
			// - replace clone & move elemenets
			$right_content = sneeit_display_responsive_smart_tags_replace($right_content);
		} else {
			$right = '';
			$right_content = '';
		}				
		$header_content = str_replace('[toggle-right]', $right, $header_content);				
	}
	
	if (empty($header_content)) {
		return;
	}
	
	if (!empty($left_content)) {
		$left_content = '<div class="'.esc_attr($left_content_class).'">'.$left_content.'</div>';
	}
	$html = '<div class="'.esc_attr($header_content_class).'">'.$header_content.'</div>';
	if (!empty($right_content)) {
		$right_content = '<div class="'.esc_attr($right_content_class).'">'.$right_content.'</div>';
	}
	if (strpos($left_action, '-above')) {
		$html = $left_content . $html;
	} else {
		$html = $html . $left_content;
	}
	if (strpos($right_action, '-above')) {
		$html = $right_content . $html;
	} else {
		$html = $html . $right_content;
	}
	if ($html) {
		$html = '<div class="sneeit-mob-clone"></div><div class="sneeit-mob"><div class="sneeit-mob-inner">'.$html.'</div></div>';
	}
	
	echo $html;
}


function sneeit_responsive_enqueue() {
	global $Sneeit_Responsive;
	wp_enqueue_style( 'sneeit-responsive', sneeit_front_enqueue_url('front-responsive.css'), array(), SNEEIT_PLUGIN_VERSION );
	wp_enqueue_script('sneeit-responsive', sneeit_front_enqueue_url('front-responsive.js'), array('jquery'), SNEEIT_PLUGIN_VERSION, true);
	
	wp_localize_script( 'sneeit-responsive', 'Sneeit_Responsive', $Sneeit_Responsive);
}