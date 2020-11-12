<?php
namespace Chestnut\Dashboard\Fields;

class Password extends Text
{
    public function __construct($prop)
    {
        parent::__construct($prop, "input");

        $this->setAttribute('type', 'password');
    }
}
