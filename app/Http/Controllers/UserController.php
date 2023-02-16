<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\RegistrationType;

use App\Enum\RoleEnum;
use App\Enum\UserStatusEnum;

use App\Http\Requests\Auth\UpdatePassword;

use Exception;
use DB;

class UserController extends Controller
{
    public function getUser() {
        $user = User::where('id', Auth::user()->id)
            ->with(['member'])
            ->first();

        if(!is_null($user)) {
            return response()->json($user);
        } else {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
    }

    public function getSponsorUser() {
        $user = User::where('id', Auth::user()->id)
            ->where('role', RoleEnum::SPONSOR)
            ->with('sponsor')
            ->first();

        if(!is_null($user)) {
            return response()->json($user);
        } else {
            return response()->json([
                'message' => 'Sponsor user not found'
            ], 404);
        }
    }

    public function updatePassword(UpdatePassword $request) {
        $validated = $request->validated();
        $user_password = User::where('id', Auth::user()->id)->first()->password;
        $current_password = $validated["current_password"];
        $new_password = $validated["password"];
        $confirm_password = $validated["confirm_password"];

        if($new_password === $confirm_password) {
            if(Hash::check($current_password, $user_password)) {
                $user = Auth::user();
                $user->password = Hash::make($confirm_password);
                $user->save();
                return response()->json([
                    'message' => 'Successfully updated password'
                ]);
            } else {
                return response()->json([
                    "message" => "Invalid current password"
                ], 400);
            }
        } else {
            return response()->json([
                "message" => "Incorrect password for confirmation"
            ], 400);
        }
    }

    public function updateField(Request $request) {
        $user = Auth::user();

        DB::beginTransaction();
        try {
            $user->update([$request->field => $request->value]);

            DB::commit();
            return response()->json([
                'message' => 'Successfully updated field',
            ]);
        } catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
}
