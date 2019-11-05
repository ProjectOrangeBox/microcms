<?php

namespace projectorangebox\cms\Exceptions\Http;

use projectorangebox\cms\Exceptions\HttpException;

class serverErrorException extends HttpException
{
	protected $code = 500;
}
