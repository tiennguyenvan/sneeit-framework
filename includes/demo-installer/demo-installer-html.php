<?php
global $Sneeit_Demo_Installer;
$demo_list = $Sneeit_Demo_Installer['declarations'];
?>
<div id="sneeit-demo-installer" class="demo-browser rendered">
    <div class="demos">
        <?php foreach($demo_list as $demo_id => $demo_data): ?>
        <div class="demo">
			<div class="demo-main">
				<div class="demo-screenshot">
					<img src="<?php echo $demo_data['screenshot']; ?>" alt="">
				</div>            
				<h3 class="demo-name" id="<?php echo $demo_id; ?>"><?php echo $demo_data['name']; ?></h3>
				<div class="demo-actions">
					<?php if (isset($demo_data['demo'])): ?>
					<a class="button button-secondary activate" href="<?php echo esc_attr($demo_data['demo']); ?>" target="_blank">
						<?php esc_html_e('Demo', 'sneeit'); ?>
					</a>&nbsp;&nbsp;
					<?php endif; ?>

					<a class="button button-primary button-start-demo-install" href="javascript:void(0)" data-id="<?php echo $demo_id; ?>">
						<?php _e('Install', 'sneeit'); ?>
					</a>
				</div>
			</div>
			<div class="demo-process">
				<div class="demo-screenshot"></div>
				<h3 class="demo-name"></h3>
				<div class="demo-process-percent">0%</div>
				<div class="demo-process-overlay"></div>
				<div class="demo-process-message">. . .</div>
			</div>
        </div>		
		<?php endforeach; ?>		
		
        <div class="demo builder">
			<div id="build-demo-process" class="demo-process">
				<div class="demo-screenshot"></div>
				<h3 class="demo-name"></h3>
				<div class="demo-process-percent">0%</div>
				<div class="demo-process-overlay"></div>
				<div class="demo-process-message">. . .</div>
			</div>
            <a href="javascript:void(0)" id="build-demo" class="demo-process action">
			    <div class="demo-screenshot"><span></span></div>
				<h3 class="demo-name"><?php _e('Build a Demo', 'sneeit'); ?></h3>
			</a>
        </div>
		
		
		
    </div>
    <div class="clear"></div>
	<div class="explored">
	</div>
	
	
</div>
