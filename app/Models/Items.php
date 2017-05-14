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
        $itemsQuery = $this->db->prepare('SELECT id, name, COLUMN_JSON(data) FROM items ORDER BY created_at DESC');

        $itemsQuery->execute();

        return $itemsQuery->fetchAll();
    }

    /**
     * @param $list_id
     *
     * @return array
     */
    public function getFromId($item_id)
    {
        $itemsQuery = $this->db->prepare('SELECT id, name, COLUMN_JSON(data) FROM items WHERE id = :item_id ORDER BY created_at DESC');

        $itemsQuery->execute([
            'item_id' => $item_id,
        ]);

        return $itemsQuery->fetchAll();
    }

    /**
     * @return array|bool
     */
    public function create()
    {
        try {
            $this->db->beginTransaction();

            $insertQuery = $this->db->prepare('INSERT INTO items (name) VALUES(:name)');
            $insertQuery->execute(['name' => $this->data['name']]);
            $new_id = $this->db->lastInsertId();
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
}