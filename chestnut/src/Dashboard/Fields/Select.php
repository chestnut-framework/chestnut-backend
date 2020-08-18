<?php
namespace Chestnut\Dashboard\Fields;

use Illuminate\Support\Collection;

class Select extends Field
{
    public $filter = false;

    public function __construct($prop, $label)
    {
        parent::__construct($prop, $label, "c-select");
    }

    public function options($options, $label_prop = "name", $value_prop = "id")
    {
        if ($options instanceof Collection) {
            $options = $options->pluck($label_prop, $value_prop);
        }

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

    public function filter()
    {
        $this->filter = true;

        return $this;
    }

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();

        $data['filter'] = $this->filter;

        return $data;
    }
}
