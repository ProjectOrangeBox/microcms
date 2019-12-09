<?php

/**
 * All of these can be overridden
 * by declaring the same function
 * before the App class is instantiated
 */

/* Wrapper */
if (!function_exists('c')) {
	function c()
	{
		/* return instance of container */
		return new \projectorangebox\cms\Container;
	}
}

/* Wrapper */
if (!function_exists('log_message')) {
	function log_message(string $type, string $msg): void
	{
		/* Is log even attached to the container yet? */
		if (isset(c()->log)) {
			c()->log->$type($msg);
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

/**
 * Get a value from an array using dot notation with a default if it's not found
 *
 * $foo = array_get_by($array,'name.first','Beats Me');
 *
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

/**
 * Set a value in an array using dot notation
 *
 * array_set_by($array,'name.first','Johnny Appleseed');
 *
 */
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

/**
 *	Get input from a passed array with optional default if it's not found
 *
 * $value = pluginInput($arguments,'name','Beats Me');
 *
 * With regular expression validation
 * $value = pluginInput($arguments,'name','Beats Me','#[A-Za-z0-9]*#');
 *
 */
function pluginInput(array &$array, string $key, $default = null, string $regex = null) /* mixed */
{
	$value = $array['hash'][$key] ?? $default;

	if ($value === null) {
		showException(new \Exception('Plugin Argument "' . $key . '" is required.'));
	}

	if ($regex) {
		if (!preg_match($regex, $value)) {
			showException(new \Exception('Plugin Value for the Argument "' . $key . '" failed validation.'));
		}
	}

	return $value;
}
