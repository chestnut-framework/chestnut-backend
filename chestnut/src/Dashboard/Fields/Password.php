<?php
namespace Chestnut\Dashboard\Fields;

use Illuminate\Support\Facades\Hash;

class Password extends Field
{
    public function __construct($prop, $label)
    {
        parent::__construct($prop, $label, "c-password");
    }

    public function process($value)
    {
        return Hash::make($value);
    }
}
