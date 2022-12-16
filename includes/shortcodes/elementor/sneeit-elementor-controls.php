<?php
namespace SneeitElementor\Controls;
if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * https://gist.github.com/iqbalrony/4372a12f994c3215041680e0075d083f
 */
class Visual extends \Elementor\Base_Data_Control {
	const type = 'sneeit-elementor-control-visual';
	public function get_type() {
		return self::type;
	}
	
	protected function get_default_settings() {
		return [
			'label_block' => true,
			'toggle' => true,
			'options' => [],
		];
	}
	
	public function enqueue () {
		wp_enqueue_style(
			'sneeit-elementor-controls', 
			plugins_url( '/css/controls.css', __FILE__ ),
			[], '1.0.1'
		);
		wp_enqueue_script('jquery');
		wp_enqueue_script(
			'sneeit-elementor-controls',
			plugins_url( '/js/controls.js', __FILE__ ),
			[
				'jquery',
			],
			'1.0.1',
			true
		);
	}
	public function content_template() {
		$control_uid = $this->get_control_uid('{{ value }}');
		?>
		<div class="elementor-control-field">
			<label class="elementor-control-title">{{{ data.label }}}</label>
			<div class="sneeit-elementor-control-visual-wrapper">
				<# _.each( data.options, function( options, value ) { #>
				<input id="<?php echo $control_uid; ?>" type="radio" name="sneeit-elementor-control-visual-{{ data.name }}-{{ data._cid }}" value="{{ value }}" data-setting="{{ data.name }}">
				<label class="sneeit-elementor-control-visual-label" for="<?php echo $control_uid; ?>" data-value="{{ value }}">
					{{{options}}}
				</label>
				<# } ); #>
			</div>
		</div>
		<# if ( data.description ) { #>
		<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}
}
