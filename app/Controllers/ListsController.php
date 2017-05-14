<?php

namespace App\Controllers;

use App\Models\Lists;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ListsController extends BaseController
{
    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function overview(Request $request, Response $response)
    {
        $List = new Lists($this->container->get('db'));

        return $response->withJson([
            'lists' => $List->getOverview(),
        ]);
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function create(Request $request, Response $response)
    {
        $List = (new Lists($this->container->get('db')))
            ->setData($request->getParsedBody());

        if ($List->validate() === false) {

            return $response->withStatus(422, 'Input incorrect');
        }

        return $response->withJson([
            'list' => $List->create(),
        ]);
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function update(Request $request, Response $response)
    {
        $route = $request->getAttribute('route');
        $list_id = (int)$route->getArgument('list_id');

        $List = (new Lists($this->container->get('db')))
            ->setData($request->getParsedBody())
            ->setId($list_id);

        if ($List->validate() === false) {

            return $response->withStatus(422, 'Input incorrect');
        }

        return $response->withJson([
            'list' => $List->update(),
        ]);
    }
}

