<?php

namespace projectorangebox\cms;

interface CacheInterface
{

	public function __construct(string $cachePath, int $ttl);
	public function get(string $key);
	public function getMetadata(string $key): array;
	public function save(string $key, $value);
	public function saveMetadata(string $key, string $data, int $ttl): bool;
	public function delete(string $key);
	public function info(): array;
	public function clean(): void;
	public function ttl(int $cacheTTL = null, bool $useWindow = true): int;
}
