/**
 * https://stackoverflow.com/questions/21729895/jquery-conflict-with-native-prototype
 * @type String
 */
var DIV_CLEAR = '<div style="clear:both;float:none"></div>';
var SNEEIT_JS_LIB = true;

/**
 * PROTOTYPES
 */
// uppercase first letter
String.prototype.toCapitalize = function() {
	return this.charAt(0).toUpperCase() + this.slice(1);
};

// replace all
String.prototype.replaceAll = function(target, replacement) {
	return this.split(target).join(replacement);
};


// has, alias of indexof
String.prototype.has = function(str) {
	return (this.indexOf(str) != -1);
};
String.prototype.hasNot = function(str) {
	return !this.has(str);
};
String.prototype.isIn = function($a) {
	if (typeof($a) == 'object' || typeof($a) == 'Object') {
		for (var i in $a) {
			if (!$a.hasOwnProperty(i)) {
				continue;
			}
			if ($a[i] === this) {
				return true;
			}
		}
	} else if (typeof($a) == 'string' || typeof($a) == 'String') {
		return $a.indexOf(this) != -1;
	}
	
	return false;
}
String.prototype.isNotIn = function($a) {
	return !this.isIn($a);
}



// all prototypes for object must stay above array
// because array is sub of Object, so I can't revert the setup
Object.defineProperty(Object.prototype, 'unSet',{
  value : function(key) {
	  delete this[key];	
  },
  enumerable : false
});
Object.defineProperty(Object.prototype, 'isSet',{
  value : function(key) {
	  return (key in this);
  },
  enumerable : false
});
Object.defineProperty(Object.prototype, 'isNotSet',{
  value : function(key) {
	  return !this.isSet(key);
  },
  enumerable : false
});

Array.prototype.unSet = function(key) {
	this.splice(key, 1);
}

Array.prototype.isSet = function(key) {
	return (typeof(this[key]) != 'undefined')
}
Array.prototype.isNotSet = function(key) {
	return !this.isSet(key);
}





/*PHP SIMULATOR*/
function array() {
	if (arguments.length == 0) {
		return new Object();
	}
	if (arguments.length == 1) {
		return arguments.length[0];
	}
	var a = new Array();
	for (var i = 0; i < arguments.length; ++i) {
		a.push(arguments[i]);
	}
    
	return a;
}
function count($a) {
	if (typeof($a) == 'object') {
		var count = 0;
		for (var i in $a) {
			if (!$a.hasOwnProperty(i)) {
				continue;
			}
			count++;
		}
		return count;
	}
	return $a.length;
}

/**
 * Use for checking object properties only
 * not for direct or global variables / functions
 * @param {type} $a
 * @returns {Boolean}
 */
function empty($a) {
	if (typeof($a) == 'undefined') {
		return true;
	}
	if (typeof($a) == 'array' && !$a.length) {
		return;
	}
	if (typeof($a) == 'object') {	
		for (var i in $a) {
			if (!$a.hasOwnProperty(i)) {
				continue;
			}
			return false;
		}
		return true;
	}
	
	return !!!$a;
}
function explode($search, $object) {
	return $object.split($search);	
}
function implode($glue, $object) {
	return $object.join($glue);
}
function in_array($search, $a) {
	if (is_array($a)) {
		return $a.indexOf($search) != -1;
	}
	if (is_object($a) == 'object') {
		return ($search in $a);
	}	
	
	return false;
}
function is_string($a) {
	return (typeof($a) == 'string' || typeof($a) == 'String');
}
function is_array($a) {
	return ((typeof($a) == 'object' || typeof($a) == 'Object') && Array.isArray($a));
}
function is_object($o) {	
	return ((typeof($o) == 'object' || typeof($o) == 'Object') && !is_array($o));
}
function is_function($a) {
	return (typeof($a) == 'function' || typeof($a) == 'Function');
}
/**
 * Use for checking object properties only
 * not for direct or global variables / functions
 * @param {type} $a
 * @returns {Boolean}
 */
function is_number($n) {
	return (typeof($n) == 'number' || typeof($n) == 'Number');
}
/**
 * Use for checking object properties only
 * not for direct or global variables / functions
 * @param {type} $a
 * @returns {Boolean}
 */
function is_numeric($n) {
	return (isset($n) && (is_number($n) || !isNaN($n)));
}
/**
 * Use for checking object properties only
 * not for direct or global variables / functions
 * @param {type} $a
 * @returns {Boolean}
 */
function isset($a) {
	if (typeof($a) == 'undefined') {
		return false;
	}
	return true;
}
function isnotset($a) {
	return !isset($a);
}
function strpos($haystack, $needle) {
	let index = $haystack.indexOf($needle)
	if (index == -1) {
		return false;
	}
	
	return index;
}
function ord($s = '') {
	return $s.charCodeAt(0);
}

function str_replace($search, $replace, $object) {
	if (is_array($search)) {
		for (var i in $search) {
			if (!$search.hasOwnProperty(i)) {
				continue;
			}
			$object = $object.replaceAll($search[i], $replace);
		}
	} else {
		$object = $object.replaceAll($search, $replace);
	}
	return $object;
}
function strlen($s = '') {
	return $s.length;
}
function strtolower($s = '') {
	return $s.toLowerCase();
}
function strtoupper($s = '') {
	return $s.toUpperCase();
}
function ucfirst($s = '') {
	return $s.toCapitalize();
}
function unset($a, $key) {
	if (is_array($a) && isset($a[$key])) {
		$a.splice($key, 1);
	}
	else if (is_object($a) && ($key in $a)) {
		delete $a[$key];
	}
	
	return $a;
}
function _GET ($index = '', $value = '') {
	if (!empty($index) && !empty($value)) {
		return  !(empty($_GET[$index]) || $_GET[$index] !== $value);
	}
	
	$_GET = new Object();
	var search = window.location.search;
	if (search) {
		search = search.substring(1);// remove ?
		var list = search.split('&');
		for (var i = 0; i < list.length; i++) {
			var l = list[i].split('=');
			if (l.length > 1) {
				$_GET[l[0]] = l[1];
			}		
		}
	}
}

/* WORDPRESS SIMULATOR */
function add_query_arg( $args = new Object(), $url = location.href ) {	
	if (is_string($args)) {
		var args = $args.split('&');
		var $args = new Object();
		for (var i in args) {
			if (!args.hasOwnProperty(i)) {
				continue;
			}
			var a = args[i].split('=');
			
			$args[a[0]] = a[1];
		}
	}	
	
	var url = new URL($url);	
	var query = url.search;
	
	if (!empty(query)) {
		query = query.substring(1);
	}
	if (empty(query)) {
		query = new Array();
	} else {
		query = query.split('&');
	}
	
	for (var key in $args) {
		if (!$args.hasOwnProperty(key)) {
			continue;
		}
		var replacer = key+'='+$args[key];
		
		for (var k in query) {
			if (!query.hasOwnProperty(k)) {
				continue;
			}
			if (query[k].indexOf(key+'=') === 0) {
				query[k] = replacer;
				replacer = '';
				break;
			}
		}
		if (!replacer) {
			continue;
		}
		
		query.push(replacer);
	}
	query = query.join('&');
	
	url.search = '?' + query;
	
	return url.href;
}
function esc_attr($a = '') {
	return $a.replaceAll('"', '&quot;').replaceAll('\'', '&#39;')
}
function wp_parse_args($args, $defaults = array() ) {
	return {...$defaults,...$args};
}


/**
 * HTML lib
 */


/**
 * 
 * @param type $attr
 * @return type
 */
function html_tag_parse_attr($attr = array()) {			
	if (empty($attr)) {
		$attr = array();
	}
	
	// we don't need process empty attrs 
	// or array attrs
	if (empty($attr) || !is_string($attr)) {	
		return $attr;
	}
	
	// now, only string attr will start from here	
	// attr query as simple query, so it's id or class	
	if (false === strpos($attr, '=')) {
		if (strpos($attr, '#') === 0) {
			$attr = {'id' : str_replace('#', '', $attr)};
		} else {			
			$attr = {'class' : str_replace('.', ' ', $attr)};
		}		
		return $attr;
	}
		
	
	// attr query as complex string, parse it to an array
	// just in case it has many attr inside a query
	var all_attrs = explode('&', $attr);

	// double check to check if '&' in value
	var temp_attrs = new Array();
	
	
	for (var i in all_attrs) {
		if (!all_attrs.hasOwnProperty(i)) {
			continue;
		}
		
		var value = all_attrs[i];
		var temp_attrs_len = count(temp_attrs);
		// this is has no key=value, so it may be a part 
		// of previous value
		
		
		if (strpos(value, '=') === false) {
			// we need previous value to cat, if not, 
			// so this is not belong to any value
			if (!temp_attrs_len) {
				continue;
			}

			// this is belong to previous value
			temp_attrs[temp_attrs_len - 1] += ('&' + value);
			continue;
		}

		// this has key=value
		temp_attrs.push(value);
	}
	
	// extract to real attr array
	$attr = array();
	for (var i in temp_attrs) {
		if (!temp_attrs.hasOwnProperty(i)) {
			continue;
		}
		value = temp_attrs[i];
		value = explode('=', value);
		
		// has '=' in value
		if (count(value) > 2) {
			var temp_value = '';
			for (var key in value ) {
				if (!value.hasOwnProperty(key)) {
					continue;
				}
				var single_value = value[key];
				// at 0, it's attr key, not belong to value
				if (!key) {
					continue;
				}

				// connect all strings to value
				if (temp_value) {
					temp_value += '=';
				}
				temp_value += single_value;
			}
			value[1] = temp_value;
		}
		$attr[value[0]] = value[1];
	}

	return $attr;
}

/**
 * 
 * @param type $attr if attr is only the string, so that will be class attr
 * if attr is only string with format key=value&key2=value2 so that will be 
 * parsed as attr array like wordpress meta mixed query
 * @return string
 */
function html_tag_attr($attr = array()) {
	var html = '';
	if (empty($attr)) {
		return html;
	}
	
	$attr = html_tag_parse_attr($attr);
	
	for (var key in $attr) {
		if (!$attr.hasOwnProperty(key)) {
			continue;
		}
		var value = $attr[key];
		html += ' ' + key + '="' + esc_attr(value) + '"';
	}
	
	
	return html;
}

/**
 * 
 * @param type $name
 * @param type $content priority than attr because many tags have no attr
 * @param type $attr 
 * @param type $echo
 * @return type
 */
function html_tag($name = '', $attr = array(), $content = '') {
	if (empty($name)) {
		return;
	}
	
	
	
	var html = '<' + $name + html_tag_attr($attr);	
	
	if (in_array($name, array('img', 'meta', 'link', 'input', 'hr'))) {
		return (html + '/>');
	}
	
	return (html+='>' + $content + '</' + $name + '>');
}

/**
 * 
 * @param type $name
 * @param type $attr
 * @param type $echo
 * @return type
 */
function html_tag_open($name = '', $attr = array()) {
	if (empty($name)) {
		return;
	}
	
	return  ('<' + $name + html_tag_attr($attr) + '>');	
}

/**
 * 
 * @param type $name
 * @param type $echo
 * @return type
 */
function html_tag_close($name = '') {
	if (empty($name)) {
		return;
	}
	
	return ('</' + $name + '>');
}

/**
 * short way to print image
 * if need more complex image tag, use html_tag
 * 
 * @param string $src
 * @param type $echo
 * @param type $attr
 * @return string
 */
function html_tag_img($attr = array(), $src = '') {
	if (empty($src) && is_string($attr)) {
		$src = $attr;
	}
	
	if (empty($src)) {
		return '';
	}	

	$attr = html_tag_parse_attr($attr);	
	
	// local image
	if (strpos($src, 'http') === false) {
		$src = Sneeit.plugin_img_url + $src;
	}
	$attr['src'] = $src;
	
	return html_tag('img', $attr, '');
}

/**
 * 
https://material.io/resources/icons/
 */
function html_tag_icon($code = '') {	
	if (!$code.has('fa-')) {
		if ($code.has(' ')) {
			$code = $code.split(' ');
			$code = $code.join('fa-');
		} else {
			$code = 'fa-'+$code;
		}		
	}
	
	if (!$code.has('fa ')) {
		$code = 'fa ' + $code;
	}
	
	return html_tag('i', $code, '');
}

/**
 * 
 * @param type $content
 * @param type $href
 * @param type $echo
 * @param type $attr
 * @return type
 */
function html_tag_a($attr = array(), $content = '', $href = 'javascript: void(0)') {
	$attr = html_tag_parse_attr($attr);
	if ($href) {
		$attr['href'] = $href;
	}
	return html_tag('a', $attr, $content);
}

/**
 * 
 */
function html_tag_injects(scripts) {
	scripts.forEach(function(file_name){
	var link = file_name.indexOf('https://') != -1 ?
		file_name : chrome.extension.getURL(file_name);
		var file = null;
		if (file_name.indexOf('.js') != -1) {
			file = document.createElement('script');
			file.src = link;
			file.type = 'text/javascript';
		} else {
			file = document.createElement('link');
			file.href = link
			file.rel = 'stylesheet';
		}

		document.getElementsByTagName("head")[0].appendChild(file);
	});
}

/**
 * 
 * @param type $name
 * @param type $async
 * @param type $content
 * @param type $echo
 * @return type
 */
function html_tag_script($src = '', $content = '', $attr = array(), $add_to_head = false) {
	$attr = html_tag_parse_attr($attr);
	if (empty($attr['id'])) {
		$attr['id'] = ss_title_to_slug($src);
	}
	
	if ($src) {
		// auto replace script for RLT languages
		// and min file for front script if not localhost 
		if (false !== strpos($src, 'front-')) {
			if (!Sneeit.is_localhost) {
				$src.replace('.js', '.min.js');
				$src.replace('front-', 'min/front-');
			}
			if (Sneeit.is_rtl) {
				$src.replace('front-', 'rtl/rtl-front-');
			}			
		}
		
		// not an extenal script
		if (0 !== strpos($src, 'http')) {
			$src = Sneeit.plugin_js_url + '/' + $src;
		}
		
		$attr['src'] = $src;
		if (empty($attr['async'])) {
			$attr['async'] = true;
		}
		if (empty($attr['defer'])) {
			$attr['defer'] = true;
		}	
	}
	
	if (!empty($add_to_head)) {
		// this script is inserted
		if (!empty($attr['id']) && 
			document.getElementById($attr['id']) !== null
		){
			return;
		}
		var script = document.createElement("SCRIPT");
		script.type = 'text/javascript';
		if (!empty($attr['id'])) {
			script.id = $attr['id'];
		}		
		if (!empty($src)) {
			script.src = $src;
		}		
		if (!empty($content)) {
			script.text = $content;
		}
		
		if (!empty($attr['async'])) {
			script.async = '';
		}
		if (empty($attr['defer'])) {
			script['defer'] = '';
		}
		document.getElementsByTagName("head")[0].appendChild(script);
		return;
	}
	
	
	return html_tag('script', $attr, $content); 
}



/**
 * 
 * @param string $href
 * @param type $content
 * @param type $echo
 * @return type
 */
function html_tag_style($href = '', $content = '', $attr = array(), $add_to_head = false) {
	var attr = array();
	
	// just raw css
	if ($content) {
		attr['type'] = 'text/css';
		return html_tag('style', attr, $content);
	}
	
	// load file
	if ($href) {
		// not an extenal style
		if (0 !== strpos($href, 'http')) {
			$href = Sneeit.plugin_css_url + $href.sneeit_file_min + '.css';
		}
		
		attr['href'] = $href;
	}
	
	
	// preload link
	var html = '';
	attr['rel'] = 'preload';
	attr['as'] = 'style';
	attr['onload'] = 'this.rel=\'stylesheet\'';
	html += html_tag('link', attr, $content);
	
	// pre rel link
	attr['rel'] = 'stylesheet';
	attr['media'] = 'print';
	attr['onload'] = 'this.media=\'all\'';
	delete(attr['as']);
	html += html_tag('link', attr, $content);
	
	
	// noscript link
	delete(attr['onload']);
	delete(attr['media']);
	
		
	html += html_tag('noscript', '', html_tag('link', attr, '', false));
	
	return html;
}

function html_tag_form_start($attr = array()) {	
	$attr = html_tag_parse_attr($attr);
	
	if (empty($attr['method'])) {
		$attr['method'] = 'POST';
	}
	if (!isset($attr['action'])) {
		$attr['action'] = '';
	}
	
	$attr = html_tag_attr($attr);
	
	var ret = (
		'<form' +  $attr  + '>'
	);
	
	return ret;
}

function html_tag_form_end($submit_text = 'Send') {
	var html = '';
	
	var disable = strstr($submit_text, 'disabled');
	if (disable) {
		$submit_text = str_replace('disabled', '', $submit_text);
	}
	
	if ($submit_text) {
		var submit_attr = {
			'type' : 'submit',
			'value' : $submit_text		
		};
	
		if (disable) {
			submit_attr['disabled'] = 'disabled';
		}
		html += html_tag_open('div', 'scc-submit-wrapper', false);
		html += html_tag('input', submit_attr, '', false);
		html += html_tag_close('div', false);
	}
	
	return ( html + '</form>');
}


/**
 * 
 *#################
 */
/*SNEEIT LIB*/
function sneeit_is_image_src(src) {
	src = src.toLowerCase();
    return(src.match(/\.(jpeg|jpg|gif|png)$/) != null);
}
function sneeit_slug_to_title(slug) {
	return slug.replace(/_/gi, ' ').replace(/-/gi, ' ').replace(/^[a-z]/, function(m){ return m.toUpperCase() });
}
function sneeit_valid_font_awesome_code(icon_code) {
	var n0 = '0'.charCodeAt(0);
	var n9 = '9'.charCodeAt(0);
	var a  = 'a'.charCodeAt(0);
	var z  = 'z'.charCodeAt(0);
	var A  = 'A'.charCodeAt(0);
	var Z  = 'Z'.charCodeAt(0);
	var m  = '-'.charCodeAt(0);
	var s  = ' '.charCodeAt(0);
	var group = 'fa ';
	

	icon_code = icon_code.toLowerCase();
	if (icon_code.indexOf('fab ') != -1) {
		group = 'fab ';
	} 
	else if (icon_code.indexOf('fas ') != -1) {
		group = 'fas ';		
	}
	else if (icon_code.indexOf('far ') != -1) {
		group = 'far ';		
	}
	
	for (i = 0; i < icon_code.length; i++) {
		c = icon_code.charCodeAt(i);
		if (c >= n0 && c <= n9 ||
			c >=  a && c <= z ||
			c >=  A && c <= Z ||
			c ==  m || c == s) {
			continue;
		}
		icon_code = icon_code.substring(0, i) + '_' + icon_code.substring(i+1);
	}
	
	
	
	
	
	
	icon_code = icon_code
					.replaceAll('_', '')
					.replaceAll('fa-', '')
					.replaceAll('fa ', '');
	icon_code = icon_code.split(' ');
	
	
	return group + 'fa-'+icon_code.join(' fa-');
}

// include both font awesome and dashicons code
function sneeit_valid_icon_code(icon_code) {
	icon_code = icon_code.toLowerCase();
	if (typeof(jQuery) != 'undefined') {
		jQuery.trim(icon_code);
	}
	if (icon_code.indexOf('fa-') != -1) {
		icon_code = sneeit_valid_font_awesome_code(icon_code);
	} else {
		if (icon_code.indexOf('dashicons-') == -1) {
			icon_code = 'dashicons-'+icon_code;
		}
		if (icon_code.indexOf('dashicons ') != 0) {
			icon_code = 'dashicons ' + icon_code;
		}		
	}
	if (icon_code.indexOf('icon ') != 0) {
		icon_code = 'icon ' + icon_code;
	}
	return icon_code;
}
function sneeit_is_variable_name_character(character) {
	var character = character.charCodeAt(0);
	if (character >= 'a'.charCodeAt(0) && 
		character <= 'z'.charCodeAt(0) ||
		character >= 'A'.charCodeAt(0) &&
		character <= 'Z'.charCodeAt(0) ||
		character >= '0'.charCodeAt(0) &&
		character <= '9'.charCodeAt(0) ||
		character == '_'.charCodeAt(0)) {
		return true;
	}

	return false;
}
function sneeit_is_slug_name_character(character) {
	var character = character.charCodeAt(0);
	if (character >= 'a'.charCodeAt(0) && 
		character <= 'z'.charCodeAt(0) ||
		character >= 'A'.charCodeAt(0) &&
		character <= 'Z'.charCodeAt(0) ||
		character >= '0'.charCodeAt(0) &&
		character <= '9'.charCodeAt(0) ||
		character == '_'.charCodeAt(0) || 
		character == '-'.charCodeAt(0)) {
		return true;
	}

	return false;
}
function sneeit_parse_json(data) {
	try {
		data = jQuery.parseJSON(data);
	} catch (e) {
		// not JSON
		return false;
	}
	return data;
}

function sneeit_included_cookie() {
	if ('cookie' in document) {
		return true;
	}
	return false;
}
function sneeit_get_cookie(c_name) {
	if (!sneeit_included_cookie()) {
		return '';
	}
    var i,x,y,ARRcookies=document.cookie.split(";");
    for (i=0;i<ARRcookies.length;i++)
    {
        x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
        y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
        x=x.replace(/^\s+|\s+$/g,"");
        if (x==c_name)
        {
            return unescape(y);
        }
    }
	return '';
}
function sneeit_has_cookie() {
	if (sneeit_set_cookie('test', 'ok')) {
		return true;
	}
	return false;
}
function sneeit_set_cookie(c_name,value,exdays) {
	if (!sneeit_included_cookie()) {
		return false;
	}
    var exdate=new Date();
    exdate.setDate(exdate.getDate() + exdays);
    var c_value=escape(value) + ((exdays==null) ? '' : '; expires='+exdate.toUTCString())+'; path=/';
    document.cookie=c_name + "=" + c_value;
	if (sneeit_get_cookie(c_name) !== value) {
		return false;
	}
	return true;
}
function sneeit_delete_cookie(c_name) {
	if (!sneeit_included_cookie()) {
		return false;
	}
	document.cookie = c_name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
	return true;
}
function sneeit_has_storage() {
	if(typeof(localStorage) !== "undefined") {
		return true;
	} 
	return false;
}
function sneeit_set_storage(key,value) {
	if (sneeit_has_storage()) {
		localStorage.setItem(key,value);
		return true;
	}
	return false;
}
function sneeit_get_storage(key) {
	if (sneeit_has_storage()) {
		var ret = localStorage.getItem(key);
		if (ret) {
			return ret;
		}
	}
	return '';
}
function sneeit_update_option(option_name, option_value) {
	if (sneeit_has_storage()) {
		return sneeit_set_storage(option_name, option_value);
	} else if (sneeit_has_cookie()) {
		return sneeit_set_cookie(option_name, option_value);
	}
	return false;
}
function sneeit_get_option(option_name) {
	if (sneeit_has_storage()) {
		return sneeit_get_storage(option_name);
	} else if (sneeit_has_cookie()) {
		return sneeit_get_cookie(option_name);
	}
	return '';
}
function sneeit_add_query_arg(key, value, url) {
	if (typeof(url) == 'undefined') {
		url = document.location.href;
	}
    key = encodeURI(key); 
	value = encodeURI(value);
	
	// the search has no any key
	if (url.indexOf('?') == -1) {
		return (url + '?' + key + '=' + value);
	}
	
	url = url.split('?');
	url[1] = '&'+url[1];
		
	// the search has no this key
	if (url[1].indexOf('&' + key + '=') == -1) {
		url[1] = url[1].replace('&', '');
		return (url.join('?') + '&' + key + '=' + value);
	}
	
	// the search has this key
	url[1] = url[1].split('&' + key + '=');
	url[1][1] = url[1][1].split('&');
	url[1][1][0] = value;
	url[1][1] = url[1][1].join('&');
	url[1] = url[1].join('&' + key + '=');
	url[1] = url[1].replace('&', '');
	return url.join('?');
}
