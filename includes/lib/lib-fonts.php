<?php
global $Sneeit_Safe_Fonts;
global $Sneeit_Google_Fonts;
global $Sneeit_Upload_Fonts;
global $Sneeit_Font_Extensions;
global $Sneeit_Font_Sizes;

$Sneeit_Safe_Fonts = array(
	'Arial' => 'Arial, sans-serif',
	'Arial Black' => '"Arial Black", Gadget, sans-serif',

	'Charcoal' => 'Charcoal, sans-serif',
	'Comic Sans MS' => '"Comic Sans MS", cursive, sans-serif',
	'Courier New' => '"Courier New", Courier, monospace',

	'Geneva' => 'Geneva, sans-serif',
	'Georgia' => 'Georgia, serif',

	'Helvetica' => 'Helvetica, sans-serif',

	'Impact' => 'Impact, sans-serif',

	'Lucida Console' => '"Lucida Console", Monaco, monospace',
	'Lucida Sans' => '"Lucida Sans Unicode", "Lucida Grande", sans-serif',


	'Palatino Linotype' => '"Palatino Linotype", "Book Antiqua", Palatino, serif',

	'Tahoma' => 'Tahoma sans-serif',
	'Times New Roman' => '"Times New Roman", Times, serif',
	'Trebuchet MS' => '"Trebuchet MS", Helvetica, sans-serif',
	
	'Verdana' => 'Verdana, sans-serif'	
);

$Sneeit_Google_Fonts = array(
	'Abel' => '',
	'Architects Daughter' => 'cursive',
	'Alegreya' => '400,400italic,700,700italic,900,900italic',
	'Armata' => '',
	'Anton' => '',
	'Archivo Narrow' => '400,400italic,700,700italic',
	'Arimo' => '400,400italic,700,700italic',
	'Arvo' => '400,400italic,700,700italic',
	'Asap' => '400,400italic,700,700italic',

	'Bitter' => '400,400italic,700',
	'Bree Serif' => 'serif',

	'Cabin' => '400,400italic,500,500italic,600,600italic,700,700italic',
	'Cabin Condensed' => '400,500,600,700',
	'Calligraffitti' => 'cursive',
	'Cantarell' => '400,400italic,700,700italic',
	'Changa One' => '400,400italic',
	'Chewy' => 'cursive',
	'Comfortaa' => '400,300,700',
	'Coming Soon' => 'cursive',
	'Crafty Girls' => 'cursive',
	'Crete Round' => '400,400italic',
	'Crimson Text' => '400,400italic,600,600italic,700,700italic',
	'Cuprum' => '400,400italic,700,700italic',

	'Dancing Script' => '400,700',
	'Dosis' => '200,300,400,500,600,700,800',
	'Droid Sans' => '400,700',
	'Droid Sans Mono' => 'empty',
	'Droid Serif' => '400,400italic,700,700italic',

	'Exo' => '400,100,100italic,200,200italic,300,300italic,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic',

	'Fjalla One' => '',
	'Francois One' => '',
	'Fredoka One' => 'cursive',

	'Gloria Hallelujah' => 'cursive',
	'Gudea' => '400,400italic,700',

	'Hammersmith One' => '',
	'Hind' => '400,300,500,600,700',

	'Inconsolata' => '400,700',
	'Indie Flower' => 'cursive',
	'Istok Web' => '400,400italic,700,700italic',

	'Josefin Sans' => '400,100,100italic,300,300italic,400italic,600,600italic,700,700italic',
	'Josefin Slab' => '400,100,100italic,300,300italic,400italic,600,600italic,700,700italic',

	'Karla' => '400,400italic,700,700italic',
	'Kreon' => '400,300,700',

	'Lato' => '400,100,100italic,300,300italic,400italic,700,700italic,900,900italic',
	'Libre Baskerville' => '400,400italic,700',
	'Lobster' => 'cursive',
	'Lora' => '400,400italic,700,700italic',
	'Luckiest Guy' => 'cursive',

	'Maven Pro' => '400,500,700,900',
	'Merriweather' => '400,300,300italic,400italic,700,700italic,900,900italic',
	'Merriweather Sans' => '400,300,300italic,400italic,700,700italic,800,800italic',
	'Monda' => '400,700',
	'Montserrat' => '400,700',
	'Muli' => '400,300,300italic,400italic',

	'News Cycle' => '400,700',
	'Nobile' => '400,400italic,700,700italic',
	'Noto Sans' => '400,400italic,700,700italic',
	'Noto Serif' => '400,400italic,700,700italic',	
	'Nunito' => '400,300,700',

	'Open Sans' => '400,300,300italic,400italic,600,600italic,700,700italic,800,800italic',
	'Open Sans Condensed' => '300,300italic,700',
	'Oswald' => '400,300,700',
	'Oxygen' => '400,300,700',

	'Pacifico' => 'cursive',
	'Pathway Gothic One' => '',
	'Philosopher' => '400,400italic,700,700italic',
	'Play' => '400,700',
	'Playfair Display' => '400,400italic,700,700italic,900,900italic',
	'Poiret One' => 'cursive',
	'Pontano Sans' => '',
	'PT Sans' => '400,400itali,700,700italic',
	'PT Sans Caption' => '400,700',
	'PT Sans Narrow' => '400,700',
	'PT Serif' => '400,400italic,700,700italic',

	'Quattrocento Sans' => '400,400italic,700,700italic',
	'Questrial' => '',
	'Quicksand' => '400,300,700',

	'Raleway' => '400,100,200,300,500,600,700,800,900',
	'Righteous' => 'cursive',
	'Roboto' => '400,100,100italic,300,300italic,400italic,500,500italic,700,700italic,900,900italic',
	'Roboto Condensed' => '400,300,300italic,400italic,700,700italic',
	'Roboto Slab' => '400,100,300,700',
	'Rock Salt' => 'cursive',
	'Rokkitt' => '400,700',
	'Ropa Sans' => '400,400italic',

	'Shadows Into Light' => 'cursive',
	'Signika' => '400,300,600,700',
	'Slabo 27px' => 'serif',
	'Source Sans Pro' => '400,200,200italic,300,300italic,400italic,600,600italic,700,700italic,900italic',
	'Special Elite' => 'cursive',
	'Squada One' => 'cursive',


	'Tangerine' => '400,700',
	'The Girl Next Door' => 'cursive',
	'Titillium Web' => '400,200,200italic,300,300italic,400italic,600,600italic,700,700italic,900',
		
	'Ubuntu' => '400,300,300italic,400italic,500,500italic,700,700italic',
	'Ubuntu Condensed' => '',
	'Unkempt' => '400,700',
		
	'Varela Round' => '',
	'Vollkorn' => '400,400italic,700,700italic',

	'Yanone Kaffeesatz' => '400,200,300,700'
);

$Sneeit_Upload_Fonts = null;

$Sneeit_Font_Extensions = array(
	'ttf' => 'application/x-font-ttf',		
	'otf' => 'font/opentype',
	'woff' => 'application/font-woff',
	'eot' => 'application/vnd.ms-fontobject',
	'oct' => 'application/octet-stream',
);

$Sneeit_Font_Sizes = array(8, 9, 10, 11, 12, 13, 14, 16, 17, 18, 20, 22, 24, 30, 36, 42, 50, 60, 70, 80);

add_filter('upload_mimes', 'sneeit_upload_mimes_fonts');
function sneeit_upload_mimes_fonts ( $existing_mimes=array() ) {
	global $Sneeit_Font_Extensions;
	foreach ($Sneeit_Font_Extensions as $extension => $mime_type) {
		$existing_mimes[$extension] = $mime_type;
	}
	return $existing_mimes;
}

function sneeit_get_uploaded_fonts() {
	global $Sneeit_Upload_Fonts;
	global $Sneeit_Font_Extensions;
	
	if ($Sneeit_Upload_Fonts == null && is_array($Sneeit_Font_Extensions)) :
		$Sneeit_Upload_Fonts = array();
		$the_query = new WP_Query( array( 
			'post_status' => 'any', 
			'post_type' => 'attachment',
			'post_mime_type' => $Sneeit_Font_Extensions)
		);
		
		// The Loop
		if (
			property_exists($the_query, 'posts') && 
			is_array($the_query->posts) &&
			count($the_query->posts)
		) {

			foreach ($the_query->posts as $font) {
				$font_url = wp_get_attachment_url($font->ID);
				if (
					empty($font->post_title) ||
					empty($font_url)
				){
					continue;
				}
				foreach ($Sneeit_Font_Extensions as $extension => $mime) {
					if (strpos($font_url, '.'.$extension) !== false) {
						$Sneeit_Upload_Fonts[$font->post_title] = $font_url;
						break;
					}					
				}				
			}
		}

		/* Restore original Post Data */
		wp_reset_postdata();
			
	endif;
}

function sneeit_get_font_family_css($setting_value, &$google_font_url, &$upload_font_url) {
	global $Sneeit_Safe_Fonts;
	global $Sneeit_Google_Fonts;	
	global $Sneeit_Upload_Fonts;
	$font_family_css = '';
	
	// santize $setting_value for raw font name
	$setting_value = str_replace('"', '', $setting_value);
	$setting_value = str_replace("'", "", $setting_value);
	$setting_value = trim($setting_value);
	$setting_value = explode(',', $setting_value);
	$setting_value = $setting_value[0];
	
	
//	var_dump($Sneeit_Google_Fonts['Rock Salt']);
	
	if (is_array($Sneeit_Safe_Fonts) && isset($Sneeit_Safe_Fonts[$setting_value]))  {
		$font_family_css = $Sneeit_Safe_Fonts[$setting_value];
	} else if (is_array($Sneeit_Google_Fonts) && isset($Sneeit_Google_Fonts[$setting_value]))  {
		$font_family_css = '"'.$setting_value.'", ';
		if ($Sneeit_Google_Fonts[$setting_value] == 'serif') {
			$font_family_css .= 'serif';
		} else if ($Sneeit_Google_Fonts[$setting_value] == 'cursive') {
			$font_family_css .= 'cursive';
		} else {
			$font_family_css .= 'sans-serif';
		}
		
		// add font to google url to enqueue later
		if (!array_key_exists($setting_value, $google_font_url)) {
			$font_name = str_replace(' ', '+', $setting_value);
			$google_font_url[$setting_value] = $font_name.':'.$Sneeit_Google_Fonts[$setting_value];
		}		
		
	} else if (is_array($Sneeit_Upload_Fonts) && isset($Sneeit_Upload_Fonts[$setting_value]))  {
		$font_family_css = '"'.$setting_value.'"';
		// add font to upload url to enqueue later
		if (!array_key_exists($setting_value, $upload_font_url)) {			
			$upload_font_url[$setting_value] = 'src: url("'.$Sneeit_Upload_Fonts[$setting_value].'")';
		}
	}

	
	return $font_family_css;
}


