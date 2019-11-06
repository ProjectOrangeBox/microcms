<?php
/*

	{{xo.set name="Johnny Appleseed" age="23" food="cookie" }}

*/
$plugin['xo:set'] = function ($options) use (&$in) {
	foreach ($options['hash'] as $name => $value) {
		$in[$name] = $value;
	}
};
