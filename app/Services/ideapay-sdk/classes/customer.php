<?php

namespace Pay\Classes;
use Pay\Abstracts;
use Pay\DataType;

/**
 * Customer information class
 *
 * @package Pay\Classes
 */
class Customer extends Abstracts\Restrict {

    /**
     * @var array $fields
     */
    protected $fields = array(
        'first_name'            =>  null,
        'last_name'             =>  null,
        'middle_name'           =>  null,
        'email_address'         =>  null,
        'phone_number'          =>  null,
        'mobile_number'         =>  null,
        'date_of_birth'         =>  null,
        # company
        'legal_name'            =>  null,
        'business_name'         =>  null,
        'date_of_registration'  =>  null
    );

    /**
     * Initialize class fields with their string restrictions.
     */
    public function _initialize_strings() {
        $this->fields['first_name'] = new DataType\LockedString(30, "^([A-Za-z0-9]+\s?[A-Za-z0-9]*)+$", true);
        $this->fields['last_name'] = new DataType\LockedString(32, "^([A-Za-z0-9]+\s?[A-Za-z0-9]*)+$", true);
        $this->fields['middle_name'] = new DataType\LockedString(32, "^([A-Za-z0-9]+\s?[A-Za-z0-9]*)+$");
        $this->fields['email_address'] = new DataType\LockedString(100, "^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$", true);
        $this->fields['phone_number'] = new DataType\LockedString(32, "^\+\d{1,3}\(\d{3}\)\d{3}\-\d{4}(\-\d{3})?$");
        $this->fields['mobile_number'] = new DataType\LockedString(32, "^\+\d{1,3}\(\d{3}\)\d{3}\-\d{4}(\-\d{3})?$");
        $this->fields['date_of_birth'] = new DataType\LockedString(10, "^\d{4}\-(1[0-2]|0[0-9])\-(0[1-9]|[1-2][0-9]|3[0-1])$");
        $this->fields['legal_name'] = $this->fields['first_name'];
        $this->fields['business_name'] = $this->fields['last_name'];
        $this->fields['date_of_registration'] = $this->fields['date_of_birth'];
    }

    /**
     * Retrieve customer information
     *
     * @return array
     */
    public function get() {
        $arr = parent::get();

        // insert signature
        $arr['signature'] = hash('sha512', $arr['first_name'] . $arr['last_name'] . $arr['email_address']);

        unset($arr['legal_name']);
        unset($arr['business_name']);
        unset($arr['date_of_registration']);

        return $arr;
    }
}