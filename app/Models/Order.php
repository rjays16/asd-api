<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
	use SoftDeletes;

	public $table = 'orders';

	public $fillable = [
		'fee_id',
        'convention_member_id',
        'amount',
        'status',
	];

	protected $appends = [
        'order_payments',
        'raw_order_payments',
        'raw_order_payments_value',
        'currency_usd',
        'currency_php',
    ];

	public function member() {
		return $this->belongsTo(ConventionMember::class, 'convention_member_id');
	}

    public function transaction() {
		return $this->hasOne(Transaction::class, 'order_id');
	}

    public function fee() {
        return $this->belongsTo(Fee::class, 'fee_id');
    }

    public function payment() {
		return $this->hasOne(Payment::class, 'order_id');
	}

    public function payments() {
		return $this->hasMany(Payment::class, 'order_id');
	}

    public function status_class() {
        return $this->belongsTo(OrderStatus::class, 'status');
    }

	public function getOrderPaymentsAttribute() {
        $payment_total = 0;
        $order_payments = $this->payment;
        if(!empty($order_payments)) {
            $payment_total = $order_payments->sum('amount');

            if($payment_total > $this->fee->amount) {
                $payment_total = number_format($this->fee->amount, 2);
            }
        }

        return $payment_total;
    }

    public function getRawOrderPaymentsAttribute() {
        $payment_total = 0;
        $order_payments = $this->payment;
        if(!empty($order_payments)) {
            $payment_total = $order_payments->sum('amount');
        }

        return number_format($payment_total, 2);
    }

    public function getRawOrderPaymentsValueAttribute() {
        $raw_order_payments_value = 0;
        $order_payments = $this->payment;
        if(!empty($order_payments)) {
            $raw_order_payments_value = $order_payments->sum('amount');
        }

        return $raw_order_payments_value;
    }

    public function getCurrencyUsdAttribute() {
		return "USD";
	}

	public function getCurrencyPhpAttribute() {
		return "Php.";
	}
}