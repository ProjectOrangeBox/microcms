<?php

namespace projectorangebox\cms\Exceptions\Http;

use projectorangebox\cms\Exceptions\HttpException;

class notAcceptedException extends HttpException
{
	protected $code = 406;
}
