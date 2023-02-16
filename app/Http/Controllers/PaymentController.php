<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Models\ConventionMember;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;

use App\Enum\OrderStatusEnum;
use App\Enum\PaymentMethodEnum;

use App\Http\Requests\Mail\ResendPayment;
use App\Http\Requests\Payment\CreateFree;

use App\Mail\Invoice;

use App\Exports\PaymentExport;
use Maatwebsite\Excel\Facades\Excel;

use Carbon\Carbon;

use Exception;
use DB;

class PaymentController extends Controller
{
    public function getPaymentLedger(Request $request) {   
        $payments = Payment::join('payment_methods', 'payment_method', '=', 'payment_methods.id')
            ->join('convention_members', 'convention_member_id', '=', 'convention_members.id')
            ->join('users', 'user_id', '=', 'users.id');

        if($request->exists('is_search') && $request->is_search) {
            $payments = $payments->whereHas('member', function ($query) use ($request) {
                $query->whereHas('user', function ($query) use ($request) { 
                    $query = $query->where('first_name', 'like', "%$request->keyword%")
                        ->orWhere('middle_name', 'like', "%$request->keyword%")
                        ->orWhere('last_name', 'like', "%$request->keyword%")
                        ->orWhere('prefix', 'like', "%$request->keyword%")
                        ->orWhere('suffix', 'like', "%$request->keyword%")
                        ->orWhere('prof_suffix', 'like', "%$request->keyword%")
                        ->orWhere('email', 'like', "%$request->keyword%")
                        ->orWhere('phone', 'like', "%$request->keyword%");
                });
            });
        } else if(!$request->show_all) {
            $payments = $payments->limit(30);
        }

        $payments = $payments->with('order.status_class', 'member.user', 'method')
            ->orderBy('payment_methods.id', 'desc')
            ->orderBy('payments.created_at', 'desc')
            ->get();

        if($payments->isNotEmpty()) {
            return response()->json($payments);
        } else {
            return response()->json([
                'message' => 'No payments found'
            ], 404);
        }
    }

    public function getPaymentHistory($member_id) {
        $payments = Payment::where('convention_member_id', $member_id)
            ->with('order', 'member.user')
            ->get();
        
        if($payments->isNotEmpty()) {
            return response()->json($payments);
        } else {
            return response()->json([
                'message' => 'No payments found'
            ], 404);
        }
    }

    public function createFree(CreateFree $request) {
        $validated = $request->validated();

        $order = Order::where('id', $validated["order_id"])->first();

        if(!is_null($order)) {
            $member = $order->member;

            DB::beginTransaction();
            try {
                $transaction = new Transaction();
                $transaction->amount = $order->amount;
                $transaction->order_id = $order->id;
                $transaction->save();

                $payment = new Payment();
                $payment->convention_member_id = $order->member->id;
                $payment->order_id = $order->id;
                $payment->payment_method = PaymentMethodEnum::FREE;
                $payment->amount = $order->amount;
                $payment->date_paid = Carbon::now();
                $payment->save();

                Mail::to($order->member->user->email)->send(new Invoice($order->member->user, $payment));

                DB::commit();
                return response()->json([
                    'message' => 'Successfully created payment for free registration.'
                ]);
            } catch(Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } else {
            return response()->json([
                'message' => 'This order does not exist.'
            ], 404);
        }
    }

    public function delete($id) {
        $payment = Payment::where('id', $id)
            ->with('order')
            ->first();
            
        if(!is_null($payment)) {
            DB::beginTransaction();
            try {                
                $payment->delete();
                $order = $payment->order;
                if($order->RawOrderPaymentsValue >= ($order->amount - $order->transaction->ideapay_fee)) {
                    $order->status = OrderStatusEnum::COMPLETED;
                }
                $order->save();
                
                DB::commit();
                return response()->json([
                    'message' => 'Successfully deleted payment'
                ]);
            } catch(Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } else {
            return response()->json([
                'message' => 'Payment not found'
            ], 404);
        }
    }

    public function resendPaymentEmail(ResendPayment $request) {
        $validated = $request->validated();

        $member = ConventionMember::where('id', $validated["member_id"])->first();
        if(!is_null($member)) {

            $payment = Payment::where('id', $validated["payment_id"])->first();
            if(!is_null($payment)) {
                if($payment->payment_method == PaymentMethodEnum::IDEAPAY) {
                    try {
                        Mail::to($member->user->email)->send(new Invoice($member->user, $payment));
                        return response()->json([
                            'message' => 'Successfully resent payment email'
                        ]);
                    } catch(Exception $e) {
                        return response()->json([
                            'message' => 'Could not resend payment email',
                            'error' => $e
                        ], 400);
                    }
                } else {
                    return response()->json([
                        'message' => 'Invalid payment method. Please contact the site admin'
                    ], 400);
                }
            } else {
                return response()->json([
                    'message' => 'Payment not found'
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'Member not found'
            ], 404);
        }
    }
}