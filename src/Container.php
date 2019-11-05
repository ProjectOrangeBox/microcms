<?php

namespace projectorangebox\cms;

use projectorangebox\cms\ContainerInterface;

class Container implements ContainerInterface
{
	static protected $services = [];

	/**
	 * container->foo = new bar;
	 *
	 * @param string $name
	 * @param mixed $object
	 * @return void
	 */
	public function __set(string $name, $object)
	{
		self::$services[\strtolower($name)] = $object;
	}

	/**
	 * $foo = container->foo;
	 *
	 * @param string $name
	 * @return void
	 */
	public function __get(string $name)
	{
		return self::$services[\strtolower($name)] ?? null;
	}

	/**
	 * $bool = isset(container->bar);
	 *
	 * @param string $name
	 * @return void
	 */
	public function __isset(string $name): bool
	{
		return isset(self::$services[\strtolower($name)]);
	}

	/**
	 * unset(container->foo);
	 *
	 * @param string $name
	 * @return void
	 */
	public function __unset(string $name): void
	{
		unset(self::$services[\strtolower($name)]);
	}
} /* end class */
