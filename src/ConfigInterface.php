<?php

namespace projectorangebox\cms;

interface ConfigInterface
{
	public function get(string $name,/* mixed */ $default = null); /* mixed */
	public function set(string $name,/* mixed */ $value = null): ConfigInterface;
	public function merge(array &$array): ConfigInterface;
	public function replace(array &$array): ConfigInterface;
	public function collect(): array;
}
