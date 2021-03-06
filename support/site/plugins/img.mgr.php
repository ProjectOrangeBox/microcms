<?php
/*

	{{xo.img src="age.jpg" width="128" height="128" }}

	<img src="age.jpg" width="128" height="128">

	{{xo.img src="age.jpg"}}

*/

$plugin['img:mgr'] = function ($options) {
	$config = service('config')->get('plugins');
	$imageNotFound = $config['image not found'] ?? 'ImageNotFound.png';
	$public = service('config')->get('paths.public');
	$name = $options['hash']['name'];

	$ext = $config['image default ext'];
	$path = '/' . trim($config['images'], '/') . '/';

	if (\FS::file_exists($path . $name)) {
		$path = $path . $name;
	} elseif (\FS::file_exists($path . $name . '.' . $ext)) {
		$path = $path . $name . '.' . $ext;
	} else {
		$path = $path . $imageNotFound;
	}

	$path = substr($path, strlen($public));

	$html = '<img src="' . $path . '"';

	if (isset($options['hash']['width'])) {
		$html .= ' width="' . $options['hash']['width'] . '" ';
	}

	if (isset($options['hash']['height'])) {
		$html .= ' height="' . $options['hash']['height'] . '" ';
	}

	$html .= '>';

	return new \LightnCandy\SafeString($html);
};
