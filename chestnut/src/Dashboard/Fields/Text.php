<?php
namespace Chestnut\Dashboard\Fields;

class Text extends Field
{
    public function __construct($prop, $label)
    {
        parent::__construct($prop, $label, "el-input");
    }
}
