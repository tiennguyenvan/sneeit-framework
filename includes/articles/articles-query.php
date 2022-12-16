<?php

add_filter('sneeit_articles_query', 'sneeit_articles_query');

function sneeit_articles_query($args = array()) {
	$paged = 1;
	$count = 5;
	$sticky_posts = array();
	
	// main elements
	if (!empty($args['count']) && is_numeric($args['count'])) {
		$count = (int) $args['count'];
	}
	if (!empty($args['paged']) && is_numeric($args['paged'])) {
		$paged = (int) $args['paged'];		
	}

	global $Sneeit_Articles_Loaded_Posts;
	if ($Sneeit_Articles_Loaded_Posts == null) {
		$Sneeit_Articles_Loaded_Posts = array();
	}
	if (!empty($args['count'])) :
		
		if (empty($args['sneeit_query_vars'])) {
			// VALIDATE INFORMATION
			$args = wp_parse_args($args, array(		
				'count' => 5,		
				'post_view_count_meta_key' => SNEEIT_KEY_POST_VIEW_COUNT,
				'post_review_average_meta_key' => SNEEIT_KEY_POST_REVIEW_AVERAGE,			
				'article_display_callback' => '',		
			));

			if (empty($args['article_display_callback']) || !function_exists($args['article_display_callback'])) {
				return;
			}

			// PREPARING QUERY ARGUMENTS TO LOAD	
			$query_vars = array();

			// - basic things
			$query_vars['post_type'] = 'post';
			$query_vars['post_status'] = 'publish';
			$query_vars['posts_per_page'] = $count;			
			$query_vars['paged'] = $paged;

			if (!empty($args['ignore_sticky_posts'])) {
				$query_vars['ignore_sticky_posts'] = true;
			}

			if (!empty($args['exclude_loaded_posts'])) {
				if (!empty($args['post__not_in'])) {
					$query_vars['post__not_in'] = $args['post__not_in'];
				} else if (!empty($Sneeit_Articles_Loaded_Posts)) {
					$query_vars['post__not_in'] = $Sneeit_Articles_Loaded_Posts;
				}
			}

			// - order for loading posts
			if (!empty($args['orderby'])) {
				switch ($args['orderby']) {
					case 'popular':
						$query_vars['meta_key'] = $args['post_view_count_meta_key'];
						$query_vars['orderby'] = 'meta_value_num';
						break;

					case 'comment':
						$query_vars['orderby'] = 'comment_count';
						break;

					case 'random':
						$query_vars['orderby'] = 'rand';
						break;

					case 'latest-review':
						$query_vars['meta_key'] = $args['post_review_average_meta_key'];
						break;

					case 'random-review':
						$query_vars['meta_key'] = $args['post_review_average_meta_key'];
						$query_vars['orderby'] = 'rand';
						break;

					case 'popular-review':
						$query_vars['meta_key'] = $args['post_review_average_meta_key'];
						$query_vars['orderby'] = 'meta_value_num';
						break;

					case 'random-related':
						$query_vars['orderby'] = 'rand';
						break;

					case 'popular-related':
						$query_vars['meta_key'] = $args['post_view_count_meta_key'];
						$query_vars['orderby'] = 'meta_value_num';				
						break;
				}			
			}


			// - categories	
			if (!empty($args['categories'])) {
				$query_vars['category__in'] = explode(',', $args['categories']);		
			}	
			// -- exclude categories if user selected
			if (!empty($args['exclude_categories'])) {
				$query_vars['category__not_in'] = explode(',', $args['exclude_categories']);		
			}

			// - tags
			if (!empty($args['tags'])) {					
				$query_vars['tag__in'] = explode(',', $args['tags']);
			}		
			// -- exclude tags if user selected
			if (!empty($args['exclude_tags'])) {			
				$query_vars['tag__not_in'] = explode(',', $args['exclude_tags']);
			}

			// - append related category and tag here
			if (!empty($args['orderby']) && 
				strpos($args['orderby'], 'related') !== false ) {		
				if (!is_single() && !is_admin()) {
					return;
				}
				unset($query_vars['tag__in']);
				unset($query_vars['category__in']);

				$post_id = get_the_ID();
				if (empty($query_vars['post__not_in'])) {
					$query_vars['post__not_in'] = array();
				}
				
				array_push($query_vars['post__not_in'], $post_id);

				// we will check with the tag first
				// if has a tag larger than count (priority smaller tag), we will select that tags only				
				$tags = wp_get_post_tags($post_id, array('fields'  => 'all'));
				$tag_max = -1;
				$tag_id = 0;
				foreach ($tags as $tag) {
					// check if this tag was excluded
					if (!empty($query_vars['tag__not_in']) && 
						in_array($tag->term_id, $query_vars['tag__not_in'])) {
						continue;
					}

					// now we can save data
					if ($tag->count > $tag_max && $tag_max < $count) {
						$tag_max = $tag->count;
						$tag_id = $tag->term_id;
					}
				}

				if ($tag_max >= $count) {		
					$query_vars['tag__in'] = array($tag_id);			
				} 
				/* if has no tag larger than count, we will get categories to check */
				else {
					$cats = wp_get_post_categories($post_id, array('fields' => 'all'));
					$cat_max = -1;
					$cat_id = 0;
					foreach ($cats as $cat) {
						// check if this category was excluded
						if (!empty($query_vars['category__not_in']) && 
							in_array($cat->term_id, $query_vars['category__not_in'])) {
							continue;
						}

						// now we can save data
						if ($cat->count > $cat_max && $cat_max < $count) {
							$cat_max = $cat->count;
							$cat_id = $cat->term_id;
						}
					}

					// found a cat has count > count
					// or not but the max cat > max tag
					if ($cat_max >= $count || $tag_max < $cat_max) {
						$query_vars['category__in'] = array($cat_id);
					}

					/* if has no cate larger than count, we will pick the biggest count from tag / category */
					else if ($tag_max > 0) {
						$query_vars['tag__in'] = array($tag_id);
					}
				}				
			}
			
			

			// authors
			if (!empty($args['authors'])) {
				$query_vars['author'] = $args['authors'];
			}
			if (!empty($args['exclude_authors'])) {		
				$query_vars['author__not_in'] = explode(',', $args['exclude_authors']);
			}

			// duration to load posts from
			if (!empty($args['duration']) && 		
				in_array($args['duration'], array(
					'1 year ago',
					'1 month ago',
					'1 week ago'
				))
			) {
				$query_vars['date_query'] = array(
					array(
						'column' => 'post_date_gmt',
						'after' => $args['duration'],
					)
				);	
			}			
		} /* empty($args['sneeit_query_vars']) */
		
		/* we are serving a default query */
		else {			
			$query_vars = $args['sneeit_query_vars'];
			$query_vars['post_status'] = 'publish';
			if (!empty($args['paged'])) {
				$query_vars['paged'] = $paged;				
			}
		}
				
		// fix wordpress return none sticky posts 
		// if you set category_in
		if (!empty($query_vars['category__in']) && 
			!empty($query_vars['ignore_sticky_posts']) &&
			empty($query_vars['paged'])
		) {
			$sticky_posts = get_option('sticky_posts');
			if (!empty($sticky_posts)) {
				$query_vars['ignore_sticky_posts'] = false;
				$stick_query = $query_vars;
				
				$stick_query['post__in'] = $sticky_posts;
				$sticky_posts = new WP_Query( $stick_query );

				wp_reset_postdata();
			}			
		}
		
		
		
		// loading entries	
		$items = new WP_Query( $query_vars );
						
		
	else: /* count=0, may be archive pages, so we use default query */
		global $wp_query;		
	
		$items = $wp_query;
		if (!empty($wp_query->query_vars['paged'])) {
			$paged = (int) $wp_query->query_vars['paged'];
			$args['paged'] = $paged;
		}
		if (!empty($wp_query->query_vars['posts_per_page'])) {
			$count = (int) $wp_query->query_vars['posts_per_page'];
		}
		// customize $args for related functions
		$args['count'] = $count;
		$args['sneeit_query_vars'] = $wp_query->query_vars;				
	endif;	
	
	
	// loop and fill content
	if (isset($args['index']) && is_numeric($args['index'])) {		
		$index = (int) $args['index'];		
	} else {
		$index = ($paged - 1) * $count;
	}	
		
	// return html
	$html = '';
	if ($items->have_posts()) :
		$processed_args = wp_parse_args($args, array(
			// META KEYS		
			'post_feature_media_meta_key' => SNEEIT_KEY_POST_FEATURE_MEDIA,
			'post_view_count_meta_key' => SNEEIT_KEY_POST_VIEW_COUNT,
			'post_review_average_meta_key' => SNEEIT_KEY_POST_REVIEW_AVERAGE,			
			'post_review_type_meta_key' => SNEEIT_KEY_POST_REVIEW_TYPE,			
			
			// THUMBNAIL
			'default_thumb' => '',
			'thumb_height' => 150,
			'auto_thumb' => false,
			'thumb_img_attr' => array(),
			'format_icon_map' => array(
				'aside' => '',
				'chat' => '',
				'gallery' => '',
				'link' => '',
				'image' => '',
				'quote' => '',
				'status' => '',
				'video' => '<i class="fa fa-play-circle-o"></i> ',
				'audio' => ''
			),
			
			// DECORATION
			'before_view_count' => '<i class="fa fa-eye"></i> ',
			'after_view_count' => '',
			
			'before_author_name' => '',
			'after_author_name' => '',
			
			'before_comment_count' => '<i class="fa fa-commenting"></i> ',
			'after_comment_count' => '',
			
			'before_date_time' => '<i class="fa fa-clock-o"></i> ',
			'after_date_time' => '',
			
			'before_read_more' => '',
			'after_read_more' => ' <i class="fa fa-chevron-circle-right"></i>',
			
			// TRANSLATE
			'%s ago' => esc_html__('%s ago', 'sneeit'),
			'Read More' => esc_html__('Read More', 'sneeit'),
			
			// CLASS and SELECTOR
			'item_extra_class' => ''
		));
		while ( $items->have_posts() ) : $items->the_post();
			
			// update loaded post list
			global $Sneeit_Articles_Loaded_Posts;
			
			if (is_array($Sneeit_Articles_Loaded_Posts)) {
				array_push($Sneeit_Articles_Loaded_Posts, get_the_ID());
			}

			// process and display article			
			$item = new Sneeit_Articles_Query_Item($processed_args, $index);
			$html .= call_user_func($args['article_display_callback'], $item);
			
			// update index
			$index++;
		
		endwhile;	
	endif;

	wp_reset_postdata();
	
	// provide a hook, so ppl can use this to create paginaiton for example
	// or any other apps for their block base on arguments
	if (!empty($args['block_id'])) {
				
		$data = apply_filters('sneeit_articles_query_args', array(
			'args' => $args,
			'found_posts' => $items->found_posts,			
			'loaded_posts' => $Sneeit_Articles_Loaded_Posts
		));
		if (is_string($data)) {
			$html .= $data;
		}		
	}

	return $html;
}
