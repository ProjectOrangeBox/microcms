<?php

/* Based off this file where is the root of our web application? */
define('__ROOT__', realpath(__DIR__ . '/../'));

/* Changes PHP's current directory */
chdir(__ROOT__);

/* Load composer auto loader */
require 'vendor/autoload.php';

$config = parse_ini_file(__ROOT__ . '/site/config.ini', true, INI_SCANNER_TYPED);
$container = new \projectorangebox\cms\Container();

/* create container and application and dispatch */
(new \projectorangebox\cms\App($config, $container))->dispatch();
