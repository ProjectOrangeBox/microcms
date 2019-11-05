<?php

namespace projectorangebox\cms;

use projectorangebox\cms\CacheInterface;

interface RouterInterface
{

	public function __construct(string $routeINIfile, CacheInterface $cache);
	public function handle(string $uri): string;
	public function captured(): array;
}
