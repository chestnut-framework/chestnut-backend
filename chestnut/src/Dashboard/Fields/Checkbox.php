<?php
namespace Chestnut\Dashboard\Fields;

class Checkbox extends Field
{
    public function __construct($prop, $label)
    {
        parent::__construct($prop, $label, "c-checkbox");
    }

    public function options($options)
    {
        return $this->setAttribute('options', $options);
    }

    public function prop($prop)
    {
        return $this->setAttribute('prop', $prop);
    }
}
