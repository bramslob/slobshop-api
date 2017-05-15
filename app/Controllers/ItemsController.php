<?php


namespace App\Controllers;

use App\Models\Items;
use App\Models\Lists;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ItemsController extends BaseController
{
    /**
     * @param int $list_id
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
     * @return mixed
     */
    public function overview(Request $request, Response $response)
    {
        if (($list_id = $this->checkListId($request)) === false) {
            return $response->withStatus(422, 'Invalid List id provided');
        }

        /**
         * @var Items $ListItems
         */
        $ListItems = (new Items($this->container->get('db')))
            ->setIds(['list_id' => $list_id]);

        return $response->withJson([
            'lists' => $ListItems->getOverview(),
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
        if (($list_id = $this->checkListId($request)) === false) {
            return $response->withStatus(422, 'Invalid List id provided');
        }

        /**
         * @var Items $ListItems
         */
        $ListItems = (new Items($this->container->get('db')))
            ->setIds(['list_id' => $list_id])
            ->setData($request->getParsedBody());

        if ($ListItems->validate() === false) {

            return $response->withStatus(422, 'Input incorrect');
        }

        return $response->withJson([
            'Item' => $ListItems->create(),
        ]);
    }

}