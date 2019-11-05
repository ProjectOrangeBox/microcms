<?php

namespace projectorangebox\cms;

use projectorangebox\cms\LoggerInterface;

class logger implements LoggerInterface
{
	protected $logFile = '';
	protected $enabled = false;
	protected $threshold = 0;
	protected $filePermissions = '644';
	protected $lineDateFormat = 'r';

	const EMERGENCY = 'emergency';
	const ALERT     = 'alert';
	const CRITICAL  = 'critical';
	const ERROR     = 'error';
	const WARNING   = 'warning';
	const NOTICE    = 'notice';
	const INFO      = 'info';
	const DEBUG     = 'debug';

	protected $psr_levels = [
		'EMERGENCY' => 1,
		'ALERT'     => 2,
		'CRITICAL'  => 4,
		'ERROR'     => 8,
		'WARNING'   => 16,
		'NOTICE'    => 32,
		'INFO'      => 64,
		'DEBUG'     => 128,
	];

	public function __construct(array &$config)
	{
		/* log threshold */
		$logThreshold = $config['threshold'] ?? '';

		if (!empty($logThreshold)) {
			/* if they sent in a string split it into a array */
			if (is_string($logThreshold)) {
				$logThreshold = explode(',', strtoupper($logThreshold));
			}

			/* is the array empty? */
			if (is_array($logThreshold)) {
				if (count($logThreshold) == 0) {
					$logThreshold = 0;
				} elseif (array_search('ALL', $logThreshold) !== false) {
					$logThreshold = 255;
				}
			}

			/* build the bitwise integer */
			if (is_array($logThreshold)) {
				$int = 0;

				foreach ($logThreshold as $t) {
					$t = strtoupper($t);

					if (isset($this->psr_levels[$t])) {
						$int += $this->psr_levels[$t];
					}
				}

				$logThreshold = $int;
			}
		}

		$this->threshold = (int) $logThreshold;

		$this->enabled = ($this->threshold > 0);

		/* log path */
		$logPath = $config['path'] ?? '/';

		$logPath = __ROOT__ . trim($logPath, '/');

		file_exists($logPath) || mkdir($logPath, 0755, true);

		/* can we write to this folder? */
		if (!is_dir($logPath) || !is_writable($logPath)) {
			$this->enabled = false;
		}

		$this->lineDateFormat = $config['line date format'] ?? 'r';

		$this->filePermissions = $config['permissions'] ?? '644';

		/* log extension */
		$fileExt = $config['extension'] ?? 'log';

		/* file name */
		$logName = $config['filename'] ?? 'log-%d';

		$logName = trim($logName, '.') . '.' . $fileExt;

		/* file date format */
		$dateFmt = $config['filename date format'] ?? 'Y-m-d';

		$this->logFile = __ROOT__ . '/' . str_replace('%d', date($dateFmt), $logName);
	}

	public function log(string $level, string $message, array $context = []): bool
	{
		$bytes = 0;

		if ($this->enabled) {
			if ($this->testLevel($level)) {
				$bytes = file_put_contents($this->logFile, $this->format($level, $message, $context), FILE_APPEND | LOCK_EX);

				chmod($this->logFile, octdec($this->filePermissions));
			}
		}

		return ($bytes > 0);
	}

	protected function format(string $level, string $message, array $context = []): string
	{
		$t = chr(9);
		$lines = '';

		$lines .= date($this->lineDateFormat) . ' ' . $level . $t . $message . PHP_EOL;

		foreach ($context as $key => $value) {
			$lines .= $t . $key . $t . json_encode($value, JSON_PRETTY_PRINT) . PHP_EOL;
		}

		return $lines;
	}

	public function getLogFile(): string
	{
		return $this->logFile;
	}

	public function isEnabled(): Bool
	{
		return $this->enabled;
	}

	public function testLevel(string $level): bool
	{
		/* normalize */
		$level = strtoupper($level);

		/* bitwise PSR 3 Mode */
		return !((!array_key_exists($level, $this->psr_levels)) || (!($this->threshold & $this->psr_levels[$level])));
	}

	public function emergency(string $message, array $context = []): bool
	{
		return $this->log('EMERGENCY', $message, $context);
	}

	public function alert(string $message, array $context = []): bool
	{
		return $this->log('ALERT', $message, $context);
	}

	public function critical(string $message, array $context = []): bool
	{
		return $this->log('CRITICAL', $message, $context);
	}

	public function error(string $message, array $context = []): bool
	{
		return $this->log('ERROR', $message, $context);
	}

	public function warning(string $message, array $context = []): bool
	{
		return $this->log('WARNING', $message, $context);
	}

	public function notice(string $message, array $context = []): bool
	{
		return $this->log('NOTICE', $message, $context);
	}

	public function info(string $message, array $context = []): bool
	{
		return $this->log('INFO', $message, $context);
	}

	public function debug(string $message, array $context = []): bool
	{
		return $this->log('DEBUG', $message, $context);
	}
} /* End of Class */
