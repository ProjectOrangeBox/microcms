<?php

namespace projectorangebox\cms;

interface ContainerInterface
{

	public function __construct(array &$configArray = null);

	public function __get(string $serviceName);
	public function get(string $serviceName);

	public function __isset(string $serviceName): bool;
	public function has(string $serviceName): bool;

	public function __set(string $serviceName, $value): void;
	public function register(string $serviceName, \closure $closure, bool $singleton = false): void;

	public function __unset(string $serviceName): void;
	public function remove(string $serviceName): void;
}
