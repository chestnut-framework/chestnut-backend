<?php
namespace Chestnut\Dashboard\Fields;

class Datetime extends Field
{
    public function __construct($prop, $label)
    {
        parent::__construct($prop, $label, "DatePicker");

        $this->setAttribute('type', 'datetime');
    }
}
