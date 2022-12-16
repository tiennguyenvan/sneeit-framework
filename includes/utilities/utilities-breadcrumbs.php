<?php

class Sneeit_Breadcrumbs {
	var $crumbs = array();
	var $depth =  3; // waiting next updates	
	var $before_item =  '';
	var $after_item =  '';
	var $item_class =  array(); 
	var $before_text =  ''; 
	var $after_text =  '';
	var $text_class =  array();
	var $separator =  '&gt;';
	var $home_text =  '';
	var $show_current =  TRUE;
	var $before_current = '';
	var $after_current = '';
	var $before_current_text = '';
	var $after_current_text = '';
	var $current_class = '';
	var $custom_taxonomy = '';
	
	public function __construct($args = array()) {
		// premade data
		$this->home_text = __('Home', 'sneeit');
		
		// extract data
		$keys = array_keys( get_object_vars( $this ) );
		foreach ( $keys as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$this->$key = $args[ $key ];
			}
		}
		
		// validate data
		$this->item_class = $this->validate_class($this->item_class, 'breadcrumb-item');
		$this->text_class = $this->validate_class($this->text_class, 'breadcrumb-item-text');
		$this->current_class = $this->validate_class($this->current_class, 'breadcrumb-current');
		
		// colect crumbs
		if ($this->home_text) {
			$this->add_crumb(get_home_url(), $this->home_text);
		}
		
		if ( !is_front_page() ) {
			
			if ( is_archive() && !is_tax() && !is_category() && !is_tag() ) {
				if ($this->show_current) {
					$this->add_crumb('', post_type_archive_title('', false));					
				}
			} else if ( is_archive() && is_tax() && !is_category() && !is_tag() ) {
              
				// If post is a custom post type
				$post_type = get_post_type();

				// If it is a custom post type display name and link
				if($post_type != 'post') {

					$post_type_object = get_post_type_object($post_type);
					$post_type_archive = get_post_type_archive_link($post_type);
					$this->add_crumb($post_type_archive, $post_type_object->labels->name);
				}
				if ($this->show_current) {
					$this->add_crumb('', get_queried_object()->name);
				}
			} 
			/* single (posts / articles or custom post type) */
			else if ( is_single() ) {
				// Check if post is a custom post type
				$post_type = get_post_type();				

				// If it is a custom post type display name and link to the type
				if ($post_type != 'post') {
					$post_type_object = get_post_type_object($post_type);
					$post_type_archive = get_post_type_archive_link($post_type);
					$this->add_crumb($post_type_archive, $post_type_object->labels->name);
				}
				
				$categories = get_the_category();
				
				if ($categories && !empty($categories)) {
					/* sort by id */
					$cats = array();
					foreach ($categories as $cat) {
						$cats[$cat->term_id] = $cat;
					}
					
					/* get primary cat */ 
					/* compatible with SEO by Yoast 
					 * Show the post's 'Primary' category, 
					 * if this Yoast feature is available, 
					 * Show the post's 'Primary' category, 
					 */					
					$primary_cat_id = 0;					
					if ( class_exists('WPSEO_Primary_Term') ) {							
						$wpseo_primary_term = new WPSEO_Primary_Term( 'category', get_the_ID() );			
						$primary_cat_id = $wpseo_primary_term->get_primary_term();
						
						if ($primary_cat_id && empty($cats[$primary_cat_id])) {
							$primary_cat_id = 0;
						}
					}													
					
					/* set child cats */
					$has_parent_cat = false;
					foreach ($cats as $cat_id => $cat) {
						if (empty($cat->parent)) {
							continue;
						}
						/* if this cat has parent and the parent is in cat list */
						if (!empty($cats[$cat->parent])) {
							$cats[$cat->parent]->child = $cat_id;
							$has_parent_cat = true;
						}
					}
					
					/* @fixme: if have primary cat and 
					 * it is in a family, just show the family 
					 * regardless the top family */		
					$top_parent_cat_id = 0;
					if ($primary_cat_id &&
						(	!empty($cats[$primary_cat_id]->parent) ||
							!empty($cats[$primary_cat_id]->child)
						)
					) {
						foreach ($cats as $cat_id => $cat) {
							/* this is the top parent */
							if (empty($cats[$primary_cat_id]->parent)) {
								break;
							}
							/* the parent not found in cat list */
							if (empty($cats[$cats[$primary_cat_id]->parent])) {
								break;
							}
							
							$primary_cat_id = $cats[$primary_cat_id]->parent;
						}
						
						/* update top parent cat id to skip 
						 * processes about parent cats */
						$top_parent_cat_id = $primary_cat_id;
					}
					
					
					/* collect top parent cats */ 
					$parent_cats = array();
					if ($has_parent_cat && !$top_parent_cat_id) {
						/* use $has_parent_cat variable to reduct processing time */
						foreach ($cats as $cat_id => $cat) {
							if ($cat->parent || !$cat->child) {
								continue;
							}
							array_push($parent_cats, $cat);
						}
					}
					
					
					/* get top parent cat base on count */					
					$top_parent_cat_count = 0;
					if (!$top_parent_cat_id) {
						foreach ($parent_cats as $cat_id => $cat) {
							if ($cat->count > $top_parent_cat_count) {
								$top_parent_cat_count = $cat->count;
								$top_parent_cat_id = $cat_id;
							}						
						}
					}
					
					
					/* get family cats of top parent cat */
					$top_family_cat_ids = array();
					if ($top_parent_cat_id) {
						foreach ($cats as $cat_id => $cat) {
							array_push($top_family_cat_ids, $top_parent_cat_id);							
							if (empty($cats[$top_parent_cat_id]->child)) {
								break;
							}
							$top_parent_cat_id = $cats[$top_parent_cat_id]->child;
						}
					}
					
					
					/* add crumbs */
					/* if set prmary cat and this cat not in top family 
					 * then we just show it first
					 */
					if ($primary_cat_id && !in_array($primary_cat_id, $top_family_cat_ids)) {
						$this->add_crumb(get_category_link($primary_cat_id), $cats[$primary_cat_id]->name);
						unset($cats[$primary_cat_id]);
					}
					
					/* show top family if have */
					foreach ($top_family_cat_ids as $cat_id) {
						$this->add_crumb(get_category_link($cat_id), $cats[$cat_id]->name);
						unset($cats[$cat_id]);
					}
					
					/* did not show any cat, 
					 * sort cat by count and
					 * show from top */
					if (count($cats) == count($categories)) {
						/* sort by count */
						for ($i = 0; $i < count($categories) - 1; $i++) {
							for ($j = $i + 1; $j < count($categories); $j++) {
								if ($categories[$i]->count < $categories[$j]->count) {
									$temp = $categories[$i];
									$categories[$i] = $categories[$j];
									$categories[$j] = $temp;
								}
							}
						}
						
						/* show */
						foreach ($categories as $cat) {
							$this->add_crumb(get_category_link($cat->term_id), $cat->name);
						}
					}
										
				} else if (!empty($this->custom_taxonomy) && $taxonomy_exists) {
					$taxonomy_terms = get_the_terms( $post->ID, $this->custom_taxonomy );
					$cat_link       = get_term_link($taxonomy_terms[0]->term_id, $this->custom_taxonomy);
					$cat_name       = $taxonomy_terms[0]->name;
					$this->add_crumb($cat_name, $cat_link);
				}

				// If it's a custom post type within a custom taxonomy
				$taxonomy_exists = taxonomy_exists($this->custom_taxonomy);
				$cat_id = '';
				$cat_link = '';
				$cat_name = '';
				
				if ($this->show_current) {
					$this->add_crumb('', get_the_title());
				}
			} else if ( is_category() ) {
				if ($this->show_current) {
					$this->add_crumb('', single_cat_title('', false));
				}
			} else if ( is_page() ) {
				global $post;
				// Standard page
				if( $post->post_parent ){

					// If child page, get parents 
					$anc = get_post_ancestors( $post->ID );

					// Get parents in the right order
					$anc = array_reverse($anc);

					// Parent page loop
					foreach ( $anc as $ancestor ) {
						$this->add_crumb(get_permalink($ancestor), get_the_title($ancestor));						
					}

					// Display parent pages
					echo $parents;

					// Current page
					if ($this->show_current) {
						$this->add_crumb('', get_the_title());
					}
				} else {
					// Current page
					if ($this->show_current) {
						$this->add_crumb('', get_the_title());
					}					
				}

			} else if ( is_tag() ) {

				// Tag page

				// Get tag information
				$term_id        = get_query_var('tag_id');
				$taxonomy       = 'post_tag';
				$args           = 'include=' . $term_id;
				$terms          = get_terms( $taxonomy, $args );
				$get_term_id    = $terms[0]->term_id;
				$get_term_slug  = $terms[0]->slug;
				$get_term_name  = $terms[0]->name;

				// Display the tag name
				if ($this->show_current) {
					$this->add_crumb('', $get_term_name);
				}				
			} elseif ( is_day() ) {
				// Day archive

				// Year link
				$this->add_crumb(get_year_link( get_the_time('Y') ), get_the_time('Y'));
				
				// Month link
				$this->add_crumb(get_month_link( get_the_time('Y'), get_the_time('m') ), get_the_time('M'));
			
				// Day display
				if ($this->show_current) {
					$this->add_crumb('', get_the_time('jS') . ' ' . get_the_time('M'));
				}				

			} else if ( is_month() ) {

				// Month Archive

				// Year link
				$this->add_crumb(get_year_link( get_the_time('Y') ), get_the_time('Y'));				

				// Month display
				if ($this->show_current) {
					$this->add_crumb('', get_the_time('M'));
				}				
			} else if ( is_year() ) {

				// Display year archive
				if ($this->show_current) {
					$this->add_crumb('', get_the_time('Y'));
				}				
			} else if ( is_author() ) {
				// Display author name
				if ($this->show_current) {
					// Auhor archive

					// Get the author information
					global $author;
					$userdata = get_userdata( $author );
					
					$this->add_crumb('', $userdata->display_name);
				}				
			} else if ( get_query_var('paged') ) {
				// Paginated archives
				if ($this->show_current) {
					$this->add_crumb('', get_query_var('paged'));
				}
			} else if ( is_search() ) {
				if ($this->show_current) {
					$this->add_crumb('', get_search_query());
				}				
			} elseif ( is_404() ) {
				
			}
		}
	}
	public function add_crumb($crumb_href = '', $crumb_text = '') {
		if (!$this->depth) {
			return;
		}
		$this->crumbs[] = array('href' => $crumb_href, 'text' => $crumb_text);
		$this->depth--;
	}
	public function validate_class($class, $default = array()) {
		if (!empty($class)) {
			if (is_string($class)) {
				$class = explode(' ', trim($class));
			}
		} else {
			$class = array();
		}
		if (!is_array($class)) {
			$class = (array) $class;
		}
		
		if (!empty($default)) {
			if (is_string($default)) {
				$default = explode(' ', trim($default));
			}
		} else {
			$default = array();
		}
		if (!is_array($default)) {
			$default = (array) $default;
		}
		
		return array_merge($class, $default);
	}
	public function crumb_class($class) {
		$class = $this->validate_class($class);
		return ' class="'.  implode($class).'"';
	}
	public function crumb_item_class() {
		return $this->crumb_class($this->item_class);
	}
	public function crumb_text_class() {
		return $this->crumb_class($this->text_class);
	}
	public function crumb_current_class() {
		return $this->crumb_class($this->current_class);
	}
}

// https://www.thewebtaylor.com/articles/wordpress-creating-breadcrumbs-without-a-plugin
add_action('sneeit_breadcrumbs', 'sneeit_utilities_breadcrumbs', 1, 1);
function sneeit_utilities_breadcrumbs($args = array()) {
		$args = wp_parse_args($args, array(
			'before' => '', 
			'after' => '',
			'separator' => '<span><i class="fa fa-angle-right"></i></span>',
			'show_current' => false,
		));
		$bc = new Sneeit_Breadcrumbs($args);
		$html = '';	
		$json = '';
		foreach ($bc->crumbs as $index => $crumb) {
			if ($crumb['href']) {
				$html .= $bc->before_item.'<span>'.
					'<a href="'.$crumb['href'].'" '.$bc->crumb_item_class().'>'.
						$bc->before_text.'<span'.$bc->crumb_text_class().'>'.$crumb['text'].'</span>'.$bc->after_text.
					'</a>'.
				'</span>'.$bc->after_item;
				if ($json) {
					$json .=',';
				}
				$json .= '{'.
				'"@type":"ListItem",'.
				'"position":'.($index+1).','.
					'"item":{'.
					'"@type":"WebSite",'.
					'"@id":"'.esc_attr($crumb['href']).'",'.
					'"name": "'.esc_attr($crumb['text']).'"'.
				'}}';				
			} else {
				$html .= $bc->before_current.'<span'.$bc->before_current_text.$bc->crumb_current_class().'>'.$crumb['text'].$bc->after_current_text.'</span>'.$bc->after_current;
			}
			if ($index < count($bc->crumbs) - 1) {
				$html .= $bc->separator;				
			}
		}
		
		if ($json) {
			$json = '<script type="application/ld+json" style="display:none">{'.
				'"@context": "https://schema.org",'.
				'"@type": "BreadcrumbList",'.
				'"itemListElement": ['.$json.
				']'.
			'}</script>';
		}
		
		
		// ouput the breadcrumbs
		echo $args['before'] . $html . $args['after'] . $json;	
}


