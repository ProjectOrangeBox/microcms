<?php

return [
	'html' => function ($container) {
		return new \projectorangebox\cms\TemplateParsers\Handlebars([
			'cache folder' => $container->config->get('cache.path') . '/handlebars', /* string - folder inside cache folder if any */
			'plugins' => \searchFor($container->config->get('paths.plugins') . '/*.' . $container->config->get('parser.handlebars plugin extension', 'php'), 'handlebar.plugins', $container->cache),
			'templates' => \searchFor($container->config->get('paths.site') . '/*.' . $container->config->get('parser.handlebars extension', 'hbs'), 'handlebar.templates', $container->cache),
			'partials' => [],
		]);
	},
	'php' => function ($container) {
		return new \projectorangebox\cms\TemplateParsers\PHPview([
			'cache folder' => $container->config->get('cache.path') . '/phpview',
			'views' => \searchFor($container->config->get('paths.site') . '/*.' . $container->config->get('parser.view extension', 'php'), 'php.templates', $container->cache),
		]);
	},
	'md' => function ($container) {
		return new \projectorangebox\cms\TemplateParsers\Markdown([
			'cache folder' => $container->config->get('cache.path') . '/markdown',
			'views' => \searchFor($container->config->get('paths.site') . '/*.' . $container->config->get('parser.markdown extension', 'md'), 'markdown.templates', $container->cache),
		]);
	},
];
