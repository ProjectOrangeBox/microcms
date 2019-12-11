<?php

namespace projectorangebox\cms;

interface ContainerInterface
{

	public function __set(string $name, $object);
	public function __get(string $name);
	public function __isset(string $name): bool;
	public function __unset(string $name): void;
	public function keys(): array;
}
