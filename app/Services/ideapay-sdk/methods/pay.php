<?php

/**
 * Use this to perform a simple payment.
 */
class Pay extends \Pay\Classes\Client implements \Pay\Interfaces\PayInterface {
    /**
     * @var \Pay\Classes\Customer $customer
     */
    private $customer;

    /**
     * @var \Pay\Classes\Billing $billing
     */
    protected $billing;

    /**
     * @var string $endpoint
     */
    protected $endpoint = '/payment';

    /**
     * Pay constructor.
     *
     * @param array $args
     * @return Pay $this
     */
    public function __construct($args = array()) {
        parent::__construct(array(
            'client_id'     =>  $args['client_id'],
            'client_secret' =>  $args['client_secret']
        ));

        if(isset($args['customer'])) {
            $this->set_customer_info($args['customer']);
        }

        if(isset($args['billing'])) {
            $this->set_billing_info($args['billing']);
        }

        return $this;
    }

    /**
     * Set customer information.
     *
     * @param array $customer
     * @return Pay $this
     */
    public function set_customer_info($customer = array()) {
        if(is_array($customer)) {
            $this->customer = new \Pay\Classes\Customer($customer);
        }

        if($customer instanceof \Pay\Classes\Customer
        || is_subclass_of($customer, '\Pay\Classes\Customer')) {
            $this->customer = $customer;
        }

        return $this;
    }

    /**
     * Set billing information.
     *
     * @param array $billing
     * @return Pay $this
     */
    public function set_billing_info($billing = array()) {
        if(is_array($billing)) {
            $this->billing = new \Pay\Classes\Billing($billing);
        }

        if($billing instanceof \Pay\Classes\Billing
        || is_subclass_of($billing, '\Pay\Classes\Billing')) {
            $this->billing = $billing;
        }

        return $this;
    }

    /**
     * Fetch payment information
     *
     * @return array
     * @throws Exception
     */
    public function get() {
        $r = array();

        try {
            $r = array_merge($r, array('customer' => $this->customer->get()));
        } catch (Exception $e) {
            throw new Exception('Encountered error retrieving Customer Information: ' . $e->getMessage());
        }

        try {
            $r = array_merge($r, array('billing' => $this->billing->get()));
        } catch (Exception $e) {
            throw new Exception('Encountered error retrieving Billing Information: ' . $e->getMessage());
        }

        return $r;
    }

    /**
     * Send payment request
     *
     * @return string
     * @throws Exception
     */
    public function send() {
        $this->payload = $this->get();

        if(! empty($this->payment_id)) {
            $this->payload['payment_id'] = $this->payment_id;
        }

        return parent::send();
    }

    /**
     * Request response callback
     *
     * @param object $json
     * @return bool|string
     */
    protected function callback($json) {
        if($json->response_code == 'R001') {
            $this->payment_id = $json->payment_id;

            return $json->payment_method_uri;
        }

        return false;
    }
}