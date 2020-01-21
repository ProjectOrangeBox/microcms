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
use projectorangebox\cms\AppInterface;
use projectorangebox\cms\ContainerInterface;

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
	protected static $container;

	public function __construct(array $config)
	{
		if (!defined('EOL')) {
			define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
		}

		define('DEBUG', ($config['application']['debug'] ?? false));

		if (DEBUG) {
			error_reporting(E_ALL & ~E_NOTICE);
			ini_set('display_errors', 1);
		} else {
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
			ini_set('display_errors', 0);
		}

		/* Bring in the "common" wrapper functions */
		require 'Common.php';

		/* set the most basic exception handler inside common.php file */
		set_exception_handler('showException');

		/* This is required */
		if (!\defined('__ROOT__')) {
			throw new Exception('__ROOT__ not defined.');
		}

		/* require abstract FileSystem Functions */
		require 'FS.php';

		/* Set Application Root Folder and chdir(...) */
		\FS::setRoot(__ROOT__, true);

		/* if services where not included then use the defaults */
		$servicesFile = $config['services config file'] ?? '/vendor/projectorangebox/cms/src/Config/services.php';

		/* Is the services configuration file there? */
		if (!\FS::file_exists($servicesFile)) {
			throw new Exception('Services configuration file not found.');
		}

		/* load the services array from the config file */
		$services = require \FS::resolve($servicesFile);

		/* did this return an array? */
		if (!\is_array($services)) {
			throw new Exception('Services configuration file is not an array.');
		}

		/* did they include a custom containerClass or should we us the default? */
		$containerClass = $config['containerClass'] ?? '\projectorangebox\cms\Container';

		/* does the container class exists? */
		if (!\class_exists($containerClass, true)) {
			throw new Exception('Services Class file not found.');
		}

		/* Create contrainer and send in the services array */
		self::$container = new $containerClass($services);

		/* Setup our configuration object with the configuration array */
		self::$container->config->replace($config);
	}

	/**
	 * return our dependency container
	 *
	 * @return ContainerInterface
	 */
	static public function container(): ContainerInterface
	{
		return self::$container;
	}

	public function dispatch(): void
	{
		\log_message('info', 'App::Dispatch');

		/* and away we go... */
		self::$container->response->display(
			self::$container->middleware->response(
				self::$container->parser->parse(
					self::$container->router->handle(
						self::$container->middleware->request(
							self::$container->request->uri()
						)
					),
					self::$container->data->merge(self::$container->parser->templateData())->collect()
				)
			)
		);
	}
} /* end app */
