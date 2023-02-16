<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Order;

use App\Enum\OrderStatusEnum;
use App\Enum\UserStatusEnum;
use App\Enum\ConventionMemberTypeEnum;

class ConventionMember extends Model
{
	use SoftDeletes;

	public $table = 'convention_members';

	public $fillable = [
		'user_id',
        'prc_number',
		'pds_number',
        'scope',
        'is_pds',
        'resident_certificate',
        'member_type'
	];

	protected $casts = [
        'scope' => 'boolean',
        'is_pds' => 'boolean'
    ];

	protected $appends = [
		'explicit_type'
	];

	public function user() {
		return $this->belongsTo(User::class, 'user_id');
	}

	public function orders() {
        return $this->hasMany(Order::class, 'convention_member_id');
    }

    public function payments() {
        return $this->hasMany(Payment::class, 'convention_member_id');
    }

    public function type() {
        return $this->belongsTo(ConventionMemberType::class, 'member_type');
    }

    public function scopeASD($query) {
		return $query->where('member_type', [ConventionMemberTypeEnum::ASD_MEMBER]);
	}

    public function scopeNonASD($query) {
		return $query->where('member_type', [ConventionMemberTypeEnum::NON_ASD_MEMBER]);
	}

    public function scopeResidentFellow($query) {
		return $query->where('member_type', [ConventionMemberTypeEnum::RESIDENT_FELLOW]);
	}

    public function scopeInternational($query) {
		return $query->where('scope', true);
	}

    public function scopeLocal($query) {
		return $query->where('scope', false);
	}

    public function scopePDS($query) {
		return $query->where('is_pds', true);
	}

    public function scopeNonPDS($query) {
		return $query->where('is_pds', false);
	}

    public function scopeDelegates($query) {
		return $query->whereIn('member_type', [
            ConventionMemberTypeEnum::ASD_MEMBER,
            ConventionMemberTypeEnum::NON_ASD_MEMBER,
            ConventionMemberTypeEnum::RESIDENT_FELLOW
        ]);
	}

    public function scopeSpeaker($query) {
		return $query->where('member_type', [ConventionMemberTypeEnum::SPEAKER]);
	}

	public function getExplicitTypeAttribute() {
		$scope = $this->scope ? "International" : "Local";

		$type = $this->type;
		$member_type = $type ? $type->name : "";

		$is_pds = $this->is_pds;
		$pds_type = $is_pds ? "(PDS)" : "";

		$explicit_type = join(' ', 
			array_filter(
				array($scope, $member_type, $pds_type)
			)
		);

		return $explicit_type;
	}
}