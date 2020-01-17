<?php

namespace projectorangebox\cms\TemplateParsers;

use projectorangebox\cms\App;
use Exception;
use projectorangebox\cms\HandlebarsException;
use LightnCandy\LightnCandy;
use projectorangebox\cms\Exceptions\IO\FileNotFoundException;
use projectorangebox\cms\Exceptions\MVC\HandlebarsException as MVCHandlebarsException;
use projectorangebox\cms\TemplateParserInterface;
use projectorangebox\cms\Exceptions\MVC\PartialNotFoundException;
use projectorangebox\cms\Exceptions\MVC\TemplateNotFoundException;

/**
 * Handlebars Parser
 *
 * This content is released under the MIT License (MIT)
 *
 * @package	CodeIgniter / Orange
 * @author	Don Myers
 * @author Zordius, Taipei, Taiwan
 * @license http://opensource.org/licenses/MIT MIT License
 * @link	https://github.com/ProjectOrangeBox
 * @link https://github.com/zordius/lightncandy
 *
 *
 *
 * Helpers:
 *
 * $helpers['foobar'] = function($options) {};
 *
 * $options =>
 * 	[name] => lex_lowercase # helper name
 * 	[hash] => Array # key value pair
 * 		[size] => 123
 * 		[fullname] => Don Myers
 * 	[contexts] => ... # full context as object
 * 	[_this] => Array # current loop context
 * 		[name] => John
 * 		[phone] => 933.1232
 * 		[age] => 21
 * 	['fn']($options['_this']) # if ??? - don't forget to send in the context
 * 	['inverse']($options['_this']) # else ???- don't forget to send in the context
 *
 */

class Handlebars implements TemplateParserInterface
{
	protected $config = [];

	protected $plugins; /* actual plugins after loaded */

	/**
	 * Constructor - Sets Handlebars Preferences
	 *
	 * The constructor can be passed an array of config values
	 *
	 * @param	array	$userConfig = array()
	 */
	public function __construct(array $config)
	{
		$requiredDefaults = [
			'plugins' => [], /* array of complete paths to every plugin */
			'templates' => [], /* assocated array name => complete path */
			'partials' => [],  /* assocated array name => complete path */
			'forceCompile' => (DEBUG == 'development'), /* boolean - always compile in developer mode */
			'cache folder' => '/cache/handlebars', /* string - folder inside cache folder if any */
			'HBCachePrefix' => 'hbs.', /* string - prefix all HBCache cached entries with */
			'delimiters' => ['{{', '}}'], /* array */
			/* lightncandy handlebars compiler flags https://github.com/zordius/lightncandy#compile-options */
			'flags' => LightnCandy::FLAG_ERROR_EXCEPTION | LightnCandy::FLAG_HANDLEBARS | LightnCandy::FLAG_HANDLEBARSJS | LightnCandy::FLAG_BESTPERFORMANCE | LightnCandy::FLAG_RUNTIMEPARTIAL, /* integer */
		];

		$this->config = array_replace($requiredDefaults, $config);

		\FS::mkdir($this->config['cache folder']);
	}

	public function exists(string $name): string
	{
		$name = strtolower(trim($name, '/'));

		log_message('info', 'Find ' . $name);

		return $this->config['templates'][$name] ?? '';
	}

	/* These are just like CodeIgniter regular parser */

	/**
	 * Parse a template
	 *
	 * Parses pseudo-variables contained in the specified template view,
	 * replacing them with the data in the second param
	 *
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	string
	 */
	public function parse(string $templateFile, array $data = [], bool $return = false): string
	{
		log_message('info', 'handlebars parse ' . $templateFile);

		$output = $this->run($this->parseTemplate($templateFile, true), $data, !$return);

		if (!$return) {
			echo $output;
		}

		return $output;
	}

	/**
	 * Parse a String
	 *
	 * Parses pseudo-variables contained in the specified string,
	 * replacing them with the data in the second param
	 *
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	string
	 */
	public function parse_string(string $templateStr, array $data = [], bool $return = false): string
	{
		log_message('info', 'handlebars parse string ' . substr($templateStr, 0, 128) . '...');

		$output = $this->run($this->parseTemplate($templateStr, false), $data, !$return);

		if (!$return) {
			echo $output;
		}

		return $output;
	}

	/*
	* set the template delimiters
	*
	* @param string/array
	* @param string
	* @return object (this)
	*/
	public function set_delimiters(/* string|array */$l = '{{', string $r = '}}'): TemplateParserInterface
	{
		/* set delimiters */
		$this->config['delimiters'] = (is_array($l)) ? $l : [$l, $r];

		/* chain-able */
		return $this;
	}

	/* handlebars library specific methods */

	/**
	 * heavy lifter - wrapper for lightncandy https://github.com/zordius/lightncandy handlebars compiler
	 *
	 * returns raw compiled_php as string or prepared (executable) php
	 *
	 * @param string
	 * @param string
	 * @param boolean
	 * @return string / closure
	 */
	public function compile(string $templateSource, string $comment = ''): string
	{
		log_message('info', 'handlebars compiling');

		/* Get our helpers if they aren't already loaded */
		$this->loadHelpers();

		/* Compile it into php magic! Thank you zordius https://github.com/zordius/lightncandy */
		return LightnCandy::compile($templateSource, [
			'flags' => $this->config['flags'], /* compiler flags */
			'helpers' => $this->plugins, /* Add the plugins (handlebars.js calls helpers) */
			'renderex' => '/* ' . $comment . ' compiled @ ' . date('Y-m-d h:i:s e') . ' */', /* Added to compiled PHP */
			'delimiters' => $this->config['delimiters'],
			'partialresolver' => function ($context, $name) { /* partial & template handling */
				/* Try if it's a partial, template or insert as html comment */
				try {
					$template = $this->findPartial($name);
				} catch (Exception $e) {
					try {
						$template = \FS::file_get_contents($this->findTemplate($name));
					} catch (Exception $e) {
						$template = '<!-- partial named "' . $name . '" could not found --!>';
					}
				}

				return $template;
			},
		]);
	}

	/* add template is a path to a file */
	public function addTemplate(string $name, string $path): Handlebars
	{
		$name = strtolower(trim($name, '/'));

		log_message('info', 'handlebars add template ' . $name);

		$this->config['templates'][$name] = '/' . trim($path, '/');

		return $this;
	}

	public function findTemplate(string $name): string
	{
		$name = strtolower(trim($name, '/'));

		log_message('info', 'handlebars find template ' . $name);

		if (!isset($this->config['templates'][$name])) {
			throw new TemplateNotFoundException($name);
		}

		return $this->config['templates'][$name];
	}

	/* a partial is a string */
	public function addPartial(string $name, string $string): Handlebars
	{
		$name = strtolower(trim($name, '/'));

		log_message('info', 'handlebars add partial ' . $name);

		$this->config['partials'][$name] = $string;

		return $this;
	}

	public function findPartial(string $name): string
	{
		$name = strtolower(trim($name, '/'));

		log_message('info', 'handlebars find partial ' . $name);

		if (!isset($this->config['partials'][$name])) {
			throw new PartialNotFoundException($name);
		}

		return $this->config['partials'][$name];
	}

	/*
	* save a compiled file
	*
	* @param string
	* @param string
	* @return boolean
	*/
	public function saveCompileFile(string $compiledFile, string $templatePhp): int
	{
		/* write out the compiled file */
		return \FS::file_put_contents($compiledFile, '<?php ' . $templatePhp . '?>');
	}

	/**
	 * parseTemplate
	 *
	 * @param string $template
	 * @param bool $isFile
	 * @return void
	 */
	public function parseTemplate(string $template, bool $isFile): string
	{
		/* build the compiled file path */
		$compiledFile = $this->config['cache folder'] . '/' . $this->config['HBCachePrefix'] . md5($template) . '.php';

		/* always compile in development or not save or compile if doesn't exist */
		if ($this->config['forceCompile'] || !file_exists($compiledFile)) {
			/* compile the template as either file or string */
			if ($isFile) {
				$source = \FS::file_get_contents($this->findTemplate($template));
				$comment = $template;
			} else {
				$source = $template;
				$comment = 'parse_string_' . md5($template);
			}

			$this->saveCompileFile($compiledFile, $this->compile($source, $comment));
		}

		return $compiledFile;
	}

	/**
	 * run
	 *
	 * @param string $compiledFile
	 * @param array $data
	 * @return void
	 */
	public function run(string $compiledFile, array $data): string
	{
		log_message('info', 'handlebars run ' . $compiledFile);

		$compiledFile = \FS::resolve($compiledFile);

		/* did we find this template? */
		if (!file_exists($compiledFile)) {
			/* nope! - fatal error! */
			throw new FileNotFoundException($compiledFile);
		}

		/* yes include it */
		$templatePHP = include $compiledFile;

		/* is what we loaded even executable? */
		if (!is_callable($templatePHP)) {
			throw new MVCHandlebarsException();
		}

		/* send data into the magic void... */
		try {
			$output = $templatePHP($data);
		} catch (Exception $e) {
			echo '<h1>Handlebars Run Error:</h1><pre>';
			var_dump($e);
			log_message('debug', \var_export($e, true));
			echo '</pre>';
			exit(1);
		}

		return $output;
	}

	/**
	 * loadHelpers
	 *
	 * @return void
	 */
	protected function loadHelpers(): void
	{
		log_message('info', 'handlebars load helpers');

		$cacheFile = $this->config['cache folder'] . '/' . $this->config['HBCachePrefix'] . 'helpers.php';

		if ($this->config['forceCompile'] || !\FS::file_exists($cacheFile)) {
			$combined  = '<?php' . PHP_EOL . '/*' . PHP_EOL . 'DO NOT MODIFY THIS FILE' . PHP_EOL . 'Written: ' . date('Y-m-d H:i:s T') . PHP_EOL . '*/' . PHP_EOL . PHP_EOL;

			/* find all of the plugin "services" */
			if (\is_array($this->config['plugins'])) {
				foreach ($this->config['plugins'] as $path) {
					$pluginSource  = php_strip_whitespace(\FS::resolve($path));
					$pluginSource  = trim(str_replace(['<?php', '<?', '?>'], '', $pluginSource));
					$pluginSource  = trim('/* ' . $path . ' */' . PHP_EOL . $pluginSource) . PHP_EOL . PHP_EOL;

					$combined .= $pluginSource;
				}
			}

			/* save to the cache folder on this machine (in a multi-machine env each will just recreate this locally) */
			\FS::file_put_contents($cacheFile, trim($combined));
		}

		/* start with empty array */
		$plugin = [];

		/* include the combined "cache" file */
		include \FS::resolve($cacheFile);

		$this->plugins = $plugin;
	}
} /* end class */
