<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;

use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Notifications\Notifiable;

use Laravel\Lumen\Auth\Authorizable;
use Laravel\Passport\HasApiTokens;

use App\Enum\RoleEnum;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use HasApiTokens, Authenticatable, Authorizable, SoftDeletes, CanResetPassword, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'password',

        'first_name',
        'middle_name',
        'last_name',

        'suffix',
        'prof_suffix',

        'certificate_name',

        'country',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'full_name',
    ];

    public function user_role() {
        return $this->belongsTo(Role::class, 'role');
    }

    public function user_status() {
        return $this->belongsTo(UserStatus::class, 'status');
    }

    public function member() {
        return $this->hasOne(ConventionMember::class, 'user_id');
    }

    public function scopeAdmin($query) {
		return $query->whereIn('role', [RoleEnum::ADMIN, RoleEnum::SUPER_ADMIN]);
	}

    public function admin_capability() {
        return $this->hasOne(AdminCapability::class, 'user_id');
    }

    public function scopeMember($query) {
		return $query->where('role', [RoleEnum::CONVENTION_MEMBER]);
	}

    public function getFullNameAttribute() {
        return join(' ', array_filter(array($this->first_name, $this->middle_name, $this->last_name)));
    }
}
