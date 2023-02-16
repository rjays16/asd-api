<?php

namespace Pay\Classes;

/**
 * Shipping information class
 *
 * @package Pay\Classes
 */
class Shipping extends Billing {

    /**
     * Initialize class fields with their string restrictions.
     */
    public function _initialize_strings() {
        parent::_initialize_strings();
        unset($this->fields['currency']);
        $this->fields['address_line1']->not_empty(false, false);
        $this->fields['city']->not_empty(false, false);
        $this->fields['state']->not_empty(false, false);
        $this->fields['country']->not_empty(false, false);
    }

    /**
     * Callback function called when a property value was changed.
     *
     * @param string $property
     */
    protected function change($property) {
        parent::change($property);
        //if any of the inputs has value, make inputs mandatory except total
        foreach($this->fields as $field) {
            if(strval($field) !== '') {
                $this->fields['address_line1']->not_empty(true, false);
                $this->fields['city']->not_empty(true, false);
                $this->fields['state']->not_empty(true, false);
                $this->fields['country']->not_empty(true, false);
                $this->fields['amount']->not_empty(true, false);
                return;
            }
        }

        $this->fields['address_line1']->not_empty(false, false);
        $this->fields['city']->not_empty(false, false);
        $this->fields['state']->not_empty(false, false);
        $this->fields['country']->not_empty(false, false);
        $this->fields['amount']->not_empty(false, false);
    }

    /**
     * Retrieve shipping information.
     *
     * @return array
     */
    public function get() {
        $arr = parent::get();

        unset($arr['currency']);
        unset($arr['discount']);

        // format 0.00
        $arr['amount'] = Utilities::format($arr['amount']);

        if(! isset($arr['address_line2'])) {
            $arr['address_line2'] = '';
        }

        // insert signature
        $arr['signature'] = hash('sha512', $arr['address_line1'] . $arr['address_line2'] . $arr['city'] . $arr['state'] . $arr['country'] . $arr['postal_code'] . $arr['amount']);

        return $arr;
    }
}