<?php

namespace App\Services;

use App\Models\Fee;

use App\Enum\FeeEnum;
use App\Enum\ConventionMemberTypeEnum;

use AmrShawky\LaravelCurrency\Facade\Currency;

class FeeService {
    private $member_type;
    private $scope;
    protected $code = 200;

    public function __construct($member_type, $scope){
        $this->member_type = $member_type;
        $this->scope = $scope;
    }

    public function getRegistrationFee() {
        $data = array();
        $data["fee"] = Fee::where([['member_type', $this->member_type],['scope', $this->scope]])->first();
        $data["code"] = $this->code;

        if(is_null($data["fee"])) {
            $data["message"] = "Registration fee for this type has not been set yet.";
            $data["member_type"] = $this->member_type;
            $data["scope"] = $this->scope;
            $data["code"] = 404;
        }
        
        return $data;
    }
}