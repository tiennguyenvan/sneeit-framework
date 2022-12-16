<?php
add_action( 'add_meta_boxes', 'sneeit_rating_add_meta_box' );

define('SNEEIT_RATING_META_BOX_PREFIX', 'sneeit-rating-meta-box');

function sneeit_rating_add_meta_box($post_type) {
	global $Sneeit_Rating_Declaration;
	
	if (!in_array($post_type, $Sneeit_Rating_Declaration['post_type'])){
		return;
	}

	if ( in_array( $post_type, $Sneeit_Rating_Declaration['post_type'] )) {			
		add_meta_box(
			$Sneeit_Rating_Declaration['id'],
			$Sneeit_Rating_Declaration['title'],
			'sneeit_rating_add_meta_box_content',
			$Sneeit_Rating_Declaration['post_type'],
			$Sneeit_Rating_Declaration['context'],
			$Sneeit_Rating_Declaration['priority']
		);
	}
}

function sneeit_rating_add_meta_box_content($post) {
	global $Sneeit_Rating_Declaration;
	
	$post_id = $post->ID;
	$review = get_post_meta($post_id, $Sneeit_Rating_Declaration['id'], true);
	if (!is_array($review)) {
		$review = array(
			'type' => '',
			'summary' => '',
			'conclusion' => '',
			'support-visitor-review' => true,

			'visitor' => array(),
			'star' => array(),
			'point' => array(),
			'percent' => array()
		);
	}

	if (!isset($review['summary'])) {
		$review['summary'] = '';
	}
	if (!isset($review['conclusion'])) {
		$review['conclusion'] = '';
	}
	if (!isset($review['support-visitor-review'])) {
		$review['support-visitor-review'] = true;
	}

	
	if (isset($Sneeit_Rating_Declaration['description'])) {
		echo '<p class="sneeit-post-meta-box-description">'.$Sneeit_Rating_Declaration['description'].'</p>';
	}
	
	// process translation
	
	$default = array(
		'text_is_product_review' => esc_html__('Is product review?', 'sneeit'),
		'text_no' => esc_html__('No', 'sneeit'),
		'text_star' => esc_html__('Star', 'sneeit'),
		'text_point' => esc_html__('Point', 'sneeit'),
		'text_percent' => esc_html__('Percent', 'sneeit'),
		'text_add_star_criteria_for_product' => esc_html__('Add star criteria for this product', 'sneeit'),
		'text_add_point_criteria_for_product' => esc_html__('Add point criteria for this product', 'sneeit'),
		'text_add_percent_criteria_for_product' => esc_html__('Add percent criteria for this product', 'sneeit'),
		'text_criteria_name' => esc_html__('Criteria name', 'sneeit'),
		'text_criteria_value' => esc_html__('Criteria value', 'sneeit'),
		'text_1_star' => esc_html__('%s star', 'sneeit'),
		'text_n_stars' => esc_html__('%s stars', 'sneeit'),
		'text_n_stars' => esc_html__('%s stars', 'sneeit'),
		'text_add_new_criteria' => esc_html__('Add New Criteria', 'sneeit'),
		'text_input_summary' => esc_html__('Input Review Summary', 'sneeit'),
		'text_input_conclusion' => esc_html__('Input Review Conclusion', 'sneeit'),
		'text_allow_visitor' => esc_html__('Allow Visitor Review', 'sneeit'),
	);
	
	$display = wp_parse_args($Sneeit_Rating_Declaration['display'], $default);
	
	?><div class="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>-fields"><?php
		?><div class="section always-show"><?php
			?><div class="field <?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>-type"><?php
				?><label style="display:inline" for="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>[type]"><?php 
					echo $display['text_is_product_review'];
				?></label> <?php
				?><select name="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>[type]" class="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>-list-review-type"><?php
					?><option value=""><?php
						echo $display['text_no'];
					?></option><?php 

					if (in_array('star', $Sneeit_Rating_Declaration['type'])) : 
					?><option value="star" <?php selected('star', $review['type'], true); ?>><?php 
						echo $display['text_star'];
					?></option><?php 
					endif; 

					if (in_array('point', $Sneeit_Rating_Declaration['type'])) : 
					?><option value="point" <?php selected('point', $review['type'], true); ?>><?php
						echo $display['text_point'];
					?></option><?php 
					endif; 

					if (in_array('percent', $Sneeit_Rating_Declaration['type'])) : 
					?><option value="percent" <?php selected('percent', $review['type'], true); ?>><?php
						echo $display['text_percent'];
					?></option><?php 
					endif; 
				?></select><?php
			?></div><?php // end of field type
		?></div><?php // end of file list


		?><div class="section show-hide <?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>-star <?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>-criteria" style="display:<?php echo ($review['type'] == 'star' ? 'block':'none'); ?>"><?php

			?><h4 class="title"><strong><?php 
				echo $display['text_add_star_criteria_for_product'];
			?></strong></h4><?php

			?><div class="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>-star-criteria"><?php
				if (count($review['star'])) : 
					foreach ($review['star'] as $star) : 
					?><label><?php
						?><span><?php echo $display['text_criteria_name']; ?> </span><?php
						?><input type="text" name="<?php
							echo SNEEIT_RATING_META_BOX_PREFIX; 
						?>[star][name][]" value="<?php echo $star['name']; ?>"/><?php

						?><span><?php echo $display['text_criteria_value']; ?> </span><?php
						?><select name="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>[star][value][]"><?php
							for ($i = 0; $i <= 5; $i++) :
								?><option value="<?php echo $i; ?>"<?php selected($i, $star['value'], true); ?>><?php
										if ($i < 2) {
											echo sprintf($display['text_1_star'], $i);
										} else {
											echo sprintf($display['text_n_stars'], $i);
										}
								?></option><?php 
							endfor; 
						?></select><?php
					?></label><?php 
					endforeach;  // list of star items (which already has value)
				else: // for star items of blank
					?><label><?php
						?><span><?php echo $display['text_criteria_name']; ?> </span><?php
						?><input type="text" name="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>[star][name][]" value=""/><?php

						?><span><?php echo $display['text_criteria_value']; ?> </span><?php
						?><select name="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>[star][value][]"><?php
							for ($i = 0; $i <= 5; $i++) :
								?><option value="<?php echo $i; ?>"><?php
										if ($i < 2) {
											echo sprintf($display['text_1_star'], $i);
										} else {
											echo sprintf($display['text_n_stars'], $i);
										}
								?></option><?php 
							endfor; 
						?></select><?php
					?></label><?php 
				endif; 
			?></div><?php // end of star criteria
		?></div><?php // end of star criteria section


		?><div class="section show-hide <?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>-point <?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>-criteria" style="display:<?php echo ($review['type'] == 'point' ? 'block':'none'); ?>"><?php

			?><h4 class="title"><?php echo $display['text_add_point_criteria_for_product']; ?>:</h4><?php

			?><div class="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>-point-criteria"><?php
				if (count($review['point'])) : 
					foreach ($review['point'] as $point) : 
						?><label><?php
							?><span><?php echo $display['text_criteria_name']; ?> </span><?php
							?><input type="text" name="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>[point][name][]" value="<?php echo $point['name']; ?>"/><?php

							?><span><?php echo $display['text_criteria_value']; ?> </span><?php
							?><input type="number" name="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>[point][value][]" value="<?php echo $point['value']; ?>" min="0" max="10" step="1"/><?php
						?></label><?php 
					endforeach; 
				else: 
					?><label><?php
						?><span><?php echo $display['text_criteria_name']; ?> </span><?php
						?><input type="text" name="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>[point][name][]" value=""/><?php

						?><span><?php echo $display['text_criteria_value']; ?> </span><?php
						?><input type="number" name="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>[point][value][]" value="" min="0" max="10" step="1"/><?php
					?></label><?php
				endif; 
			?></div><?php // end of point criteria
		?></div><?php // end of point criteria section

		?><div class="section show-hide <?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>-percent <?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>-criteria" style="display:<?php echo ($review['type'] == 'percent' ? 'block':'none'); ?>"><?php

			?><h4 class="title"><?php echo $display['text_add_percent_criteria_for_product']; ?>:</h4><?php
			
			?><div class="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>-percent-criteria"><?php
				if (count($review['percent'])) : 
					foreach ($review['percent'] as $percent) :
						?><label><?php
							?><span><?php echo $display['text_criteria_name']; ?> </span><?php
							?><input type="text" name="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>[percent][name][]" value="<?php echo $percent['name']; ?>"/><?php

							?><span><?php echo $display['text_criteria_value']; ?> </span><?php
							?><input type="number" name="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>[percent][value][]" value="<?php echo $percent['value']; ?>" min="0" max="100" step="1"/><?php
						?></label><?php 
					endforeach; 
				else: 
					?><label><?php
						?><span><?php echo $display['text_criteria_name']; ?> </span><?php
						?><input type="text" name="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>[percent][name][]" value=""/><?php

						?><span><?php echo $display['text_criteria_value']; ?> </span><?php
						?><input type="number" name="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>[percent][value][]" value="" min="0" max="100" step="1"/><?php
					?></label><?php 
				endif; 
			?></div><?php // end of percent criteria
		?></div><?php // end of percent criteria section

		?><div class="section show-hide <?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>-action" style="display:<?php echo ($review['type'] == '' ? 'none':'block'); ?>"><?php

			?><a <?php echo SNEEIT_HREF_VOID; ?> class="button button-primary button-large <?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>-add-criteria"><?php
				echo $display['text_add_new_criteria'];
			?></a><br/><br/><?php
			
			?><label style="display:<?php echo (in_array('summary', $Sneeit_Rating_Declaration['support']) ? 'block' : 'none'); ?>"><?php
				?><span><?php echo $display['text_input_summary']; ?></span><br/><?php
				?><textarea class="widefat" rows="6" name="<?php 
					echo SNEEIT_RATING_META_BOX_PREFIX; 
				?>[summary]"><?php
					echo esc_textarea($review['summary']); 
				?></textarea><?php
			?></label><?php

			?><label style="display:<?php echo (in_array('conclusion', $Sneeit_Rating_Declaration['support']) ? 'block' : 'none'); ?>"><?php
				?><span><?php echo $display['text_input_conclusion']; ?></span><br/><?php
				?><input type="text" name="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>[conclusion]" value="<?php echo $review['conclusion']; ?>" class="widefat"/><?php
			?></label><?php

			?><label style="display:<?php echo (in_array('visitor', $Sneeit_Rating_Declaration['support']) ? 'block' : 'none'); ?>"><?php
				?><input type="checkbox" name="<?php echo SNEEIT_RATING_META_BOX_PREFIX; ?>[support-visitor-review]" value="on" <?php
					checked(!empty($review['support-visitor-review']), true, true); 
				?>/> <?php
				?><span><?php echo $display['text_allow_visitor'];; ?></span><?php
			?></label><?php
		?></div><?php // end of extra fields
	?></div><?php // end of extra fields section
	
	$nonce_action = 'sneeit-rating-'.$Sneeit_Rating_Declaration['id'].'-nonce-action';
	$nonce_name = 'sneeit-rating-'.$Sneeit_Rating_Declaration['id'].'-nonce-name';
	wp_nonce_field( $nonce_action, $nonce_name );
	
	add_action( 'admin_print_footer_scripts', 'sneeit_rating_enqueue', 1);	
}

function sneeit_rating_enqueue() {
	// enqueue
	wp_enqueue_style( 'sneeit-rating-meta-box', SNEEIT_PLUGIN_URL_CSS . 'rating.css', array(), SNEEIT_PLUGIN_VERSION );
	wp_enqueue_script( 'sneeit-rating-meta-box', SNEEIT_PLUGIN_URL_JS .'rating.js', array(), SNEEIT_PLUGIN_VERSION, true );
	
	wp_localize_script( 'sneeit-rating-meta-box', 'Sneeit_Rating', array(
		'prefix' => SNEEIT_RATING_META_BOX_PREFIX,
		'text' => array(
			'remove_criteria' => 'Remove Criteria'
		)
	));
}

add_action( 'save_post', 'sneeit_rating_save' );
function sneeit_rating_save($post_id) {
	global $Sneeit_Rating_Declaration;

	$nonce_action = 'sneeit-rating-'.$Sneeit_Rating_Declaration['id'].'-nonce-action';
	$nonce_name = 'sneeit-rating-'.$Sneeit_Rating_Declaration['id'].'-nonce-name';
	
	// Check if our nonce is set.
	if ( ! isset( $_POST[$nonce_name] ) ) {
		return false;
	}

	$nonce = $_POST[$nonce_name];

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
		return false;
	}

	// If this is an autosave, our form has not been submitted,
			//     so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  {
		return false;
	}

	// Check the user's permissions.
	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return false;
		}					
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}				
	}


	// save your own rating field here
	if (isset($_POST[SNEEIT_RATING_META_BOX_PREFIX])) {
		$got_review = $_POST[SNEEIT_RATING_META_BOX_PREFIX];
		$review = array();
		$review['type'] = $got_review['type'];

		$support_types = array('star', 'point', 'percent');


		foreach ($support_types as $type) {
			$review[$type] = array();

			if (is_array($got_review[$type])) {	
				foreach ($got_review[$type]['name'] as $key => $name) {
					$value = $got_review[$type]['value'][$key];
					if (!is_numeric($value)) {
						$value = 0;
					}						
					array_push($review[$type], array(
						'name' => $name,
						'value' => $got_review[$type]['value'][$key]
					));
				}
			}
		}


		$review['summary'] = esc_textarea($got_review['summary']);
		$review['conclusion'] = esc_attr($got_review['conclusion']);

		if (isset($got_review['support-visitor-review'])) {
			$review['support-visitor-review'] = !empty($got_review['support-visitor-review']);
		} else {
			$review['support-visitor-review'] = false;
		}
		update_post_meta($post_id, $Sneeit_Rating_Declaration['id'], $review);
	} else {
		delete_post_meta($post_id, $Sneeit_Rating_Declaration['id']);
	}
}