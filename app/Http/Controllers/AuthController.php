<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\RegistrationType;

use App\Enum\RoleEnum;
use App\Enum\UserStatusEnum;

use App\Http\Requests\Auth\LoginRequest;

use Exception;
use DB;

class AuthController extends Controller
{
    public function loginAdmin(LoginRequest $request) {
        $validated = $request->validated();

        $user = User::where('email', $validated["email"])->admin()->first();
        if(is_null($user)) {
            return response()->json([
                'message' => 'Admin user not found.'
            ], 404);
        }

        return self::checkUser($validated, $user);
    }

    public function loginMember(LoginRequest $request) {
        $validated = $request->validated();

        $user = User::where('email', $validated["email"])->member()->first();
        if(is_null($user)) {
            return response()->json([
                'message' => 'Member account not found'
            ], 404);
        }

        if($user->status != UserStatusEnum::REGISTERED){
            return response()->json([
                'message' => 'Member is not yet registered and paid.'
            ], 404);
        }

        return self::checkUser($validated, $user);
    }

    private static function checkUser($validated, $user) {
        try {
            if(Hash::check($validated["password"], $user->password)) {
                $token = $user->createToken('API Token')->accessToken;
                $user->active_token = $token;
                $user->save();

                return response()->json([
                    'token' => $token
                ]);
            } else {
                return response()->json([
                    'message' => 'Invalid credentials.'
                ], 401);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function logout(Request $request) {
        $token = $request->user();
        $token->token()->revoke();

        $user = User::where('id', Auth::user()->id)->first();
        $user->active_token = null;
        $user->save();

        return response()->json([
            'message' => 'Successfully logged out.'
        ]);
    }
}
