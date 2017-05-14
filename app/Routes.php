<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->group('/api', function () {
    $this->group('/v1', function () {

        $this->get('', function (Request $request, Response $response) {

            return $response->withJson(['data' => 'Welcome']);
        });

        $this->group('/lists', function() {
            $this->get('', \App\Controllers\Lists::class . ':overview');
            $this->post('', \App\Controllers\Lists::class . ':create');
            $this->get('/{list_id}', \App\Controllers\Lists::class . ':view');
            $this->post('/{list_id}', \App\Controllers\Lists::class . ':update');
        });
    });
});