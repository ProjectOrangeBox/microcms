<?php

/**
 * All of these can be overridden
 * by declaring the same function
 * before the App class is instantiated
 */

/* wrapper to get service from container which is attached to the application */
if (!function_exists('service')) {
	function service(string $serviceName = null)
	{
		return ($serviceName) ? \projectorangebox\cms\App::container()->get($serviceName) : \projectorangebox\cms\App::container();
	}
}

/* Wrapper */
if (!function_exists('log_message')) {
	function log_message(string $type, string $msg): void
	{
		/* Is log even attached to the container yet? */
		if (service()->has('log')) {
			service('log')->$type($msg);
		}
	}
}

/* The most basic exception handler */
if (!function_exists('showException')) {
	function showException($exception): void
	{
		$exception = (string) $exception;

		log_message('critical', $exception);

		echo '<h2>Exception Thrown:</h2><pre>Error: ' . $exception . '</pre>';

		exit(1);
	}
}

/**
 * Add some stateless functions
 */

function array_get_by(array $array, string $notation, $default = null) /* mixed */
{
	$value = $default;

	if (is_array($array) && array_key_exists($notation, $array)) {
		$value = $array[$notation];
	} elseif (is_object($array) && property_exists($array, $notation)) {
		$value = $array->$notation;
	} else {
		$segments = explode('.', $notation);

		foreach ($segments as $segment) {
			if (is_array($array) && array_key_exists($segment, $array)) {
				$value = $array = $array[$segment];
			} elseif (is_object($array) && property_exists($array, $segment)) {
				$value = $array = $array->$segment;
			} else {
				$value = $default;
				break;
			}
		}
	}

	return $value;
}

function array_set_by(array &$array, string $notation, $value): void
{
	$keys = explode('.', $notation);

	while (count($keys) > 1) {
		$key = array_shift($keys);

		if (!isset($array[$key])) {
			$array[$key] = [];
		}

		$array = &$array[$key];
	}

	$key = reset($keys);

	$array[$key] = $value;
}

function pluginInput(array &$array, string $key, $default = '##EMPTYVALUE##', string $regex = null) /* mixed */
{
	$value = $array['hash'][$key] ?? $default;

	if ($value === '##EMPTYVALUE##') {
		showException(new \Exception('Plugin Argument "' . $key . '" is required.'));
	}

	if ($regex) {
		if (!preg_match($regex, $value)) {
			showException(new \Exception('Plugin Value for the Argument "' . $key . '" failed validation.'));
		}
	}

	return $value;
}

function array_sort_by_column(array &$array, string $column, int $dir = SORT_ASC, int $flags = null)
{
	$sortColumn = array_column($array, $column);
	var_dump($sortColumn);
	array_multisort($sortColumn, $dir, $array, $flags);
}

function searchFor(string $path, string $cacheKey, \projectorangebox\cms\CacheInterface $cache): array
{
	/* build the complete cache key */
	$cacheKey = 'app.search.for.' . $cacheKey . '.php';

	if (!$found = $cache->get($cacheKey)) {
		$pathinfo = \pathinfo($path);

		$stripFromBeginning = $pathinfo['dirname'];
		$stripLen = \strlen($stripFromBeginning) + 1;

		$extension = $pathinfo['extension'];
		$extensionLen = \strlen($extension) + 1;

		$found = [];

		foreach (\FS::glob($path, 0, true, true) as $file) {
			$found[\strtolower(\substr($file, $stripLen, -$extensionLen))] = $file;
		}

		$cache->save($cacheKey, $found);
	}

	return $found;
}
