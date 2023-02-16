<?php

namespace Pay\Classes;
use Pay\Abstracts;
use Pay\DataType;

/**
 * Billing information class.
 *
 * @package Pay\Classes
 */
class Billing extends Abstracts\Restrict {

    /**
     * Billing information fields.
     *
     * @var array $fields
     */
    protected $fields = array(
        'address_line1' =>  null,
        'address_line2' =>  null,
        'city'          =>  null,
        'state'         =>  null,
        'country'       =>  null,
        'postal_code'   =>  null,
        'currency'      =>  null,
        'amount'        =>  null,
        'discount'      =>  null
    );

    /**
     * Initialize class fields with their string restrictions.
     */
    public function _initialize_strings() {
        $this->fields['address_line1'] = new DataType\LockedString(100, null);
        $this->fields['address_line2'] = new DataType\LockedString(100);
        $this->fields['city'] = new DataType\LockedString(30, "^([A-Za-z]+\s?[A-Za-z]*)+$");
        $this->fields['state'] = new DataType\LockedString(30, "^([A-Za-z]+\s?[A-Za-z]*)+$");
        $this->fields['country'] = new DataType\LockedString(3, "^[A-Z]+$");
        $this->fields['postal_code'] = new DataType\LockedString(12);
        $this->fields['currency'] = new DataType\LockedString(3, "^[A-Za-z]$", true);
        $this->fields['amount'] = new DataType\LockedString(100, "^\d+(\.\d{2})?$", true);
        $this->fields['discount'] = new DataType\LockedString(100, "^\d+(\.\d{2})?$");
    }

    /**
     * Callback function called when a property value was changed.
     *
     * @param string $property
     */
    protected function change($property) {
        $this->fields['address_line1']->not_empty(false, false);
        $this->fields['city']->not_empty(false, false);
        $this->fields['state']->not_empty(false, false);
        $this->fields['country']->not_empty(false, false);

        //if any of the inputs has value, make vital inputs mandatory except total
        foreach($this->fields as $field => $value) {
            if(in_array($field, array('address_line1', 'city', 'state', 'country')) && strval($value) !== '') {
                $this->fields['address_line1']->not_empty(true, false);
                $this->fields['city']->not_empty(true, false);
                $this->fields['state']->not_empty(true, false);
                $this->fields['country']->not_empty(true, false);
            }
        }

        if($property === 'country') {
            $country = strval($this->fields['country']);

            if(in_array($country, array('CA' ,'US'))) {
                try {
                    $this->fields['postal_code']->not_empty(true);

                    switch($country) {
                        case 'CA':
                            $this->fields['postal_code']->restrict("^[A-Za-z]\d[A-Za-z]\s\d[A-Za-z]\d$");
                            break;
                        case 'US':
                            $this->fields['postal_code']->restrict("^\d{5}(\-\d{4})?$");
                            break;
                    }
                } catch (\Exception $e) {
                    $this->fields['postal_code']->not_empty(false, false);
                    $this->{$property}('');
                    $this->change($property);
                }
            }
        }
    }

    /**
     * Retrieve billing information.
     *
     * @return array
     */
    public function get() {
        if(strval($this->fields['amount']) === '') {
            $this->amount('0.00');
        }

        if(strval($this->fields['discount']) === '') {
            $this->discount('0.00');
        }

        $arr = parent::get();

        $address_line1 = isset($arr['address_line1']) ? $arr['address_line1'] : '';
        $city = isset($arr['city']) ? $arr['city'] : '';
        $state = isset($arr['state']) ? $arr['state'] : '';
        $country = isset($arr['country']) ? $arr['country'] : '';

        if(isset($arr['postal_code']) && $arr['postal_code'] != '') {
            $postal_code = $arr['postal_code'];
        } else {
            unset($arr['postal_code']);
            $postal_code = '';
        }

        if(! isset($arr['currency'])) {
            $arr['currency'] = '';
        }

        // insert signature
        $arr['signature'] = hash('sha512', $address_line1 . $city . $state . $country . $postal_code . $arr['currency'] . $arr['amount'] . $arr['discount']);

        return $arr;
    }
}