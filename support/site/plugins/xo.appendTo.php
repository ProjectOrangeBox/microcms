<?php
/*

	{{xo.appendTo name="Johnny Appleseed" }}

*/

$plugin['xo:appendTo'] = function ($options) use (&$in) {
	foreach ($options['hash'] as $name => $value) {
		$current = \array_get_by($in, $name, '');

		$newValue = $current . $value;

		\array_set_by($in, $name, $newValue);
	}
};
