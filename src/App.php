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

use projectorangebox\cms\FileHandler;
use projectorangebox\cms\AppInterface;
use projectorangebox\cms\AppFileTraits;
use projectorangebox\cms\CacheInterface;
use projectorangebox\cms\ContainerInterface;
use projectorangebox\cms\TemplateParsers\PHPview;
use projectorangebox\cms\TemplateParsers\Markdown;
use projectorangebox\cms\TemplateParsers\Handlebars;

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
class App implements AppInterface
{
	use AppFileTraits;

	protected $config;
	protected $container;

	public function __construct(array &$config, ContainerInterface $container)
	{
		$this->config = &$config;
		$this->container = $container;

		/* Bring in the "common" wrapper functions */
		require 'Common.php';

		/* set the most basic exception handler inside common.php file */
		set_exception_handler('showException');

		/* This is required and the CORE of how the App Traits work! */
		if (!\defined('__ROOT__')) {
			throw new \Exception('__ROOT__ not defined.');
		}

		define('DEBUG', ($config['application']['debug'] ?? false));

		if (DEBUG) {
			error_reporting(E_ALL & ~E_NOTICE);
			ini_set('display_errors', 1);
		} else {
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
			ini_set('display_errors', 0);
		}

		$this->bootstrap($config, $container);
	}

	public function dispatch(): void
	{
		/* and away we go... */
		$this->container->response->display(
			$this->container->middleware->response(
				$this->container->parser->parse(
					$this->container->router->handle(
						$this->container->middleware->request(
							$this->container->request->uri()
						)
					),
					$this->container->data->add(
						$this->templateData($this->container)
					)->collect()
				)
			)
		);
	}

	/**
	 * If you need to override the bootstrapping
	 * Extend this class
	 */
	public function bootstrap(array &$config, ContainerInterface $container): void
	{
		$container->config = new Config($config);
		$container->log = new Logger($container->config->get('log'));

		$container->cache = new Cache($container->config->get('cache.path', '/cache'), $container->config->get('cache.ttl', 0));

		$container->file = new FileHandler($container->config->get('paths.data'), $container->cache);

		$container->data = new Data($container->config->get('data', []));

		$container->request = new Request($container->config->get('request'));

		$container->parser = new Parser($container->config->get('response.404 view'));

		$container->parser->html = new Handlebars([
			'cache folder' => $container->config->get('cache.path') . '/handlebars', /* string - folder inside cache folder if any */
			'plugins' => $this->searchFor('handlebar.plugins', $container->config->get('paths.plugins') . '/*.' . $container->config->get('parser.handlebars plugin extension', 'php'), $container->cache),
			'templates' => $this->searchFor('handlebar.templates', $container->config->get('paths.site') . '/*.' . $container->config->get('parser.handlebars extension', 'hbs'), $container->cache),
			'partials' => [],
		]);

		$container->parser->php = new PHPview([
			'cache folder' => $container->config->get('cache.path') . '/phpview',
			'views' => $this->searchFor('php.templates', $container->config->get('paths.site') . '/*.' . $container->config->get('parser.view extension', 'php'), $container->cache),
		]);

		$container->parser->md = new Markdown([
			'cache folder' => $container->config->get('cache.path') . '/markdown',
			'views' => $this->searchFor('markdown.templates', $container->config->get('paths.site') . '/*.' . $container->config->get('parser.markdown extension', 'md'), $container->cache),
		]);

		$container->response = new Response();

		$container->middleware = new Middleware($container->config->get('filters.config', ''), $container->cache);

		$container->router = new Router($container->config->get('router.routes'), $container->cache);
	}

	/**
	 * If you need to override the bootstrapping
	 * Extend this class
	 */
	public function templateData(ContainerInterface $container): array
	{
		$data = [];

		$data['request.captured'] = $container->router->captured();
		$data['request.ajax'] = $container->request->isAjax();
		$data['request.method'] = $container->request->requestMethod();
		$data['request.segments'] = $container->request->segments();
		$data['request.uri'] = $container->request->uri();
		$data['request.server'] = $container->request->server();
		$data['request.baseUrl'] = $container->request->baseUrl();
		$data['request.request'] = $container->request->request();

		return $data;
	}

	protected function searchFor(string $cacheName, string $path, CacheInterface $cache): array
	{
		$cacheKey = 'app.' . $cacheName . '.php';

		if (!$found = $cache->get($cacheKey)) {
			$pathinfo = \pathinfo($path);

			$stripFromBeginning = self::path($pathinfo['dirname']);
			$stripLen = \strlen($stripFromBeginning) + 1;

			$extension = $pathinfo['extension'];
			$extensionLen = \strlen($extension) + 1;

			$root = self::path('');
			$rootLen = \strlen($root) - 1;

			$found = [];

			foreach (self::globr($path) as $file) {
				$found[\strtolower(\substr($file, $stripLen, -$extensionLen))] = \substr($file, $rootLen);
			}

			$cache->save($cacheKey, $found);
		}

		return $found;
	}
} /* end app */
