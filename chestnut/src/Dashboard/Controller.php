<?php
namespace Chestnut\Dashboard;

use App\User;
use Chestnut\Dashboard\Exceptions\NutCreateException;
use Chestnut\Dashboard\ORMDriver\EloquentDriver;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class Controller
{
    protected $ormDriver = [];

    /**
     * Get Resource Action string
     *
     * @param String $action action name
     *
     * @return string
     */
    public function getAction(string $action)
    {
        return static::class . "@" . $action;
    }

    /**
     * Get fields collection
     *
     * @return Iluminate/Support/Collection
     */
    public function getFields()
    {
        return method_exists($this, 'fields') ? collect($this->fields()) : collect([]);
    }

    public function getORMDriver($driver = "eloquent")
    {
        if (!isset($this->ormDriver[$driver])) {
            return $this->defaultORMDriver();
        }

        return $this->ormDriver[$driver];
    }

    public function defaultORMDriver()
    {
        if (isset($this->ormDriver["eloquent"])) {
            return $this->ormDriver["eloquent"];
        }

        $driver = new EloquentDriver($this->getModel());

        if (isset($this->with)) {
            $driver->setWith($this->with);
        }

        $this->setORMDriver("eloquent", $driver);

        return $driver;
    }

    public function setORMDriver($driver = "eloquent", $driverInstance)
    {
        $this->ormDriver[$driver] = $driverInstance;
    }

    public function before(User $user)
    {
        if ($user->roles['type'] == 'admin') {
            return true;
        }
    }

    public function create(User $user)
    {
        return $user->can("{$this->getName()}.create");
    }

    public function update(User $user)
    {
        return $user->can("{$this->getName()}.edit");
    }

    public function delete(User $user)
    {
        return $user->can("{$this->getName()}.delete");
    }

    public function getCreate()
    {
        $fields = $this->getFields();

        $fields = $fields->filter(function ($field) {
            return $field->showInCreate();
        });

        return $fields->map(function ($field) {
            if ($field->isReadonly()) {
                $field->setAttribute('readonly', false);
            }

            return $field;
        })->values();
    }

    public function getTable(Request $request)
    {
        $driver = $this->getORMDriver();

        $query = $driver->getQuery();
        $query = $driver->tableQuery($query);

        $size = $request->input('pageSize', 10);

        $query = $query->orderBy($request->input('sortBy', 'created_at'), $request->input('descending', 'desc'));

        if (method_exists($this, "beforeGet")) {
            $this->beforeGet($query);
        }

        $model = $query->paginate($size);

        if (method_exists($this, "afterGet")) {
            $this->afterGet($model);
        }

        $fields = $this->getFields();
        $fields = $fields->filter(function ($field) {
            return $field->showInTable();
        })->values();

        $data = [
            'code' => 200,
            'data' => $model->items(),
            'total' => $model->total(),
            'columns' => $fields,
        ];

        return $data;
    }

    public function getEdit(Request $request, $id)
    {
        $driver = $this->getORMDriver();

        $query = $driver->getQuery();
        $query = $driver->editorQuery($query);

        $fields = $this->getFields();

        $data = [
            'code' => 200,
            'data' => $query->find($id),
            'columns' => $fields->filter(function ($field) {
                return $field->showInEdit();
            })->values(),
        ];

        return $data;
    }

    public function getDetail(Request $request, $id)
    {
        $driver = $this->getORMDriver();

        $query = $driver->getQuery();
        $query = $driver->detailQuery($query);

        $fields = $this->getFields()->filter(function ($field) {
            return $field->showInDetail();
        });
        $item = $query->find($id);

        $data = [
            'code' => 200,
            'data' => $item,
            'columns' => $fields->values(),
        ];

        return $data;

    }

    public function destroy(Request $request)
    {
        if (!$request->user()->can('delete', $this->namespace . $this->getName())) {
            return response()->json(["message" => "未获得删除权限"], 403);
        }

        $deleted = $this->getORMDriver()->getQuery()->find($request->id)->delete();

        return ["code" => 200, 'message' => "删除成功"];
    }

    public function putEdit(Request $request, $id)
    {
        if (!$request->user()->can('update', $this->namespace . $this->getName())) {
            return response()->json(["message" => "未获得修改权限"], 403);
        }

        $query = $this->getORMDriver()->getQuery();

        $validator = $this->validate($request);

        $model = $query->find($id);

        $props = $this->getProps($request);

        foreach ($props as $prop => $value) {
            try {
                $this->setAttribute($model, $prop, $value);
            } catch (NutCreateException $e) {
            }
        }

        if (method_exists($this, "saving")) {
            $this->saving($model, $props);
        }

        if ($model->push()) {
            if (method_exists($this, "saved")) {
                $this->saved($model, $props);
            }

            return ["code" => 200, "message" => "编辑成功"];
        }
    }

    public function postCreate(Request $request)
    {
        if (!$request->user()->can('create', $this->namespace . $this->getName())) {
            return response()->json(["message" => "未获得创建权限"], 403);
        }

        $validator = $this->validate($request);

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

        if (method_exists($this, "saving")) {
            $this->saving($model, $props);
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

            if (method_exists($this, "saved")) {
                $this->saved($model, $props);
            }

            return ["code" => 200, "message" => "创建成功"];
        }
    }

    protected function setAttribute($obj, $prop, $value)
    {
        if ($obj === null) {
            throw new NutCreateException();
        }

        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $this->setAttribute($obj[$prop], $key, $val);
            }
            return;
        }

        if ($value instanceof UploadedFile) {
            $value = '/storage' . substr($value->store('public/uploads'), 6);
        }

        $obj[$prop] = $value;
    }

    protected function getProps(Request $request)
    {
        $fields = $this->getFields();
        $except = $this->except ?? [];

        $prop_names = $fields->filter(function ($field) {
            return !$field->isReadonly() || $field->showInCreate();
        })->pluck("prop");

        if (count($except) > 0) {
            $prop_names = $fields->filter(function ($prop) use ($except) {
                return !in_array($prop, $except);
            });
        }

        $props = $request->only($prop_names);

        return $props;
    }

    public function getExceptRequest()
    {
        return app('request')->only($this->except);
    }

    public function getExceptProp($prop)
    {
        if (!in_array($prop, $this->except)) {
            throw new Error("prop [$prop] not except");
        }

        return isset($this->getExceptRequest()[$prop]) ? $this->getExceptRequest()[$prop] : [];
    }

    public function validate($request)
    {
        $fields = $this->getFields();

        $validates = $fields->pluck('validate', 'prop')->reject(function ($item, $prop) {
            return is_null($item) || $prop == 'password';
        })->all();

        $attributes = $fields->pluck('label', 'prop')->all();

        return Validator::make($request->all(), $validates, [], $attributes)->validate();
    }
}
