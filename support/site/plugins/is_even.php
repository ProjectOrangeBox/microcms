<?php
/*

	{{#is_even variable}}
		is even!
	{{/is_even}}

	{{#is_even variable}}
		is even!
	{{else}}
		is not even!
	{{/is_even}}

*/

$plugin['is_even'] = function ($value, $options) {
	/* parse the "then" (fn) or the "else" (inverse) */
	$return = '';

	if (!($value % 2)) {
		$return = $options['fn']($options['_this']);
	} elseif ($options['inverse'] instanceof \Closure) {
		$return = $options['inverse']($options['_this']);
	}

	return $return;
};
