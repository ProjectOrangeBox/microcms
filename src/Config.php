<?php

/**
 * Project Orange Box CMS
 *
 * File Based CMS
 *
 * This content is released under the MIT License (MIT)
 * Copyright (c) 2014 - 2019, Project Orange Box
 */

namespace projectorangebox\cms;

use projectorangebox\cms\ConfigInterface;

/**
 *
 * @package XO
 * @author Don Myers
 * @copyright 2019
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/dmyers2004
 * @version v1.0.0
 * @filesource
 *
 */
class Config implements ConfigInterface
{
	protected $config = [];

	public function get(string $notation,/* mixed */ $default = null) /* mixed */
	{
		return $this->_get($this->config, $notation, $default);
	}

	public function set(string $notation, $value = null): ConfigInterface
	{
		return $this->_set($this->config, $notation, $value);
	}

	public function merge(array &$array): ConfigInterface
	{
		foreach ($array as $index => $value) {
			$this->set($index, $value);
		}

		return $this;
	}

	public function replace(array &$array): ConfigInterface
	{
		$this->config = &$array;

		return $this;
	}

	public function collect(): array
	{
		return $this->config;
	}

	/**
	 * Add some stateless functions
	 */

	protected function _get(array $array, string $notation, $default = null) /* mixed */
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

	protected function _set(array &$array, string $notation, $value): ConfigInterface
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

		return $this;
	}
} /* end class */
