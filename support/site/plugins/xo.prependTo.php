<?php
/*

	{{xo.prependTo page.css="Johnny Appleseed" age="23" food="cookie" }}

*/

$plugin['xo:prependTo'] = function ($options) use (&$in) {
	foreach ($options['hash'] as $name => $value) {
		$current = \array_get_by($in, $name, '');

		$newValue = $value . $current;

		\array_set_by($in, $name, $newValue);
	}
};
