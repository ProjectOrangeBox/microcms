<?php
/*

	{{xo.img src="age.jpg" width="128" height="128" }}

	<img src="age.jpg" width="128" height="128">

	{{xo.img src="age.jpg"}}

*/

$plugin['xo:img'] = function ($options) {
	$html = '<img src="' . $options['hash']['src'] . '"';

	if (isset($options['hash']['width'])) {
		$html .= ' width="' . $options['hash']['width'] . '" ';
	}

	if (isset($options['hash']['height'])) {
		$html .= ' height="' . $options['hash']['height'] . '" ';
	}

	$html .= '>';

	return new \LightnCandy\SafeString($html);
};
