<?php

namespace projectorangebox\cms;

interface TemplateParserInterface
{

	public function __construct(array $config);
	public function parse(string $templateFile, array $data = [], bool $return = false): string;
	public function parse_string(string $templateStr, array $data = [], bool $return = false): string;

	public function exists(string $name): string;
}
