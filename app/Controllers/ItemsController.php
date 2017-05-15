<?php


namespace App\Controllers;

use App\Models\Items;
use App\Models\Lists;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ItemsController extends BaseController
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
     * @param Request $request
     *
     * @return bool|int
     */
    protected function checkItemId(Request $request)
    {
        $route = $request->getAttribute('route');
        $item_id = (int)$route->getArgument('item_id');

        if ($item_id <= 0) {
            return false;
        }

        /**
         * @var Items
         */
        if ((new Items($this->container->get('db')))->checkId($item_id) === false) {
            return false;
        }

        return $item_id;
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
        if (($item_id = $this->checkItemId($request)) === false) {
            return $response->withStatus(422, 'Invalid Item id provided');
        }

        /**
         * @var Items $ListItems
         */
        $ListItems = (new Items($this->container->get('db')))
            ->setData($request->getParsedBody())
            ->setIds([
                'list_id' => $list_id,
                'item_id' => $item_id,
            ]);

        if ($ListItems->validate() === false) {

            return $response->withStatus(422, 'Input incorrect');
        }

        return $response->withJson([
            'item' => $ListItems->update(),
        ]);
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function check(Request $request, Response $response)
    {
        if (($list_id = $this->checkListId($request)) === false) {
            return $response->withStatus(422, 'Invalid List id provided');
        }
        if (($item_id = $this->checkItemId($request)) === false) {
            return $response->withStatus(422, 'Invalid Item id provided');
        }

        /**
         * @var Items $ListItems
         */
        $ListItems = (new Items($this->container->get('db')))
            ->setIds(['item_id' => $item_id]);

        return $response->withJson([
            'item' => $ListItems->updateCheck()
        ]);
    }
}