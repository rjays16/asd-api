<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Config;

use App\Enum\RoleEnum;
use App\Enum\ConfigTypeEnum;

class RegistrationConfigService {
    private $role;
    private $password;
    private $confirm_password;
    protected $code = 200;

    public function __construct($role, $password, $confirm_password) {
        $this->role = $role;
        $this->password = $password;
        $this->confirm_password = $confirm_password;
    }

    public function checkRegisterableStatus() {
        $data = array();
        $data["is_registration_enabled"] = false;
        $data["is_registration_allowed"] = false;
        $data["message"] = "Something went wrong, please contact the site admin.";
        $data["code"] = $this->code;

        $switch = Config::where('type', ConfigTypeEnum::REGISTRATION_SWITCH)->first();
        if(!is_null($switch)) {
            $data["is_registration_enabled"] = $switch->value == "Yes" ? true : false;
        }

        if(!$data["is_registration_enabled"]) {
            $data["message"] = 'Registration is disabled as of the moment.';
            $data["code"] = 400;
        } else {
            $registerable_role = Role::where('id', RoleEnum::CONVENTION_MEMBER)->first();
            if(is_null($registerable_role)) {
                $data["message"] = 'Registration roles have not been set yet.';
                $data["code"] = 404;
            } else {
                $member_roles = [RoleEnum::CONVENTION_MEMBER, (string) RoleEnum::CONVENTION_MEMBER];
                if(!in_array($this->role, $member_roles)) {
                    $data["message"] = 'Your registration role is invalid.';
                    $data["code"] = 400;
                } else if($this->password !== $this->confirm_password) {
                    $data["message"] = 'Your password and confirmation password should match.';
                    $data["code"] = 400;
                }
            }
        }

        $data["is_registration_allowed"] = $data["code"] == 200 ? true : false;

        return $data;
    }
}