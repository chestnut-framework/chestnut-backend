<?php
namespace Chestnut\Dashboard\Fields;

class Editor extends Field
{
    public function __construct($prop, $label)
    {
        parent::__construct($prop, $label, "c-editor");
    }
}
