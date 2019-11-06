<?php
/*


	{{#is_odd variable}}
		is odd!
	{{/is_odd}}

	{{#is_odd variable}}
		is odd!
	{{else}}
		is not odd!
	{{/is_odd}}

*/

$plugin['is_odd'] = function ($value, $options) {
	/* parse the "then" (fn) or the "else" (inverse) */
	$return = '';

	if ($value % 2) {
		$return = $options['fn']($options['_this']);
	} elseif ($options['inverse'] instanceof \Closure) {
		$return = $options['inverse']($options['_this']);
	}

	return $return;
};
