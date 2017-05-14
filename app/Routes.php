<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->group('/api', function () {
    $this->group('/v1', function () {

        $this->get('', function (Request $request, Response $response) {

            return $response->withJson(['data' => 'Welcome']);
        });

        $this->group('/lists', function () {
            $this->get('', \App\Controllers\Lists::class . ':overview');
            $this->post('', \App\Controllers\Lists::class . ':create');
            $this->post('/{list_id}', \App\Controllers\Lists::class . ':update');

            $this->group('/{list_id}/items', function () {
                $this->get('', \App\Controllers\Items::class . ':overview');
                $this->post('', \App\Controllers\Items::class . ':create');
                $this->get('/{item_id}', \App\Controllers\Items::class . ':view');
                $this->post('/{item_id}', \App\Controllers\Items::class . ':update');
            });
        });
    });
});