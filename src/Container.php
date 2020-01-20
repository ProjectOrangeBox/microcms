<?php

namespace projectorangebox\cms;

class Container implements ContainerInterface
{
	/**
	 * Registered Services
	 *
	 * @var array
	 */
	protected $registeredServices = [];

	/**
	 * __construct
	 *
	 * @param mixed array (optional)
	 * @return di
	 */
	public function __construct(array &$configArray = null)
	{
		$serviceArray = $configArray['services'] ?? null;

		/* we don't need these anymore */
		unset($configArray['services']);

		if (is_array($serviceArray)) {
			foreach ($serviceArray as $serviceName => $closureSingleton) {
				$this->register($serviceName, $closureSingleton[0], $closureSingleton[1]);
			}
		}

		/* is there a service named "config" registered? if so inject the entire config array */
		if (isset($this->registeredServices['config']) && \is_array($configArray)) {
			$this->registeredServices['config']['reference'] = $this->registeredServices['config']['closure']($configArray);
		}
	}

	/**
	 * __get
	 *
	 * see get(...)
	 *
	 * @param mixed $serviceName
	 * @return mixed
	 */
	public function __get($serviceName)
	{
		return $this->get($serviceName);
	}

	/**
	 * __isset
	 *
	 * see has(...)
	 *
	 * @param mixed $serviceName
	 * @return bool
	 */
	public function __isset($serviceName): bool
	{
		return $this->has($serviceName);
	}

	/**
	 * __set
	 *
	 * see regsiter(...)
	 *
	 * @param mixed $serviceName
	 * @param mixed $value
	 * @return void
	 */
	public function __set($serviceName, $value): void
	{
		$this->register($serviceName, $value[0], $value[1]);
	}

	/**
	 * __unset
	 *
	 * see remove(...)
	 *
	 * @param mixed $serviceName
	 * @return void
	 */
	public function __unset($serviceName): void
	{
		$this->remove($serviceName);
	}

	/**
	 * Get a PHP object by service name
	 *
	 * @param string $serviceName
	 * @return mixed
	 */
	public function get(string $serviceName)
	{
		$serviceName = strtolower($serviceName);

		/* Is this service even registered? */
		if (!isset($this->registeredServices[$serviceName])) {
			/* fatal */
			throw new \Exception('"' . $serviceName . '" service not registered.');
		}

		/* Is this a singleton or factory? */
		return ($this->registeredServices[$serviceName]['singleton']) ? self::singleton($serviceName) : self::factory($serviceName);
	}

	/**
	 * Check whether the Service been registered
	 *
	 * @param string $serviceName
	 * @return bool
	 */
	public function has(string $serviceName): bool
	{
		return isset($this->registeredServices[strtolower($serviceName)]);
	}

	/**
	 * Register a new service as a singleton or factory
	 *
	 * @param string $serviceName Service Name
	 * @param closure $closure closure to call in order to instancate it.
	 * @param bool $singleton should this be a singleton or factory
	 * @return void
	 */
	public function register(string $serviceName, \closure $closure, bool $singleton = false): void
	{
		$this->registeredServices[strtolower($serviceName)] = ['closure' => $closure, 'singleton' => $singleton, 'reference' => null];
	}

	/**
	 * Remove a Registered Service
	 *
	 * @param string $serviceName
	 * @return void
	 */
	public function remove(string $serviceName): void
	{
		unset($this->registeredServices[strtolower($serviceName)]);
	}

	/**
	 * Get the same instance of a service
	 *
	 * @param string $serviceName
	 * @return mixed
	 */
	protected function singleton(string $serviceName)
	{
		return $this->registeredServices[$serviceName]['reference'] ?? $this->registeredServices[$serviceName]['reference'] = self::factory($serviceName);
	}

	/**
	 * Get new instance of a service
	 *
	 * @param string $serviceName
	 * @return mixed
	 */
	protected function factory(string $serviceName)
	{
		return $this->registeredServices[$serviceName]['closure']($this);
	}

	/**
	 * returns a debug array
	 *
	 * @return array
	 */
	public function debug(): array
	{
		$debug = [];

		foreach ($this->registeredServices as $key => $record) {
			$debug[$key] = ['singleton' => $record['singleton'], 'attached' => isset($this->registeredServices[$key]['reference'])];
		}

		return $debug;
	}
} /* end class */
