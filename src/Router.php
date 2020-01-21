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

use projectorangebox\cms\RouterInterface;
use projectorangebox\cms\CacheInterface;

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
class Router implements RouterInterface
{
	protected $routes = [];

	protected $cache;
	protected $config;
	protected $routeINIfile;
	protected $captured = [];

	public function __construct(string $routeINIfile, CacheInterface $cache)
	{
		$this->routeINIfile = $routeINIfile;
		$this->cache = &$cache;
	}

	public function handle(string $uri): string
	{
		$this->routes = $this->loadRoutesIni($this->routeINIfile);

		/* Don't allow any protected files or folder - these start with _ */
		$uri = str_replace('/_', '/', $uri);

		log_message('info', 'URI ' . $uri);

		/* default to no match */
		$route = '';

		foreach ($this->routes as $regex => $route) {
			if (preg_match($regex, $uri, $params)) {
				log_message('info', 'Matched the URI: ' . $uri . ' Against: ' . $regex . ' New URI: ' . $route);

				foreach ($params as $key => $value) {
					/* replace dynamically captured sections */
					$route = str_replace('$' . $key, $value, $route);

					log_message('info', 'Captured ' . $key . ' ' . $value . '.');

					$this->captured[$key] = $value;
				}

				log_message('info', 'Final New URI: ' . $route);

				break; /* found one no need to stay in loop */
			}
		}

		return $route;
	}

	public function captured(): array
	{
		return $this->captured;
	}

	protected function loadRoutesIni(string $iniFile): array
	{
		log_message('info', 'Load Route INI file "' . $iniFile . '".');

		$cacheKey = 'app.routes.ini';

		if (!$ini = $this->cache->get($cacheKey)) {
			$ini = [];

			if (!\FS::file_exists($iniFile)) {
				log_message('info', 'Could not locate the routes.ini file at "' . $iniFile . '".');
			} else {
				$lines = \FS::file($iniFile);
				$re = '/<(.[^>]*)>/m';

				foreach ($lines as $line) {
					$line = trim($line);

					if ($line[0] != '#' && $line[0] != ';') {
						$x = str_getcsv($line, '=');

						if (count($x) == 2) {
							$regex = trim($x[0]);

							if (preg_match_all($re, $regex, $matches)) {
								foreach ($matches[0] as $idx => $match) {
									/* (?<folder>[^/]*) */
									$regex = str_replace($match, '(?<' . $matches[1][$idx] . '>[^/]*)', $regex);
								}
							}

							$regex = '#^/' . ltrim($regex, '/') . '$#im';

							$ini[$regex] = trim($x[1]);
						}
					}
				}
			}

			$this->cache->save($cacheKey, $ini);
		}

		return $ini;
	}
} /* end class */
