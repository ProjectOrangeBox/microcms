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

use projectorangebox\cms\ResponseInterface;

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
class Response implements ResponseInterface
{
	protected $finalOutput = '';

	public function get(): string
	{
		return $this->finalOutput;
	}

	public function set(string $output): ResponseInterface
	{
		$this->finalOutput = $output;

		return $this;
	}

	public function append(string $output): ResponseInterface
	{
		$this->finalOutput .= $output;

		return $this;
	}

	public function display(string $output = null): void
	{
		if ($output) {
			$this->finalOutput = $output;
		}

		echo $this->finalOutput;
	}

	public function setRespondsCode(int $code): ResponseInterface
	{
		http_response_code($code);

		return $this;
	}
} /* end class */
