<?php
function sneeit_theme_option_panel_item_add($level = 0, $item_id = '', $item_options = '', $parent_id = '') {
	// add section to its panel
	if (!isset($item_options['title'])) {
		$item_options['title'] = sneeit_slug_to_title($item_id);
	}
	
	
?>
	<div class="panel-item <?php echo ' level-'.$level; ?> inactive" data-id="<?php echo ($item_id); ?>"<?php
	if ($parent_id) {
		echo ' data-parent_id="'.esc_attr($parent_id).'"';
	}
		
	?>>
		<a href="#<?php echo esc_attr($item_id);?>" <?php		
			if (isset($item_options['description'])):
				echo ' title="'.esc_attr($item_options['description']).'"';
			endif;	
		?>>
			<?php 
			if ($level) {
				echo sneeit_font_awesome_tag('angle-right') . '&nbsp; ';
			}
			if (isset($item_options['icon'])) {
				if (strpos($item_options['icon'], 'fa-') !== false) {
					echo sneeit_font_awesome_tag($item_options['icon']) . ' ';
				} else {
					echo sneeit_get_dashicons_tag($item_options['icon']);
				}
				echo ' ';
				
			}
			
			echo esc_html($item_options['title']); 
			
			if (isset($item_options['sections'])) {
				echo ' ' . sneeit_font_awesome_tag('angle-down');
			}
			?>
		</a>
	</div>
<?php
}

$level_counter = 0;
foreach ($Sneeit_Theme_Options['declarations'] as $level_1_id => $level_1_value) :
	if (!isset($level_1_value['sections']) && !isset($level_1_value['settings'])) {
		// only allow panels or sections in this level
		continue;
	}
	
	$level_1_next = array();
	$level_1_next_index = '';
	if (isset($level_1_value['sections'])) {		
		// this is a panel
		sneeit_theme_option_panel_item_add($level_counter, $level_1_id, $level_1_value);
		
		$level_1_next = $level_1_value['sections'];
		$level_1_next_index = 'sections';
		$level_counter++;
	} else if (isset($level_1_value['settings'])) {
		// this is a setting				
		sneeit_theme_option_panel_item_add($level_counter, $level_1_id, $level_1_value);
		
		$level_1_next = $level_1_value['settings'];
		$level_1_next_index = 'settings';
		$level_counter++;
	}
	
	// next level 1
	foreach ($level_1_next as $level_2_id => $level_2_value) :
		if (isset($level_2_value['sections'])) {
			// not allow panel in this level, only allow sections or settings
			continue;
		}

		if (isset($level_2_value['settings'])) {
			// this is a section
			if ($level_1_next_index == 'settings') {
				// a section can not be a child of another section, not allow
				continue;
			}
			
			// add section to its panel
			sneeit_theme_option_panel_item_add($level_counter, $level_2_id, $level_2_value, $level_1_id);
			
		}
	endforeach;
	
	if (isset($level_1_value['sections']) || isset($level_1_value['settings'])) {
		$level_counter--;
	}
	
	
endforeach;
?>

