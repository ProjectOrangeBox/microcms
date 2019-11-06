<?php
/*

	{{xo:merge file="examples/foo.ini" template="_partials/example"}}
	{{xo:merge file="examples/foo.ini"}}

	if the imported variables includes a template key then it will be used.
	this makes it so variable files can specify templates

*/
$plugin['xo:merge'] = function ($options) use (&$in) {
	$data = [];

	$escape = $options['hash']['escape'] ?? false;

	try {
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
	} catch (\Exception $e) {
		showException($e);
	}

	$namespace = ($options['hash']['namespace']) ?? false;

	foreach ($data as $name => $value) {
		if ($namespace) {
			$in[$namespace][$name] = $value;
		} else {
			$in[$name] = $value;
		}
	}

	$template = false;

	if (isset($data['template'])) {
		/* value from the merged file */
		$template = $data['template'];
	} elseif (isset($options['hash']['template'])) {
		/* template="_partial/calendar" */
		$template = $options['hash']['template'];
	}

	$template = '/' . trim($template, '/');

	try {
		if ($escape) {
			$html = c()->parser->html->parse($template, $in, true);
		} else {
			$html = new \LightnCandy\SafeString(c()->parser->html->parse($template, $in, true));
		}
	} catch (Exception $e) {
		showException($e);
	}

	return $html;
};
