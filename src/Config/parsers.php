<?php

return [
	'html' => function ($container) {
		return new \projectorangebox\cms\TemplateParsers\Handlebars([
			'cache folder' => $container->config->get('cache.path') . '/handlebars', /* string - folder inside cache folder if any */
			'plugins' => $container->tools::searchFor('handlebar.plugins', $container->config->get('paths.plugins') . '/*.' . $container->config->get('parser.handlebars plugin extension', 'php'), $container->cache),
			'templates' => $container->tools::searchFor('handlebar.templates', $container->config->get('paths.site') . '/*.' . $container->config->get('parser.handlebars extension', 'hbs'), $container->cache),
			'partials' => [],
		]);
	},
	'php' => function ($container) {
		return new \projectorangebox\cms\TemplateParsers\PHPview([
			'cache folder' => $container->config->get('cache.path') . '/phpview',
			'views' => $container->tools::searchFor('php.templates', $container->config->get('paths.site') . '/*.' . $container->config->get('parser.view extension', 'php'), $container->cache),
		]);
	},
	'md' => function ($container) {
		return new \projectorangebox\cms\TemplateParsers\Markdown([
			'cache folder' => $container->config->get('cache.path') . '/markdown',
			'views' => $container->tools::searchFor('markdown.templates', $container->config->get('paths.site') . '/*.' . $container->config->get('parser.markdown extension', 'md'), $container->cache),
		]);
	},
];
