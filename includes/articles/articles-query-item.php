<?php
/**
 * This class is for sneeit article query to display items
 * We must restrict function length and direct arguments to
 * increase performance.
 * 
 * If you want to add more flexibile feture, just start with 
 * the Object $args instead of direct $args from functions
 */

class Sneeit_Articles_Query_Item {
	var $ID = 0;
	var $permalink = '';
	var $title = '';
	var $title_esc_attr = '';
	var $content = '';
	var $format = '';
	var $args = array();	
	var $index = 0;	
	
	/**
	 * THIS FUNCTION RUN WHEN CREATE CLASS VARIABLE
	 * we will use this to get some basic thing that will be use in every methods
	 */
	function __construct($args = array(), $index = 0) {
		$this->ID = get_the_ID();
		$this->permalink = get_permalink();
		$this->title = wp_kses(get_the_title(), array());		
		$this->title_esc_attr = esc_attr($this->title);			
		$this->index = $index;
		
		$this->args = $args;
		
		// process data
		// if have prefix
		
		// thumbnail height
		if (!is_numeric($this->args['thumb_height'])) {
			$this->args['thumb_height'] = 150;
		}
		$this->args['thumb_height'] = (int) $this->args['thumb_height'];		
	}
	
	/**
	 * 
	 * @param type $extra_class
	 * @return type
	 */
	public function item_class($extra_class = '') {
		$class = 'item item-'.$this->index;
		
		/* in arguments extra class */
		if (!empty($this->args['item_extra_class'])) {
			$class .= ' ' . esc_attr(trim($this->args['item_extra_class']));
		}
		
		/* direct extra class */
		if (!empty($extra_class)) {
			$class .= ' ' . esc_attr(trim($extra_class));
		}
		
		/* add post format to class */
		if (!$this->format) {
			$this->format = get_post_format();			
		}
		if ($this->format && 'standard' != $this->format) {
			$class .= ' item-' . $this->format;
		}
		
		return ' class="'.$class.'"';
	}
	
	/**
	 * return full content of post
	 * call this when you need only to save performances
	 * this will return raw content, without video embedded or shortcode
	 */
	public function content() {
		
		if ($this->content) {
			return $this->content;
		}
		/* we not use the_content
		 * because a post insert code to show recent posts
		 * which include itself, we will have a forver loop
		 * when the_content do shortcode directly
		 * 
		 * So we must have a way to remove all shortcodes
		 * because we don't want to show them to readers
		 */
		$this->content = get_the_content();
		
		/* Since 4.9
		 * 
		 * We don't use global $shortcode_tags; anymore
		 * when it takes too much steps to be done
		 * 
		 * This new method may be not stable, please 
		 * stress test it if you get any report
		 */
		/* remove scripts / styles */
		/* PENDING: still not found an efficient way to do */
		
		/* remove shortcodes */
		$start = strpos($this->content, '[');
		$end = strpos($this->content, ']', $start);
		if ($start !== false && $end !== false && $end > $start + 1) {
			$this->content = str_replace(array('['), '<', $this->content);
			$this->content = str_replace(array(']'), '>', $this->content);
			
			/* we remove all tags but keep images and iframes
			 * just incase some one need to scane images tags
			 * or youtube / vimeo or other social included
			 */
			$this->content = wp_kses($this->content, array(
				'img' => array(
					'src' => array(),
					'class' => array(),
				),
				'iframe' => array(
					'src' => array(),				
				),
			));
		}		
		
		return $this->content;
	}
	
	/**
	 * If you use SNEEIT THUMBNAIL HOOKS:
	 * Images not from library will have src
	 * Images from library will have no src, but data-s,
	 * and javascript will measure the thumbnail size
	 * and get the right image for src attribute
	 */
	private function get_post_image($size = 'post-thumbnail', $attr = array()) {		
		// DEFINE
		$image_html = '';
		$src = '';			

		// IF HAVE THUMBNAIL
		if (has_post_thumbnail( $this->ID ) ) {
			apply_filters( 'sneeit_articles_get_post_image_before', '');
			$image_html = get_the_post_thumbnail( $this->ID, $size, $attr );
			apply_filters( 'sneeit_articles_get_post_image_after', '');
			return $image_html;
		}
		
		// CHECK IF HAVE FEATURE MEDIA FIELD
		// scan in 		
		if (!empty($this->args['post_feature_media_meta_key'])) {
			$feature_media_meta_value = get_post_meta($this->ID, $this->args['post_feature_media_meta_key'], true);
			if ($feature_media_meta_value) {
				$src = sneeit_get_youtube_image($feature_media_meta_value);
				if (!$src) {
					$src = sneeit_get_vimeo_image($feature_media_meta_value);
				}		
			}
		}
		
		
		
		// NOT FOUND ANY IMAGE
		if (!$src) {
			
			// so, now, we must scan the first image			
			$src = sneeit_article_get_image_src($this->content());

			// found an attachment id
			if (is_numeric($src)) {				
				apply_filters( 'sneeit_articles_get_post_image_before', '');
				$image_html = wp_get_attachment_image($src, $size, false, $attr);
				apply_filters( 'sneeit_articles_get_post_image_after', '');
				return $image_html;
			}
		}
		

		// found image, just output the image link
		if ( $src ) {
			// maybe external image or not in library
			$image_html = '<img src="' . esc_url( $src ) . '"';
			foreach ( $attr as $key => $value ) {
				$image_html .= ' ' . $key . '="' . esc_attr( $value ) . '"';
			}
			$image_html .= '/>';			
		}

		return $image_html;
	}
	
	/**
	 * 
	 */
	public function thumbnail($size = 'post-thumbnail') {
		
		if ($this->args['thumb_height'] <= 0 && empty($this->args['auto_thumb'])) {
			return '';
		}
		
		// element attributes
		$image_attr = array(
			'alt' => $this->title_esc_attr
		);
		$image_attr = wp_parse_args($image_attr, $this->args['thumb_img_attr']);
				
		
		$link_attr = array(
			'title' => $this->title_esc_attr,
			'class' => 'sneeit-thumb' . ($this->args['auto_thumb'] ? ' sneeit-thumb-a' : ' sneeit-thumb-f'),
			'href' => $this->permalink,
		);
								
		// get image html
		$image = $this->get_post_image($size, $image_attr);
				
		// if not found, we need to use default thumbnail
		if (!$image && !empty($this->args['default_thumb'])) {
			$image = '<img src="'.esc_url($this->args['default_thumb']).'"';
			foreach ($image_attr as $attr_name => $attr_value) {
				$image .= ' '.$attr_name.'="'.esc_attr($attr_value).'"';
			}
			$image .= '/>';
		}
		if (!$image) {
			return '';
		}
		
		// add link
		$image_link = '<a';
		foreach ($link_attr as $attr_name => $attr_value) {
			$image_link .= ' '.$attr_name.'="'.esc_attr($attr_value).'"';
		}
		$image_link .= '>'.$image.'</a>';
		
		return $image_link;
	}
	
	/**
	 * You must input your format_icon_map
	 */
	public function format_icon() {
		if (empty($this->args['show_format_icon']) || 
			empty($this->args['format_icon_map'])) {
			return;
		}
		if (!$this->format) {
			$this->format = get_post_format();
		}
		if (empty($this->args['format_icon_map'][$this->format])) {
			return '';
		}
		
		return '<a class="item-format-icon" href="'.$this->permalink.'#post-review" title="'.$this->title_esc_attr.'">'.$this->args['format_icon_map'][$this->format].'</a>';
	}
	
	/**
	 * use this if you are using sneeit rating hook
	 * you must input post review average and post review type meta key
	 * or framework will use the default
	 */	
	public function review() {
		// check condition
		if (empty($this->args['show_review_score']) || 
			empty($this->args['post_review_average_meta_key'])) {
			return '';
		}
		
		
		// rating data
		$post_review_average = get_post_meta($this->ID, $this->args['post_review_average_meta_key'], true);				
		if (empty($post_review_average)) {
			return '';
		}
					
		$post_review_type = '';
		if (!empty($this->args['post_review_type_meta_key'])) {
			$post_review_type = get_post_meta($this->ID, $this->args['post_review_type_meta_key'], true);		
		}		
		
		return sneeit_review_percent_circle($post_review_average, $post_review_type, 'a', array(
			'class' => 'item-review',
			'title' => $this->title_esc_attr,
			'href' => $this->permalink
		));		
	}
	
	/** 
	 * use this if you are using sneeit views hook
	 * you must input view meta key
	 * or framework will use the default
	 */
	public function views() {
		if (empty($this->args['show_view_count']) ||
			empty($this->args['views_meta_key'])) {
			return '';
		}
		
		$views = get_post_meta($this->ID, $this->args['views_meta_key'], true);
		
		if (empty($views)) {
			return '';
		}
		
		return '<span class="item-views">'.$this->args['before_view_count'] . $views. $this->args['after_view_count'].'</span>';
	}
	
	/**
	 * 
	 */
	public function author() {
		if (empty($this->args['show_author'])) {
			return '';
		}
		$id = get_the_author_meta('ID');
		$name = get_the_author_meta( 'display_name' );
		switch ($this->args['show_author']) {
			case 'icon':
				$this->args['before_author_name'] .= '<i class="fa fa-user"></i> ';
				break;
			case 'avatar':
				$avatar = get_avatar($id, 16, '', esc_attr($name));
				if (!empty($avatar)) {
					$this->args['before_author_name'] .= $avatar . ' ';
				}				
				break;
			default:
				break;
		}

		return '<a href="'.esc_url(get_author_posts_url($id)).'" target="_blank" class="item-author">'.$this->args['before_author_name'].$name.$this->args['after_author_name'].'</a>';
	}
	public function comment_count() {
		if (empty($this->args['show_comment'])) {
			return '';
		}
		
		return '<a class="item-comment-count" href="'.esc_url(get_comments_link()).'">'.$this->args['before_comment_count'].get_comments_number(). $this->args['after_comment_count'].'</a>';
	}
	
	/**
	 * 
	 */
	public function date_time() {
		if (empty($this->args['show_date'])) {
			return '';
		}
		
		$ret = '<a class="item-date-time" href="'.esc_url($this->permalink).'">'.$this->args['before_date_time'];

		switch ($this->args['show_date']) {
			case 'pretty':
				$ret .= sprintf( $this->args['%s ago'], human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) );
				break;

			case 'short':
				if (get_option('date_format')) {
					$ret .= get_the_date(str_replace('F', 'M', get_option('date_format')));
				}
				break;
				
			case 'time':
				$ret .= get_the_time();
				break;

			case 'date':
				$ret .= get_the_date();
				break;
			
			default:
				$ret .= get_the_date().' '.get_the_time();
				break;
		}
		$ret .= $this->args['after_date_time'].'</a>';
		
		return $ret;
	}
	
	/**
	 * 
	 */
	public function categories() {
		if (empty($this->args['number_cates']) || 
			!is_numeric($this->args['number_cates'])) {
			return '';
		}
		$categories = get_the_category();
		if (empty($categories)) {
			return '';
		}
		
		$ret_cats = array();
		
		/* get primary cat */ 
		/* compatible with SEO by Yoast 
		 * Show the post's 'Primary' category, 
		 * if this Yoast feature is available, 
		 * Show the post's 'Primary' category, 
		 */	
		$primary_cat_id = 0;
		if ( class_exists('WPSEO_Primary_Term') ) {							
			$wpseo_primary_term = new WPSEO_Primary_Term( 'category', $this->ID );			
			$primary_cat_id = $wpseo_primary_term->get_primary_term();
		}
		
		$limit = (int) $this->args['number_cates'];
		$index = 0;
		foreach($categories as $cat) :	
			/* not add to ret list more if reached limit
			 * but we will not break if we have primary id 
			 * and it still not in ret list
			 * 
			 * so if we have primary cat id, we will add
			 * cat until the primary cat stay in ret list
			 */
			if ($index >= $limit &&
				(!$primary_cat_id || !empty($ret_cats[$primary_cat_id]))) {
				break;
			}
						
			$ret_cats[$cat->term_id] = $cat;				
			$index++;
		endforeach;
		
		/* move the primary cat to beginning of list
		 * to make sure the primary cat show first
		 * regardless the limit	 
		 * */
		if (!empty($ret_cats[$primary_cat_id]) && count($ret_cats) > 1) {				
			$temp = array();
			$temp[$primary_cat_id] = $ret_cats[$primary_cat_id];
			unset($ret_cats[$primary_cat_id]);
			$ret_cats = $temp + $ret_cats;	
		}
		
		
		/* now show the ret list*/
		$ret = '';
		$sep = ', ';
		if (isset($this->args['categories_sep'])) {
			$sep = $this->args['categories_sep'];
		}
		
		$index = 0;
		foreach($ret_cats as $cat_id => $cat) :						
			if ($ret) {
				$ret .= $sep;
			}
			
			$ret .= 
			'<a class="item-category" href="'.esc_url(get_category_link( $cat_id )).'" title="' . esc_attr($cat->cat_name) . '">' . esc_html( $cat->cat_name ) . '</a>';				
			$index++;
			if ($index >= $limit) {
				break;
			}
		endforeach;
		
		if ($ret) {
			$ret = '<span class="item-categories">' . $ret . '</span>';
		}
		
		return $ret;
	}
	
	/**
	 * 
	 */
	public function title() {
		return '<h3 class="item-title"><a href="'.$this->permalink.'" title="'.$this->title_esc_attr.'">'.$this->title.'</a></h3>';
	}
	
	/**
	 * 
	 */
	public function meta() {
		
		if (empty($this->args['meta_order'])) {
			return '';
		}
		$meta_order = explode(',', $this->args['meta_order']);
		$ret = '';
		foreach ($meta_order as $meta) {
			switch ($meta) {
				case 'cat':
					$ret .= $this->categories();
					break;
				
				case 'ico':
					$ret .= $this->format_icon();
					break;
				
				case 'review':
					$ret .= $this->review();
					break;
				
				case 'view':
					$ret .= $this->views();
					break;
				
				case 'author':
					$ret .= $this->author();
					break;
				
				case 'comment':
					$ret .= $this->comment_count();
					break;
				
				case 'date':
					$ret .= $this->date_time();
					break;
				
				case 'read-more':
					$ret .= $this->read_more_link();
					break;
				
				default:
					break;
			}
		}
		
		if ($ret) {
			$ret = '<span class="item-meta">'.$ret.'</span>';
		}
		
		return $ret;
	}
	
	/**
	 * 
	 */
	public function snippet($show_read_more = true) {
		if (empty($this->args['snippet_length'])) {
			return '';
		}		
		
		// check from excerpt first
		if (has_excerpt()) {
			$snippet = get_the_excerpt();
		} else {
			$snippet = wp_kses($this->content(), array());
		}
		
		// cut off snippet 
		$snippet = wp_trim_words( $snippet, (int) $this->args['snippet_length'], ' ...' );
		return 
		'<p class="item-snippet"><span>'.$snippet . '</span> ' . 
			($show_read_more ? $this->read_more_link() : '') . 
		'</p>';
	}
	
	
	/**
	 * since 5.0
	 * we use read_more_text to check if you enable 
	 * read more or not instead of show_readmore option
	 * 
	 * So if your theme created before 5.0,
	 * please kindly update it to compatible with this 
	 * new check up way
	 * 
	 * @return string
	 */
	public function read_more_link() {
		
		if (empty($this->args['read_more_text'])) {
			return '';
		}
		return '<a class="item-read-more" title="'.$this->title_esc_attr.'" href="'.$this->permalink.'#more">'.$this->args['before_read_more'].$this->args['read_more_text'].$this->args['after_read_more'].'</a>';
	}
	
	
	
	/**
	 * since 5.1
	 * show some basic sharing buttons (without style)
	 * 
	 * not recommend using this because it will increase
	 * number of HTML on load and also the number of link on archive
	 * That's why we not auto collaborate with args settings to show this
	 * But if you want to use it, just create your own block option,
	 * manually check and show.
	 * */
	public function sharing_buttons($args = array()) {
		if (is_string($args)) {
			$networks = $args;
			$args = array();
			$args['networks'] = $networks;			
		}
		
		$args = wp_parse_args($args, array(
			'before' => '<div class="item-sharing">',
			'after' => '</div>',
			'link_class' => '',		
			
			/* network can be array:
			 * key (network name) => value (text to display for button)
			 * 
			 * if network is string like: facebook, twitter
			 * it will be splitted by "," and key will be the value, 
			 * when text will be fontawesome icon
			 */
			
			/**
			 * @since 5.1
			 * show the name of network */
			'show_name' => false,
		));		
		
		if (empty($args['networks'])) {				
			return;
		}
		
		if (is_string($args['networks'])) {
			$args['networks'] = explode(',', $args['networks']);
		}		
		
		$html = '';
		
		foreach ($args['networks'] as $key => $value) {
			$network = $value;
								
			// validate data
			if (is_array($value) || !is_numeric($key)) {
				$network = $key;
			}			
			
			$link = '';
			$link_text = '';
			switch (trim(strtolower($network))) {
				case 'facebook':
				case 'fb':
				case 'face':
					$network = 'facebook';
					$link = 'https://www.facebook.com/sharer.php?u='.esc_url($this->permalink);
					$link_text = '<i class="fa fa-facebook"></i>';					
					break;
				
				case 'mail':
				case 'email':
				case 'e-mail':
					$network = 'e-mail';
					$link = 'mailto:?subject='.esc_url($this->title_esc_attr).'&body='.esc_attr($this->permalink);
					$link_text = '<i class="fa fa-envelope-o"></i>';
					break;
				
				case 'twitter' :
				case 'tw' :
				case 'tweet' :
					$network = 'twitter';
					$link = 'https://twitter.com/intent/tweet?text='.esc_url($this->title_esc_attr).'&url='.esc_url($this->permalink);
					$link_text = '<i class="fa fa-twitter"></i>';
					break;
				
				case 'pin' :
				case 'pinterest' :
					$network = 'pinterest';
					$link = 'https://uk.pinterest.com/pin/create/bookmarklet/?url='.esc_url($this->permalink).'&title='.esc_url($this->title_esc_attr);
					$link_text = '<i class="fa fa-pinterest-p"></i>';
					break;
				
				case 'whatsapp' :
					$network = 'whatsapp';
					$link = 'whatsapp://send?text='.esc_url($this->title_esc_attr . ' ' . $this->permalink);
					$link_text = '<i class="fa fa-whatsapp"></i>';
					break;
				
				case 'linkedin' :
				case 'linked' :
				case 'in' :
					$network = 'linkedin';
					$link = 'https://www.linkedin.com/shareArticle?mini=true&url='.esc_url($this->permalink).'&title='.esc_url($this->title_esc_attr);
					$link_text = '<i class="fa fa-linkedin"></i>';
					break;
				
				case 'skype':
					$network = 'skype';
					$link = 'https://web.skype.com/share?url='.esc_url($this->permalink);
					$link_text = '<i class="fa fa-skype"></i>';
					break;
				
				case 'google-plus' :
				case 'g+' :
				case 'g-plus' :
				case 'gplus' :
				case 'google+' :
				case 'googleplus' :
					$network = 'google-plus';
					$link = 'https://plus.google.com/share?url='.esc_url($this->permalink);
					$link_text = '<i class="fa fa-google-plus"></i>';
					break;
				
				default:
					break;
			}
			
			/**
			 * @since 5.0
			 * Show name of network if user want
			 */
			if (!empty($args['show_name'])) {
				$link_text .= '<span>' . ucfirst($network) . '</span>';
			}
			
			if ($link) {
				$before_link = '';
				$after_link = '';
				$before_text = '';
				$after_text = '';
				
				
				// get before / after link tag
				if (!empty($args['before_link'])) {
					$before_link = $args['before_link'];
				}
				if (!empty($value['before_link'])) {
					$before_link = $args['before_link'];
				}
				if (!empty($args['after_link'])) {
					$after_link = $args['after_link'];
				}
				if (!empty($value['after_link'])) {
					$after_link = $args['after_link'];
				}

				// get before / after text tag
				if (!empty($args['before_text'])) {
					$before_link = $args['before_text'];
				}
				if (!empty($value['before_text'])) {
					$before_link = $args['before_text'];
				}
				if (!empty($args['after_text'])) {
					$after_link = $args['after_text'];
				}
				if (!empty($value['after_text'])) {
					$after_link = $args['after_text'];
				}
				
				
				// link text data
				if (!is_numeric($key) && is_string($value)) {
					$link_text = $value['text'];
				} else if (is_array($value) && !empty ($value['text'])) {
					$link_text = $value['text'];				
				}
			}
			
			$target = 'target="_blank"';
			if (!empty($args['window']) || !isset($args['window'])) {
				$target = 'onclick="window.open(this.href, \'mywin\',\'left=50,top=50,width=600,height=350,toolbar=0\'); return false;"';
			}
			
			if ($args['link_class']) {
				$args['link_class'] .= ' ';
			}
			
			$html .= 
				$before_link .
					'<a href="'.$link.'" '.$target.' class="'.$args['link_class'].$network.'" title="'.esc_attr(ucfirst($network)).'">' . 
						$before_text . $link_text . $after_text .
					'</a>' .
				$after_link;			
		}
		
		if ($html) {
			$html = $args['before'].$html.$args['after'];			
		}
		
		return $html;
	}
}
