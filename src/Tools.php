<?php

namespace projectorangebox\cms;

class Tools
{
	static public function templateData($di): array
	{
		/* router captured */
		$data['request.captured'] = $di->router->captured();

		/* request captured */
		$data['request.ajax'] = $di->request->isAjax();
		$data['request.method'] = $di->request->requestMethod();
		$data['request.segments'] = $di->request->segments();
		$data['request.uri'] = $di->request->uri();
		$data['request.server'] = $di->request->server();
		$data['request.baseUrl'] = $di->request->baseUrl();
		$data['request.request'] = $di->request->request();

		return $data;
	}

	static public function searchFor(string $cacheName, string $path, CacheInterface $cache): array
	{
		$cacheKey = 'app.' . $cacheName . '.php';

		if (!$found = $cache->get($cacheKey)) {
			$pathinfo = \pathinfo($path);

			$stripFromBeginning = $pathinfo['dirname'];
			$stripLen = \strlen($stripFromBeginning) + 1;

			$extension = $pathinfo['extension'];
			$extensionLen = \strlen($extension) + 1;

			$found = [];

			foreach (\FS::glob($path, 0, true, true) as $file) {
				$found[\strtolower(\substr($file, $stripLen, -$extensionLen))] = $file;
			}

			$cache->save($cacheKey, $found);
		}

		return $found;
	}
}
