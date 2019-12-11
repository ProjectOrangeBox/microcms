<?php

namespace projectorangebox\cms\TemplateParsers;

use projectorangebox\cms\App;
use projectorangebox\cms\Exceptions\IO\FileNotFoundException as IOFileNotFoundException;
use projectorangebox\cms\Exceptions\MVC\ViewNotFoundException as MVCViewNotFoundException;
use projectorangebox\cms\FileNotFoundException;
use projectorangebox\cms\ViewNotFoundException;
use projectorangebox\cms\FileWriteFailedException;
use projectorangebox\cms\TemplateParserInterface;

class PHPview implements TemplateParserInterface
{
	protected $config = [];

	public function __construct(array $config)
	{
		$requiredDefaults = [
			'cache folder' => '/cache/phpview', /* assocated array name => complete path */
			'forceCompile' => (DEBUG == 'development'), /* boolean - always compile in developer mode */
			'views' => [],
		];

		$this->config = array_replace($requiredDefaults, $config);

		App::mkdir($this->config['cache folder'], 0777);
	}

	public function exists(string $name): string
	{
		$name = strtolower(trim($name, '/'));

		log_message('info', 'Find ' . $name);

		return $this->config['views'][$name] ?? '';
	}

	public function parse(string $view, array $data = [], bool $return = false): string
	{
		log_message('info', 'handlebars parse ' . $view);

		$output = $this->_parse(App::resolve($this->findView($view)), $data);

		if (!$return) {
			echo $output;
		}

		return $output;
	}

	public function parse_string(string $templateStr, array $data = [], bool $return = false): string
	{
		log_message('info', 'handlebars parse string ' . substr($templateStr, 0, 128) . '...');

		$tempFile = App::resolve($this->config['cache path'] . '/parse_string.' . md5($templateStr) . '.php');

		if ($this->config['forceCompile'] || !\file_exists($tempFile)) {
			if (!file_put_contents($tempFile, '<!-- compiled @ ' . date('Y-m-d h:i:s e') . ' -->' . PHP_EOL . $templateStr, LOCK_EX)) {
				throw new IOFileNotFoundException($tempFile);
			}
		}

		$output = $this->_parse($tempFile, $data);

		if (!$return) {
			echo $output;
		}

		return $output;
	}

	public function _parse(string $__path, array $__data = []): string
	{
		extract($__data, EXTR_PREFIX_INVALID, '_');

		ob_start();

		include $__path;

		return ob_get_clean();
	}

	public function findView(string $name): string
	{
		$name = strtolower(trim($name, '/'));

		log_message('info', 'PHPView find template ' . $name);

		if (!isset($this->config['views'][$name])) {
			throw new MVCViewNotFoundException($name);
		}

		return $this->config['views'][$name];
	}
} /* end class */
