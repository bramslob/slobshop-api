<?php

namespace App\Models;

use PDO;

abstract class BaseModel
{
    /**
     * @var array
     */
    protected $validation = [];

    /**
     * @var PDO
     */
    protected $db;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var int
     */
    protected $id;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * @return array
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * @param array $data
     *
     * @return BaseModel
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return bool
     */
    public function validate()
    {
        return count(array_intersect_key($this->data, $this->validation)) > 0;
    }

    /**
     * @param mixed $id
     *
     * @return BaseModel
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}