<?php
namespace Chestnut\Dashboard\Fields;

class Avatar extends Field
{
    public function __construct($prop, $label)
    {
        parent::__construct($prop, $label, "c-avatar");
    }
}
