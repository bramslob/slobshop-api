<?php

use App\Controllers\ItemsController;
use App\Controllers\ListsController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->group('/api', function () {
    $this->group('/v1', function () {

        $this->get('', function (Request $request, Response $response) {

            return $response->withJson(['data' => 'Welcome']);
        });

        $this->group('/lists', function () {
            $this->get('', ListsController::class . ':overview');
            $this->post('', ListsController::class . ':create');
            $this->post('/{list_id}', ListsController::class . ':update');

            $this->group('/{list_id}/items', function () {
                $this->get('', ItemsController::class . ':overview');
                $this->post('', ItemsController::class . ':create');
                $this->get('/{item_id}', ItemsController::class . ':view');
                $this->post('/{item_id}', ItemsController::class . ':update');
                $this->post('/{item_id}/check', ItemsController::class . ':check');
                $this->post('/{item_id}/delete', ItemsController::class . ':delete');
            });
        });
    });
});