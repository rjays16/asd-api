<?php

namespace Pay\Interfaces;

/**
 * Interface PayInterface
 *
 * @package Pay\Interfaces
 */
interface PayInterface {
    /**
     * @param array $customer
     * @return self
     */
    public function set_customer_info($customer = array());

    /**
     * @param array $billing
     * @return self
     */
    public function set_billing_info($billing = array());
}