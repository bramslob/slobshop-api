<?php

namespace App\Controllers;

use App\Models\Lists;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ListsController extends BaseController
{
    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function checkListId(Request $request)
    {
        $route = $request->getAttribute('route');
        $list_id = (int)$route->getArgument('list_id');

        if ($list_id <= 0) {
            return false;
        }

        /**
         * @var Lists
         */
        if ((new Lists($this->container->get('db')))->checkId($list_id) === false) {
            return false;
        }

        return $list_id;
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function overview(Request $request, Response $response)
    {
        /**
         * @var Lists $List
         */
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
        /**
         * @var Lists $List
         */
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
        if (($list_id = $this->checkListId($request)) === false) {
            return $response->withStatus(422, 'Invalid List id provided');
        }

        /**
         * @var Lists $List
         */
        $List = (new Lists($this->container->get('db')))
            ->setData($request->getParsedBody())
            ->setIds(['id' => $list_id]);

        if ($List->validate() === false) {

            return $response->withStatus(422, 'Input incorrect');
        }

        return $response->withJson([
            'list' => $List->update(),
        ]);
    }
}

