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
		$file = '';

		foreach ($this->routes as $regex => $file) {
			if (preg_match($regex, $uri, $params)) {
				log_message('info', 'Match - Template: ' . $file);

				foreach ($params as $key => $value) {
					log_message('info', 'Captured ' . $key . ' ' . $value . '.');

					$file = str_replace('$' . $key, $value, $file);

					$this->captured[$key] = $value;
				}

				log_message('info', 'Rewritten - Template: ' . $file);

				break; /* found one no need to stay in loop */
			}
		}

		return $file;
	}

	public function captured(): array
	{
		return $this->captured;
	}

	protected function loadRoutesIni(string $filename): array
	{
		log_message('info', 'Load Route INI file "' . $filename . '".');

		$cacheKey = 'app.routes.ini';

		if (!$ini = $this->cache->get($cacheKey)) {
			$ini = [];

			$routeFile = __ROOT__ . $filename;

			if (!file_exists($routeFile)) {
				log_message('info', 'Could not locate the routes.ini file at "' . $routeFile . '".');
			} else {
				$lines = file($routeFile);
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
