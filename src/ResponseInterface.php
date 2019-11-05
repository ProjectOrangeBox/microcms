<?php

namespace projectorangebox\cms;

interface ResponseInterface
{
	public function get(): string;
	public function set(string $output): ResponseInterface;
	public function append(string $output): ResponseInterface;
	public function display(string $output = null): void;
	public function setRespondsCode(int $code): ResponseInterface;
}
