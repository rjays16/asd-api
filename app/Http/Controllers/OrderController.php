<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Order;
use App\Models\ConventionMember;
use App\Models\ForExRate;

use App\Enum\OrderStatusEnum;
use App\Enum\IdeapayStatusEnum;
use App\Enum\PaymentMethodEnum;
use App\Enum\RegistrationTypeEnum;

use App\Http\Requests\Order\Update;

use App\Services\IdeapayService;
use App\Services\FeeService;

use Exception;
use DB;

use Carbon\Carbon;

class OrderController extends Controller
{
    public function getOrder($id) {
        $order = Order::where('id', $id)->first();

        if(!is_null($order)) {
            return response()->json($order);
        } else {
            return response()->json(['message' => 'Order not found'], 404);
        }
    }

    public function getUserOrders(Request $request) {
        $orders = Order::where('convention_member_id', $request->member_id)
            ->with(['transaction', 'payment'])
            ->get();
        
        if($orders->isNotEmpty()) {
            return response()->json($orders);
        } else {
            return response()->json(['message' => 'This member has no order fees'], 404);
        }
    }

    public function update(Update $request) {
        $validated = $request->validated();
        $order = Order::where('id', $validated['order_id'])->first();
        $member = ConventionMember::where('id', $validated['convention_member_id'])->first();

        DB::beginTransaction();
        try {
            if(!is_null($member)) {
                if(!is_null($order)) {
                    if($order->RawOrderPaymentsValue >= ($order->amount - $order->transaction->ideapay_fee)) {
                        $order->status = OrderStatusEnum::COMPLETED;
                    }
                    
                    $order->save();

                    DB::commit();
                    return response()->json([
                        'message' => 'Order updated',
                        'order_status' => $order->status
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Order not found'
                    ], 404);
                }
            } else {
                return response()->json([
                    'message' => 'Member not found'
                ], 404);
            }
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}