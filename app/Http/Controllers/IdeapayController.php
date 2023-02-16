<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\Ideapay;
use App\Models\Config;

use App\Enum\OrderStatusEnum;
use App\Enum\IdeapayStatusEnum;

use App\Services\IdeapayService;
use App\Services\PaymentService;

use Carbon\Carbon;

use Exception;
use DB;

class IdeapayController extends Controller
{
    public function success() {
        return response('successful payment');
    }

    public function verifyOrderStatus(Request $request){
        $data = [
            'response_code' => $request->response_code,
            'response_message' => $request->response_message,
            'payment_id' => $request->payment_id,
    		'signature' => hash('sha512', config('ideapay.client_secret'))
        ];

        $payment = Ideapay::where('payment_ref', $data['payment_id'])
            ->first();

        if(is_null($payment)) {
            return response('Payment not found');
        }

        if($payment->status == IdeapayStatusEnum::PENDING) {
            try {
                $status = (new IdeapayService())->getStatus($data);
                $payment->status = $status;
                $payment->save();

                if($payment->transaction) {
                    $order = $payment->transaction->order;
                    $order->status = $payment->status;
                    $order->save();
                }

                // Record payment on success
                if($payment->status == IdeapayStatusEnum::SUCCESS) {
                    new PaymentService($payment);
                    // return response('Payment verified');
                    return view('ideapay.success');
                } else {
                    // return response('Payment was unsuccessful');
                    return view('ideapay.error');
                }
            } catch(Exception $e) {
                throw $e;
            }
        } elseif($payment->status == IdeapayStatusEnum::SUCCESS) {
            // return response('Payment was successful');
            return view('ideapay.success');
        } elseif($payment->status == IdeapayStatusEnum::FAILED) {
            // return response('Payment was unsuccessful');
            return view('ideapay.error');
        } else {
            return response('Payment record could not be processed');
        }
    }

    public function create(Request $request) {
        $order_id = $request->order_id;
        $ideapay_fee = Config::getIdeapayFee();
        $order = Order::with('transaction')->where('id', $order_id)->first();

        if(!is_null($order)) {
            if($order->status == OrderStatusEnum::COMPLETED) {
                return response()->json([
                    'message' => 'This order has already been completed'
                ], 400);
            } else {
                $payment = IdeapayService::create($order);

                $ideapay = new Ideapay();
                $ideapay->transaction_id = $order->transaction->id;
                $ideapay->status = IdeapayStatusEnum::PENDING;
                $ideapay->payment_ref = $payment['payment_ref']; # Make sure to uncomment this before pushing to staging
                $ideapay->payment_url = $payment['url']; # Make sure to uncomment this before pushing to staging
                // $ideapay->payment_ref = 'test'; # For testing on local in case of SSL error
                // $ideapay->payment_url = 'https://google.com'; # For testing on local in case of SSL error
                $ideapay->save();

                $transaction = new Transaction();
                $transaction->amount = $order->amount;
                $transaction->ideapay_id = $ideapay->id;
                $transaction->order_id = $order_id;
                $transaction->ideapay_fee = $order->convenience_fee;
                $transaction->save();

                return response()->json([
                    'payment_url' => $ideapay->payment_url,
                    'order_id' => $transaction->order_id
                ]);
            }
        } else {
            return response()->json([
                'message' => 'This order does not exist'
            ], 404);
        }
    }
}