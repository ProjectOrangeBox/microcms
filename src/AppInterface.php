<?php

namespace projectorangebox\cms;

use projectorangebox\cms\ContainerInterface;

interface AppInterface
{
	static public function container(): ContainerInterface;

	public function __construct(array $config);
	public function dispatch(): void;
}
