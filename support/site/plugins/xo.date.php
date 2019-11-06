<?php
/*

	<div class="date">Posted on {{xo:date entry_date format="Y-m-d H:i:s"}}</div>

*/

$plugin['xo:date'] = function ($arg1, $options) {
	$timestamp = strtotime($arg1);

	$format = $options['hash']['format'] ?? 'Y-m-d H:i:s';

	return date($format, $timestamp);
};
