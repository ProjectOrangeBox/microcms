<?php

namespace projectorangebox\cms;

use projectorangebox\cms\TemplateParserInterface;

interface ParserInterface
{
	public function __construct(string $fourohfour);

	/* get handler for extension */
	public function __get(string $extension);

	/* set handler for extension */
	public function __set(string $extension, TemplateParserInterface $parser);

	public function parse(string $path, array $data = []): string;
	public function parse_string(string $string, string $extension, array $data = []): string;
}
