<?php
define('SNEEIT_HREF_VOID', 'href="javascript:void(0)" ');

define('SNEEIT_CACHE_TIME', 86400); /*60*60*24 = 1 day*/
define('SNEEIT_ADMIN_CACHE_REFRESH_TIME', 3600); /* 60 mins */
define('SNEEIT_ADMIN_CACHE_REFRESH_TIME_KEY', 'sneeit_admin_cache'); /* 30 mins */

define('SNEEIT_REMOTE_TIMEOUT', 60); /* 50 seconds*/

define('SNEEIT_MAX_NESTED_COLUMN_LEVEL', 20);
define('SNEEIT_NESTED_COLUMN_SEPARATOR', 'sneeit-shortcode-enable-nested');
define('SNEEIT_MAX_VIMEO_VIDEO_ID_LENGTH', 10);
define('SNEEIT_FULL_HEIGHT_IMAGE', -10);
define('SNEEIT_FULL_POST_SNIPPET', -10);


/** 
 * META KEY DEFAULT KEY 
 * - when we say POST_, usually meta key
 * - when we say ARTICLE_, usually option or mod
 */
define('SNEEIT_KEY_POST_VIEWS', 'post-views'); // save array of view objects
define('SNEEIT_KEY_POST_VIEW_COUNT', 'post-view-count'); // save number of views as integer
define('SNEEIT_KEY_POST_REVIEW_AVERAGE', 'post-review-average');
define('SNEEIT_KEY_POST_REVIEW_TYPE', 'post-review-type');
define('SNEEIT_KEY_POST_REVIEW', 'post-review');
define('SNEEIT_KEY_POST_FEATURE_MEDIA', 'post-feature-media');
define('SNEEIT_KEY_POST_FEATURE_CAPTION', 'post-feature-caption');

define('SNEEIT_KEY_ARTICLE_BREADCRUMB', 'article-breadcrumb');
define('SNEEIT_KEY_ARTICLE_FEATURE', 'article-feature');
define('SNEEIT_KEY_ARTICLE_EXCERPT', 'article-excerpt');

define('SNEEIT_KEY_ARTICLE_SHARING_BUTTONS', 'article-sharing-buttons');
define('SNEEIT_KEY_ARTICLE_SHARING_POSITION', 'article-sharing-position');
define('SNEEIT_KEY_ARTICLE_SHARING_CUSTOM_CODE', 'article-sharing-custom-code');

define('SNEEIT_KEY_ARTICLE_AUTHOR', 'article-author');
define('SNEEIT_KEY_ARTICLE_DATE_TIME', 'article-date-time');
define('SNEEIT_KEY_ARTICLE_COMMENTS_NUMBER', 'article-comments-number');
define('SNEEIT_KEY_ARTICLE_CATEGORIES', 'article-categories');
define('SNEEIT_KEY_ARTICLE_TAGS', 'article-tags');
define('SNEEIT_KEY_ARTICLE_AUTHOR_BOX', 'article-author-box');
define('SNEEIT_KEY_USER_SOCIAL_LINKS', 'user-social-links');
define('SNEEIT_KEY_ARTICLE_NEXTPREV', 'article-nextprev');

define('SNEEIT_KEY_SITE_LOGO', 'site-logo');

$whitelist = array(
    '127.0.0.1',
    '::1'
);


/* MISC */
define('SNEEIT_IS_LOCALHOST', 
	in_array($_SERVER['REMOTE_ADDR'], array(
		'127.0.0.1',
		'::1'
		)
	)
);

define('SNEEIT_IS_RLT', is_rtl());

define('SNEEIT_MIN_ENQUEUE', SNEEIT_IS_LOCALHOST ? '' : '.min');

define('SNEEIT_KEY_SNEEIT_EXPORT_IMPORT', 'snfwto_export_import');
define('SNEEIT_KEY_SNEEIT_EXPORT', 'snfwto_export');
define('SNEEIT_KEY_SNEEIT_IMPORT', 'snfwto_import');
