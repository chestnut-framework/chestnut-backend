<?php

namespace Chestnut\Auth\Nuts;

use Chestnut\Dashboard\Nut;
use Chestnut\Facades\Shell;

class Permission extends Nut
{
    //
    protected $namespace = 'Chestnut\Auth\Models';

    protected $icon = "view-grid-add";

    public function fields(): array
    {
        return [
            Shell::ID(),
            Shell::Text('name', "名称"),
        ];
    }

    public function group()
    {
        return "permissions@user-group";
    }
}
