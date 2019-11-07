<?php

/**
 * Project Orange Box CMS
 *
 * File Based CMS
 * File Loading Functions
 *
 * This content is released under the MIT License (MIT)
 * Copyright (c) 2014 - 2019, Project Orange Box
 */

namespace projectorangebox\cms;

use projectorangebox\cms\App;
use Michelf\Markdown;
use projectorangebox\cms\FileHandlerInterface;
use projectorangebox\cms\CacheInterface;

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

class FileHandler implements FileHandlerInterface
{
	protected $dataPath = '';
	protected $cache;

	/**
	 * __construct
	 *
	 * @param string $dataPath
	 * @return void
	 */
	public function __construct(string $dataPath, CacheInterface $cache)
	{
		$this->cache = $cache;

		$this->dataPath = '/' . trim($dataPath, '/') . '/';

		App::file_exists($this->dataPath, true);
	}

	/* auto detect by extension */
	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param string $filename
	 *
	 * @throws
	 * @return
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function load(string $filename, bool $cache = false) /* mixed */
	{
		$data = [];

		switch (App::pathinfo($this->normalize($filename), PATHINFO_EXTENSION)) {
			case 'md':
				$data = $this->md($filename, $cache);
				break;
			case 'yaml':
				$data = $this->yaml($filename, $cache);
				break;
			case 'ini':
				$data = $this->ini($filename, $cache);
				break;
			case 'array':
				$data = $this->array($filename, $cache);
				break;
			case 'json':
				$data = $this->json($filename, $cache);
				break;
		}

		return $data;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param string $filename
	 *
	 * @throws
	 * @return array
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function array(string $filename, bool $cache = false): array
	{
		$filename = $this->normalize($filename, '.array');

		$array = '';

		if (App::file_exists($filename)) {
			$array = include App::path($filename);
		}

		return $array;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param string $filename
	 *
	 * @throws
	 * @return array
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function json(string $filename, bool $cache = false): array
	{
		$filename = $this->normalize($filename, '.json');

		$array = '';

		if (App::file_exists($filename)) {
			$array = json_decode(App::file_get_contents($filename), true);
		}

		return $array;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param string $filename
	 *
	 * @throws
	 * @return string
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function md(string $filename, bool $cache = false): string
	{
		$filename = $this->normalize($filename, '.md');

		$html = '';

		if (App::file_exists($filename)) {
			$html = Markdown::defaultTransform(App::file_get_contents($filename));
		}

		return $html;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param string $filename
	 *
	 * @throws
	 * @return array
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function yaml(string $filename, bool $cache = false): array
	{
		$filename = $this->normalize($filename, '.yaml');

		$yaml = '';

		if (App::file_exists($filename)) {
			$yaml = yaml_parse(App::file_get_contents($filename));
		}

		return $yaml;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param string $filename
	 *
	 * @throws
	 * @return array
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function ini(string $filename, bool $cache = false): array
	{
		$filename = $this->normalize($filename, '.ini');

		$ini = [];

		if (App::file_exists($filename)) {
			$ini = App::parse_ini_file($filename, true, INI_SCANNER_NORMAL);
		}

		return $ini;
	}

	/**
	 * getDataPath
	 *
	 * @return void
	 */
	public function getDataPath(): string
	{
		return $this->dataPath;
	}

	/**
	 * normalize
	 *
	 * @param string $filename
	 * @param mixed string
	 * @return void
	 */
	protected function normalize(string $filename, string $extension = ''): string
	{
		/* remove extension if it's there if not do nothing */
		if (substr($filename, -strlen($extension)) == $extension) {
			$filename = substr($filename, 0, -strlen($extension));
		}

		$sitePath = $this->dataPath . $filename . $extension;

		log_message('info', 'FileHandler Get "' . $sitePath . '" .');

		return $sitePath;
	}
} /* end class */
