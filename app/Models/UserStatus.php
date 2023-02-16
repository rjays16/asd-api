<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStatus extends Model
{
    public $table = 'user_status';

	public $fillable = [
		'name',
	];
}