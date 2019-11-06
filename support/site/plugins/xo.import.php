<?php
/*

	{{xo:merge file="examples/foo.ini" template="_partials/example"}}
	{{xo:merge file="examples/foo.ini"}}

	if the imported variables includes a template key then it will be used.
	this makes it so variable files can specify templates

*/
$plugin['xo:import'] = function ($options) use (&$in) {
	$data = [];

	if (isset($options['hash']['file'])) {
		$data = c()->file->load($options['hash']['file']);
	}

	if (isset($options['hash']['array'])) {
		$data = c()->file->array($options['hash']['array']);
	}

	if (isset($options['hash']['ini'])) {
		$data = c()->file->ini($options['hash']['ini']);
	}

	if (isset($options['hash']['json'])) {
		$data = c()->file->json($options['hash']['json']);
	}

	if (isset($options['hash']['yaml'])) {
		$data = c()->file->yaml($options['hash']['yaml']);
	}

	$namespace = ($options['hash']['namespace']) ?? false;

	foreach ($data as $name => $value) {
		if ($namespace) {
			$in[$namespace][$name] = $value;
		} else {
			$in[$name] = $value;
		}
	}

	return '';
};
