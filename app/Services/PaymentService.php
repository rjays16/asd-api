<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

use App\Models\Payment;
use App\Models\Transaction;

use App\Enum\PaymentMethodEnum;
use App\Enum\UserStatusEnum;

use App\Mail\Invoice;
use App\Events\Payment\Redirect;

use Carbon\Carbon;

use Exception;
use DB;

class PaymentService {
    private $ideapay_payment;

    public function __construct($ideapay_payment)
    {
        $this->ideapay_payment = $ideapay_payment;
        $this->create();
    }

    public function create() {
        DB::beginTransaction();
        try {
            $ideapay_payment = $this->ideapay_payment;
            $transaction = $ideapay_payment->transaction;

            $transaction = Transaction::with('order')->where('ideapay_id', $ideapay_payment->id)->first();
            
            if(is_null($transaction)) {
                throw new Exception("Unable to process payment, please report to the site admin. Ideapay: $ideapay_payment->id");
            }

            $order = $transaction->order;
            $convention_member = $order->member;

            $user = $convention_member->user;
            $user->status = UserStatusEnum::REGISTERED;
            $user->save();

            $payment = new Payment();
            $payment->convention_member_id = $convention_member->id;
            $payment->payment_method = PaymentMethodEnum::IDEAPAY;
            $payment->order_id = $ideapay_payment->transaction->order->id;
            $payment->amount = $ideapay_payment->transaction->order->amount;
            $payment->date_paid = Carbon::now();
            $payment->save();
            DB::commit();

            event(new Redirect($order));
            Mail::to($convention_member->user->email)->send(new Invoice($convention_member->user, $payment));
        } catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
}