<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

use App\Models\User;
use App\Models\ConventionMember;
use App\Models\Fee;
use App\Models\Config;
use App\Models\Role;

use App\Enum\RoleEnum;
use App\Enum\UserStatusEnum;
use App\Enum\ConventionMemberTypeEnum;
use App\Enum\ConfigTypeEnum;

// use App\Mail\Invoice;

use App\Http\Requests\Registration\Create;
use App\Http\Requests\Registration\ValidateEmail;
use App\Http\Requests\Registration\ValidatePDSNumber;

use App\Services\OrderService;
use App\Services\RegistrationConfigService;
use App\Services\FeeService;

use Exception;
use DB;

class RegistrationController extends Controller
{
    public function register(Create $request) {
        $validated = $request->validated();
        $user = null;
        $registration_config_service = new RegistrationConfigService($validated["role"], $validated["password"], $validated["confirm_password"]);
        $registration_config = $registration_config_service->checkRegisterableStatus();

        if(!$registration_config["is_registration_allowed"]) {
            return response()->json([
                'message' => $registration_config["message"],
            ], $registration_config["code"]);
        }

        $validated["password"] = Hash::make($validated["password"]);

        try {
            $user = User::where([['role', RoleEnum::CONVENTION_MEMBER],['email', $validated["email"]]])->with(['member.type'])->first();

            $message_registerered = "This account has already been registered.";
            $message_registerered_secondary = "If this is your 2nd time trying to register, you can login to your account using the email & password you have previously set and try to process your payment again through the profile dashboard page.";

            if(!is_null($user) && $user->status == UserStatusEnum::REGISTERED) {
                return response()->json([
                    'message' => $message_registerered,
                    'message_body' => $message_registerered_secondary
                ], 400);
            }

            switch($validated["member_type"]){
                case ConventionMemberTypeEnum::ASD_MEMBER:
                break;

                case ConventionMemberTypeEnum::NON_ASD_MEMBER:
                    if(!is_null($user)) {
                        return response()->json([
                            'message' => $message_registerered,
                            'message_body' => $message_registerered_secondary
                        ], 400);
                    }
                break;

                case ConventionMemberTypeEnum::RESIDENT_FELLOW:
                    if(!is_null($user)) {
                        return response()->json([
                            'message' => $message_registerered,
                            'message_body' => $message_registerered_secondary
                        ], 400);
                    }
                    
                    if($request->hasFile('resident_certificate')) {
                        $fileExtension = $request->file('resident_certificate')->getClientOriginalName();
                        $file = pathinfo($fileExtension, PATHINFO_FILENAME);
                        $extension = $request->file('resident_certificate')->getClientOriginalExtension();
                        $fileStore = $file.'_'.time().'.'.$extension;
                        $request->file('resident_certificate')->storeAs('public/images/resident_certificates', $fileStore);
                        $validated["resident_certificate"] = config('settings.APP_URL')."/storage/images/resident_certificates/".$fileStore;
                    } else {
                        return response()->json([
                            'message' => 'Please upload your resident certificate.',
                        ], 400);
                    }
                break;

                default:
                    return response()->json([
                        'message' => 'Invalid member type.',
                        'member_type' => $validated["member_type"]
                    ], 400);
            }

            if(is_null($user)) {
                DB::beginTransaction();
                try {
                    $validated["status"] = UserStatusEnum::IMPORTED_PENDING;
                    $user = User::create($validated);

                    $validated["user_id"] = $user->id;
                    ConventionMember::create($validated);

                    DB::commit();
                } catch(Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            if($user->status == UserStatusEnum::IMPORTED_PENDING) {
                $fee = Fee::where([
                    ['member_type', $validated['member_type']],
                    ['scope', $validated['scope']],
                    ['is_pds', $validated['is_pds']]
                ])
                ->with('member_type')
                ->first();

                if(!is_null($fee)) {
                    $order_service = new OrderService(null);
                    $member_order = $order_service->addToMember($user->member, $fee);

                    DB::beginTransaction();
                    try {
                        if($member_order["code"] == 200) {
                            $validated["status"] = UserStatusEnum::REGISTERED;
                            $user->update($validated); # ONLY UPDATE THE WALK-IN DELEGATE IF THE REGISTRATION IS SUCCESSFUL
                            $user->member->update($validated);
                            DB::commit();

                            return response()->json([
                                'message' => 'Successfully registered delegate account.',
                                'data' => $member_order
                            ]);
                        } else {
                            $user->email = $user->email.'_deleted_'.time();
                            $user->first_name = $user->first_name.'_deleted_'.time();
                            $user->save();

                            if(!is_null($user->member)) {
                                $user->member->delete();
                            }
                            $user->delete();
                            
                            DB::commit();
                            
                            return response()->json([
                                'message' => $member_order["message"],
                                'error' => $member_order["error"],
                            ], 400);
                        }
                    } catch(Exception $e) {
                        DB::rollBack();
                        return array(
                            'message' => 'Unable to proceed with registration.',
                            'error' => $e,
                            'code' => 400,
                        );
                    }
                } else {
                    $user->email = $user->email.'_deleted_'.time();
                    $user->first_name = $user->first_name.'_deleted_'.time();
                    $user->save();

                    if(!is_null($user->member)) {
                        $user->member->delete();
                    }
                    $user->delete();
                    
                    DB::commit();
                    return response()->json([
                        'message' => 'Unable to proceed to registration. Please contact the site admin.',
                        'error' => 'Registration fees for this registration type (delegate) has not been set yet.',
                        'member_type' => $validated['member_type'],
                        'scope' => $validated['scope'],
                        'is_pds' => $validated['is_pds']
                    ], 404);
                }
            } else {
                return response()->json([
                    'message' => 'This account is ineligible for registration.',
                    'user_status' => $user->status
                ]);
            }
        } catch(Exception $e) {
            throw $e;
        }
    }

    public function registerASD(Create $request) {
        $validated = $request->validated();

        $registration_config_service = new RegistrationConfigService($validated["role"], $validated["password"], $validated["confirm_password"]);
        $registration_config = $registration_config_service->checkRegisterableStatus();
        if(!$registration_config["is_registration_allowed"]) {
            return response()->json([
                'message' => $registration_config["message"],
            ], $registration_config["code"]);
        }

        $validated["password"] = Hash::make($validated["password"]);
        if(!in_array($validated["member_type"], [ConventionMemberTypeEnum::ASD_MEMBER, (string) ConventionMemberTypeEnum::ASD_MEMBER])) {
            return response()->json([
                'message' => 'This member type is ineligible for ASD Member registration.',
                'member_type' => $validated["member_type"]
            ], 400);
        }

        $user = User::where([
            ['role', RoleEnum::CONVENTION_MEMBER],
            ['email', $validated["email"]
        ]])
        ->whereHas('member', function ($query) {
            $query->where('member_type', $validated["member_type"]);
        })
        ->with(['member.type'])
        ->first();

        if(is_null($user)) {
            return response()->json([
                'message' => 'This account has not been imported yet in our records. Please contact the ASD registration committee.'
            ], 404);
        }

        if($user->status == UserStatusEnum::IMPORTED_PENDING) {
            $fee = Fee::where([
                ['member_type', $validated['member_type']],
                ['scope', $validated['scope']],
                ['is_pds', $validated['is_pds']]
            ])
            ->with('member_type')
            ->first();

            if(!is_null($fee)) {
                $order_service = new OrderService(null);
                $member_order = $order_service->addToMember($user->member, $fee);

                DB::beginTransaction();
                try {
                    if($member_order["code"] == 200) {
                        $validated["status"] = UserStatusEnum::ORDERED;
                        $user->update($validated); # ONLY UPDATE THE ASD DELEGATE IF THE ORDER IS SUCCESSFUL
                        $user->member->update($validated);
                        DB::commit();

                        return response()->json([
                            'message' => 'Successfully registered the delegate account.',
                            'data' => $member_order
                        ]);
                    } else {                
                        return response()->json([
                            'message' => $member_order["message"],
                            'error' => $member_order["error"],
                        ], 400);
                    }
                } catch(Exception $e) {
                    DB::rollBack();
                    return array(
                        'message' => 'Unable to proceed with registration.',
                        'error' => $e,
                        'code' => 400,
                    );
                }
            } else {
                return response()->json([
                    'message' => 'Fees for this registration type (ASD Member) has not been set yet.',
                    'member_type' => $validated['member_type'],
                    'scope' => $validated['scope'],
                    'is_pds' => $validated['is_pds']
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'This account is ineligible for registration.',
                'user_status' => $user->status
            ]);
        }
    }

    public function registerNonASD(Create $request) {
        $validated = $request->validated();

        $registration_config_service = new RegistrationConfigService($validated["role"], $validated["password"], $validated["confirm_password"]);
        $registration_config = $registration_config_service->checkRegisterableStatus();
        if(!$registration_config["is_registration_allowed"]) {
            return response()->json([
                'message' => $registration_config["message"],
            ], $registration_config["code"]);
        }

        $validated["password"] = Hash::make($validated["password"]);
        if(!in_array($validated["member_type"], [ConventionMemberTypeEnum::ASD_MEMBER, (string) ConventionMemberTypeEnum::ASD_MEMBER])) {
            return response()->json([
                'message' => 'This member type is ineligible for ASD Member registration.',
                'member_type' => $validated["member_type"]
            ], 400);
        }

        $user = User::where([
            ['role', RoleEnum::CONVENTION_MEMBER],
            ['email', $validated["email"]
        ]])
        ->whereHas('member', function ($query) {
            $query->where('member_type', $validated["member_type"]);
        })
        ->with(['member.type'])
        ->first();

        if(is_null($user)) {
            return response()->json([
                'message' => 'This account has not been imported yet in our records. Please contact the ASD registration committee.'
            ], 404);
        }

        if($user->status == UserStatusEnum::IMPORTED_PENDING) {
            $fee = Fee::where([
                ['member_type', $validated['member_type']],
                ['scope', $validated['scope']],
                ['is_pds', $validated['is_pds']]
            ])
            ->with('member_type')
            ->first();

            if(!is_null($fee)) {
                $order_service = new OrderService(null);
                $member_order = $order_service->addToMember($user->member, $fee);

                DB::beginTransaction();
                try {
                    if($member_order["code"] == 200) {
                        $validated["status"] = UserStatusEnum::ORDERED;
                        $user->update($validated); # ONLY UPDATE THE ASD DELEGATE IF THE ORDER IS SUCCESSFUL
                        $user->member->update($validated);
                        DB::commit();

                        return response()->json([
                            'message' => 'Successfully registered the delegate account.',
                            'data' => $member_order
                        ]);
                    } else {                
                        return response()->json([
                            'message' => $member_order["message"],
                            'error' => $member_order["error"],
                        ], 400);
                    }
                } catch(Exception $e) {
                    DB::rollBack();
                    return array(
                        'message' => 'Unable to proceed with registration.',
                        'error' => $e,
                        'code' => 400,
                    );
                }
            } else {
                return response()->json([
                    'message' => 'Fees for this registration type (ASD Member) has not been set yet.',
                    'member_type' => $validated['member_type'],
                    'scope' => $validated['scope'],
                    'is_pds' => $validated['is_pds']
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'This account is ineligible for registration.',
                'user_status' => $user->status
            ]);
        }
    }

    public function registerResidentFellow(Create $request) {
        $validated = $request->validated();
        $user = null;
        $registration_config_service = new RegistrationConfigService($validated["role"], $validated["password"], $validated["confirm_password"]);
        $registration_config = $registration_config_service->checkRegisterableStatus();

        if(!$registration_config["is_registration_allowed"]) {
            return response()->json([
                'message' => $registration_config["message"],
            ], $registration_config["code"]);
        }

        $validated["password"] = Hash::make($validated["password"]);

        try {
            $user = User::where([['role', RoleEnum::CONVENTION_MEMBER],['email', $validated["email"]]])->with(['member.type'])->first();

            switch($validated["member_type"]){
                case ConventionMemberTypeEnum::RESIDENT_FELLOW:
                    if(!is_null($user)) {
                        return response()->json([
                            'message' => 'This account has already been registered.',
                        ], 400);
                    }
                    
                    if($request->hasFile('resident_certificate')) {
                        $fileExtension = $request->file('resident_certificate')->getClientOriginalName();
                        $file = pathinfo($fileExtension, PATHINFO_FILENAME);
                        $extension = $request->file('resident_certificate')->getClientOriginalExtension();
                        $fileStore = $file.'_'.time().'.'.$extension;
                        $request->file('resident_certificate')->storeAs('public/images/resident_certificates', $fileStore);
                        $validated["resident_certificate"] = config('settings.APP_URL')."/storage/images/resident_certificates/".$fileStore;
                    } else {
                        return response()->json([
                            'message' => 'Please upload your resident certificate.',
                        ], 400);
                    }
                break;

                default:
                    return response()->json([
                        'message' => 'Invalid member type.',
                        'member_type' => $validated["member_type"]
                    ], 400);
            }

            if(is_null($user)) {
                DB::beginTransaction();
                try {
                    $validated["status"] = UserStatusEnum::IMPORTED_PENDING;
                    $user = User::create($validated);

                    $validated["user_id"] = $user->id;
                    ConventionMember::create($validated);

                    DB::commit();
                } catch(Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            if($user->status == UserStatusEnum::IMPORTED_PENDING) {
                $fee = Fee::where([
                    ['member_type', $validated['member_type']],
                    ['scope', $validated['scope']],
                    ['is_pds', $validated['is_pds']]
                ])
                ->with('member_type')
                ->first();

                if(!is_null($fee)) {
                    $order_service = new OrderService(null);
                    $member_order = $order_service->addToMember($user->member, $fee);

                    DB::beginTransaction();
                    try {
                        if($member_order["code"] == 200) {
                            $validated["status"] = UserStatusEnum::REGISTERED;
                            $user->update($validated); # ONLY UPDATE THE WALK-IN DELEGATE IF THE REGISTRATION IS SUCCESSFUL
                            $user->member->update($validated);
                            DB::commit();

                            return response()->json([
                                'message' => 'Successfully registered delegate account.',
                                'data' => $member_order
                            ]);
                        } else {
                            $user->email = $user->email.'_deleted_'.time();
                            $user->first_name = $user->first_name.'_deleted_'.time();
                            $user->save();

                            $user->member->delete();
                            $user->delete();
                            
                            DB::commit();
                            
                            return response()->json([
                                'message' => $member_order["message"],
                                'error' => $member_order["error"],
                            ], 400);
                        }
                    } catch(Exception $e) {
                        DB::rollBack();
                        return array(
                            'message' => 'Unable to proceed with registration.',
                            'error' => $e,
                            'code' => 400,
                        );
                    }
                } else {
                    $user->email = $user->email.'_deleted_'.time();
                    $user->first_name = $user->first_name.'_deleted_'.time();
                    $user->save();

                    $user->member->delete();
                    $user->delete();
                    
                    DB::commit();
                    return response()->json([
                        'message' => 'Unable to proceed to registration. Please contact the site admin.',
                        'error' => 'Registration fees for this registration type (delegate) has not been set yet.',
                        'member_type' => $validated['member_type'],
                        'scope' => $validated['scope'],
                        'is_pds' => $validated['is_pds']
                    ], 404);
                }
            } else {
                return response()->json([
                    'message' => 'This account is ineligible for registration.',
                    'user_status' => $user->status
                ]);
            }
        } catch(Exception $e) {
            throw $e;
        }
    }

    public function calculate(RegisterCreate $request) {
        $registration_data = $request->validated();

        $fee = Fee::where([
            ['member_type', $registration_data['member_type']],
            ['scope',$registration_data['scope']]
        ])->with('member_type')
        ->first();
        
        if(is_null($fee)) {
            return response()->json([
                'message' => 'The registration fee for this type has not been set yet.'
            ], 404);
        }

        $order_service = new OrderService(null);
        $member_order = $order_service->calculate($registration_data, $fee);

        return response()->json([
            $member_order
        ], $member_order["code"]);
    }

    public function validateEmail(ValidateEmail $request) {
        $validated = $request->validated();

        $user = User::where([['role', RoleEnum::CONVENTION_MEMBER],['email', $validated["email"]]])->with('member')->first();
        
        if(is_null($user)) {
            return response()->json([
                'message' => 'No user found',
            ]);
        } else {
            if($user->status === UserStatusEnum::REGISTERED){
                return response()->json([
                    'email' => $validated["email"],
                    'message' => 'User was already registered'
                ]);
            }
            return response()->json([
                'user' => $user,
                'email' => $validated["email"],
                'message' => 'An existing user with this email exists'
            ]);
        }
    }

    public function validatePDSNumber(ValidatePDSNumber $request) {
        $validated = $request->validated();

        $user = User::where([['role', RoleEnum::CONVENTION_MEMBER]])
                ->whereHas('member', function ($query) use ($validated) { 
                    $query->where(
                        [
                            ['pds_number', $validated["pds_number"]],
                            ['member_type',$validated["member_type"]],
                            ['is_pds', $validated["is_pds"]]
                        ]
                );
                })
                ->with('member')
                ->first();
        
        if(is_null($user)) {
            return response()->json([
                'message' => 'No user found',
            ]);
        } else {
            if($user->status !== UserStatusEnum::IMPORTED_PENDING){
                return response()->json([
                    'pds_number' => $validated["pds_number"],
                    'message' => 'User was already registered'
                ]);
            }
            return response()->json([
                'user' => $user,
                'pds_number' => $validated["pds_number"],
                'message' => 'An existing user with this PDS ID Number exists'
            ]);
        }
    }
}