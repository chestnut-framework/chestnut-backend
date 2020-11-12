<?php

namespace Chestnut\Dashboard;

use App\User;
use Chestnut\Dashboard\Exceptions\NutCreateException;
use Chestnut\Dashboard\ORMDriver\Driver;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Str;

class Controller
{
    /**
     * Resource eloquent model namespace
     *
     * @var string
     */
    protected $namespace = "App";

    /**
     * ORM Drivers
     *
     * @var array
     */
    protected $ormDriver = [];

    /**
     * Front end search columns
     *
     * @var array
     */
    protected $search = [];

    /**
     * Front end list component
     *
     * @var string
     */
    protected $listComponent = 'Table';

    /**
     * Front end editor component
     *
     * @var string
     */
    protected $editorComponent = "Editor";

    /**
     * Resource name
     *
     * @var string
     */
    private $_name;

    /**
     * Resource eloquent model
     *
     * @var string
     */
    protected $model;

    /**
     * Resource Construct
     */
    public function __construct()
    {
        $this->model = $this->_getModelName();
        $this->_name = strtolower($this->model);

        $this->boot();
    }

    public function boot()
    {
    }

    /**
     * Get resource name
     *
     * @return String
     */
    private function _getModelName(): string
    {
        $name = explode("\\", get_class($this));

        return array_pop($name);
    }

    /**
     * Get Resource Action string
     *
     * @param String $action action name
     *
     * @return string
     */
    public function getAction(string $action): string
    {
        return static::class . "@" . $action;
    }

    /**
     * Resource struct
     *
     * @return array
     */
    public function fields(): array
    {
        return [];
    }

    /**
     * Get fields collection
     *
     * @return Iluminate/Support/Collection
     */
    public function getFields(): Collection
    {
        return collect($this->fields());
    }

    /**
     * Get ORM Driver
     *
     * @param string $driver
     * @return Chestnut\Dashboard\ORMDriver\Driver
     */
    public function getORMDriver($driver = null): Driver
    {
        if (is_null($driver)) {
            $driver = config("chestnut.dashboard.driver");
        }

        if (!isset($this->ormDriver[$driver])) {
            return $this->defaultORMDriver();
        }

        return $this->ormDriver[$driver];
    }

    /**
     * Get default orm driver
     *
     * @return Chestnut\Dashboard\ORMDriver\Driver
     */
    public function defaultORMDriver(): Driver
    {
        $default = config('chestnut.dashboard.driver');

        if (isset($this->ormDriver[$default])) {
            return $this->ormDriver[$default];
        }

        $driver = config('chestnut.dashboard.drivers.' . $default);

        $driver = new $driver($this->getModel());

        if (isset($this->with)) {
            $driver->setWith($this->with);
        }

        $this->setORMDriver($default, $driver);

        return $driver;
    }

    /**
     * Store driver instance
     *
     * @param string $driver
     * @param Chestnut\Dashboard\ORMDriver\Driver $driverInstance
     * @return void
     */
    public function setORMDriver($driver, $driverInstance)
    {
        $this->ormDriver[$driver] = $driverInstance;
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @return Model
     */
    public function newQuery()
    {
        return $this->getORMDriver()->getQuery();
    }

    /**
     * Action table by [GET] Request
     *
     * @param Request $request
     * @return array
     */
    public function getTable(Request $request)
    {
        $driver = $this->getORMDriver();

        $query = $driver->getQuery();

        foreach ($this->search as $search) {
            $query = $query->where($search, 'like', "%{$request->get('search')}%");
        }

        $filters = $request->input("filters");

        if ($filters != "") {
            foreach (explode("|", $filters) as $filter) {
                return $filters;
                list($filter, $filterData) = explode(":", $filter);

                $query->whereIn($filter, explode(",", $filterData));
            }
        }

        $size = $request->get('size', 10);

        $this->sort($query, $request);

        $model = $query->paginate($size);

        $data = [
            'code' => 200,
            'data' => $model,
        ];

        return $data;
    }

    /**
     * Resolve sort options to query
     *
     * @param Illuminate\Database\Builder $query
     * @param Illuminate\Http\Request $request
     * @return void
     */
    private function sort($query, $request)
    {
        $sort = $request->input('sortBy', "created_at:desc");

        list($column, $order) = explode(":", $sort);

        if (strpos($column, ".")) {
            list($relation, $column) = explode(".", $column);

            $query->whereHas($relation, function ($query) use ($column, $order) {
                $query->orderBy($column, $order);
            });
        } else {
            $query->orderBy($column, $order);
        }
    }

    /**
     * Action edit by [GET] request
     *
     * @param integer $id
     * @return array
     */
    public function getEdit($id)
    {
        $driver = $this->getORMDriver();

        $query = $driver->getQuery();

        $data = [
            'code' => 200,
            'data' => $query->find($id),
        ];

        return $data;
    }

    /**
     * Action columns by [GET] request
     *
     * @return array
     */
    public function getColumns()
    {
        $fields = $this->getFields();

        if ($this->isSoftDelete()) {
            $fields->push(Shell::SoftDelete('删除时间'));
        }

        return ['code' => 200, 'data' => $fields];
    }

    /**
     * Destroy model
     *
     * @param Request $request
     * @return array
     */
    public function destroy(Request $request)
    {
        if (!$request->user()->can('delete', $this->namespace . $this->getName())) {
            return response()->json(["message" => "未获得删除权限"], 403);
        }

        $deleted = $this->getORMDriver()->getQuery()->find($request->id)->delete();

        return ["code" => 200, 'message' => "删除成功"];
    }

    /**
     * Action modify resource by [PUT] request
     *
     * @param Request $request
     * @param integer $id
     * @return array
     */
    public function putEdit(Request $request, $id)
    {
        if (!$request->user()->can('update', $this->namespace . $this->getName())) {
            return response()->json(["message" => "未获得修改权限"], 403);
        }

        $query = $this->getORMDriver()->getQuery();

        $model = $query->find($id);

        $props = $this->getProps($request);

        foreach ($props as $prop => $value) {
            try {
                $this->setAttribute($model, $prop, $value);
            } catch (NutCreateException $e) {
            }
        }

        if ($model->push()) {
            return ["code" => 200, "message" => "编辑成功"];
        }
    }

    /**
     * Restore resource by [PUT] request
     *
     * @param Request $request
     * @return array
     */
    public function putRestore(Request $request)
    {
        $model = $this->newQuery()->find($request->id);

        $model->restore();

        return ["code" => 200, "message" => "还原成功"];
    }

    /**
     * Action store resource by [POST] request
     *
     * @param Request $request
     * @return array
     */
    public function postCreate(Request $request)
    {
        if (!$request->user()->can('create', $this->namespace . $this->getName())) {
            return response()->json(["message" => "未获得创建权限"], 403);
        }

        $props = $this->getProps($request);

        $model = $this->getORMDriver()->getModel();

        $relations = [];

        foreach ($props as $prop => $value) {
            try {
                $this->setAttribute($model, $prop, $value);
            } catch (NutCreateException $e) {
                $relations[$prop] = $value;
            }
        }

        if ($model->save()) {
            if (count($relations) > 0) {
                foreach ($relations as $relation => $attributes) {
                    $relationAttributes = collect();
                    foreach ($attributes as $key => $value) {
                        $this->setAttribute($relationAttributes, $key, $value);
                    }

                    $model->$relation()->create($relationAttributes->all());
                }
            }

            return ["code" => 200, "message" => "创建成功"];
        }
    }

    /**
     * Set resource property
     *
     * @param [type] $obj
     * @param [type] $prop
     * @param [type] $value
     * @return void
     */
    protected function setAttribute($obj, $prop, $value)
    {
        if ($obj === null) {
            throw new NutCreateException();
        }

        if (is_array($value)) {
            foreach ($value as $key => $val) {
                if (is_int($key)) {
                    $this->setAttribute($obj, $prop, $val);
                } else {
                    $this->setAttribute($obj[$prop], $key, $val);
                }
            }
            return;
        }

        if ($value instanceof UploadedFile) {
            $value = asset($value->store(config("chestnut.dashboard.upload_storage")));

            $obj[$prop] = empty($obj[$prop]) ? $value : $obj[$prop] . ",$value";
        } else {
            $obj[$prop] = $value;
        }
    }

    protected function getProps(Request $request)
    {
        $fields = $this->getFields();
        $except = $this->except ?? [];

        $prop_names = $fields->filter(function ($field) {
            return !$field->isReadonly() || $field->showInCreate();
        })->pluck("prop");

        if (count($except) > 0) {
            $prop_names = $prop_names->filter(function ($prop) use ($except) {
                return !in_array($prop, $except);
            });
        }

        $props = $request->only($prop_names->all());

        return $props;
    }

    public function getExceptRequest()
    {
        return app('request')->only($this->except);
    }

    public function getExceptProp($prop)
    {
        if (!in_array($prop, $this->except)) {
            throw new \Error("prop [$prop] not except");
        }

        return isset($this->getExceptRequest()[$prop]) ? $this->getExceptRequest()[$prop] : [];
    }

    /**
     * Get Resource name
     *
     * @return String
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get resource model name
     *
     * @return void
     */
    public function getModel()
    {
        return $this->namespace . '\\' . $this->model;
    }

    /**
     * Determine resource model enabled softdelete
     *
     * @return boolean
     */
    public function isSoftDelete()
    {
        return $this->getORMDriver()->isSoftDelete();
    }
}
