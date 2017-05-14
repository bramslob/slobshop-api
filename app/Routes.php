<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->group('/api', function () {
    $this->group('/v1', function () {

        $this->get('', function (Request $request, Response $response) {

            return $response->withJson(['data' => 'Welcome']);
        });


    });
});