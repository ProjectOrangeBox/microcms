<?php
/*

{{ xo:stringFormat format="There are %d monkeys in the %s" "5" "tree" }}
{{ xo:stringFormat format="There are %d monkeys in the %s" "5" "tree" set="newvalue" }}

*/

$plugin['xo:stringFormat'] = function () use (&$in) {
	$format = pluginInput($options, 'format', '');
	$escape = pluginInput($options, 'escape', false);
	$set = pluginInput($options, 'set', false);

	/* first is string */
	$args = func_get_args();

	/* last argument is plugin options - pop that off */
	$options = array_pop($args);

	array_unshift($args, $format);

	$newValue = call_user_func_array('sprintf', $args);

	if (!$set) {
		if ($escape) {
			return $newValue;
		} else {
			return new \LightnCandy\SafeString($newValue);
		}
	} else {
		$in[$set] = $newValue;
	}

	return '';
};
