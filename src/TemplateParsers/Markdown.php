<?php

namespace projectorangebox\cms\TemplateParsers;

use projectorangebox\cms\App;
use Michelf\Markdown as MichelfMarkdown;
use projectorangebox\cms\FileNotFoundException;
use projectorangebox\cms\TemplateParserInterface;

class Markdown implements TemplateParserInterface
{
	protected $config = [];


	public function __construct(array $config)
	{
		$requiredDefaults = [
			'cache folder' => '/cache/markdown', /* assocated array name => complete path */
			'forceCompile' => (DEBUG == 'development'), /* boolean - always compile in developer mode */
			'delimiters' => ['{{', '}}'], /* array */
			'views' => [],
		];

		$this->config = array_replace($requiredDefaults, $config);

		App::mkdir($this->config['cache folder']);
	}

	public function parse(string $templateFile, array $data = [], bool $return = false): string
	{
		return $this->_parse(App::file_get_contents($this->config['views'][strtolower(trim($templateFile, '/'))], true), $data, $return);
	}

	public function parse_string(string $templateStr, array $data = [], bool $return = false): string
	{
		return $this->_parse($templateStr, $data, $return);
	}

	protected function _parse(string $template, array $data, bool $return): string
	{
		$template = $this->merge(App::file_get_contents($this->compileFile($template)), $data);

		if (!$return) {
			echo $template;
		}

		return $template;
	}

	protected function merge(string $string, array $parameters): string
	{
		$left_delimiter = preg_quote($this->config['delimiters'][0]);
		$right_delimiter = preg_quote($this->config['delimiters'][1]);

		$replacer = function ($match) use ($parameters) {
			return isset($parameters[$match[1]]) ? $parameters[$match[1]] : $match[0];
		};

		return preg_replace_callback('/' . $left_delimiter . '\s*(.+?)\s*' . $right_delimiter . '/', $replacer, $string);
	}

	protected function compileFile(string $template): string
	{
		$compiledFile = $this->config['cache folder'] . '/' . md5($template) . '.php';

		if ($this->config['forceCompile'] || !file_exists($compiledFile)) {
			App::file_put_contents($compiledFile, MichelfMarkdown::defaultTransform($template));
		}

		return $compiledFile;
	}

	public function set_delimiters(/* string|array */$l = '{{', string $r = '}}'): TemplateParserInterface
	{
		/* set delimiters */
		$this->config['delimiters'] = (is_array($l)) ? $l : [$l, $r];

		/* chain-able */
		return $this;
	}
	public function exists(string $name): string
	{
		$name = strtolower(trim($name, '/'));

		log_message('info', 'Find ' . $name);

		return $this->config['views'][$name] ?? '';
	}
} /* end class */
