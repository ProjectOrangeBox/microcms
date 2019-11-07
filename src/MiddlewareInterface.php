<?php

namespace projectorangebox\cms;

interface MiddlewareInterface
{
	public function __construct(string $configINI, CacheInterface $cache);
	public function request(string $uri);
	public function response(string $output);
}
