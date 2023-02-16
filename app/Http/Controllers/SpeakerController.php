<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\ConventionMember;

use App\Enum\RoleEnum;
use App\Enum\UserStatusEnum;
use App\Enum\ConventionMemberTypeEnum;

use App\Http\Requests\Speaker\Create;

use Exception;
use DB;

class SpeakerController extends Controller
{
    public function getSpeakers(Request $request) {
        $speakers = ConventionMember::speaker()
            ->whereHas('user')
            ->join('users', 'users.id', '=', 'convention_members.user_id');

        if($request->exists('is_search') && $request->is_search) {
            $speakers = $speakers->whereHas('user', function ($query) use ($request) { 
                $query->where('first_name', 'like', "%$request->keyword%")
                    ->orWhere('middle_name', 'like', "%$request->keyword%")
                    ->orWhere('last_name', 'like', "%$request->keyword%")
                    ->orWhere('email', 'like', "%$request->keyword%");
            });
        } else if(!$request->show_all) {
            $speakers = $speakers->limit(30);
        }

        $speakers = $speakers->with(['user', 'type'])
            ->orderBy('users.last_name', 'asc')
            ->get();

        if($speakers->isNotEmpty()) {
            return response()->json($speakers);
        } else {
            return response()->json(['message' => 'No speakers were found.'], 404);
        }
    }

    public function getSpeaker($id) {
        $speaker = ConventionMember::speaker()
            ->whereHas('user', function ($query) use ($id) { 
                $query->where('id', $id);
            })
            ->with(['user', 'type'])
            ->first();

        if(!is_null($speaker)) {
            return response()->json($speaker);
        } else {
            return response()->json(['message' => 'Speaker not found.'], 404);
        }
    }

    public function update(Create $request, $id) {
        $validated = $request->validated();

        $speaker = ConventionMember::where('id', $id)
            ->speaker()
            ->with(['user', 'type'])
            ->first();

        if(is_null($speaker)) {
            return response()->json(['message' => 'Speaker not found.'], 404);
        }

        DB::beginTransaction();
        try {
            if(Auth::user()->role == RoleEnum::ADMIN) {
                if($request->exists("is_good_standing")) {
                    $validated["is_good_standing"] = $request["is_good_standing"];
                }
            }

            $speaker->fill($validated);
            $speaker->save();

            $speaker->user->fill($validated);
            $speaker->user->save();

            DB::commit();
            return response()->json([
                'message' => 'Successfully updated speaker.'
            ]);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        $speaker = ConventionMember::speaker()
            ->whereHas('user', function ($query) use ($id) { 
                $query->where('id', $id);
            })
            ->with(['user', 'orders', 'orders.transaction'])
            ->first();

        if(!is_null($speaker)) {
            DB::beginTransaction();
            try {
                if(!empty($speaker->orders)) {
                    $orders = $speaker->orders;
                    foreach($orders as $order) {
                        $order->transaction->delete();
                        $order->transaction->ideapay->delete();

                        if(!empty($order->payment)) {
                            $payments = $order->payment;
                            foreach($payments as $payment) {
                                $payment->delete();
                            }
                        }
                        
                        $order->delete();
                    }
                }

                $speaker->user->email = $speaker->user->email.'_deleted_'.time();
                $speaker->user->save();

                $speaker->delete();
                $speaker->user->delete();
                DB::commit();
                return response()->json(['status' => 'success', 'message' => 'Successfully deleted account.']);
            } catch(Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } else {
            return response()->json(['message' => 'Account not found.'], 404);
        }
    }
}