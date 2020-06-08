<?php
namespace Chestnut\Dashboard\Fields;

class CheckboxGroup extends Field
{
    public $edit_only = true;
    public $creatble  = true;
    public $sources;

    public function __construct($prop, $label, $sources = [])
    {
        parent::__construct($prop, $label, "c-checkboxgroup");

        $this->sources($sources);
    }

    public function sources($sources)
    {
        $this->sources = $sources;
    }
}
