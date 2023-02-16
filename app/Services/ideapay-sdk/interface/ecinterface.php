<?php

namespace Pay\Interfaces;

/**
 * Interface ECInterface
 *
 * @package Pay\Interfaces
 */
interface ECInterface {
    /**
     * @param array $shipping
     * @return self
     */
    public function set_shipping_info($shipping = array());

    /**
     * @param array $item
     * @return self
     */
    public function add_item($item = array());
}