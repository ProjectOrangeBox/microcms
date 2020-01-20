<?php
/*

	{{xo:merge file="examples/foo.ini" template="_partials/example"}}
	{{xo:merge file="examples/foo.ini"}}

	if the imported variables includes a template key then it will be used.
	this makes it so variable files can specify templates

*/
$plugin['xo:merge'] = function ($options) use (&$in) {
	$escape = pluginInput($options, 'escape', false);
	$namespace = pluginInput($options, 'namespace', false);

	$data = [];

	try {
		if (isset($options['hash']['file'])) {
			$data = service('file')->load($options['hash']['file']);
		}

		if (isset($options['hash']['array'])) {
			$data = service('file')->array($options['hash']['array']);
		}

		if (isset($options['hash']['ini'])) {
			$data = service('file')->ini($options['hash']['ini']);
		}

		if (isset($options['hash']['json'])) {
			$data = service('file')->json($options['hash']['json']);
		}

		if (isset($options['hash']['yaml'])) {
			$data = service('file')->yaml($options['hash']['yaml']);
		}
	} catch (\Exception $e) {
		showException($e);
	}

	if ($namespace) {
		$in[$namespace] = $data;
	} else {
		$in = array_replace_recursive($in, $data);
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
			$html = service('parser')->html->parse($template, $in, true);
		} else {
			$html = new \LightnCandy\SafeString(service('parser')->html->parse($template, $in, true));
		}
	} catch (Exception $e) {
		showException($e);
	}

	return $html;
};
