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

	$rootPath = \projectorangebox\cms\App::path(c()->file->getDataPath());
	$searchPath =  ltrim($path, '/');
	$completePath = $rootPath . '/' . $searchPath;
	$foundFiles = \projectorangebox\cms\App::globr($completePath . '/*');

	foreach ($foundFiles as $file) {
		if (\projectorangebox\cms\App::is_file($file)) {
			$sorted[]['path'] = substr($file, strlen($rootPath));
			if (++$limitMet >= $limit) {
				break;
			}
		}
	}

	/* put it in a variable other plugins can get to */
	$in[$set] = $sorted;

	return '';
};
