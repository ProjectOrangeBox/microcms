<?php

namespace projectorangebox\cms;

use projectorangebox\cms\Exceptions\FileChmodFailedException;
use projectorangebox\cms\Exceptions\IO\FileNotFoundException;
use projectorangebox\cms\Exceptions\IO\FileRenameFailedException;
use projectorangebox\cms\Exceptions\IO\FileWriteFailedException;
use projectorangebox\cms\Exceptions\IO\FolderNotWritableException;

/**
 * These provide wrappers around file functions which base there files off of __ROOT__
 */

trait AppFileTraits
{

	/**
	 * Format a given path so it's based on the applications root folder __ROOT__.
	 *
	 * @param string $path
	 * @param boolean $throw
	 * @return string
	 */
	static public function path(string $path, bool $throw = false): string
	{
		$path = (substr($path, 0, strlen(__ROOT__)) != __ROOT__) ? __ROOT__ . '/' . \trim($path, '/') : \rtrim($path, '/');

		if ($throw && !\file_exists($path)) {
			throw new FileNotFoundException($path);
		}

		return $path;
	}

	/**
	 * Remove the applications root folder if it's present
	 *
	 * @param string $path
	 * @return string
	 */
	static public function removeRoot(string $path): string
	{
		/* remove anything below the __ROOT__ folder from the passed path */
		$path = (substr($path, 0, strlen(__ROOT__)) == __ROOT__) ? substr($path, strlen(__ROOT__)) : $path;

		return rtrim($path, '/');
	}

	/* read */

	/**
	 * Recursively find pathnames matching a pattern
	 *
	 * @param string $pattern
	 * @param int $flags
	 * @return array
	 */
	static public function globr(string $pattern, int $flags = 0): array
	{
		/* fire off the recursive version (loops onto itself) */
		return self::_globr(self::path($pattern), $flags);
	}

	/* internal recursive loop */
	static protected function _globr(string $pattern, int $flags = 0): array
	{
		$files = \glob($pattern, $flags);

		foreach (\glob(\dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $directory) {
			/* recursive loop */
			$files = \array_merge($files, self::_globr($directory . '/' . \basename($pattern), $flags));
		}

		return $files;
	}

	/**
	 * Find pathnames matching a pattern
	 *
	 * @param string $pattern
	 * @param int $flags
	 * @return array
	 */
	static public function glob(string $pattern, int $flags = 0): array
	{
		return \glob(self::path($pattern), $flags);
	}

	/**
	 * Reads entire file into a string
	 *
	 * @param string $filename
	 * @param bool $throw
	 * @return string
	 */
	static public function file_get_contents(string $filename, bool $throw = true): string
	{
		return \file_get_contents(self::path($filename, $throw));
	}

	/**
	 * Returns trailing name component of path
	 *
	 * @param string $path
	 * @param string $suffix
	 * @param bool $throw
	 * @return string
	 */
	static public function basename(string $path, string $suffix = '', bool $throw = true): string
	{
		return \basename(self::path($path, $throw), $suffix);
	}

	/**
	 * Returns information about a file path
	 *
	 * @param string $path
	 * @param int $options
	 * @param bool $throw
	 * @return mixed
	 */
	static public function pathinfo(string $path, int $options = PATHINFO_DIRNAME | PATHINFO_BASENAME | PATHINFO_EXTENSION | PATHINFO_FILENAME, bool $throw = true) /* mixed */
	{
		return \pathinfo(self::path($path, $throw), $options);
	}

	/**
	 * Reads a file and writes it to the output buffer.
	 *
	 * @param string $filename
	 * @param bool $throw
	 * @return int
	 */
	static public function readfile(string $filename, bool $throw = true): int
	{
		return \readfile(self::path($filename, $throw));
	}

	/**
	 * Gets file size
	 *
	 * @param string $filename
	 * @param bool $throw
	 * @return int
	 */
	static public function filesize(string $filename, bool $throw = true): int
	{
		return \filesize(self::path($filename, $throw));
	}

	/**
	 * Tells whether the filename is a regular file
	 *
	 * @param string $filename
	 * @param bool $throw
	 * @return bool
	 */
	static public function is_file(string $filename, bool $throw = true): bool
	{
		return \is_file(self::path($filename, $throw));
	}

	/**
	 * Parse a configuration file
	 *
	 * @param string $filename
	 * @param bool $process_sections create a multidimensional array
	 * @param int $scanner_mode INI_SCANNER_NORMAL, INI_SCANNER_RAW, INI_SCANNER_TYPED
	 * @param bool $throw
	 * @return array
	 */
	static public function parse_ini_file(string $filename, bool $process_sections = FALSE, int $scanner_mode = INI_SCANNER_NORMAL, bool $throw = true): array
	{
		$array = parse_ini_file(self::path($filename, $throw), $process_sections, $scanner_mode);

		return ($array) ? $array : [];
	}

	/**
	 * Checks whether a file or directory exists
	 *
	 * @param string $filename
	 * @return bool
	 */
	static public function file_exists(string $filename): bool
	{
		return \file_exists(self::path($filename));
	}

	/**
	 * Reads entire file into an array
	 *
	 * @param string $filename
	 * @param int $flags
	 * @param bool $throw
	 * @return array
	 */
	static public function file(string $filename, int $flags = 0, bool $throw = true): array
	{
		return \file(self::path($filename, $throw), $flags);
	}

	/**
	 * Opens file
	 *
	 * @param string $filename
	 * @param string $mode
	 * @param bool $throw
	 * @return resource
	 */
	static public function fopen(string $filename, string $mode, bool $throw = true)
	{
		return \fopen(self::path($filename, $throw), $mode);
	}

	/* write */

	/**
	 * Write data to a file
	 * This should have thrown an error before not being able to write a file_exists
	 * This writes the file in a atomic fashion unless you use $flags
	 *
	 * @param string $filepath
	 * @param mixed $content
	 * @param int $flags
	 * @return mixed returns the number of bytes that were written to the file, or FALSE on failure.
	 */
	static public function file_put_contents(string $filepath, $content, int $flags = 0) /* mixed */
	{
		return ($flags) ? \file_put_contents(self::path($filepath), $content, $flags) : self::atomic_file_put_contents($filepath, $content);
	}

	/**
	 * atomic_file_put_contents
	 *
	 * @param string $filepath
	 * @param mixed $content
	 * @return mixed returns the number of bytes that were written to the file, or FALSE on failure.
	 */
	static public function atomic_file_put_contents(string $filepath, $content) /* mixed */
	{
		$filepath = self::path($filepath);

		/* get the path where you want to save this file so we can put our file in the same file */
		$dirname = \dirname($filepath);

		/* is the directory writeable */
		if (!is_writable($dirname)) {
			throw new FolderNotWritableException($dirname);
		}

		/* create file with unique file name with prefix */
		$tmpfname = \tempnam($dirname, 'afpc_');

		/* did we get a temporary filename */
		if ($tmpfname === false) {
			throw new FileWriteFailedException($tmpfname);
		}

		/* write to the temporary file */
		$bytes = \file_put_contents($tmpfname, $content);

		/* did we write anything? */
		if ($bytes === false) {
			throw new FileWriteFailedException($bytes);
		}

		/* changes file permissions so I can read/write and everyone else read */
		if (\chmod($tmpfname, 0644) === false) {
			throw new FileChmodFailedException($tmpfname);
		}

		/* move it into place - this is the atomic function */
		if (\rename($tmpfname, $filepath) === false) {
			throw new FileRenameFailedException($tmpfname . ' > ' . $filepath);
		}

		/* if it's cached we need to flush it out so the old one isn't loaded */
		self::remove_php_file_from_opcache($filepath);

		/* return the number of bytes written */
		return $bytes;
	}

	/**
	 * Deletes a file
	 *
	 * @param string $filename
	 * @param bool $throw
	 * @return bool
	 */
	static public function unlink(string $filename, bool $throw = false): bool
	{
		self::remove_php_file_from_opcache($filename);

		return \unlink(self::path($filename, $throw));
	}

	/**
	 * Makes directory
	 *
	 * @param string $pathname
	 * @param int $mode
	 * @param bool $recursive
	 * @return bool
	 */
	static public function mkdir(string $pathname, int $mode = 0777, bool $recursive = true): bool
	{
		$pathname = self::path($pathname);

		if (!\file_exists($pathname)) {
			$umask = \umask(0);
			$bool = \mkdir($pathname, $mode, $recursive);
			\umask($umask);
		} else {
			$bool = true;
		}

		return $bool;
	}

	/**
	 * Renames a file or directory
	 *
	 * @param string $oldname
	 * @param string $newname
	 * @param bool $throw
	 * @return void
	 */
	static public function rename(string $oldname, string $newname, bool $throw = true): bool
	{
		return \rename(self::path($oldname, $throw), self::path($newname));
	}

	/**
	 * Invalidates a cached script
	 *
	 * @param string $filepath
	 * @return bool
	 */
	static public function remove_php_file_from_opcache(string $filepath): bool
	{
		$filepath = self::path($filepath);

		$success = true;

		/* flush from the cache */
		if (\function_exists('opcache_invalidate')) {
			$success = \opcache_invalidate($filepath, true);
		} elseif (\function_exists('apc_delete_file')) {
			$success = \apc_delete_file($filepath);
		}

		return $success;
	}

	static public function var_export_php($data): string
	{
		if (\is_array($data) || \is_object($data)) {
			$string = '<?php return ' . \str_replace(['Closure::__set_state', 'stdClass::__set_state'], '(object)', \var_export($data, true)) . ';';
		} elseif (\is_scalar($data)) {
			$string = '<?php return "' . \str_replace('"', '\"', $data) . '";';
		} else {
			throw new FileWriteFailedException('Cache export save unknown data type.');
		}

		return $string;
	}
} /* end class */
