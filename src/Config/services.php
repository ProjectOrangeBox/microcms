<?php

return [
	'config' => [function (&$config) {
		return new \projectorangebox\cms\Config($config);
	}, true],
	'log' => [function ($container) {
		return new \projectorangebox\cms\Logger($container->config->get('log'));
	}, true],
	'cache' => [function ($container) {
		return new \projectorangebox\cms\CacheFile($container->config->get('cache.path', '/cache'), $container->config->get('cache.ttl', 0));
	}, true],
	'tools' => [function ($container) {
		return new \projectorangebox\cms\Tools;
	}, true],
	'file' => [function ($container) {
		return new \projectorangebox\cms\FileHandler($container->config->get('paths.data'), $container->cache);
	}, true],
	'data' => [function ($container) {
		return new \projectorangebox\cms\Data($container->config->get('data', []));
	}, true],
	'request' => [function ($container) {
		return new \projectorangebox\cms\Request($container->config->get('request'));
	}, true],
	'parser' => [function ($container) {
		$parserConfigFile = $container->config->get('parsers', __ROOT__ . '/vendor/projectorangebox/cms/src/Config/parsers.php');

		return new \projectorangebox\cms\Parser($container, $parserConfigFile, $container->config->get('response.404 view'));
	}, true],
	'response' => [function ($container) {
		return new \projectorangebox\cms\Response;
	}, true],
	'middleware' => [function ($container) {
		return new \projectorangebox\cms\Middleware($container->config->get('filters.config', ''), $container->cache);
	}, true],
	'router' => [function ($container) {
		return new \projectorangebox\cms\Router($container->config->get('router.routes'), $container->cache);
	}, true],
];
