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

use projectorangebox\cms\RequestInterface;

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
class Request implements RequestInterface
{
	protected $server = [];
	protected $request = [];
	protected $isAjax = false;
	protected $baseUrl = '';
	protected $requestMethod = '';
	protected $uri = '';
	protected $segments = [];
	protected $captured = [];
	protected $config;

	public function __construct(array &$config)
	{
		$this->config = &$config;

		$this->server = $this->config['server'] ?? array_change_key_case($_SERVER, CASE_LOWER);

		$request = [];

		parse_str(file_get_contents('php://input'), $request);

		$this->request = $this->config['request'] ?? $request;

		/* is this a ajax request? */
		$this->isAjax = (isset($this->server['http_x_requested_with']) && strtolower($this->server['http_x_requested_with']) === 'xmlhttprequest') ? true : false;

		/* what's our base url */
		$this->baseUrl = trim($this->server['http_host'] . dirname($this->server['script_name']), '/');

		/* get the http request method */
		$this->requestMethod = strtolower($this->server['request_method']);

		/* get the uri (uniform resource identifier) */
		$uri = trim(urldecode(substr(parse_url($this->server['request_uri'], PHP_URL_PATH), strlen(dirname($this->server['script_name'])))), '/');

		$allow = $this->config['allow'] ?? 'A BCDEFGHIJKLMNOPQRSTUVWXYZ0123456789/0_-.+';

		/* filter out NOT in allow */
		$uri = preg_replace("/[^" . preg_quote($allow, '/') . "]/i", '', $uri);

		/* get the uri pieces */
		$this->segments = explode('/', $uri);

		$this->uri = $uri;
	}

	public function isAjax(): bool
	{
		return $this->isAjax;
	}

	public function baseUrl(): string
	{
		return $this->baseUrl;
	}

	public function requestMethod(): string
	{
		return $this->requestMethod;
	}

	public function uri(): string
	{
		return '/' . $this->uri;
	}

	public function segments(): array
	{
		return $this->segments;
	}

	public function server(string $name = null) /* mixed */
	{
		if ($name) {
			return (isset($this->server[$name])) ? $this->server[$name] : '';
		} else {
			return $this->server;
		}
	}

	public function request(string $name = null, $default = null) /* mixed */
	{
		if ($name) {
			return (isset($this->request[$name])) ? $this->request[$name] : $default;
		} else {
			return $this->request;
		}
	}
} /* end class */
