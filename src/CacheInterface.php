<?php

namespace projectorangebox\cms;

interface CacheInterface
{

	public function __construct(string $cachePath, int $ttl);
	public function get(string $key);
	public function getMetadata(string $key): array;
	public function save(string $key, $value, int $ttl = null);
	public function buildMetadata(string $valueString, int $ttl): array;
	public function delete(string $key);
	public function info(): array;
	public function clean(): void;
	public function ttl(int $cacheTTL = null, bool $useWindow = true): int;
}
