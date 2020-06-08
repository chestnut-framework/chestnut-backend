<?php

namespace Chestnut\Dashboard\ORMDriver;

class EloquentDriver
{
    public $model;

    public $with;

    public function __construct($name)
    {
        $this->model = $name;
    }

    public function setWith(array $with)
    {
        $this->with = $with;
    }

    /**
     * Get Resource Model
     * Default namespace is App
     *
     * Set namespace by define $namespace in class parameter
     *
     * @return Model
     */
    public function getModel()
    {
        $model = new $this->model();

        return $model;
    }

    public function getQuery()
    {
        $query = $this->getModel();

        if (isset($this->with)) {
            $query = $query->with($this->with);
        }

        return $query;
    }

    public function tableQuery($query)
    {
        return $query;
    }

    public function detailQuery($query)
    {
        return $query;
    }

    public function editorQuery($query)
    {
        return $query;
    }
}
