<?php

namespace projectorangebox\cms\Exceptions\Http;

use projectorangebox\cms\Exceptions\HttpException;

class notFoundException extends HttpException
{
	protected $code = 404;
}
