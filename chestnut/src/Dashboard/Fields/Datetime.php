<?php
namespace Chestnut\Dashboard\Fields;

class Datetime extends Field
{
    public function __construct($prop, $label)
    {
        parent::__construct($prop, $label, "el-date-picker");

        $this->setAttribute('type', 'datetime');
    }
}
