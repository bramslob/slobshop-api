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
     * @return Response
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
     * @return Response
     */
    public function create(Request $request, Response $response)
    {

        $route = $request->getAttribute('route');
        $list_id = (int)$route->getArgument('list_id');

        if (empty($list_id) || $list_id <= 0) {
            return $response->withStatus(422, 'List id not provided');
        }

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

            $insertQuery = $db->prepare('INSERT INTO items (name, list_id) VALUES(:name, :list_id)');
            $insertQuery->execute([
                'name'    => $body['name'],
                'list_id' => $list_id,
            ]);
            $last_inserted_id = $db->lastInsertId();


            if (!empty($body['data']) && is_array($body['data'])) {
                $this->saveDynamicColumns($db, $last_inserted_id, $body['data']);
            }

            $db->commit();
        } catch (\Exception $exception) {

            $db->rollBack();

            return $response->withStatus(422, 'Insert failed');
        }

        $itemsQuery = $db->prepare('SELECT id, name, COLUMN_JSON(data) FROM items WHERE id = :item_id');
        $itemsQuery->execute(['item_id' => $last_inserted_id]);

        return $response->withJson([
            'item' => $itemsQuery->fetchAll(),
        ]);
    }

    /**
     * @param array $data
     */
    protected function saveDynamicColumns($db, $item_id, array $data = [])
    {
        // for now we only accept key -> value.
        if (count($data) <= 0) {
            return;
        }

        $dynamic_column_creation_query = $db->prepare('UPDATE items SET data = COLUMN_CREATE(:key, :value) WHERE id = :item_id');
        $dynamic_column_addition_query = $db->prepare('UPDATE items SET data = COLUMN_ADD(data, :key, :value) WHERE id = :item_id');

        $dynamic_column_created = false;
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                continue;
            }

            try {
                $data = [
                    'item_id' => $item_id,
                    'key'     => $key,
                    'value'   => $value,
                ];

                if ($dynamic_column_created === true) {
                    $dynamic_column_addition_query->execute($data);
                }
                else {
                    $dynamic_column_creation_query->execute($data);
                    $dynamic_column_created = true;
                }

            } catch (\Exception $exception) {
                continue;
            }
        }
    }
}