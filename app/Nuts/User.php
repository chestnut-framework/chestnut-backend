<?php

namespace App\Nuts;

use Chestnut\Dashboard\Nut;
use Chestnut\Facades\Shell;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class User extends Nut
{
    protected $with = ['info', 'roles:id,name'];

    public function fields()
    {
        return [
            Shell::ID(),
            Shell::Text("phone", "手机号")->readonly()->rules('required'),
            Shell::Text("info.nickname", "昵称")->sortable()
                ->rules('required'),
            Shell::Avatar("info.avatar", "头像")->rules('required'),
            Shell::Password('password', '密码')->hideInTable()->hideInEdit()->hideInDetail()->rules('required|confirmed'),
            Shell::Select('roles', '角色')->options(Role::all(['name as value', 'name as label']))->multiple()->prop('name'),
            Shell::Datetime("created_at", "注册时间")->onlyTable()->readonly()->sortable(),
        ];
    }

    public function registerRoutes($router)
    {
        $router->get('me', $this->getAction("me"));
        $router->get('chart_data', $this->getAction("getCharts"));
    }

    public function me(Request $request)
    {
        return ['code' => 200, 'data' => auth()->user()->info];
    }

    public function saving($model, $props)
    {
        unset($model->roles);
    }

    public function saved($model, $props)
    {
        if (!$model->hasRole($props['roles'])) {
            $model->assignRole($props['roles']);
        }
    }
}
