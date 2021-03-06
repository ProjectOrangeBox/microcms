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
class CacheFile implements CacheInterface
{
	protected $cachePath = '';
	protected $ttl;

	public function __construct(string $cachePath, int $ttl)
	{
		/* make cache path ready to use */
		$this->cachePath = rtrim($cachePath, '/') . '/';
		$this->ttl = $ttl;

		\FS::mkdir($this->cachePath);
	}

	public function get(string $key)
	{
		\log_message('info', 'Cache Get ' . $key);

		$get = false;

		if ($this->ttl > 1) {
			if (\FS::file_exists($this->cachePath . $key . '.meta' . $this->suffix) && \FS::file_exists($this->cachePath . $key)) {
				$meta = $this->getMetadata($key);

				if ($this->isExpired($meta['expire'])) {
					$this->delete($key);
				} else {
					$get = include \FS::resolve($this->cachePath . $key);
				}
			} else {
				\log_message('info', 'Cache ttl less that 1 therefore caching loading skipped.');
			}
		}

		return $get;
	}

	protected function isExpired(int $expire): bool
	{
		return (time() > $expire);
	}

	public function getMetadata(string $key): array
	{
		$file = $this->cachePath . $key;

		$metaData = [];

		if (\FS::is_file($file . '.meta') && \FS::is_file($file)) {
			$metaData = include \FS::resolve($file . '.meta');
		}

		return $metaData;
	}

	public function save(string $key, $value, int $ttl = null)
	{
		\log_message('info', 'Cache Save ' . $key);

		$valuePHP = \FS::var_export_php($value);
		$metaPHP = \FS::var_export_php($this->buildMetadata($valuePHP, $this->ttl($ttl)));

		return ((bool) \FS::atomic_file_put_contents($this->cachePath . $key . '.meta', $metaPHP) && (bool) \FS::atomic_file_put_contents($this->cachePath . $key, $valuePHP));
	}

	public function buildMetadata(string $valueString, int $ttl): array
	{
		return [
			'strlen' => strlen($valueString),
			'time' => time(),
			'ttl' => (int) $ttl,
			'expire' => (time() + $ttl)
		];
	}

	public function delete(string $key)
	{
		\log_message('info', 'Cache Delete ' . $key);

		$file = $this->cachePath . $key;

		if (\FS::file_exists($file)) {
			\FS::unlink($file);
		}
	}

	public function cache_info(): array
	{
		$keys = [];

		foreach (\FS::glob($this->cachePath . '*') as $path) {
			$keys[] = \FS::basename($path);
		}

		return $keys;
	}

	public function clean(): void
	{
		foreach (\FS::glob($this->cachePath . '*') as $path) {
			self::delete($path);
		}
	}

	public function ttl(int $cacheTTL = null, bool $useWindow = true): int
	{
		$cacheTTL = $cacheTTL ?? $this->ttl;

		/* are we using the window option? */
		if ($useWindow) {
			/*
			let determine the window size based on there cache time to live length no more than 5 minutes
			if your traffic to the cache data is that light then cache stampede shouldn't be a problem
			*/
			$window = min(300, ceil($cacheTTL * .02));

			/* add it to the cache_ttl to get our "new" cache time to live */
			$cacheTTL += mt_rand(-$window, $window);
		}

		return $cacheTTL;
	}
} /* end class */
