<?php


namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Items extends BaseController
{

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return mixed
     */
    public function overview(Request $request, Response $response)
    {
        $route = $request->getAttribute('route');
        $list_id = (int)$route->getArgument('list_id');

        if (empty($list_id) || $list_id <= 0) {
            return $response->withStatus(422, 'List id not provided');
        }

        /**
         * @var \PDO $db
         */
        $db = $this->container->get('db');

        $itemsQuery = $db->prepare('SELECT id, name, COLUMN_JSON(data) FROM items WHERE list_id = :list_id ORDER BY created_at DESC');
        $itemsQuery->execute(
            ['list_id' => $list_id]
        );

        return $response->withJson([
            'items' => $itemsQuery->fetchAll(),
        ]);
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return static
     */
    public function create(Request $request, Response $response)
    {
        $validation = [
            'name' => '',
        ];

        $body = $request->getParsedBody();
        if (count(array_intersect_key($body, $validation)) <= 0) {
            return $response->withStatus(422, 'Required fields not provided');
        }
        /**
         * @var \PDO $db
         */
        $db = $this->container->get('db');

        try {
            $db->beginTransaction();
            $insertQuery = $db->prepare('INSERT INTO items (name) VALUES(:name)');
            $insertQuery->execute(['name' => $body['name']]);
            $db->commit();
        } catch (\Exception $exception) {
            $db->rollBack();

            return $response->withStatus(422, 'Insert failed');
        }

        $itemsQuery = $db->prepare('SELECT id, name FROM items WHERE name = :name');
        $itemsQuery->execute(['name' => $body['name']]);

        return $response->withJson([
            'item' => $itemsQuery->fetch(),
        ]);
    }
}