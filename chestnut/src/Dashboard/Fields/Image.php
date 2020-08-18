<?php
namespace Chestnut\Dashboard\Fields;

class Image extends Field
{
    public function __construct($prop, $label)
    {
        parent::__construct($prop, $label, "c-image");
    }

    public function multiple()
    {
        return $this->setAttribute('multiple', true);
    }
}
