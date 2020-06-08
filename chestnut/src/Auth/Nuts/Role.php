<?php

namespace Chestnut\Auth\Nuts;

use Chestnut\Dashboard\Nut;
use Chestnut\Facades\Shell;

class Role extends Nut
{
    //
    protected $namespace = 'Spatie\Permission\Models';

    public function fields()
    {
        return [
            Shell::ID(),
            Shell::Text("name", "名称"),
        ];
    }

    public function group()
    {
        return __("chestnut::chestnut.permission.manager");
    }

    public function registerRoutes($router)
    {
        $router->get('all', function () {
            return ['code' => 200, 'data' => $this->getModel()->all()];
        });
    }
}
