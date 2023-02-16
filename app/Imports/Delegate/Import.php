<?php

namespace App\Imports\Delegate;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

use App\Models\User;
use App\Models\ConventionMember;
use App\Enum\RoleEnum;
use App\Enum\UserStatusEnum;
use App\Enum\RegistrationTypeEnum;

use DB;

class Import implements ToModel, WithStartRow, WithCalculatedFormulas
{
    /**
    * @param Collection $collection
    */

    private $rejected_emails = [];
    private $num_imported = 0;
    protected $member_type;

    public function __construct(int $member_type = null) {
        $this->member_type = $member_type;
    }

    // Starts on the 2nd row so that column names are excluded
    public function startRow():int {
        return 3;
    }

    public function getRejected() {
        return $this->rejected_emails;
    }

    public function getNumImported() {
        return $this->num_imported;
    }

    public function model(array $row) {
        if(!$row[0]) { // skip rows with no IDs - in the first index of the row
            return null;
        }
        
        $last_name = $row[1];
        $first_name = $row[2];
        $middle_name = $row[3];
        $certificate_name = $row[4];
        $email = $row[10];

        $other_user = null;
        $other_user_query = User::where('role', RoleEnum::CONVENTION_MEMBER)
            ->whereHas('member', function ($query) { 
                $query->where('type', $this->member_type);
            });

        if($email) {
            $other_user = User::where('email', $email)->first();
            if(!is_null($other_user)) {
                array_push($this->rejected_emails, $email);
            }
        } else {
            $other_user = $other_user_query->where('first_name', $first_name)
                ->where('last_name', $last_name)
                ->first();
        }

        if(!is_null($other_user)) {
            return null;
        }

        DB::beginTransaction();
        try {
            $user = new User();
            $user->first_name = $first_name;
            $user->middle_name = $middle_name ?? NULL;
            $user->last_name = $last_name;
            $user->certificate_name = $certificate_name;
            $user->email = $email ?? NULL;
            $user->password = Hash::make(config('settings.DEFAULT_MEMBER_PASSWORD'));
            $user->role = RoleEnum::CONVENTION_MEMBER;
            $user->status = UserStatusEnum::IMPORTED_PENDING;
            $user->save();

            // Create the delegate account
            $member = ConventionMember::create([
                'user_id' => $user->id,
                'member_type' => $this->member_type, # Put the member_type of the delegate here based on the selected registration type
                'scope' => true
            ]);
            
            DB::commit();

            if($member) {
                $this->num_imported += 1;
            }
        } catch(Exception $e) {
            DB::rollback();
            return null;
        }
    }
}