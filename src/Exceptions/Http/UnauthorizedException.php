<?php

namespace projectorangebox\cms\Exceptions\Http;

use projectorangebox\cms\Exceptions\HttpException;

class unauthorizedException extends HttpException
{
	protected $code = 401;
}
