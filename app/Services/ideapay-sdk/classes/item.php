<?php

namespace Pay\Classes;
use Pay\Abstracts;
use Pay\DataType;

/**
 * Item information class
 * @package Pay\Classes
 */
class Item extends Abstracts\Restrict {

    /**
     * @var array $fields
     */
    protected $fields = array(
        'name'      =>  null,
        'quantity'  =>  null,
        'value'     =>  null
    );

    /**
     * Initialize class fields with their string restrictions.
     */
    public function _initialize_strings() {
        $this->fields['name'] = new DataType\LockedString(0, "/[A-Za-z0-9\s]+/", true);
        $this->fields['quantity'] = new DataType\LockedString(0, "/^\d+$/", true);
        $this->fields['value'] = new DataType\LockedString(0, "/^\d+\.\d{2}$/", true);
    }
}