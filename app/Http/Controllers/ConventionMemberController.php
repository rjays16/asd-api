<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

use App\Models\User;
use App\Models\ConventionMember;
// use App\Models\Order;
// use App\Models\Payment;
// use App\Models\Fee;

use App\Enum\RoleEnum;
use App\Enum\UserStatusEnum;
// use App\Enum\PaymentMethodEnum;
// use App\Enum\RegistrationTypeEnum;
// use App\Enum\FeeEnum;

// use App\Services\OrderService;

use App\Http\Requests\ConventionMember\Create;

use Carbon\Carbon;

use Exception;
use DB;

class ConventionMemberController extends Controller
{
    public function getConventionMembers(Request $request) {
        $members = ConventionMember::whereHas('user')
            ->join('users', 'users.id', '=', 'convention_members.user_id');

        if($request->exists('is_search') && $request->is_search) {
            $members = $members->whereHas('user', function ($query) use ($request) { 
                $query->where('first_name', 'like', "%$request->keyword%")
                    ->orWhere('middle_name', 'like', "%$request->keyword%")
                    ->orWhere('last_name', 'like', "%$request->keyword%")
                    ->orWhere('prefix', 'like', "%$request->keyword%")
                    ->orWhere('suffix', 'like', "%$request->keyword%")
                    ->orWhere('prof_suffix', 'like', "%$request->keyword%")
                    ->orWhere('email', 'like', "%$request->keyword%")
                    ->orWhere('phone', 'like', "%$request->keyword%");
            });
        } else if(!$request->show_all) {
            $members = $members->limit(30);
        }

        $members = $members->with(['user', 'type'])
            ->orderBy('users.last_name', 'asc')
            ->get();

        if($members->isNotEmpty()) {
            return response()->json($members);
        } else {
            return response()->json(['message' => 'No members were found'], 404);
        }
    }

    public function getPending() {
        $members = ConventionMember::whereHas('user', function ($query) { 
            $query->where('status', UserStatusEnum::PENDING);
        })->with(['user', 'type'])
        ->orderBy('updated_at', 'desc')
        ->get();

        if($members->isNotEmpty()) {
            return response()->json($members);
        } else {
            return response()->json(['message' => 'No pending members were found'], 404);
        }
    }

    public function getActive(Request $request) {
        $members = ConventionMember::whereHas('user', function ($query) { 
            $query->where('status', UserStatusEnum::APPROVED);
        });

        if($request->exists('is_search') && $request->is_search) {
            $members = $members->whereHas('user', function ($query) use ($request) { 
                $query->where('first_name', 'like', "%$request->keyword%")
                    ->orWhere('middle_name', 'like', "%$request->keyword%")
                    ->orWhere('last_name', 'like', "%$request->keyword%")
                    ->orWhere('prefix', 'like', "%$request->keyword%")
                    ->orWhere('suffix', 'like', "%$request->keyword%")
                    ->orWhere('prof_suffix', 'like', "%$request->keyword%")
                    ->orWhere('email', 'like', "%$request->keyword%")
                    ->orWhere('phone', 'like', "%$request->keyword%");
            });
        } else if(!$request->show_all) {
            $members = $members->limit(30);
        }

        $members = $members->with(['user', 'type'])
            ->orderBy('updated_at', 'desc')
            ->get();

        if($members->isNotEmpty()) {
            return response()->json($members);
        } else {
            return response()->json(['message' => 'No active members were found'], 404);
        }
    }

    public function getPaid(Request $request) {
        $paid_member_ids = User::whereHas('member.payments');

        if($request->exists('is_search') && $request->is_search) {
            $members = $members->where('first_name', 'like', "%$request->keyword%")
                ->orWhere('middle_name', 'like', "%$request->keyword%")
                ->orWhere('last_name', 'like', "%$request->keyword%")
                ->orWhere('prefix', 'like', "%$request->keyword%")
                ->orWhere('suffix', 'like', "%$request->keyword%")
                ->orWhere('prof_suffix', 'like', "%$request->keyword%")
                ->orWhere('email', 'like', "%$request->keyword%")
                ->orWhere('phone', 'like', "%$request->keyword%");
        } else if(!$request->show_all) {
            $members = $members->limit(30);
        }

        $members = $members->with(['member.type', 'member.payments'])
            ->where('role', RoleEnum::CONVENTION_MEMBER)
            ->orderBy('last_name', 'asc')
            ->get();

        if($members->isNotEmpty()) {
            return response()->json($members);
        } else {
            return response()->json(['message' => 'No paid members were found'], 404);
        }
    }

    public function getConventionMember($id) {
        $member = ConventionMember::where('id', $id)->with(['user'])->first();

        if(!is_null($member)) {
            return response()->json($member);
        } else {
            return response()->json([
                'message' => 'Member not found',
                // 'id' => $id
            ], 404);
        }
    }

    public function update(Create $request, $id) {
        $validated = $request->validated();

        $member = ConventionMember::where('id', $id)->with(['user'])->first();
        if(is_null($member)) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        DB::beginTransaction();
        try {
            if(Auth::user()->role == RoleEnum::ADMIN) {
                if($request->exists("is_good_standing")) {
                    $validated["is_good_standing"] = $request["is_good_standing"];
                }
            }

            $member->fill($validated);
            $member->save();

            $member->user->fill($validated);
            $member->user->save();

            DB::commit();
            return response()->json([
                'message' => 'Successfully updated convention member'
            ]);
        } catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        $member = ConventionMember::where('id', $id)
            ->with(['user', 'orders', 'orders.transaction'])
            ->first();

        if(!is_null($member)) {
            DB::beginTransaction();
            try {
                if(!empty($member->orders)) {
                    $orders = $member->orders;
                    foreach($orders as $order) {
                        $order->transaction->delete();
                        $order->transaction->ideapay->delete();

                        if(!is_null($order->payment)) {
                            $payment->delete();
                        }
                        
                        $order->delete();
                    }
                }

                $member->user->email = $member->user->email.'_deleted_'.time();
                $member->user->save();

                $member->delete();
                $member->user->delete();
                DB::commit();
                return response()->json(['status' => 'success', 'message' => 'Member deleted.']);
            } catch(Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } else {
            return response()->json(['status' => 'fail', 'message' => 'Member not found.'], 404);
        }
    }
}