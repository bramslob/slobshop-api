<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Lists extends BaseController
{
    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return mixed
     */
    public function overview(Request $request, Response $response)
    {
        /**
         * @var \PDO $db
         */
        $db = $this->container->get('db');

        $listsQuery = $db->prepare('SELECT id, name FROM lists ORDER BY created_at DESC');
        $listsQuery->execute();

        return $response->withJson([
            'lists' => $listsQuery->fetchAll(),
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
            $insertQuery = $db->prepare('INSERT INTO lists (name) VALUES(:name)');
            $insertQuery->execute(['name' => $body['name']]);
            $db->commit();
        } catch (\Exception $exception) {
            $db->rollBack();

            return $response->withStatus(422, 'Insert failed');
        }

        $listsQuery = $db->prepare('SELECT id, name FROM lists WHERE name = :name');
        $listsQuery->execute(['name' => $body['name']]);

        return $response->withJson([
            'list' => $listsQuery->fetch(),
        ]);
    }
}

