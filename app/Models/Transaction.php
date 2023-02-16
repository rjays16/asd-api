<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
	use SoftDeletes;

	public $table = 'transactions';

	public $fillable = [
		'order_id',
        'amount',
        'intl_amount',
        'ideapay_id',
        'ideapay_fee',
        'intl_ideapay_fee'
	];

	public function order() {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function ideapay() {
        return $this->belongsTo(Ideapay::class, 'ideapay_id');
    }
}