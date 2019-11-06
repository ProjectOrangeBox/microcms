<?php
/*

	{{xo:lookup key=value file="lookup.json"}}
	{{xo:lookup key=value json="lookup"}}
	{{xo:lookup key="name" json=value set="name"}}

*/
$plugin['xo:lookup'] = function ($options) use (&$in) {
	$searchKey = $options['hash']['key'];
	$default = $options['hash']['default'] ?? null;
	$set = $options['hash']['set'] ?? false;

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

	$keyFound = false;

	foreach ($data as $key => $value) {
		if ($key == $searchKey) {
			$keyFound = true;
			break;
		}
	}

	if ($keyFound && $set) {
		$in[$set] = $value;
	}

	return ($keyFound) ? $value : $default;
};