<?php
/*

{{ ex:nav_active startswith="/about" }}

*/
$plugin['ex:blogs'] = function ($options) use (&$in) {
	$limitMet = 0;
	$limit = $options['hash']['limit'] ?? 10;
	$path = $options['hash']['path'] ?? 'blogs';
	$set = $options['hash']['set'] ?? 'entries';

	$sorted = [];

	$rootPath = \FS::resolve(service('file')->getDataPath());
	$searchPath =  ltrim($path, '/');
	$completePath = $rootPath . '/' . $searchPath;
	$foundFiles = \FS::glob($completePath . '/*', 0, true, true);

	foreach ($foundFiles as $file) {
		if (\FS::is_file($file)) {
			$sorted[]['path'] = $file;
			if (++$limitMet >= $limit) {
				break;
			}
		}
	}

	/* put it in a variable other plugins can get to */
	$in[$set] = $sorted;

	return '';
};
