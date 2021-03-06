<?php

namespace Chestnut\Auth\Nuts;

use Chestnut\Dashboard\Nut;
use Chestnut\Facades\Shell;
use Spatie\Permission\Models\Permission;

class Role extends Nut
{
    //
    protected $namespace = 'Chestnut\Auth\Models';

    protected $except = ['permissions'];

    protected $with = ['permissions:id,name'];

    protected $icon = "user-circle";

    public function boot()
    {
        $model = $this->getModel();

        $model::saved(function ($model) {
            $permissions       = $this->getExceptProp('permissions');
            $gavePermissions   = $model->getPermissionNames();
            $removePermissions = $gavePermissions->diff($permissions);

            foreach ($removePermissions as $remove) {
                $model->revokePermissionTo($remove);
            }

            if (empty($permissions)) {
                return;
            }

            $permissions = collect($permissions)->map(function ($permission) {
                return Permission::firstOrCreate(['name' => $permission]);
            });

            $model->givePermissionTo($permissions);
        });
    }

    public function fields(): array
    {
        $options = app('shell')->getNuts()->map(function ($nut) {
            return $nut->getName();
        })->map(function ($nut) {
            $result = [
                'value' => $nut,
                'label' => __("chestnut::chestnut.$nut"),
                'group' => collect(['create', 'edit', 'delete'])->map(function ($op) use ($nut) {
                    return ["value" => "$nut.$op", "label" => __("chestnut::chestnut.$op", ["name" => __("chestnut::chestnut.$nut")])];
                }),
            ];

            return $result;
        });

        return [
            Shell::ID(),
            Shell::Text("name", "名称"),
            Shell::Checkbox("permissions", "权限")->options($options)->prop("name")->hideInTable(),
        ];
    }

    public function group()
    {
        return "permissions@user-group";
    }
}
