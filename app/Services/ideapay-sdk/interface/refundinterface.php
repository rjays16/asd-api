<?php

namespace Pay\Interfaces;

/**
 * Interface RefundInterface
 *
 * @package Pay\Interfaces
 */
interface RefundInterface {
    /**
     * @return string
     */
    public function get_payment_id();

    /**
     * @param string $payment_id
     */
    public function set_payment_id($payment_id);
}