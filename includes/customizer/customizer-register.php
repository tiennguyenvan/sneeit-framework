<?php
$level_1_priority = SNEEIT_DEFAULT_CUSTOMIZER_PRIORITY;
$level_2_priority = SNEEIT_DEFAULT_CUSTOMIZER_PRIORITY;
foreach ($Sneeit_Customize_Declarations as $level_1_id => $level_1_value) :
	if (!isset($level_1_value['sections']) && !isset($level_1_value['settings'])) {
		// only allow panels or sections in this level
		continue;
	}
		
	$level_1_options = array(
		'title' => (isset($level_1_value['title'])? $level_1_value['title'] : sneeit_slug_to_title($level_1_id)),
		'priority' => (isset($level_1_value['priority'])? $level_1_value['priority'] : $level_1_priority),
		'description' => (isset($level_1_value['description'])? $level_1_value['description'] : '')
	);
	$level_1_priority++;
	$level_1_next = array();
	$level_1_next_index = '';
	if (isset($level_1_value['sections'])) {		
		// this is a panel
		$wp_customize->add_panel($level_1_id, $level_1_options);
		$level_1_next = $level_1_value['sections'];
		$level_1_next_index = 'sections';
	} else if (isset($level_1_value['settings'])) {
		// this is a setting				
		$wp_customize->add_section($level_1_id, $level_1_options);
		$level_1_next = $level_1_value['settings'];
		$level_1_next_index = 'settings';
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
			$level_2_section_options = array(
				'title' => (isset($level_2_value['title'])? $level_2_value['title'] : sneeit_slug_to_title($level_1_id)),
				'priority' => (isset($level_2_value['priority'])? $level_2_value['priority'] : $level_2_priority),
				'description' => (isset($level_2_value['description'])? $level_2_value['description'] : ''),
				'panel' => $level_1_id
			);
			$level_2_priority++;
			$wp_customize->add_section($level_2_id, $level_2_section_options);

			// scan for last level of declaration
			foreach ($level_2_value['settings'] as $level_3_id => $level_3_value) {
				if (isset($level_3_value['sections']) || isset($level_3_value['settings'])) {
					// only allow setting here, not allow panels or sections in this level
					continue;
				}
				sneeit_add_customize_setting($wp_customize, $level_2_id, $level_3_id, $level_3_value);			
			}
			
		} else {
			// this is a setting		
			if ($level_1_next_index == 'sections') {
				// a setting can not be a child of a panel, not allow
				continue;
			}
			
			sneeit_add_customize_setting($wp_customize, $level_1_id, $level_2_id, $level_2_value);			

		}
	endforeach;
endforeach;
