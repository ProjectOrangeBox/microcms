<?php
/*

{{ xo:function strtolower "5" "tree" }}
{{ xo:function trim app.captured.folder "/" set="newvalue" }}
{{ xo:function trim app.captured.folder "/" raw="true" }}

*/

$plugin['xo:function'] = function () use (&$in) {
	/* first is string */
	$args = func_get_args();

	/* last argument is plugin options - pop that off */
	$options = array_pop($args);

	/* the php function is the first */
	$function = array_shift($args);

	if (isset($options['fn'])) {
		$args = [$options['fn']($options['_this'])] + $args;
	}

	$newValue = call_user_func_array($function, $args);

	if (!isset($options['hash']['set'])) {
		return $newValue;
	}

	$in[$options['hash']['set']] = $newValue;

	return '';
};
