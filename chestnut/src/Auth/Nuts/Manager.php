<?php

namespace Chestnut\Auth\Nuts;

use Chestnut\Dashboard\Nut;
use Chestnut\Facades\Shell;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class Manager extends Nut
{
    protected $namespace = 'Chestnut\Auth\Models';

    protected $with = ['roles:id,name'];

    protected $except = ['roles'];

    protected $icon = "users";

    public function boot()
    {
        $model = $this->getModel();

        $model::updated(function ($model) {
            if (!$model->wasChanged('api_token')) {
                $roles = $this->getExceptProp('roles');

                $assignedRoles = $model->getRoleNames();

                $removeRoles = $assignedRoles->diff($roles);

                foreach ($removeRoles as $remove) {
                    $model->removeRole($remove);
                }

                if (empty($roles)) {
                    return;
                }

                $model->assignRole($roles);
            }
        });
    }

    public function fields(): array
    {
        return [
            Shell::ID(),
            Shell::Text('email', '电子邮箱')->rules("account"),
            Shell::Text("phone", "手机号")->readonly()->rules('account'),
            Shell::Text("name", "昵称")->sortable()
                ->rules('required'),
            Shell::Avatar("avatar", "头像")->rules('required'),
            Shell::Password('password', '密码')->hideInTable()->rules('password'),
            Shell::Select('roles', '角色')->options(Role::all(), 'name', 'name')->multiple()->prop('name'),
            Shell::CreatedAt("注册时间"),
        ];
    }

    public function registerRoutes($router)
    {
        $router->get('me', $this->getAction("me"));
    }

    public function group()
    {
        return "permissions@user-group";
    }

    public function me(Request $request)
    {
        return ['code' => 200, 'data' => $request->user()->only("name", "avatar")];
    }
}
