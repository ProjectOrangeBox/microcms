<?php

namespace projectorangebox\cms;

interface ConfigInterface
{

	public function __construct(array &$config);
	public function get(string $name,/* mixed */ $default = null); /* mixed */
	public function set(string $name,/* mixed */ $value): ConfigInterface;
	public function add(array $array): ConfigInterface;
	public function collect(): array;
}
