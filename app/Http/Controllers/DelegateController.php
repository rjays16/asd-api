<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\ConventionMember;

use App\Enum\RoleEnum;
use App\Enum\UserStatusEnum;
use App\Enum\ConventionMemberTypeEnum;

use App\Http\Requests\Delegate\Create;

use App\Imports\Delegate\Import;

use Maatwebsite\Excel\Facades\Excel;

use Exception;
use DB;

class DelegateController extends Controller
{
    public function getDelegates(Request $request) {
        $delegates = ConventionMember::delegates()
            ->whereHas('user')
            ->join('users', 'users.id', '=', 'convention_members.user_id');

        if($request->exists('is_search') && $request->is_search) {
            $delegates = $delegates->whereHas('user', function ($query) use ($request) { 
                $query->where('first_name', 'like', "%$request->keyword%")
                    ->orWhere('middle_name', 'like', "%$request->keyword%")
                    ->orWhere('last_name', 'like', "%$request->keyword%")
                    ->orWhere('email', 'like', "%$request->keyword%");
            });
        } else if(!$request->show_all) {
            $delegates = $delegates->limit(30);
        }

        $delegates = $delegates->with(['user.user_status', 'type'])
            ->orderBy('users.last_name', 'asc')
            ->get();

        if($delegates->isNotEmpty()) {
            return response()->json($delegates);
        } else {
            return response()->json(['message' => 'No delegate accounts were found.'], 404);
        }
    }

    public function getDelegate($id) {
        $delegate = ConventionMember::delegates()
            ->whereHas('user', function ($query) use ($id) { 
                $query->where('id', $id);
            })
            ->with(['user', 'type'])
            ->first();

        if(!is_null($delegate)) {
            return response()->json($delegate);
        } else {
            return response()->json(['message' => 'Delegate account was not found.'], 404);
        }
    }

    public function update(Create $request, $id) {
        $validated = $request->validated();

        $delegate = ConventionMember::where('id', $id)
            ->delegates()
            ->with(['user', 'type'])
            ->first();

        if(is_null($delegate)) {
            return response()->json(['message' => 'Delegate not found.'], 404);
        }

        DB::beginTransaction();
        try {
            if(Auth::user()->role == RoleEnum::ADMIN) {
                if($request->exists("is_good_standing")) {
                    $validated["is_good_standing"] = $request["is_good_standing"];
                }
            }

            $delegate->fill($validated);
            $delegate->save();

            $delegate->user->fill($validated);
            $delegate->user->save();

            DB::commit();
            return response()->json([
                'message' => 'Successfully updated delegate.'
            ]);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        $delegate = ConventionMember::delegates()
            ->whereHas('user', function ($query) use ($id) { 
                $query->where('id', $id);
            })
            ->with(['user', 'orders', 'orders.transaction'])
            ->first();

        if(!is_null($delegate)) {
            DB::beginTransaction();
            try {
                if(!empty($delegate->orders)) {
                    $orders = $delegate->orders;
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

                $delegate->user->email = $delegate->user->email.'_deleted_'.time();
                $delegate->user->save();

                $delegate->delete();
                $delegate->user->delete();
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

    public function import(Request $request) {
        if($request->hasFile('file')) {
            try {
                $import = new Import($request->delegate_type);
                Excel::import($import, $request->file('file'));

                $num_imported = $import->getNumImported();

                $message = "No new delegates were created";
                if($num_imported > 0) {
                    $message = "Successfully imported $num_imported delegate/s";
                }

                return response()->json([
                    'message' => $message
                ]);
            } catch(Exception $e) {
                throw $e;
            }
        } else {
            return response()->json([
                'message' => 'No file selected'
            ], 400);
        }
    }
}