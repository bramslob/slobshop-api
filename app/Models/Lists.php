<?php

namespace App\Models;

class Lists extends BaseModel
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
        $listsQuery = $this->db->prepare('SELECT id, name FROM lists ORDER BY created_at DESC');

        $listsQuery->execute();

        return $listsQuery->fetchAll();
    }

    /**
     * @param $list_id
     *
     * @return array
     */
    public function getFromId($list_id)
    {
        $listsQuery = $this->db->prepare('SELECT id, name FROM lists WHERE id = :list_id ORDER BY created_at DESC');

        $listsQuery->execute([
            'list_id' => $list_id,
        ]);

        return $listsQuery->fetchAll();
    }

    /**
     * @return array|bool
     */
    public function create()
    {
        try {
            $this->db->beginTransaction();

            $insertQuery = $this->db->prepare('INSERT INTO lists (name) VALUES(:name)');
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
        try {
            $this->db->beginTransaction();

            $updateQuery = $this->db->prepare('UPDATE lists SET name  = :name WHERE id = :list_id');
            $updateQuery->execute([
                'name'    => $this->data['name'],
                'list_id' => $this->id,
            ]);
            $this->db->commit();

            return $this->getFromId($this->id);

        } catch (\Exception $exception) {

            $this->db->rollBack();

            return false;
        }
    }
}