<?php

/**
 * Project Orange Box CMS
 *
 * File Based CMS
 *
 * This content is released under the MIT License (MIT)
 * Copyright (c) 2014 - 2019, Project Orange Box
 */

namespace projectorangebox\cms;

use Exception;
use projectorangebox\cms\ParserInterface;
use projectorangebox\cms\TemplateParserInterface;
use projectorangebox\cms\Exceptions\MVC\TemplateNotFoundException;
use projectorangebox\cms\Exceptions\MVC\ParserForExtentionNotFoundException;

/**
 *
 * @package XO
 * @author Don Myers
 * @copyright 2019
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/dmyers2004
 * @version v1.0.0
 * @filesource
 *
 */
class Parser implements ParserInterface
{
	protected $parsers = [];
	protected $fourohfour = '';
	protected $reparseKey;

	public function __construct(string $fourohfour)
	{
		$this->fourohfour = $fourohfour;
	}

	/* pass thru based on extension ...parser->html->parse(...) */
	public function __get(string $extension)
	{
		$extension = $this->normalizeExtension($extension);

		if (!\array_key_exists($extension, $this->parsers)) {
			throw new ParserForExtentionNotFoundException($extension);
		}

		return $this->parsers[$extension];
	}

	/* set parser extension handler ...parser->html = $handlebars */
	public function __set(string $extension, TemplateParserInterface $parser)
	{
		$this->parsers[$this->normalizeExtension($extension)] = &$parser;
	}

	public function reparse(string $key): ParserInterface
	{
		$this->reparseKey = $key;

		return $this;
	}

	public function parse(string $key, array $data = []): string
	{
		/* parse the router provided template */
		$html = $this->_parse($key, $data);

		/**
		 * If somewhere on the orginal template they set the reparseKey
		 * we need to re-parse the new template with the same data
		 * this replaces the current output
		 */
		while ($this->reparseKey) {
			$html = $this->_parse($this->reparseKey, $data);
		}

		/* return the output */
		return $html;
	}

	public function _parse(string $key, array $data = []): string
	{
		$key = $this->normailizedKey($key);
		$extension = $this->findView($key);

		if (empty($extension)) {
			$key = $this->normailizedKey($this->fourohfour);
			$extension = $this->findView($key);

			if (empty($extension)) {
				throw new TemplateNotFoundException($key . ' or ' . $this->fourohfour);
			}
		}

		return $this->parsers[$extension]->parse($key, $data, true);
	}

	public function parse_string(string $string, string $extension, array $data = []): string
	{
		$extension = $this->normalizeExtension($extension);

		if (!\array_key_exists($extension, $this->parsers)) {
			throw new ParserForExtentionNotFoundException($extension);
		}

		return $this->parsers[$extension]->parse_string($string, $data, true);
	}

	public function normailizedKey(string $key): string
	{
		return strtolower(trim($key, '/'));
	}

	public function normalizeExtension(string $extension): string
	{
		return strtolower(trim($extension, '.'));
	}

	protected function findView(string $key): string
	{
		foreach (\array_keys($this->parsers) as $extension) {
			if (!empty($this->parsers[$extension]->exists($key))) {
				return $extension;
			}
		}

		/* return the handler that said they have the matching key */
		return '';
	}
} /* end class */
