<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fee extends Model
{
	use SoftDeletes;

	public $table = 'fees';

	public $fillable = [
		'name',
        'description',
        'year',
        'is_pds',
        'scope', # if true, it is global (USD). If false, it is local (PHP)
        'amount', # Before August 16
        'uses_late_amount',
        'late_amount', # From August 16 onwards
        'late_amount_starts_on',
        'member_type'
	];

    protected $casts = [
        'is_pds' => 'boolean',
        'scope' => 'boolean',
        'uses_late_amount' => 'boolean',
        'year' => 'string'
    ];

    public function member_type() {
        return $this->belongsTo(ConventionMemberType::class, 'member_type');
    }
}