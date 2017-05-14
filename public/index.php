<?php

require __DIR__ . '/../vendor/autoload.php';

$container = new \Slim\Container;

$app = new \Slim\App($container);

require_once(__DIR__ . '/../setup/Dependencies.php');
require_once(__DIR__ . '/../app/Routes.php');

$app->run();