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

		\FS::file_exists($this->dataPath, true);
	}

	/* auto detect by extension */
	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param string $filePath
	 *
	 * @throws
	 * @return
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function load(string $filePath, bool $cache = false) /* mixed */
	{
		$data = [];

		switch (\pathinfo($filePath, PATHINFO_EXTENSION)) {
			case 'md':
				$data = $this->md($filePath, $cache);
				break;
			case 'yaml':
				$data = $this->yaml($filePath, $cache);
				break;
			case 'ini':
				$data = $this->ini($filePath, $cache);
				break;
			case 'array':
				$data = $this->array($filePath, $cache);
				break;
			case 'json':
				$data = $this->json($filePath, $cache);
				break;
		}

		return $data;
	}

	public function glob(string $pattern = '*'): array
	{
		$found = [];

		foreach (\FS::glob($this->getDataPath() . trim($pattern, '/')) as $file) {
			$fileData = $this->load($this->strip(\FS::resolve($file, true), $this->getDataPath(), 'start'));

			$filename = \FS::pathinfo($file, PATHINFO_BASENAME);

			if (\is_array($fileData)) {
				$fileData['_filename'] = $filename;
			} elseif (\is_object($fileData)) {
				$fileData->_filename = $filename;
			}

			$found[$filename] = $fileData;
		}

		return $found;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param string $filePath
	 *
	 * @throws
	 * @return array
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function array(string $filePath, bool $cache = false): array
	{
		$filePath = $this->addExtension($this->addPath($filePath), '.array');

		$array = '';

		if (\FS::file_exists($filePath)) {
			$array = include \FS::resolve($filePath);
		}

		return $array;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param string $filePath
	 *
	 * @throws
	 * @return array
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function json(string $filePath, bool $cache = false): array
	{
		$filePath = $this->addExtension($this->addPath($filePath), '.json');

		$array = '';

		if (\FS::file_exists($filePath)) {
			$array = json_decode(\FS::file_get_contents($filePath), true);
		}

		return $array;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param string $filePath
	 *
	 * @throws
	 * @return string
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function md(string $filePath, bool $cache = false): string
	{
		$filePath = $this->addExtension($this->addPath($filePath), '.md');

		$html = '';

		if (\FS::file_exists($filePath)) {
			$html = Markdown::defaultTransform(\FS::file_get_contents($filePath));
		}

		return $html;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param string $filePath
	 *
	 * @throws
	 * @return array
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function yaml(string $filePath, bool $cache = false): array
	{
		$filePath = $this->addExtension($this->addPath($filePath), '.yaml');

		$yaml = '';

		if (\FS::file_exists($filePath)) {
			$yaml = \yaml_parse(\FS::file_get_contents($filePath));
		}

		return $yaml;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param string $filePath
	 *
	 * @throws
	 * @return array
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function ini(string $filePath, bool $cache = false): array
	{
		$filePath = $this->addExtension($this->addPath($filePath), '.ini');

		$ini = [];

		if (\FS::file_exists($filePath)) {
			$ini = \FS::parse_ini_file($filePath, true, INI_SCANNER_NORMAL);
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
	 * addPath
	 *
	 * @param string $filePath
	 * @return void
	 */
	protected function addPath(string $filePath): string
	{
		return $this->getDataPath() . $filePath;
	}

	/**
	 * normalize path
	 *
	 * @param string $filePath
	 * @param mixed string
	 * @return void
	 */
	protected function addExtension(string $filePath, string $extension = ''): string
	{
		return $this->strip($filePath, $extension, 'end') . $extension;
	}

	/**
	 * strip
	 *
	 * @param string $string
	 * @param string $strip
	 * @param string $from
	 * @return void
	 */
	protected function strip(string $string, string $strip, string $from): string
	{
		if ($from == 'end') {
			$string = (substr($string, -strlen($strip)) == $strip) ? substr($string, 0, strlen($string) - strlen($strip)) : $string;
		} else {
			$string = (substr($string, 0, strlen($strip)) == $strip) ? substr($string, strlen($strip)) : $string;
		}

		return $string;
	}
} /* end class */
