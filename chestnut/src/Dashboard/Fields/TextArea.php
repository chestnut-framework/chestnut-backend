<?php
namespace Chestnut\Dashboard\Fields;

class TextArea extends Field
{
    public function __construct($prop, $label)
    {
        parent::__construct($prop, $label, "c-textarea");
    }
}
