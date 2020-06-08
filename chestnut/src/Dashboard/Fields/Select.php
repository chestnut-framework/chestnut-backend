<?php
namespace Chestnut\Dashboard\Fields;

class Select extends Field
{
    public function __construct($prop, $label)
    {
        parent::__construct($prop, $label, "c-select");
    }

    public function options($options)
    {
        return $this->setAttribute('options', $options);
    }

    public function multiple()
    {
        return $this->setAttribute('multiple', true);
    }

    public function prop(string $prop)
    {
        return $this->setAttribute('prop', $prop);
    }

    public function lazyLoad(string $api)
    {
        return $this->setAttribute('lazyLoadApi', $api);
    }
}
