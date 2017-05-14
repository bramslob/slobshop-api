<?php

use Psr\Container\ContainerInterface;

$container = $app->getContainer();

$env = (new josegonzalez\Dotenv\Loader(__DIR__ . '/../.env'))
    ->parse()
    ->toArray();

$container['config'] = function (ContainerInterface $container) use ($env) {
    return $env;
};
unset($env);

$container['db'] = function (ContainerInterface $container) {
    $settings = $container->get('config');

    $pdo = new PDO(
        'mysql:dbname=' . $settings['DB_DATABASE'] . ';host=' . $settings['DB_HOST'],
        $settings['DB_USERNAME'],
        $settings['DB_PASSWORD'],
        [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    return $pdo;
};