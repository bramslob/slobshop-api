<?php

namespace App\Models;

class Items extends BaseModel
{
    /**
     * @var array
     */
    protected $validation = [
        'name' => '',
    ];

    /**
     * @return array
     */
    public function getOverview()
    {
        $itemsQuery = $this->db->prepare('SELECT id, name, COLUMN_JSON(data) AS `data` FROM items WHERE list_id = :list_id ORDER BY created_at DESC');

        $itemsQuery->execute($this->ids);

        return $this->toArray($itemsQuery->fetchAll());
    }

    /**
     * @param $item_id
     *
     * @return array
     */
    public function getFromId($item_id): array
    {
        $itemsQuery = $this->db->prepare('SELECT id, name, COLUMN_JSON(data) AS `data` FROM items WHERE id = :item_id ORDER BY created_at DESC');

        $itemsQuery->execute([
            'item_id' => $item_id,
        ]);

        return $this->toArray($itemsQuery->fetchAll());
    }

    /**
     * @param $data
     *
     * @return array
     */
    protected function toArray($data): array
    {
        if (!is_array($data)) {
            $data = [$data];
        }

        foreach ($data as &$row) {
            try {
                $row['data'] = json_decode($row['data']);
            } catch (\Exception $exception) {
                continue;
            }
        }

        return $data;
    }

    /**
     * @return array|bool
     */
    public function create()
    {
        try {
            $this->db->beginTransaction();

            $insertQuery = $this->db->prepare('INSERT INTO items (name, list_id) VALUES(:name, :list_id)');
            $insertQuery->execute(
                array_merge(
                    ['name' => $this->data['name']],
                    $this->ids
                )
            );

            $new_id = $this->db->lastInsertId();

            if (!empty($this->data['data']) && is_array($this->data['data'])) {
                $this->saveDynamicColumns($new_id, $this->data['data']);
            }

            $this->db->commit();

            return $this->getFromId($new_id);

        } catch (\Exception $exception) {
            $this->db->rollBack();

            return false;
        }
    }

    /**
     * @return array|bool
     */
    public function update()
    {

    }

    /**
     * @param array $data
     */
    protected function saveDynamicColumns($item_id, array $data = [])
    {
        if (count($data) <= 0) {
            return;
        }

        $dynamic_column_creation_query = $this->db->prepare('UPDATE items SET data = COLUMN_CREATE(:key, :value) WHERE id = :item_id');
        $dynamic_column_addition_query = $this->db->prepare('UPDATE items SET data = COLUMN_ADD(data, :key, :value) WHERE id = :item_id');

        $dynamic_column_created = false;
        foreach ($data as $key => $value) {

            if (null === $value) {
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