<?php
function sneeit_theme_option_control_section($section_id, $section_value) {
	
	if (!isset($section_value['title'])) {
		$section_value['title'] = sneeit_slug_to_title($section_id);
	}
	
	
	?>
	
<div class="section" data-id="<?php echo esc_attr($section_id);?>">
	<div class="section-header">
		<div class="note"><?php esc_html_e('You are customizing', 'sneeit');?></div>
		<div class="title">
			<span>
				<?php
				echo esc_html($section_value['title']);
				?>
			</span>
			<?php if ($section_id != SNEEIT_KEY_SNEEIT_EXPORT_IMPORT) : ?>
			<a href="javascript:void(0)" class="sneeit-theme-options-section-reset-button button button-secondary" data-id="<?php echo esc_attr($section_id); ?>">
				<?php esc_html_e('Reset Section', 'sneeit'); ?>
			</a>
			<?php endif; ?>
		</div>
		<?php if (isset($section_value['description'])) : ?>
		<div class="description">
			<?php echo esc_html($section_value['description']);?>
		</div>
		<?php endif; ?>
	</div>
	
	<div class="content">
		<?php
			foreach ($section_value['settings'] as $control_id => $control_args) {
				new Sneeit_Controls($control_id, $control_args, get_theme_mod($control_id));
			}
		?>		
	</div>
	
</div>
	
	<?php
	
}
foreach ($Sneeit_Theme_Options['declarations'] as $level_1_id => $level_1_value) :
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
		// this is a section
		sneeit_theme_option_control_section($level_1_id, $level_1_value);
		continue;
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
			
			sneeit_theme_option_control_section($level_2_id, $level_2_value, $level_1_id, $level_1_value);			
		}
	endforeach;
endforeach;
?>
