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
		return \array_get_by($this->config, $notation, $default);
	}

	public function set(string $notation, $value = null): ConfigInterface
	{
		\array_set_by($this->config, $notation, $value);

		return $this;
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
} /* end class */
