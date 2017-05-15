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
     * @var array
     */
    protected $ids = [];

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
     * @param array $ids
     *
     * @return BaseModel
     */
    public function setIds(array $ids)
    {
        $this->ids = $ids;

        return $this;
    }
}