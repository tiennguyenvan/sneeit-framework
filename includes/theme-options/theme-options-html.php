<?php
$current_theme = wp_get_theme();

$current_theme_name = $current_theme->get('Name');
$current_theme_version = $current_theme->get('Version');
$current_theme_uri = $current_theme->get('ThemeURI');

// with multiple select, you must save as array
?>
<div id="sneeit-theme-options" class="opacity-0">
	<div class="inner">
		<div class="header">
			<div class="brand">
				<a href="<?php echo esc_attr($current_theme_uri); ?>" target="_blank">
					<?php echo esc_html($current_theme_name); ?>
				</a>
				<span>
					<?php
					echo sprintf(esc_html__('Version %s', 'sneeit'), $current_theme_version);
					?>
				</span>				
			</div>
			<div class="actions">
				<a id="sneeit-to-reset" href="javascript:void(0)" class="button button-secondary">
					<?php esc_html_e('Reset All', 'sneeit'); ?>
				</a>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<a id="sneeit-to-save" href="javascript:void(0)" class="button button-primary" disabled data-text="<?php
				   esc_attr_e('Save & Publish', 'sneeit');
				?>">
					<?php esc_html_e('All Were Saved', 'sneeit'); ?>
				</a>
			</div>
		</div>
		<div class="clear"></div>
		<div class="panel">
			<div class="panel-item panel-search">
				<input type="text" class="panel-search-input" placeholder="<?php
					esc_attr_e('Type Field Name and Enter', 'sneeit');
				?>"/>
				<i class="fa fa-search"></i>
			</div>

			<?php
			include_once 'theme-options-panel-items.php';
			?>

			<div class="clear"></div>
		</div>
		<div class="controls">
			<div class="inner">
				<div class="search-result-note">
				</div>
				<?php include_once 'theme-options-controls.php'; ?>
			</div>
			<div class="clear"></div>
		</div>
	</div>	
</div>

<div id="sneeit-theme-options-saving">
	<div class="inner">
		<i class="fa fa-spin fa-spinner"></i>
		<span class="text"><?php esc_html_e('Saving ...', 'sneeit');?></span>
	</div>
</div>
