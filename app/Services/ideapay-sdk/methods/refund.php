<?php

/**
 * Use this to request payment refunds
 */
class Refund extends \Pay\Classes\Client implements \Pay\Interfaces\RefundInterface {
    /**
     * @var string $ed
     *
     */
    protected $endpoint = '/refund';

    /**
     * @var string
     */
    private $reason = '';

    /**
     * @var string
     */
    private $amount = '0.00';

    /**
     * Refund constructor.
     *
     * @param array $args
     */
    public function __construct($args = array()) {
        parent::__construct(array(
            'client_id'     =>  $args['client_id'],
            'client_secret' =>  $args['client_secret']
        ));

        if(isset($args['payment_id'])) {
            $this->set_payment_id($args['payment_id']);
        }

        return $this;
    }

    /**
     * @param string $reason
     */
    public function set_reason($reason = '') {
        $this->reason = $reason;
    }

    /**
     * @param string $amount
     */
    public function set_amount($amount = '0.00') {
        $this->amount = $amount;
    }

    /**
     * Send refund request
     *
     * @return string
     * @throws Exception
     */
    public function send() {
        if($this->payment_id == null) {
            throw new Exception('Payment ID missing');
        }

        if(floatval($this->amount) == 0) {
            throw new Exception('Refund amount invalid');
        }

        $this->payload = array(
            'payment_id'    =>  $this->payment_id,
            'reason'        =>  $this->reason,
            'amount'        =>  $this->amount
        );

        return parent::send();
    }

    /**
     * Request response callback
     *
     * @param $json
     * @return bool
     * @throws Exception
     */
    protected function callback($json) {
        switch($json->response_code) {
            case 'PR001':
                return true;
            case 'PR006':
            case 'PR007':
                throw new \Exception($json->response_message);
        }

        return false;
    }
}