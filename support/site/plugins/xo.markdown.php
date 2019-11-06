<?php
/*

{{ xo:markdown file="files/markdown.md" }}

*/
$plugin['xo:markdown'] = function ($options) {
	$file = $options['hash']['file'];

	$path = trim(pathinfo($file, PATHINFO_DIRNAME) . '/' . pathinfo($file, PATHINFO_FILENAME), './') . '.md';

	/* markdown to html */
	$html = c()->file->md($path);

	return new \LightnCandy\SafeString($html);
};
