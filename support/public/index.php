<?php

/* Based off this file where is the root of our web application? */
define('__ROOT__', realpath(__DIR__ . '/../'));

/* Changes PHP's current directory */
chdir(__ROOT__);

/* Load composer auto loader */
require 'vendor/autoload.php';

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

$config = array_replace(['services' => require __ROOT__ . '/vendor/projectorangebox/cms/src/Config/services.php'], parse_ini_file(__ROOT__ . '/site/config.ini', true, INI_SCANNER_TYPED));

/* create container and application and dispatch */
$app = new \projectorangebox\cms\App(new \projectorangebox\cms\Container($config));

$app->dispatch();
