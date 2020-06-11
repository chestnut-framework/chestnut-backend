<?php
/**
 * Chestnut Admin Resource Field.
 *
 */
namespace Chestnut\Dashboard\Fields;

use JsonSerializable;

/**
 * Chestnut Admin Resource Field abstract class
 *
 * @category Abstract_Field
 * @package  Chestnut\Dashboard
 * @author   Leon Zhang <33543015@qq.com>
 */
abstract class Field implements JsonSerializable
{
    public $prop;
    public $label;
    public $component;
    public $attrs;
    public $hidden;

    /**
     * Field Constructor
     *
     * @param {String} $prop  property name
     * @param {String} $label property show label text
     * @param {String} $type  property type
     */
    public function __construct($prop, $label, $component)
    {
        $this->prop = $prop;
        $this->label = $label;
        $this->component = $component;

        $this->attrs = collect();
        $this->hidden = collect();
    }

    public function setAttribute($key, $value)
    {
        $this->attrs[$key] = $value;

        return $this;
    }

    public function hiddenIn($key)
    {
        $this->hidden[$key] = true;

        return $this;
    }

    /**
     * Set property readonly
     *
     * @return self
     */
    public function readonly()
    {
        return $this->setAttribute('readonly', true);
    }

    /**
     * Set property sortable
     *
     * @return self
     */
    public function sortable()
    {
        return $this->setAttribute('sortable', true);
    }

    /**
     * Set show property
     *
     * If you want to show by relation's property
     *
     * @param String $showProp relation's property name
     *
     * @return self
     */
    public function showAs(string $showProp)
    {
        return $this->setAttribute('show', $showProp);
    }

    public function onlyCreate()
    {
        return $this->hideInTable()->hideInEdit()->hideInDetail();
    }

    public function onlyEdit()
    {
        return $this->hideInTable()->hideInCreate()->hideInDetail();
    }

    public function onlyDetail()
    {
        return $this->hideInTable()->hideInCreate()->hideInEdit();
    }

    public function onlyTable()
    {
        return $this->hideInCreate()->hideInEdit();
    }

    public function hideInTable()
    {
        return $this->hiddenIn("table");
    }

    public function hideInCreate()
    {
        return $this->hiddenIn("create");
    }

    public function hideInEdit()
    {
        return $this->hiddenIn("edit");
    }

    public function hideInDetail()
    {
        return $this->hiddenIn("detail");
    }

    public function showInTable()
    {
        return !$this->hidden->get('table', false);
    }

    public function showInCreate()
    {
        return !$this->hidden->get('create', false);
    }

    public function showInEdit()
    {
        return !$this->hidden->get('edit', false);
    }

    public function showInDetail()
    {
        return !$this->hidden->get('detail', false);
    }

    public function rules()
    {
        $rules = func_get_args();

        return $this->setAttribute("rules", $rules);
    }

    /**
     * Set field validator
     *
     * @param String $validate
     *
     * @return self
     */
    public function validate(string $validate)
    {
        return $this->setAttribute('validate', $validate);
    }

    public function jsonSerialize()
    {
        return [
            "name" => $this->prop,
            "label" => $this->label,
            "component" => $this->component,
            "align" => 'center',
            "attrs" => $this->attrs->all(),
            "hidden" => $this->hidden->all(),
        ];
    }

    public function isReadonly()
    {
        return isset($this->attrs['readonly']) && $this->attrs['readonly'];
    }
}
