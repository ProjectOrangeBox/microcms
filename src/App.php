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
		/* if services where not included then use the defaults */
		$config['services'] = require __ROOT__ . ($config['services config file'] ?? '/vendor/projectorangebox/cms/src/Config/services.php');

		$containerClass = $config['containerClass'] ?? '\projectorangebox\cms\Container';

		self::$container = new $containerClass($config);

		/* Bring in the "common" wrapper functions */
		require 'Common.php';

		/* set the most basic exception handler inside common.php file */
		set_exception_handler('showException');

		/* This is required */
		if (!\defined('__ROOT__')) {
			throw new \Exception('__ROOT__ not defined.');
		}

		/* require abstract FileSystem Functions */
		require 'FS.php';

		/* Set Application Root Folder */
		\FS::setRoot(__ROOT__);

		define('DEBUG', self::$container->config->get('application.debug', false));

		if (DEBUG) {
			error_reporting(E_ALL & ~E_NOTICE);
			ini_set('display_errors', 1);
		} else {
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
			ini_set('display_errors', 0);
		}
	}

	static public function container(): ContainerInterface
	{
		return self::$container;
	}

	public function dispatch(): void
	{
		/* and away we go... */
		self::$container->response->display(
			self::$container->middleware->response(
				self::$container->parser->parse(
					self::$container->router->handle(
						self::$container->middleware->request(
							self::$container->request->uri()
						)
					),
					self::$container->data->add(
						self::$container->tools::templateData(self::$container)
					)->collect()
				)
			)
		);
	}
} /* end app */
