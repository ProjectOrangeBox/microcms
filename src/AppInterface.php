<?php

namespace projectorangebox\cms;

use projectorangebox\cms\AppInterface;
use projectorangebox\cms\ContainerInterface;

interface AppInterface
{

	public function __construct(ContainerInterface $container);
	public function container(): ContainerInterface;
	public function dispatch(): void;
}
