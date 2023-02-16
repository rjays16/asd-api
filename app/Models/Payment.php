<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
	use SoftDeletes;

	public $table = 'payments';

	public $fillable = [
        'convention_member_id',
        'order_id',
        'payment_method',
        'amount',
		'intl_amount',
        'date_paid',
	];

	protected $casts = [
        'date_paid' => 'date',
    ];

	protected $appends = [
        'currency_usd',
        'currency_php',
    ];

	public function order() {
		return $this->belongsTo(Order::class, 'order_id');
	}

	public function member() {
		return $this->belongsTo(ConventionMember::class, 'convention_member_id');
	}

	public function method() {
		return $this->belongsTo(PaymentMethod::class, 'payment_method');
	}

	public function getCurrencyUsdAttribute() {
		return "$";
	}

	public function getCurrencyPhpAttribute() {
		return "₱";
	}
}