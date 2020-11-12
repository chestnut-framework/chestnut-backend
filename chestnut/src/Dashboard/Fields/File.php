<?php
namespace Chestnut\Dashboard\Fields;

class File extends Field
{
    public function __construct($prop, $label)
    {
        parent::__construct($prop, $label, "File");

        $this->hideInTable();
    }
}
