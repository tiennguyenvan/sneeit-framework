<?php
function sneeit_review_percent_circle($percent = 5, $type = 'point', $wrapper = 'span', $wrapper_attr = array()) {	
	
	// ui data	
	if (!isset($wrapper_attr['class'])) {
		$wrapper_attr['class'] = '';
	}
	if (!empty($wrapper_attr['class'])) {
		$wrapper_attr['class'] .= ' ';
	}
	$wrapper_attr['class'] .= 'sneeit-percent';
	
	$fill = ((int) (((int) $percent) - ((int) $percent) % 25)) + 25;
	$percent_text = $percent;
	if ($type == 'percent') {
		$percent_text = number_format($percent , 1);
	} else {
		$percent_text = number_format($percent / 10, 1);
	}
	

	// html
	$html = '<'.$wrapper;
	foreach ($wrapper_attr as $attr => $value) {
		$html .= ' '.$attr.'="'.$value.'"';
	}
	$html .= '>
<span class="sneeit-percent-bg"></span>
<span class="sneeit-percent-fill sneeit-percent-fill-'.$fill.'"></span>
<span class="sneeit-percent-modifier" style="transform: rotate('.($percent * 3.6 + 45).'deg);"></span>
'.($percent > 75 ? '<span class="sneeit-percent-mask"></span>' : '').'
<span class="sneeit-percent-text">'.$percent_text.'</span>';
	$html .= '</'.$wrapper.'>';
		
	// return
	return $html;
}