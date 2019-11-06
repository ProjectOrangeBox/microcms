<?php
/*

{{ ex:nav_active startswith="/about" }}

*/
$plugin['ex:nav_active'] = function ($options) {
	$startswith = $options['hash']['startswith'];
	$uri = '/' . c()->request->uri();

	$active = '';

	if (strlen($startswith) >= strlen($uri)) {
		$active = (substr($uri, 0, strlen($startswith)) == $startswith) ? 'active' : '';
	}

	return $active;
};
