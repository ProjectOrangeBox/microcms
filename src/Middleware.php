<?php

namespace projectorangebox\cms;

use projectorangebox\cms\App;
use projectorangebox\cms\CacheInterface;

class Middleware implements MiddlewareInterface
{
	protected $config;
	protected $uri;

	protected $cache;

	public function __construct(string $configINI, CacheInterface $cache)
	{
		$this->cache = &$cache;

		$this->config = $this->loadRoutesIni($configINI);
	}

	public function request(string $uri)
	{
		if ($phpFile = $this->fileExist($uri, (array) $this->config['in'])) {
			\log_message('debug', 'Middleware Request for ' . $phpFile);

			include App::resolve($phpFile);
		}

		return $this->uri = $uri;
	}

	public function response(string $output)
	{
		if ($phpFile = $this->fileExist($this->uri, (array) $this->config['out'])) {
			\log_message('debug', 'Middleware Response for ' . $phpFile);

			include App::resolve($phpFile);
		}

		return $output;
	}

	protected function fileExist(string $uri, array $search): string
	{
		$found = '';

		if (count($search)) {
			if ($phpFile = $this->match($uri, $search)) {
				$phpFile = trim($this->config['path'], '/') . '/' . trim($phpFile, '/');

				if (App::file_exists($phpFile)) {
					$found = $phpFile;
				} else {
					\log_message('error', 'Filter file "' . $phpFile . '" Not found.');
				}
			}
		}

		return $found;
	}

	protected function match(string $uri, array $routes): string
	{
		log_message('debug', 'Middleware URI ' . $uri);

		/* default to no match */
		$phpfile = '';

		foreach ($routes as $regex => $file) {
			if (preg_match($regex, $uri)) {
				$phpfile = $file;

				log_message('debug', 'Middleware Matched ' . $phpfile);

				break; /* found one no need to stay in loop */
			}
		}

		return $phpfile;
	}

	protected function loadRoutesIni(string $filename): array
	{
		log_message('info', 'Load Route INI file "' . $filename . '".');

		$cacheKey = 'app.filters';

		if (!$ini = $this->cache->get($cacheKey)) {
			$ini = [];

			if (App::file_exists($filename)) {
				$lines = App::file($filename);
				$section = '_root_';

				foreach ($lines as $line) {
					$line = trim($line);

					/* if not a comment */
					if ($line[0] != '#' && $line[0] != ';') {
						if ($line[0] == '[') {
							$section = substr($line, 1, -1);
						} elseif (strpos($line, '=') !== false) {
							list($key, $value) = str_getcsv($line, '=');

							switch ($section) {
								case 'in':
								case 'out':
									$ini[$section]['#^/' . ltrim(trim($key), '/') . '$#im'] = trim($value);
									break;
								case '_root_':
									$ini[trim($key)] = trim($value);
									break;
								default:
									$ini[$section][trim($key)] = trim($value);
							}
						}
					}
				}
			} else {
				\log_message('debug', 'Could not find "' . $filename . '".');
			}

			$this->cache->save($cacheKey, $ini);
		}

		return $ini;
	}
}
