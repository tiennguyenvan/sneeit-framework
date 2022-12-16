<?php
namespace SneeitElementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Shortcodes extends Widget_Base {
	
	public $id = null;
	public $declaration = null;
	public $script = null;

	/**
	 * Retrieve the widget name.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return $this->id;
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return $this->declaration['title'];
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return $this->declaration['icon'];
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ $this->script ];
	}

	/**
	 * Retrieve the list of scripts the widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return [ $this->script ];
	}

	/**
	 * Register the widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _register_controls() {		
		if (empty($this->declaration['fields'])) {
			return;
		}		
		
		include_once 'sneeit-elementor-controls.php';
		
		$this->start_controls_section(
			'section_general',
			[
				'label' => __( 'General', 'sneeit' ),
			]
		);
				
		$categories = array();
		$tags = array();
		$users = array();
		$sidebars = array();
		foreach ($this->declaration['fields'] as $field_id => $field) {
			
			switch ($field['type']) {												
				case 'category'://																
				case 'categories':					
					$categories = get_terms( array(
						'taxonomy' => 'category',
						'hide_empty' => false,
						'fields' => 'id=>name',	
						'number' => 5000,
					) );
					break;
				case 'tag':
				case 'tags':
					$tags = get_terms( array(
						'taxonomy' => 'post_tag',
						'hide_empty' => false,
						'fields' => 'id=>name',	
						'number' => 5000,
					) );
					break;
				
				case 'user':										
				case 'users':					
					$all_users = get_users( array() );					
					foreach ($all_users as $user) {
						$users[$user->data->ID] = $user->data->display_name;
					}
					break;
				
				/* SIDEBAR & SIDEBARS */
				case 'sidebars':						
				case 'sidebar':		
					global $wp_registered_sidebars;
					unset($wp_registered_sidebars['wp_inactive_widgets']);
					$sidebars = $wp_registered_sidebars;
					break;

				default:					
					
					break;
			}			
		}
		
				
		foreach ($this->declaration['fields'] as $field_id => $field) {
			/* start another setting section */
			if (!empty($field['heading'])) {
				$this->end_controls_section();
				$this->start_controls_section(
					'section_'.$field_id,
					[
						'label' => $field['heading'],
					]
				);
			}
			
			// common control arguments
			$control_args = array(
				'label' => $field['label']
			);
			if (!empty($field['default'])) {
				$control_args['default'] = $field['default'];
			}
			if (!empty($field['description']) && 
				$field['label'] != $field['description']
			) {
				$control_args['description'] = $field['description'];
			}
			$control_args['label_block'] = true;
			$control_args['type'] = \Elementor\Controls_Manager::TEXT;
			
			
			
			// specific control arguments
			switch ($field['type']) {								
				/* CATEGORY / TAG / USER */
				case 'category':
					$control_args['type'] = \Elementor\Controls_Manager::SELECT;
					$control_args['options'] = $categories;					
					break;
				case 'tag':
					$control_args['type'] = \Elementor\Controls_Manager::SELECT;
					$control_args['options'] = $tags;					
					break;
				case 'user':
					$control_args['type'] = \Elementor\Controls_Manager::SELECT;
					$control_args['options'] = $users;
					break;
				
				/* CATOGORIES / TAGS / USERS */
				case 'categories':
					$control_args['type'] = \Elementor\Controls_Manager::SELECT2;
					$control_args['options'] = $categories;
					$control_args['multiple'] = true;
					break;
				case 'tags':
					$control_args['type'] = \Elementor\Controls_Manager::SELECT2;
					$control_args['options'] = $tags;
					$control_args['multiple'] = true;
					break;
				case 'users':
					$control_args['type'] = \Elementor\Controls_Manager::SELECT2;
					$control_args['options'] = $users;
					$control_args['multiple'] = true;					
					break;
				
								
				/* CHECKBOX */
				case 'checkbox':
					$control_args['type'] = \Elementor\Controls_Manager::SWITCHER;
					
					if (isset($control_args['default'])) {
						$control_args['default'] = 
							$control_args['default'] ? 'on' : '';
					}			
					$control_args['return_value'] = 'on';
					$control_args['label_on'] = __('On', 'sneeit');
					$control_args['label_off'] = __('Off', 'sneeit');
					
					break;

				/* RADIO */
				case 'radio':
					$control_args['type'] = \Elementor\Controls_Manager::CHOOSE;
					$control_args['options'] = array();
					foreach ($field['choices'] as $choice_id => $choice_title) {
						$control_args['options'][$choice_id] = array(
							'title' => $choice_title
						);
					}
					break;

				/* SELECT & SELECTS */
				case 'select':
					$control_args['type'] = \Elementor\Controls_Manager::SELECT;
					$control_args['options'] = $field['choices'];
					break;
					
				case 'selects':			
					$control_args['type'] = \Elementor\Controls_Manager::SELECT2;
					$control_args['options'] = $field['choices'];
					$control_args['multiple'] = true;
					break;

				/* TEXTAREA / CONTENT */
				case 'content':
					$control_args['type'] = \Elementor\Controls_Manager::WYSIWYG;
					break;
				
				case 'textarea':
					$control_args['type'] = \Elementor\Controls_Manager::TEXTAREA;
					break;

				/* COLOR */
				case 'color':
					$control_args['type'] = \Elementor\Controls_Manager::COLOR;
					break;

				/* MEDIA *//* IMAGE */
				case 'media':									
				case 'image':
					if (!empty($control_args['default']) && 
						is_string($control_args['default'])
					) {
						$control_args['default'] = array('url' => $control_args['default']);
					}
					$control_args['type'] = \Elementor\Controls_Manager::MEDIA;
					break;

				/* RANGE */
				case 'range':
					
					break;

				/* SIDEBAR & SIDEBARS */
				case 'sidebars':
					$control_args['type'] = \Elementor\Controls_Manager::SELECT;
					$control_args['options'] = $sidebars;	
					break;
					
				case 'sidebar':				
					$control_args['type'] = \Elementor\Controls_Manager::SELECT2;
					$control_args['options'] = $sidebars;
					$control_args['multiple'] = true;
					break;

				/* VISUAL PICKER */
				case 'visual':
					$control_args['type'] = \SneeitElementor\Controls\Visual::type;
					$control_args['options'] = $field['choices'];
					break;

				/* FONT FAMILY & font*/
				case 'font-family':
				case 'font':
					
					break;
					
					
				/* box-model: PADDING / MARGIN / BORDER / POSITION */
				case 'box-padding':
				case 'box-margin':
				case 'box-position':
				case 'box-padding-px':
				case 'box-margin-px':
				case 'box-position-px':
					
					break;

				default:					
					
					break;
			}
			
			// real progress to add control
			$this->add_control(
				$field_id,
				$control_args
			);
			
		}
		
		// test custom controls
//		$this->add_control(
//			'sneeit-custom-control',
//			[				
//				'label' => 'Custom Category',
//				'label_block' => true,
//				'type' => \SneeitElementor\Controls\Category::type,
//				'default' => $this->id,				
//			]
//		);

		$this->add_control(
			'sneeit-shortcode-id',
			[				
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => $this->id,
				'input_type' => 'hidden'
			]
		);
		

		
		$this->end_controls_section();
	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		
		if (empty($settings['sneeit-shortcode-id'])) {
			_e('Conflict define, please press Update and try again', 'Sneeit');
			return;
		}
		$id = $settings['sneeit-shortcode-id'];
		global $Sneeit_ShortCodes;
		
		$declaration = $Sneeit_ShortCodes['declarations'][$id];
		
		// process values before pass to callback function
		$content = '';
		foreach ($declaration['fields'] as $field_id => $field) {
			if (!empty($field['type']) && 
				'content' == $field['type'] &&
				!empty($settings[$field_id])
			) {
				$content = $settings[$field_id];
				break;
			}
			if (!empty($field['type']) && 
				in_array($field['type'], array(
					'categories', 'tags', 'users', 'sidebars'
				)) &&
				!empty($settings[$field_id]) &&
				is_array($settings[$field_id])
			) {
				$settings[$field_id] = implode(',', $settings[$field_id]);
				break;
			}
		}	
				
		
		// put to callback function	
		echo call_user_func($declaration['display_callback'], $settings, $content);		
	}
	
}
