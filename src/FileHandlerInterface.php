<?php

namespace projectorangebox\cms;

use projectorangebox\cms\CacheInterface;

interface FileHandlerInterface
{

	public function __construct(string $dataPath, CacheInterface $cache);
	public function load(string $filename, bool $cache = false); /* mixed */
	public function array(string $filename, bool $cache = false): array;
	public function json(string $filename, bool $cache = false): array;
	public function md(string $filename, bool $cache = false): string;
	public function yaml(string $filename, bool $cache = false): array;
	public function ini(string $filename, bool $cache = false): array;
	public function getDataPath(): string;
}
