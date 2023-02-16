<?php

namespace App\Services;

use App\Models\Config;
use App\Models\ConventionMember;
use App\Models\Order;
use App\Models\Ideapay;
use App\Models\Transaction;
use App\Models\ConventionMemberType;
use App\Models\ForExRate;

use App\Enum\OrderStatusEnum;
use App\Enum\IdeapayStatusEnum;

use App\Services\IdeapayService;

use Exception;
use DB;

use Carbon\Carbon;

class OrderService {
    private $fee;
    private $member;

    public function __construct($fee) {
        $this->fee = $fee;
    }

    public function calculate($registration_data, $fee) {
        try {
            $ideapay_fee = 0;
            $ideapay_rate = Config::getIdeapayRate();
            $php_rate_for_usd = ForExRate::getActivePHPRate();
            $current_date = Carbon::now()->format('Y-m-d');

            $is_earlybird = true;
            $amount = $fee->amount;
            $intl_amount = $fee->intl_amount;
            
            if($fee->uses_late_amount && $current_date >= $fee->late_amount_starts_on) {
                $amount = $fee->late_amount;
                $intl_amount = $fee->late_intl_amount;
                $is_earlybird = false;
            }

            $is_international = $fee->scope; # Check if it's an international fee
            $local_amount = $amount * $php_rate_for_usd;

            $type = ConventionMemberType::where('id', $registration_data["member_type"])->first();

            $currency = $is_international ? "USD" : "PHP";
            $registration_fee = $is_international ? $fee->amount : $local_amount;
            $ideapay_fee_converted = $registration_fee * $ideapay_rate;
            $total_amount = $registration_fee + $ideapay_fee_converted;
            $late_amount = $fee->late_amount;

            $order_data = array(
                'amount' => $local_amount." ".$fee->amount." ".$is_international,
                'first_name' => $registration_data["first_name"],
                'middle_name' => $registration_data["middle_name"],
                'last_name' => $registration_data["last_name"],
                'suffix' => $registration_data["suffix"],
                'prof_suffix' => $registration_data["prof_suffix"],
                'certificate_name' => $registration_data["certificate_name"],
                'email' => $registration_data["email"],
                'country' => $registration_data["country"],
                'institution_organization' => $registration_data["institution_organization"],
                'delegate_type' => $type->name,
                'prc_number' => $registration_data["prc_number"],
                'pds_number' => $registration_data["pds_number"],
                'registration_fee' => $currency." ".number_format($registration_fee, 2),
                'convenience_fee' => number_format($ideapay_fee_converted, 2),
                'total_amount' => number_format($total_amount, 2),
                'is_earlybird' => $is_earlybird,
                'usd-php' => $php_rate_for_usd,
                'ideapay_rate' => $ideapay_rate,
                'order_amount' => number_format($total_amount * $php_rate_for_usd, 2),
            );

            return array(
                'message' => 'Successfully calculated order data',
                'order' => $order_data,
                'code' => 200,
            );
        } catch(Exception $e) {
            return array(
                'message' => "An error in the rate calculation has occurred: $e",
                'code' => 400,
            );
        }
    }

    public function addToMember($member, $fee) {
        DB::beginTransaction();
        try {
            $ideapay_fee = 0;
            $ideapay_rate = Config::getIdeapayRate();
            $php_rate_for_usd = ForExRate::getActivePHPRate();
            $current_date = Carbon::now()->format('Y-m-d');
            $local_amount = 0;

            $registration_fee_amount = $fee->amount;
            $base_fee_amount = $fee->amount;
            $is_registration_fee_international = $fee->scope;
            $is_earlybird = true;
            if($fee->uses_late_amount && $current_date >= $fee->late_amount_starts_on) {
                $registration_fee_amount = $fee->late_amount;
                $base_fee_amount = $fee->late_amount;
                $is_earlybird = false;
            }

            if($is_registration_fee_international) {
                $registration_fee_amount = $registration_fee_amount * $php_rate_for_usd;
            }

            $local_amount = $registration_fee_amount * $php_rate_for_usd;

            $member_orders = Order::where('convention_member_id', $member->id)
                ->where('fee_id', $fee->id)
                ->get();
            
            if($member_orders->isEmpty()) {
                $currency = $is_registration_fee_international ? "USD" : "PHP";
                $user = $member->user;
                // $registration_fee = $is_registration_fee_international ? '1' : '53.09'; 
                $registration_fee = $is_registration_fee_international ? $fee->amount: $local_amount;
                $convenience_fee = $registration_fee * $ideapay_rate;
                $total_amount = $convenience_fee + $registration_fee;

                $absolute_local_amount = $base_fee_amount * $php_rate_for_usd;
                $absolute_order_amount = $absolute_local_amount + ($absolute_local_amount * $ideapay_rate);

                $order = new Order();
                $order->convention_member_id = $member->id;
                // $order->amount = $local_amount; 
                $order->amount = $absolute_order_amount; 
                $order->intl_amount = $fee->amount;
                $order->convenience_fee = $convenience_fee;
                $order->status = OrderStatusEnum::PENDING;
                $order->fee_id = $fee->id;
                $order->save();

                $payment = IdeapayService::create($order);

                $ideapay = new Ideapay();
                $ideapay->status = IdeapayStatusEnum::PENDING;
                $ideapay->payment_ref = $payment['payment_ref'];
                $ideapay->payment_url = $payment['url'];
                // $ideapay->payment_ref = 'test'; # For testing on local in case of SSL error
                // $ideapay->payment_url = 'https://google.com'; # For testing on local in case of SSL error
                $ideapay->save();

                $transaction = new Transaction();
                $transaction->amount = $order->amount;
                $transaction->order_id = $order->id;
                $transaction->ideapay_id = $ideapay->id;
                $transaction->ideapay_fee = $ideapay_fee;
                $transaction->save();

                $ideapay->transaction_id = $transaction->id;
                $ideapay->save();

                DB::commit();

                $order_data = array(
                    'order_id' => $order->id,

                    'first_name' => $user->first_name,
                    'middle_name' => $user->middle_name,
                    'last_name' => $user->last_name,
                    'suffix' => $user->suffix,
                    'prof_suffix' => $user->prof_suffix,
                    'certificate_name' => $user->certificate_name,
                    'email' => $user->email,
                    'country' => $user->country,
                    'institution_organization' => $member->institution_organization,
                    'delegate_type' => $member->type->name,
                    'prc_number' => $member->prc_number,
                    'pds_number' => $member->pds_number,

                    'registration_fee' => $currency." ".number_format($registration_fee, 2),
                    'convenience_fee' => $currency." ".number_format($convenience_fee, 2),
                    'total_amount' => $currency." ".number_format($total_amount, 2),
                    'is_earlybird' => $is_earlybird,
                    'usd-php' => $php_rate_for_usd,
                    'ideapay_rate' => $ideapay_rate,
                    'order_amount' => $currency." ".number_format($total_amount, 2),
                    'order_rates' => [
                        'amount' => number_format($order->amount, 2),
                        'intl_amount' => number_format($order->intl_amount, 2)
                    ],
                    'registration_fee_amount' => $registration_fee_amount,
                    'convenience_fee_rates' => [
                        'amount' => number_format($convenience_fee, 2),
                        'intl_amount' => number_format($convenience_fee * $php_rate_for_usd, 2)
                    ],
                );

                return array(
                    'message' => 'Successfully added order',
                    'order' => $order_data,
                    'code' => 200,
                );
            } else {
                return array(
                    'message' => 'Unable to add order.',
                    'member_orders' => $member_orders,
                    'error' => 'This member already has this registration fee.',
                    'code' => 400,
                );
            }
        } catch(Exception $e) {
            DB::rollBack();
            // throw $e;
            return array(
                'message' => 'Unable to add order.',
                'error' => $e,
                'code' => 400,
            );
        }
    }
}