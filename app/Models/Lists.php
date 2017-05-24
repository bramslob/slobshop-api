<?php

namespace App\Models;

class Lists extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'lists';

    /**
     * @var array
     */
    protected $validation = [
        'name' => '',
    ];

    /**
     * @return array
     */
    public function getOverview($archive = false)
    {
        $listsQuery = $this->db->prepare('SELECT id, identifier, name, checked FROM lists WHERE checked = :checked ORDER BY updated_at DESC');

        $listsQuery->execute([
            'checked' => $archive,
        ]);

        return $listsQuery->fetchAll();
    }

    /**
     * @param $list_id
     *
     * @return array
     */
    public function getFromId($list_id)
    {
        $listsQuery = $this->db->prepare('SELECT id, name, checked FROM lists WHERE id = :list_id');

        $listsQuery->execute([
            'list_id' => $list_id,
        ]);

        return $listsQuery->fetch();
    }

    /**
     * @param $identifier
     *
     * @return array
     */
    public function getFromIdentifier($identifier)
    {
        $listsQuery = $this->db->prepare('SELECT id, name, checked FROM lists WHERE identifier = :identifier');

        $listsQuery->execute([
            'identifier' => $identifier,
        ]);

        return $listsQuery->fetch();
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

            $current = $this->getFromId($this->ids['id']);

            if (($diff = $this->diff($current)) === []) {
                return $current;
            }

            $updateQuery = $this->db->prepare('UPDATE lists SET name = :name WHERE id = :id');
            $updateQuery->execute(
                array_merge(
                    $diff,
                    $this->ids
                )
            );

            $this->db->commit();

            return $this->getFromId($this->ids['id']);

        } catch (\Exception $exception) {

            $this->db->rollBack();

            return false;
        }
    }

    /**
     * @return array|bool
     */
    public function updateCheck()
    {
        try {
            $this->db->beginTransaction();

            $updateQuery = $this->db->prepare('UPDATE lists SET checked = NOT checked WHERE id = :id');
            $updateQuery->execute($this->ids);

            $this->db->commit();

            return $this->getFromId($this->ids['id']);

        } catch (\Exception $exception) {

            $this->db->rollBack();

            return false;
        }
    }
}