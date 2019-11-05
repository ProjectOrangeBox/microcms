<?php

namespace projectorangebox\cms;

interface CacheInterface
{

	public function __construct(string $path);
	public function get(string $key);
	public function save(string $key, $value);
	public function delete(string $key);
	public function info(): array;
	public function clean(): void;

}
