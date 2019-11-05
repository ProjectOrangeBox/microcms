<?php

namespace projectorangebox\cms;

interface RequestInterface
{
	public function __construct(array &$config);
	public function isAjax(): bool;
	public function baseUrl(): string;
	public function requestMethod(): string;
	public function uri(): string;
	public function segments(): array;
	public function server(string $name = null); /* mixed */
	public function request(string $name = null, $default = null); /* mixed */
}
