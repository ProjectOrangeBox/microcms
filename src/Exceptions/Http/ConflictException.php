<?php

namespace projectorangebox\cms\Exceptions\Http;

use projectorangebox\cms\Exceptions\HttpException;

class conflictException extends HttpException
{
	protected $code = 409;
}
