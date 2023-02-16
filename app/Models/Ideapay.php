<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ideapay extends Model
{
	use SoftDeletes;

	public $table = 'ideapay';

	public $fillable = [
        'transaction_id',
		'payment_ref',
        'payment_url',
        'status',
	];

	public function ideapay_status() {
        return $this->belongsTo(IdeapayStatus::class, 'status');
    }

    public function transaction() {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}