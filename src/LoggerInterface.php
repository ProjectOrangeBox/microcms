<?php

namespace projectorangebox\cms;

interface LoggerInterface
{

	public function __construct(array &$config);

	public function emergency(string $message,array $context = []): bool;
	public function alert(string $message,array $context = []): bool;
	public function critical(string $message,array $context = []): bool;
	public function error(string $message,array $context = []): bool;
	public function warning(string $message,array $context = []): bool;
	public function notice(string $message,array $context = []): bool;
	public function info(string $message,array $context = []): bool;
	public function debug(string $message,array $context = []): bool;

	public function log(string $level,string $message, array $context = []) : bool;

}
