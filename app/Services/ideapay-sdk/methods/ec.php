<?php

/**
 * E-commerce class.
 *
 * Use this on e-commerce transactions
 */
class EC extends Pay implements \Pay\Interfaces\ECInterface {
    /**
     * @var \Pay\Classes\Shipping $shipping
     */
    private $shipping;

    /**
     * @var \Pay\Classes\Order $order
     */
    private $order;

    /**
     * EC constructor.
     *
     * @param array $args
     */
    public function __construct($args = array()) {
        parent::__construct($args);

        if(isset($args['shipping'])) {
            $this->set_shipping_info($args['shipping']);
        }

        return $this;
    }

    /**
     * Set shipping information
     *
     * @param array $shipping
     * @return EC $this
     */
    public function set_shipping_info($shipping = array()) {
        if(is_array($shipping)) {
            $this->shipping = new \Pay\Classes\Shipping($shipping);
        }

        if($shipping instanceof \Pay\Classes\Shipping
        || is_subclass_of($shipping, '\Pay\Classes\Shipping')) {
            $this->shipping = $shipping;
        }


        return $this;
    }

    /**
     * Add item to e-commerce transaction request
     *
     * @param array $item
     * @return EC $this
     */
    public function add_item($item = array()) {
        $new_item = new \Pay\Classes\Item($item);

        if(! $this->order) $this->order = new \Pay\Classes\Order();
        $this->order->add($new_item);

        return $this;
    }

    /**
     * Fetch e-commerce information
     *
     * @return array
     * @throws Exception
     */
    public function get() {
        $r = array();

        try {
            $r = array_merge($r, array('order' => $this->order->get()));
        } catch (Exception $e) {
            throw new Exception('Encountered error retrieving Order Details: ' . $e->getMessage());
        }

        $amount = floatval($r['order']['subtotal']);

        if($this->shipping) {
            try {
                $r = array_merge($r, array('shipping' => $this->shipping->get()));
            } catch (Exception $e) {
                throw new Exception('Encountered error retrieving Shipping Information: ' . $e->getMessage());
            }

            $amount += floatval($r['shipping']['amount']);
        }

        $this->billing->amount = \Pay\Classes\Utilities::format($amount);

        $r = array_merge($r, parent::get());

        return $r;
    }
}