<?php

namespace Chestnut\Auth\Nuts;

use Chestnut\Dashboard\Nut;
use Chestnut\Facades\Shell;

class Permission extends Nut
{
    //
    protected $namespace = 'Spatie\Permission\Models';

    public function fields()
    {
        return [
            Shell::ID(),
            Shell::Text('name', "名称"),
        ];
    }

    public function group()
    {
        return __("chestnut::chestnut.permission.manager");
    }
}
