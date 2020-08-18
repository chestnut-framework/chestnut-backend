<?php
namespace Chestnut\Dashboard\Fields;

class Password extends Field
{
    public function __construct($prop, $label)
    {
        parent::__construct($prop, $label, "el-input");

        $this->setAttribute('show-password', true);
    }
}
