<?php

namespace App\Models;

class Items extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'items';

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
        $itemsQuery = $this->db->prepare('SELECT id, name, COLUMN_JSON(data) AS `data`, checked FROM items WHERE list_id = :list_id ORDER BY created_at DESC');

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
        $itemsQuery = $this->db->prepare('SELECT id, name, COLUMN_JSON(data) AS `data`, checked FROM items WHERE id = :item_id ORDER BY created_at DESC');

        $itemsQuery->execute([
            'item_id' => $item_id,
        ]);

        return $this->toArray([$itemsQuery->fetch()]);
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
                $row['data'] = json_decode($row['data'], true);
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
        try {
            $this->db->beginTransaction();

            $current = $this->getFromId($this->ids['item_id'])[0];

            if (($diff = $this->diff($current)) === []) {
                return $current;
            }

            if (!empty($diff['name'])) {
                $this->updateColumns($diff);
            }
            if (!empty($diff['data'])) {
                $this->updateData($current['data'], $diff['data']);
            }

            $this->db->commit();

            return $this->getFromId($this->ids['item_id']);

        } catch (\Exception $exception) {

            $this->db->rollBack();

            return false;
        }
    }

    /**
     * Update just the name, in the future the need for more data here is prop. needed, but for now : YAGNI
     */
    protected function updateColumns($diff)
    {
        $updateQuery = $this->db->prepare('UPDATE items SET name = :name WHERE id = :item_id');
        $updateQuery->execute(
            array_merge(
                $diff,
                ['item_id' => $this->ids['item_id']]
            )
        );
    }

    /**
     * @param $current_data
     * @param $update_data
     */
    protected function updateData($current_data, $update_data)
    {
        $this->saveDynamicColumns($update_data, count($current_data) > 0);
        $this->removeDynamicData(array_diff_key($current_data, $update_data));
    }

    /**
     * @return array|bool
     */
    public function updateCheck()
    {
        try {
            $this->db->beginTransaction();

            $updateQuery = $this->db->prepare('UPDATE items SET checked = NOT checked WHERE id = :item_id');
            $updateQuery->execute(['item_id' => $this->ids['item_id']]);

            $this->db->commit();

            return $this->getFromId($this->ids['item_id']);

        } catch (\Exception $exception) {

            $this->db->rollBack();

            return false;
        }
    }

    /**
     * @param array $data
     * @param bool  $dynamic_column_created
     */
    protected function saveDynamicColumns(array $data = [], $dynamic_column_created = false)
    {
        if (count($data) <= 0) {
            return;
        }

        $dynamic_column_creation_query = $this->db->prepare('UPDATE items SET data = COLUMN_CREATE(:key, :value) WHERE id = :item_id');
        $dynamic_column_addition_query = $this->db->prepare('UPDATE items SET data = COLUMN_ADD(data, :key, :value) WHERE id = :item_id');

        foreach ($data as $key => $value) {

            if (null === $value) {
                continue;
            }

            try {
                $data = [
                    'item_id' => $this->ids['item_id'],
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

    /**
     * @param array $data
     */
    protected function removeDynamicData(array $data = [])
    {
        if (count($data) <= 0) {
            return;
        }

        $dynamic_column_deletion_query = $this->db->prepare('UPDATE items SET data = COLUMN_DELETE(data, :key) WHERE id = :item_id');

        foreach ($data as $key => $value) {
            try {
                $dynamic_column_deletion_query->execute([
                    'item_id' => $this->ids['item_id'],
                    'key'     => $key,
                ]);

            } catch (\Exception $exception) {
                continue;
            }
        }
    }
}