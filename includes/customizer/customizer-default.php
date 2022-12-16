<?php
// set default value for at beginning
if (is_admin()) {
	$mods = get_theme_mods();
//	global $Sneeit_Customize_Declarations;
	foreach ($Sneeit_Customize_Declarations as $level_1_id => $level_1_value) :
		if (!isset($level_1_value['sections']) && !isset($level_1_value['settings'])) {
			// only allow panels or sections in this level
			continue;
		}

		$level_1_next = array();
		$level_1_next_index = '';
		if (isset($level_1_value['sections'])) {		
			// this is a panel		
			$level_1_next = $level_1_value['sections'];
			$level_1_next_index = 'sections';
		} else if (isset($level_1_value['settings'])) {
			// this is a setting						
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

				// scan for last level of declaration
				foreach ($level_2_value['settings'] as $level_3_id => $level_3_value) {
					if (isset($level_3_value['sections']) || isset($level_3_value['settings'])) {
						// only allow setting here, not allow panels or sections in this level
						continue;
					}
					if ( !isset( $mods[$level_3_id] ) && isset($level_3_value['default'])) {
						set_theme_mod($level_3_id, $level_3_value['default']);
					}
				}

			} else {
				// this is a setting		
				if ($level_1_next_index == 'sections') {
					// a setting can not be a child of a panel, not allow
					continue;
				}
				if ( !isset( $mods[$level_2_id] ) && isset($level_2_value['default'])) {
					set_theme_mod($level_2_id, $level_2_value['default']);
				}				
			}
		endforeach;
	endforeach;
}
