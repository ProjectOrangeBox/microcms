<?php

namespace projectorangebox\cms;

use projectorangebox\cms\ContainerInterface;

interface AppInterface
{

	public function __construct(array &$config, ContainerInterface $container);
	public function bootstrap(array &$config, ContainerInterface $container): void;

	public function dispatch(): void;
	public function templateData(ContainerInterface $container): array;
}
