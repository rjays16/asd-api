<?php

namespace App\Services;

use App\Models\Order;

use App\Enum\IdeapayStatusEnum;
use App\Enum\RegistrationTypeEnum;

use Exception;

class IdeapayService
{
	private $verified;

	public function __construct($_verified = false) {
		$this->verified = $_verified;
	}

	public static function create(Order $order) {
		require_once(__DIR__ . '/ideapay-sdk/load.php');
		if($order->member->scope){
			$client_credentials = array(
				'client_id'     =>  config('ideapay.client_id_intl'),
				'client_secret' =>  config('ideapay.client_secret_intl')
			);
		}else{
			$client_credentials = array(
				'client_id'     =>  config('ideapay.client_id'),
				'client_secret' =>  config('ideapay.client_secret')
			);
		}
		$order->load(['member']);

		$currency = 'PHP';

		$client = new \Pay($client_credentials);
		$client->set_origin(config('app.url') . '/web/payment');

		if(config('ideapay.live')) {
			$client->live();
		} else {
			$client->sandbox();
		}

		$client->set_customer_info(array(
			'first_name'    =>  $order->member->user->first_name,
			'last_name'     =>  $order->member->user->last_name,
			'email_address' =>  $order->member->user->email,
			// 'phone_number'  =>  $data['phone_number'] ?? '',
			// 'mobile_number' =>  $data['mobile_number'] ?? '',
			// 'date_of_birth' =>  ''
		));
		$billing_info = array(
			// 'address_line1' =>  $data['billing']['address_line1'] ?? '',
			// 'address_line2' =>  $data['billing']['address_line2'] ?? '',
			// 'city'          =>  $data['billing']['city'] ?? '',
			// 'state'         =>  $data['billing']['state'] ?? '',
			// 'country'       =>  $data['billing']['country'] ?? '',
			// 'postal_code'   =>  $data['billing']['postal_code'] ?? '',
			'currency'      =>  $currency,
			'amount' => $order->amount
		);

		$client->set_billing_info($billing_info);

		$url = $client->send();
		$payment_ref = $client->get_payment_id();

		return [
			'url' => $url,
			'payment_ref' => $payment_ref
		];
	}

	public function getStatus($data)
	{
		// if(!$this->verified) {
		// 	self::verifySignature($data);
		// 	$this->verified = true;
		// }

		$status = null;
		if(array_key_exists('response_code', $data)) {
			$status = $this->getResponseCodeStatus($data['response_code']);
			if($status == 'cancelled') {
				if(!array_key_exists('cancel', $data)) {
					$status = null;
				}
			}
		}
		return $status;
	}

	private function getResponseCodeStatus($code)
	{
		$status = null;
		switch($code) {
			case 'P001':
				$status = IdeapayStatusEnum::SUCCESS;
				break;
			case 'P002':
				$status = IdeapayStatusEnum::FAILED;
				break;
			case 'P003':
				$status = IdeapayStatusEnum::PENDING;
		}
		return $status;
	}

	public function isSuccess($data)
	{
		// if(!$this->verified) {
		// 	self::verifySignature($data);
		// 	$this->verified = true;
		// }
		if(array_key_exists('response_code', $data)) {
			if($data['response_code'] == 'P001') {
				return true;
			}
		}

		return false;
	}

	public function isCancelled($data)
	{
		if(array_key_exists('response_code', $data) && array_key_exists('cancel', $data)) {
			if($data['response_code'] == 'P002') {
				return true;
			}
		}

		return false;
	}

	public static function verifySignature($data)
	{
		$sign_keys = ['response_code', 'response_message', 'payment_id'];
		$data_sign = array_intersect_key($data, array_flip(array_merge($sign_keys, ['signature'])));
		if(sizeof($data_sign) == 4) {
			$concat_sign = '';
			foreach($sign_keys as $value) {
				$concat_sign .= $data_sign[$value];
			}
			$concat_sign .= config('ideapay.client_secret');
			$hash_sign = hash('sha512', $concat_sign);
			if($hash_sign != $data_sign['signature']) {
				throw new Exception('Invalid signature');
			}
		}
		else {
			throw new Exception('Missing required parameter');
		}
	}
}