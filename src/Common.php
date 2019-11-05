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
