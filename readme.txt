=== Sneeit Framework ===
Contributors: tien-nguyen
Tags: customize, customizer, sidebars, widgets, menu fields, menu locations, post meta boxes, user meta boxes, shortcodes, page builder, admin, admin interface, options, options framework, settings, web fonts, google fonts, theme framework
Requires at least: 3.7.0
Tested up to: 4.9.x
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sneeit Framework will generate dashboard UI for your Wordpress theme automatically. You just need to define setup arrays in your theme.


== Description ==

Sneeit Framework will help you save the time for developing your Wordpress theme dashboard. You only need to define some setup arrays for your theme, and this Wordpress plugin will generate the backend user interface automatically.

= Why Use Sneeit Framework? =
**Saving time in developing your Wordpress themes**
Do we need to spend a week to build dashboard functions for a theme? No, NEVER. You can build the whole dashboard of a theme in only 10 minutes. And everything you need to do is really and very simple: placing your arrays in your code and Sneeit Framework will generate everything.

**Saving time in maintaining your Wordpress themes**
You have hundred themes, and then getting errors on your themes's dashboard. What will you do? Waste a month to fix your dashboard code for 100 themes?

Don't. Be SMARTER. Just integrate a plugin like Sneeit Framework for your themes and everything you need to do when you getting errors is NOTHING. Your users will just need to update the plugin and your business will continue going smoothly.


= Features =
Define setup arrays in your theme and Sneeit Framework will generate the following features:
* Page Builder
* Shortcodes
* Customizer
* Sidebars
* Widgets
* Menu Locations
* Menu Custom Fields
* Post Meta Boxes
* User Meta Boxes

= Staying Touch =
Sneeit Framework is an ever-changing, living system. Want to stay up to date or contribute? Subscribe to one of our mailing lists or join us on [Facebook](https://www.facebook.com/Sneeit-622691404530609) or [Twitter](https://twitter.com/tiennguyentweet)


== Changelog ==

= 1.0 =
* Initial release

= 1.1 =
* Improve: allow specific depth level for custom menu fields 
* Improve: make better look for customizer controls

= 2.0 =
* Fixed: Menu field label is not right
* Fixed: allow css out for multi selector
* New: added action hook to adapted Blogger / Blogspot body class 
* New: added action hook to support HTML5 for IE 
* New: added action hook to support font-awesome 
* New: added action hook to support thread comment script 
* New: support upload font for customizer font picker 
* New: support visual picker for customizer 
* New: support priority of customizer panels and sections 
* New: support range / slide control for customizer 
* New: support custom sidebar action hook 
* New: support prefix and choices attributes for post meta box sidebar 
* New: support prefix and choices attributes for shortcode sidebar fields 
* New: support prefix and choices attributes for customizer sidebar fields 
* New: support range / slide control for shortcode fields 
* New: add class to custom shortcode button icon for custom style 
* New: support range / slide control for widget fields 
* New: support filter to get social counts without providing API ID or KEYS 
* New: support description for post meta box fields 
* New: support visual picker for post meta box fields 
* New: support visual picker for shortcode fields 
* New: support description for widget field
* New: support do_action to show breadcrumbs
* New: support filter to output fontawesome tag from code: sneeit_font_awesome_tag
* New: support filter to apply simple contact form
* New: support icon for customizer panel and section
* New: support reset button for customizer settings
* New: support declaration for custom sidebar
* New: support 1 click installation builder
* New: support shortcut to display sidebar on font-end

= 2.1 =
* Fixed: not compatible with PHP5.2

= 2.3 =
* Fixed: One Click Installation return fail if has no theme mod
* New: page builder support unlimited nested column inside column shortcode
* New: add new filter: sneeit_get_vimeo_id, sneeit_get_vimeo_player, sneeit_get_youtube_id, sneeit_get_youtube_player

= 2.4 = 
* Fixed: one click installation override the user roles

= 2.5 =
* New: support Envato Theme Activation and Auto Update

= 2.6 =
* Fixed: can not modify column attribute if has nested columns inside
* Fixed: page builder not keep adding text when switch to Text mode.
* Fixed: tutorial image for Envato API key can not be found
* Fixed: update FontAwesome to version 4.5.0
* Fixed: log out after install demo
* Fixed: can not active theme for auto update
* New: Have hook to check theme activated neither

= 2.7 =
* New: allow define dependency for controls
* Fixed: can not get some social counters
* Fixed: show icon for customizer panel also

= 2.7.1 =
* Fixed: can not pick category and tag in page builder 

= 2.8 =
* New: support 'sneeit_youtube_api_key_collector' and 'sneeit_social_api_key_collector' actions 

= 3.0 =
* New: allow add demo link to preview before install demo
* Fixed: demo installer demo export code is not right
* Fixed: demo installer not work with large number of files
* Fixed: demo installer logout user in some cases

= 3.1 =
* New: support 'media' type for customizer
* New: support theme options

= 3.2 =
* Fixed: theme option not work properly
* New: Dependency for theme option field

= 3.3 =
* New: support full rating / review system from backend to front end

= 3.4 =
* New: All features use same object to generate controls
* New: Update Documentation

= 3.8 = 
* Fixed: shortcode not display to contributors

= 4.2 =
* New: Export / Import for customizer and theme options
* New: Added control BOX MODEL
* New: Support compact menu
* New: Support field heading
* New: Support inline style
* New: Support tooltip for shortcode button in page builder panel
* New: Support article queries
* New: Support article queries pagination
* New: Support widget and sidebar backend tooltip
* New: Support post type for meta box fields
* New: Added responsive elements
* New: Added sticky elements
* New: Added page view utilities
* New: Added optimize image

= 4.3 = 
* New: support sneeit_widget_bundle (pending)
* New: added compact slider (pending)
* Fixed: auto update not work for some hosting (pending)
* Fixed: export / import settings - options did not delete temp files after finishing (pending)


= 5.0 =
* New: support include / exclude widgets for sidebar (not document)
* New: support sneeit-bg-thumb (not document)
* Fixed: change all readmore to read-more or read more
* Fixed: use read_more_text instead of show_readmore option


= 5.4 =
* Fixed: admin can't not login after install demo
* Fixed: Envato API cache not work properly on some servers


= 5.5 =
* Fixed: sticky column not work properly in some cases
* Fixed: taxonomy fields sometime not load in shortcode options
* Fixed: not enough memory when decode JSON for demo installation
* Fixed: can not get right number of Facebook and Instagram counter


= 5.6 =
* New: add filter for getting youtube image
* Fixed: review "percent" type not work
- Fixed: shortcode builder conflict with some plugins


= 5.7 =
* New: compatible with SEO Yoast Primary Category feature.
* New: support grid layout for front end


= 5.8 =
* Fixed: add rel parameter to allow removing related videos from youtube player

= 5.9 =
* Fixed: showing draft post in archive pages

= 6.1 =
* Fixed: rating css not work properly

= 6.2 =
* Fixed: min file missing

























- add option to show popular post in last 12h, 24h
- change update_option to sneeit_update_option to save server performance
- Widget searcher
- Add widget directly from sidebar
- Copy / Duplicate Widgets
- Copy / Duplicate Menu Items
- Have showcase feedback if user allowed after activating license. If disable theme, disable from show list also
- "Open in New Window" option for menu item
WHEN RELEASE
- minify files, if rtl file has no .min, it will use the same code as ltr

== To do list ==
- still get errors when switch tabs on Markup Format of WordPress test data (make wrong tags)

STRUCTURE OF VIEWS ARRAY IS REALLY BAD, IT WILL SLOW WHEN TOO MANY VIEWS
- show / hide widgets / blocks
- Optimize using Query Manager plugin
- Conflict to CKEditor plugin
- If add same shortcode many time, when editing one, the change mirror to the next
- SKIP javascript append in page builder (example: text field, content field, we can use noscript tag)
- WIDGET BUNDLE (Page 1 - 5)
	+ https://wordpress.org/plugins/black-studio-tinymce-widget/
	+ https://wordpress.org/plugins/so-widgets-bundle/
	+ https://wordpress.org/plugins/image-widget/
	+ https://wordpress.org/plugins/widget-logic/
	+ https://wordpress.org/plugins/wordpress-popular-posts/
	+ https://wordpress.org/plugins/display-widgets/
	+ https://wordpress.org/plugins/widget-importer-exporter/
	+ https://wordpress.org/plugins/contact-widgets/
	+ https://wordpress.org/plugins/recent-tweets-widget/
	+ https://wordpress.org/plugins/simple-social-icons/
	+ https://wordpress.org/plugins/social-media-widget/

- support <p> tag for Visual Editor (similar post editor output, included shortcode) inside shortcode content
- support fields for custom wp-admin pages
- get_term sem can not work with WP4.4
- move to shortcode, allow section for shortcode
- repeatable field
- menu icon for top and bottom
- post picker

- because override whole option table, so the field wp_user_roles can be override by a useless field but same ID, must check after write, if did not see, must recover. CHeck same with login transition to prevent logout
- demo switcher
- faster slider

wp_capabilities in user_meta table must be replace with PREFIX_capabilities

- post / page / custom post type / custom taxonomy picker
- document 'sneeit_youtube_api_key_collector' and 'sneeit_social_api_key_collector' actions 
* sortable rating criteria, also field (key) name to read data information
* instagram counter
* hang up when have a lot of tags and categories and authors (we must list by ajax or list only once like page builder)
* Add item detail: Best framework for theme developers
Author account can not see shortcode list
Can not get number of https://www.pinterest.com/sharp/
** ANY IMAGE RESIZER: https://developer.wordpress.org/reference/functions/wp_get_image_editor/#source-code
- update envato api to document
* content field need more perfect (special with other browser than Chrome)
* global menu settings
* Upload metabox (file field) for both user and post
* Keep changes when input content in text / visual mode then switching to page builder
* Page builder template manager
* Show shortcode as list
* Icons for panel and sections in panel of customize (even with default)
* custom taxonomy picker
* build demo sites:
	- https://wordpress.org/plugins/wp-demo-builder/
	- https://www.cozmoslabs.com/45226-create-wordpress-demo-site-with-wordpress-multisite/
	- https://www.webdesign.org/content-management-system/wordpress/how-to-make-a-wordpress-demo-site.21664.html
	- https://wpmututorials.com/how-to/theme-demo-sites/
	- https://thomasgriffin.io/create-live-demo-wordpress-plugin/
* Restrict display for custom sidebars
	
* 8d58e0bd-1a3e-4216-8496-ef322eb19fd3



* have button to allow clone widgets


==================================================================

WIDGET IDEAS:


BOTTOM PAGE widget doesn’t work when use to display bottom of all pages.
Just like UNDER POST CONTENT widget, there must be an additional another widget called ABOVE CONTENT widget to display banner ads below the Post Titles.
BEFORE CONTENT and AFTER CONTENT widgets must also be display for all Index Pages i.e. Home Page, Tag Page, Category Page, Search Page, Static Page, Archive Page etc. Currently it’s just display on Post Pages.



APPEARANCE CUSTOMIZE FEATURES IDEAS:

Under customize section (Colors, Fonts, Background), there is no option to change Top Menu Background Color when Hover mouse. See here example: https://pasteboard.co/csiVrLKKO.png
Under customize section (Post Content and Archive Page Design), there is no option to Hide Meta Comment Icon/Counts from Archive Pages and Post Pages, just like other option of Hiding Author and Date.
Under customize section (Colors, Fonts, Background), there is no option to Change Footer Widget Fonts and Size, just like other Sidebar Fonts changing option.
Under customize section, there is no option to Centralized Header Main Menu Text Links to be appear in middle of menu bar.
Under customize section, there is no option to Translate or Change Different Headings and Keywords or Texts of whole theme easily i.e. Read More, Recommended Article etc.
Under customize section, there is no option to Translate or Change Different Headings and Keywords or Texts of whole theme easily i.e. Read More, Recommended Article etc.
Under customize section, Just like Blogger MagOne version, on WordPress there is no option to include Tag/Category headings just before tag/category links appeared at below of articles, and also there is no option to add Share heading/title just before ShareThis sharing links, and also share buttons are not align with FontAwesome Share icons, and moved downside as appearing at below of articles. See here example: https://pasteboard.co/csvj16Ebg.png
Just like Blogger version, on WordPress there is no option of Post Pagination Short Code to be include within articles or widgets. Please include Post Pagination short code. On button Short Codes there is no option to use FontAwesome Icons with Buttons on Post Articles using Short Code.
On WordPress theme there is No Random Post Short Code available on theme, Just like Blogger MagOne version.
BUGS FIXES IDEAS:
There is a serious bug in MagOne WordPress version, under customize section (THEME OPTION), when we change any settings/options from Theme Customize section tab, But those changes doesn’t apply on THEME OPTION section under Customize tab. Under THEME OPTION all settings/options remain default, even after changing same settings/options from Theme Customize section.
Blow Post Titles, Article Excerpt (description) must display “META Description” of a particular article instead of showing Wordpress Post Excerpt description. Because Post Meta Description is more good for Excerpt. And when a user (like me) who don’t use WordPress Post Excerpt while writing articles, the MagOne theme shows Snippet as Excerpt, even after disabling “Don’t show Snippet as Excerpt” option under CUSTOMIZE section (Post Content).
As appearing Below Post Titles, Break Links texts Fonts Size and Article Excerpt (description) text Fonts Size doesn’t change with the change of overall SITE FONT SIZE under Customize section. See live example here: https://webloglab.com/nikon-s7000-review/
On Mobile Screens or Smaller Resolutions, FontAwesome Icons doesn't appear on Main Menu Bar and Top Menu Bar. Whereas this same feature is enabled on Blogger MagOne theme version. See example here: https://pasteboard.co/ct5ppfIEG.png or live example here https://webloglab.com
On Main Menu Bar, When i Add 10 to 12 texts links with FontAwesome Icons on Main Menu, then menu text links shows in two lines instead of single Menu Bar, Menu Bar should compress its size to place all 12 text links in one menu bar line. See example here: https://pasteboard.co/ct8Vr0WGF.png
FontAwesome icons doesnot dispay in Footer section, when we add Custom Menu widgets in Footer section or any kind of Widget Titles with FontAwesome Icons in Footer Area.
Under Customize section (Social Links), There must be an option to include additional Custom Social Media Profile Links or Custom Site Links in Top Right Header.
On Google Search, the site URL is appearing as BreadCrumbs, instead of appearing full length Post URL, this is not good for SEO purpose, there must be an option to select full length URL links appeared on Google Search Results. See example here: https://pasteboard.co/ctgkn8lr5.png
On homepage, main blog title is tag with <H1> heading, which is good. But what not good is, <H2> tags are appeared for sidebar title, and <H3> tags are appearing for article/post title. Which i think is Not Good for SEO. You must replace <h2> with <h3> and <h3> with <h2> on all Index Pages i.e. Home Page, Archive Page, Search Page, Tag Page, Category Page, Static Page etc. to improve blog SEO juice.
https://cssjanus.github.io/